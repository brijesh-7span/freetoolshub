<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Contact form — [fth_contact_form]
 *
 * Emails messages to the site admin address (Settings → General), changeable with
 * the `fth_contact_recipient` filter. Spam protection: nonce, hidden honeypot field,
 * minimum fill time and a per-visitor rate limit. Nothing is stored in the database.
 *
 * On a live server, install an SMTP plugin (e.g. WP Mail SMTP) so emails are delivered.
 */
class FTH_Contact_Form
{

    const SHORTCODE    = 'fth_contact_form';
    const MIN_SECONDS  = 3;   // faster submissions are treated as bots
    const RATE_LIMIT   = 3;   // messages per visitor…
    const RATE_WINDOW  = 600; // …per 10 minutes

    /**
     * Errors and submitted values from a failed submission, for re-display.
     */
    private $errors = array();
    private $values = array();

    public function __construct()
    {

        add_shortcode(self::SHORTCODE, array($this, 'render'));
        add_action('template_redirect', array($this, 'handle_submission'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
    }


    /**
     * Subject options (value => label)
     */
    private function subjects()
    {

        return array(
            'general'    => __('General question', 'freetoolshub-tools'),
            'suggestion' => __('Suggest a new tool', 'freetoolshub-tools'),
            'bug'        => __('Report a problem with a tool', 'freetoolshub-tools'),
            'business'   => __('Business or partnership', 'freetoolshub-tools'),
            'other'      => __('Something else', 'freetoolshub-tools'),
        );
    }


    /**
     * Load the form styles in <head> on pages that contain the form
     */
    public function enqueue_styles()
    {

        wp_register_style(
            'fth-contact',
            FTH_PLUGIN_URL . 'assets/css/fth-contact.css',
            array(),
            fth_plugin_asset_version('assets/css/fth-contact.css')
        );

        if (is_singular() && has_shortcode(get_post_field('post_content', get_queried_object_id()), self::SHORTCODE)) {
            wp_enqueue_style('fth-contact');
        }
    }


    /**
     * Validate and send a submitted message
     */
    public function handle_submission()
    {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['fth_contact_nonce'])) {
            return;
        }

        $this->values = array(
            'name'    => sanitize_text_field(wp_unslash($_POST['fth_name'] ?? '')),
            'email'   => sanitize_email(wp_unslash($_POST['fth_email'] ?? '')),
            'subject' => sanitize_key(wp_unslash($_POST['fth_subject'] ?? '')),
            'message' => sanitize_textarea_field(wp_unslash($_POST['fth_message'] ?? '')),
        );

        if (!wp_verify_nonce(sanitize_key(wp_unslash($_POST['fth_contact_nonce'])), 'fth_contact')) {
            $this->errors['form'] = __('Your session expired. Please send the form again.', 'freetoolshub-tools');
            return;
        }

        // Bots fill every field, or submit instantly. Pretend it worked so they don't retry.
        $started = (int) ($_POST['fth_started'] ?? 0);

        if (!empty($_POST['fth_website']) || time() - $started < self::MIN_SECONDS) {
            $this->redirect_sent();
        }

        if ($this->values['name'] === '' || mb_strlen($this->values['name']) > 100) {
            $this->errors['name'] = __('Please enter your name.', 'freetoolshub-tools');
        }

        if (!is_email($this->values['email'])) {
            $this->errors['email'] = __('Please enter a valid email address.', 'freetoolshub-tools');
        }

        if (!isset($this->subjects()[$this->values['subject']])) {
            $this->values['subject'] = 'general';
        }

        $length = mb_strlen($this->values['message']);

        if ($length < 10 || $length > 5000) {
            $this->errors['message'] = __('Please write a message between 10 and 5,000 characters.', 'freetoolshub-tools');
        }

        if ($this->errors) {
            return;
        }

        $rate_key = 'fth_contact_' . md5($this->visitor_ip());
        $sent     = (int) get_transient($rate_key);

        if ($sent >= self::RATE_LIMIT) {
            $this->errors['form'] = __('You\'ve sent several messages in a short time. Please wait a few minutes and try again.', 'freetoolshub-tools');
            return;
        }

        if (!$this->send_email()) {
            $this->errors['form'] = __('Sorry, your message couldn\'t be sent right now. Please try again later.', 'freetoolshub-tools');
            return;
        }

        set_transient($rate_key, $sent + 1, self::RATE_WINDOW);

        $this->redirect_sent();
    }


    private function send_email()
    {

        $site    = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
        $subject = $this->subjects()[$this->values['subject']];

        $body = implode("\n", array(
            __('Name:', 'freetoolshub-tools') . ' ' . $this->values['name'],
            __('Email:', 'freetoolshub-tools') . ' ' . $this->values['email'],
            __('Subject:', 'freetoolshub-tools') . ' ' . $subject,
            '',
            $this->values['message'],
            '',
            '—',
            /* translators: %s: page URL */
            sprintf(__('Sent from the contact form at %s', 'freetoolshub-tools'), get_permalink()),
        ));

        return wp_mail(
            apply_filters('fth_contact_recipient', get_option('admin_email')),
            sprintf('[%s] %s — %s', $site, $subject, $this->values['name']),
            $body,
            array('Reply-To: ' . $this->values['name'] . ' <' . $this->values['email'] . '>')
        );
    }


