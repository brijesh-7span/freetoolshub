<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Security hardening and front-end performance clean-up.
 *
 * Kept in the plugin (not the theme) so it stays active if the theme changes.
 * Each feature can be switched off with the `fth_hardening_features` filter, e.g.
 *
 *     add_filter('fth_hardening_features', function ($features) {
 *         $features['disable_xmlrpc'] = false; // needed for Jetpack / mobile app
 *         return $features;
 *     });
 */
class FTH_Hardening
{

    private $features;

    public function __construct()
    {

        $this->features = apply_filters('fth_hardening_features', array(
            'disable_xmlrpc'         => true,
            'hide_version'           => true,
            'block_user_enumeration' => true,
            'generic_login_errors'   => true,
            'security_headers'       => true,
            'disable_emojis'         => true,
            'webp_uploads'           => true,
        ));

        if ($this->features['disable_xmlrpc']) {
            $this->disable_xmlrpc();
        }

        if ($this->features['hide_version']) {
            remove_action('wp_head', 'wp_generator');
            add_filter('the_generator', '__return_empty_string');
        }

        if ($this->features['block_user_enumeration']) {
            // Priority 1: before WordPress's redirect_canonical(), which would otherwise
            // redirect ?author=1 to /author/{username}/ and reveal the login name.
            add_action('template_redirect', array($this, 'block_author_scans'), 1);
            add_filter('rest_endpoints', array($this, 'hide_rest_users'));
        }

        if ($this->features['generic_login_errors']) {
            add_filter('login_errors', array($this, 'generic_login_error'));
        }

        if ($this->features['security_headers']) {
            add_action('send_headers', array($this, 'send_security_headers'));
        }

        if ($this->features['disable_emojis']) {
            $this->disable_emojis();
        }

        if ($this->features['webp_uploads']) {
            add_filter('image_editor_output_format', array($this, 'webp_output_format'));
        }
    }


    /* --------------------------------------------------------------------
     * Security
     * ----------------------------------------------------------------- */

    /**
     * XML-RPC is a common target for password-guessing attacks and isn't used by this site.
     */
    private function disable_xmlrpc()
    {

        add_filter('xmlrpc_enabled', '__return_false');
        remove_action('wp_head', 'rsd_link');

        // Remove the X-Pingback header that advertises xmlrpc.php.
        add_filter('wp_headers', function ($headers) {
            unset($headers['X-Pingback']);
            return $headers;
        });
    }

    /**
     * ?author=1 and /author/name/ reveal login names. This site has no author pages,
     * so send those requests to the homepage.
     */
    public function block_author_scans()
    {

        if (is_user_logged_in()) {
            return;
        }

        if (is_author() || isset($_GET['author'])) {
            wp_safe_redirect(home_url('/'), 301);
            exit;
        }
    }

    /**
     * Hide the public REST list of users (/wp-json/wp/v2/users) from visitors who aren't logged in.
     */
    public function hide_rest_users($endpoints)
    {

        if (is_user_logged_in()) {
            return $endpoints;
        }

        foreach (array_keys($endpoints) as $route) {
            if (strpos($route, '/wp/v2/users') === 0) {
                unset($endpoints[$route]);
            }
        }

        return $endpoints;
    }

    /**
     * Don't tell attackers whether the username or the password was wrong.
     */
    public function generic_login_error()
    {

        return __('<strong>Error:</strong> The username or password is incorrect.', 'freetoolshub-tools');
    }

    /**
     * Browser security headers for every page WordPress serves.
     * (Static files get theirs from .htaccess.)
     */
    public function send_security_headers()
    {

        if (headers_sent()) {
            return;
        }

        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=()');

        // Only over HTTPS — sending HSTS on plain HTTP has no effect and could lock out a site without SSL.
        if (is_ssl()) {
            header('Strict-Transport-Security: max-age=31536000');
        }
    }


    /* --------------------------------------------------------------------
     * Performance
     * ----------------------------------------------------------------- */

    /**
     * WordPress loads an emoji script + styles on every page to support very old
     * browsers. All current browsers and operating systems display emoji natively.
     */
    private function disable_emojis()
    {

        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('wp_enqueue_scripts', 'wp_enqueue_emoji_styles');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

        add_filter('emoji_svg_url', '__return_false');

        // Admin emoji hooks are registered in admin-filters.php, which loads after
        // plugins — so they can only be removed once admin_init fires.
        add_action('admin_init', array($this, 'disable_admin_emojis'));
    }

    /**
     * Without this the admin emoji script swaps every emoji (e.g. the tool icons
     * in the Tools list) for an image from s.w.org, which shows broken offline.
     */
    public function disable_admin_emojis()
    {

        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_action('admin_enqueue_scripts', 'wp_enqueue_emoji_styles');
    }

    /**
     * Save resized versions of uploaded JPEG photos as WebP (typically 25–35% smaller).
     * The original upload is kept. PNGs stay PNG to keep logos and screenshots crisp.
     * Skipped automatically if the server's image library can't write WebP.
     */
    public function webp_output_format($formats)
    {

        static $supported = null;
        static $checking  = false;

        // wp_image_editor_supports() can apply this same filter — guard against recursion.
        if ($checking) {
            return $formats;
        }

        if ($supported === null) {
            $checking  = true;
            $supported = wp_image_editor_supports(array('mime_type' => 'image/webp'));
            $checking  = false;
        }

        if ($supported) {
            $formats['image/jpeg'] = 'image/webp';
        }

        return $formats;
    }
}
