<?php

if (!defined('ABSPATH')) {
    exit;
}

class FTH_Plugin
{

    private static $instance = null;

    /**
     * Singleton instance
     */
    public static function instance()
    {

        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {

        $this->load_dependencies();

        add_action('plugins_loaded', array($this, 'init'));
    }

    /**
     * Load required files
     */
    private function load_dependencies()
    {

        require_once FTH_PLUGIN_PATH . 'includes/class-fth-post-types.php';

        require_once FTH_PLUGIN_PATH . 'includes/class-fth-shortcodes.php';

        require_once FTH_PLUGIN_PATH . 'includes/class-fth-meta-boxes.php';

        require_once FTH_PLUGIN_PATH . 'includes/class-fth-tool-content.php';

        require_once FTH_PLUGIN_PATH . 'includes/class-fth-seo.php';

        require_once FTH_PLUGIN_PATH . 'includes/class-fth-hardening.php';

        require_once FTH_PLUGIN_PATH . 'includes/class-fth-contact-form.php';
    }

    /**
     * Initialize plugin functionality
     */
    public function init()
    {

        new FTH_Post_Types();

        new FTH_Shortcodes();

        new FTH_Meta_Boxes();

        new FTH_SEO();

        new FTH_Hardening();

        new FTH_Contact_Form();
    }
}