    private function visitor_ip()
    {

        return isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
    }


    /**
     * Post/Redirect/Get, so refreshing the page doesn't send the message twice
     */
    private function redirect_sent()
    {

        wp_safe_redirect(add_query_arg('contact', 'sent', get_permalink()) . '#contact-form');
        exit;
    }


    /**
     * Shortcode output
     */
    public function render()
    {

        $id = wp_unique_id('fth-contact-');

        ob_start();

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only
        if (isset($_GET['contact']) && $_GET['contact'] === 'sent') :
?>
            <div class="fth-contact-success" id="contact-form" role="status" tabindex="-1">
                <strong><?php esc_html_e('Thanks — your message has been sent!', 'freetoolshub-tools'); ?></strong>
                <?php esc_html_e('We usually reply within 2 working days.', 'freetoolshub-tools'); ?>
            </div>
        <?php
            return ob_get_clean();
        endif;

        $field = function ($name) {
            return isset($this->values[$name]) ? $this->values[$name] : '';
        };

        $error = function ($name) use ($id) {
            if (!empty($this->errors[$name])) {
                printf('<span class="fth-contact-error" id="%s">%s</span>', esc_attr($id . '-' . $name . '-error'), esc_html($this->errors[$name]));
            }
        };

        $invalid = function ($name) use ($id) {
            if (!empty($this->errors[$name])) {
                printf(' aria-invalid="true" aria-describedby="%s"', esc_attr($id . '-' . $name . '-error'));
            }
        };

        $privacy_url = get_privacy_policy_url();
        ?>

        <form class="fth-contact-form" id="contact-form" method="post" action="<?php echo esc_url(get_permalink() . '#contact-form'); ?>">

            <?php if (!empty($this->errors['form'])) : ?>
                <div class="fth-contact-alert" role="alert"><?php echo esc_html($this->errors['form']); ?></div>
            <?php elseif ($this->errors) : ?>
                <div class="fth-contact-alert" role="alert"><?php esc_html_e('Please fix the highlighted fields and try again.', 'freetoolshub-tools'); ?></div>
            <?php endif; ?>

            <div class="fth-contact-row">
                <p class="fth-contact-field">
                    <label for="<?php echo esc_attr($id . '-name'); ?>"><?php esc_html_e('Your name', 'freetoolshub-tools'); ?></label>
                    <input type="text" id="<?php echo esc_attr($id . '-name'); ?>" name="fth_name" value="<?php echo esc_attr($field('name')); ?>" maxlength="100" autocomplete="name" required<?php $invalid('name'); ?>>
                    <?php $error('name'); ?>
                </p>

                <p class="fth-contact-field">
                    <label for="<?php echo esc_attr($id . '-email'); ?>"><?php esc_html_e('Your email', 'freetoolshub-tools'); ?></label>
                    <input type="email" id="<?php echo esc_attr($id . '-email'); ?>" name="fth_email" value="<?php echo esc_attr($field('email')); ?>" maxlength="200" autocomplete="email" required<?php $invalid('email'); ?>>
                    <?php $error('email'); ?>
                </p>
            </div>

            <p class="fth-contact-field">
                <label for="<?php echo esc_attr($id . '-subject'); ?>"><?php esc_html_e('What is it about?', 'freetoolshub-tools'); ?></label>
                <select id="<?php echo esc_attr($id . '-subject'); ?>" name="fth_subject">
                    <?php foreach ($this->subjects() as $value => $label) : ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($field('subject'), $value); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p class="fth-contact-field">
                <label for="<?php echo esc_attr($id . '-message'); ?>"><?php esc_html_e('Message', 'freetoolshub-tools'); ?></label>
                <textarea id="<?php echo esc_attr($id . '-message'); ?>" name="fth_message" rows="7" minlength="10" maxlength="5000" required<?php $invalid('message'); ?>><?php echo esc_textarea($field('message')); ?></textarea>
                <?php $error('message'); ?>
            </p>

            <!-- Honeypot: hidden from people, filled in by spam bots -->
            <p class="fth-contact-hp" aria-hidden="true">
                <label for="<?php echo esc_attr($id . '-website'); ?>">Website</label>
                <input type="text" id="<?php echo esc_attr($id . '-website'); ?>" name="fth_website" tabindex="-1" autocomplete="off">
            </p>

            <input type="hidden" name="fth_started" value="<?php echo esc_attr(time()); ?>">
            <?php wp_nonce_field('fth_contact', 'fth_contact_nonce', false); ?>

            <p class="fth-contact-privacy">
                <?php
                if ($privacy_url) {
                    printf(
                        /* translators: %s: link to the privacy policy */
                        esc_html__('We only use your details to reply to you. See our %s.', 'freetoolshub-tools'),
                        '<a href="' . esc_url($privacy_url) . '">' . esc_html__('Privacy Policy', 'freetoolshub-tools') . '</a>'
                    );
                } else {
                    esc_html_e('We only use your details to reply to you.', 'freetoolshub-tools');
                }
                ?>
            </p>

            <button type="submit" class="fth-contact-submit"><?php esc_html_e('Send message', 'freetoolshub-tools'); ?></button>

        </form>

<?php
        return ob_get_clean();
    }
}
