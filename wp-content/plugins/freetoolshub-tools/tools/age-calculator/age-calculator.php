<?php

/**
 * Age Calculator — markup for the [fth_age_calculator] shortcode
 *
 * @var string $fth_id Unique ID prefix for this instance.
 */

if (!defined('ABSPATH')) {
    exit;
}

$fth_details = array(
    'total-months'  => __('Total months', 'freetoolshub-tools'),
    'total-weeks'   => __('Total weeks', 'freetoolshub-tools'),
    'total-days'    => __('Total days', 'freetoolshub-tools'),
    'next-birthday' => __('Next birthday', 'freetoolshub-tools'),
    'born-on'       => __('Born on', 'freetoolshub-tools'),
);

?>

<div class="fth-tool fth-age-calculator" data-fth-age-calculator>

    <!-- Inputs -->
    <div class="fth-age-inputs">

        <div class="fth-age-field">
            <label for="<?php echo esc_attr($fth_id . '-dob'); ?>" class="fth-tool-label">
                <?php esc_html_e('Date of birth', 'freetoolshub-tools'); ?>
            </label>
            <input type="date" id="<?php echo esc_attr($fth_id . '-dob'); ?>" class="fth-age-input" min="1900-01-01" data-age-dob>
        </div>

        <div class="fth-age-field">
            <div class="fth-age-label-row">
                <label for="<?php echo esc_attr($fth_id . '-target'); ?>" class="fth-tool-label">
                    <?php esc_html_e('Age on this date', 'freetoolshub-tools'); ?>
                </label>
                <button type="button" class="fth-age-link-btn" data-age-today>
                    <?php esc_html_e('Use today', 'freetoolshub-tools'); ?>
                </button>
            </div>
            <input type="date" id="<?php echo esc_attr($fth_id . '-target'); ?>" class="fth-age-input" min="1900-01-01" data-age-target>
        </div>

    </div>

    <span class="fth-tool-message is-error fth-age-message" data-age-message role="alert"></span>


    <!-- Placeholder -->
    <div class="fth-age-placeholder" data-age-placeholder>
        <span aria-hidden="true">🎂</span>
        <?php esc_html_e('Enter your date of birth to see your exact age.', 'freetoolshub-tools'); ?>
    </div>


    <!-- Results -->
    <div class="fth-age-results" data-age-results role="status" aria-live="polite" hidden>

        <div class="fth-age-main">
            <div class="fth-age-unit">
                <span class="fth-age-unit-value" data-age-years>0</span>
                <span class="fth-age-unit-label"><?php esc_html_e('Years', 'freetoolshub-tools'); ?></span>
            </div>
            <div class="fth-age-unit">
                <span class="fth-age-unit-value" data-age-months>0</span>
                <span class="fth-age-unit-label"><?php esc_html_e('Months', 'freetoolshub-tools'); ?></span>
            </div>
            <div class="fth-age-unit">
                <span class="fth-age-unit-value" data-age-days>0</span>
                <span class="fth-age-unit-label"><?php esc_html_e('Days', 'freetoolshub-tools'); ?></span>
            </div>
        </div>

        <p class="fth-age-summary" data-age-summary></p>

        <dl class="fth-age-details">
            <?php foreach ($fth_details as $fth_key => $fth_label) : ?>
                <div class="fth-age-detail">
                    <dt><?php echo esc_html($fth_label); ?></dt>
                    <dd data-age-<?php echo esc_attr($fth_key); ?>></dd>
                </div>
            <?php endforeach; ?>
        </dl>

    </div>


    <div class="fth-tool-actions">
        <button type="button" class="fth-btn fth-btn-secondary" data-age-reset disabled>
            <?php esc_html_e('Reset', 'freetoolshub-tools'); ?>
        </button>
    </div>

    <p class="fth-tool-note">
        <?php esc_html_e('Your age is calculated in your browser — your date of birth is never sent to our server. People born on 29 February celebrate on 28 February in common years.', 'freetoolshub-tools'); ?>
    </p>

</div>
