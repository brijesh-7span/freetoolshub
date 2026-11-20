<?php

/**
 * Plugin Name: FreeToolsHub Tools
 * Plugin URI: http://localhost/freetoolshub
 * Description: Custom online tools functionality for FreeToolsHub.
 * Version: 1.1.0
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Author: FreeToolsHub
 * Text Domain: freetoolshub-tools
 * Update URI: false
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('FTH_PLUGIN_VERSION', '1.1.0');

define(
    'FTH_PLUGIN_PATH',
    plugin_dir_path(__FILE__)
);

define(
    'FTH_PLUGIN_URL',
    plugin_dir_url(__FILE__)
);

/**
 * Asset version = file modification time, so browsers can cache plugin assets
 * for a long time yet always fetch the new file after it changes.
 */
function fth_plugin_asset_version($relative_path)
{

    $file = FTH_PLUGIN_PATH . ltrim($relative_path, '/');

    return file_exists($file) ? (string) filemtime($file) : FTH_PLUGIN_VERSION;
}

// Block theme/plugin file editing in wp-admin (a common way attackers plant code
// after stealing an admin login). Set it in wp-config.php instead to override.
if (!defined('DISALLOW_FILE_EDIT')) {
    define('DISALLOW_FILE_EDIT', true);
}

// Load plugin classes
require_once FTH_PLUGIN_PATH . 'includes/class-fth-plugin.php';

// Initialize plugin
FTH_Plugin::instance();
