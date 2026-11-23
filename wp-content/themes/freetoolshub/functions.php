<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme setup
 */
function fth_theme_setup()
{

    // Dynamic document title
    add_theme_support('title-tag');

    // Featured images
    add_theme_support('post-thumbnails');

    // Custom logo
    add_theme_support('custom-logo');

    // Page excerpts are shown as the intro under the page title (page.php)
    add_post_type_support('page', 'excerpt');

    // HTML5 support
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script'
    ));

    // Navigation menus
    register_nav_menus(array(
        'primary-menu' => __('Primary Menu', 'freetoolshub'),
        'footer-menu'  => __('Footer Menu', 'freetoolshub'),
    ));
}

add_action('after_setup_theme', 'fth_theme_setup');


/**
 * Asset version = file modification time, so browsers can cache assets for a
 * long time yet always fetch the new file after it changes.
 */
function fth_asset_version($relative_path)
{

    $file = get_template_directory() . '/' . ltrim($relative_path, '/');

    return file_exists($file) ? (string) filemtime($file) : wp_get_theme()->get('Version');
}


/**
 * Pages built only from theme templates (no block-editor content), which
 * therefore don't need WordPress's block library CSS.
 */
function fth_is_template_only_page()
{

    return is_front_page()
        || is_singular('tool')
        || is_post_type_archive('tool')
        || is_tax('tool_category');
}


/**
 * Enqueue theme styles
 *
 * style.css is not enqueued: it only holds the theme header comment.
 * Fonts are self-hosted via @font-face in main.css (no Google Fonts request).
 */
function fth_enqueue_assets()
{

    wp_enqueue_style(
        'fth-custom-style',
        get_template_directory_uri() . '/assets/css/main.css',
        array(),
        fth_asset_version('assets/css/main.css')
    );

    if (fth_is_template_only_page()) {
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        wp_dequeue_style('classic-theme-styles');
        wp_dequeue_style('global-styles');
    }

    // Tools directory: instant search + category filters
    if (is_post_type_archive('tool') || is_tax('tool_category')) {

        wp_enqueue_script(
            'fth-tools-directory',
            get_template_directory_uri() . '/assets/js/tools-directory.js',
            array(),
            fth_asset_version('assets/js/tools-directory.js'),
            array('in_footer' => true, 'strategy' => 'defer')
        );

        wp_localize_script('fth-tools-directory', 'fthDirectory', array(
            /* translators: %d: number of tools */
            'all'             => array(__('Showing %d tool', 'freetoolshub'), __('Showing all %d tools', 'freetoolshub')),
            /* translators: 1: number of matching tools, 2: total number of tools */
            'some'            => array(__('Showing %1$d of %2$d tool', 'freetoolshub'), __('Showing %1$d of %2$d tools', 'freetoolshub')),
            /* translators: %s: the visitor's search text */
            'noMatchQuery'    => __('No tools match “%s”. Try a different word or browse all categories.', 'freetoolshub'),
            'noMatchCategory' => __('No tools match your search. Try a different word or browse all categories.', 'freetoolshub'),
        ));
    }
}

add_action('wp_enqueue_scripts', 'fth_enqueue_assets', 20);


/**
 * Global styles (~9 KB of block-editor CSS variables) are printed outside the
 * normal style queue, so they can't simply be dequeued. Unhook them on pages
 * that don't use block content — this theme doesn't use those presets.
 */
function fth_skip_global_styles()
{

    if (fth_is_template_only_page()) {
        remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
        remove_action('wp_footer', 'wp_enqueue_global_styles', 1);
        remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
    }
}

add_action('template_redirect', 'fth_skip_global_styles');


/**
 * Preload the two fonts visible on first paint (headings + body text),
 * so text renders in the right font without a visible swap.
 */
function fth_preload_fonts()
{

    $fonts = array(
        'assets/fonts/momo-trust-display-400-latin.woff2',
        'assets/fonts/fira-sans-400-latin.woff2',
    );

    foreach ($fonts as $font) {
        printf(
            '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
            esc_url(get_template_directory_uri() . '/' . $font)
        );
    }
}

add_action('wp_head', 'fth_preload_fonts', 2);


/**
 * Tools: admin columns and template helpers (post type + meta box live in the FreeToolsHub Tools plugin)
 */
require_once get_template_directory() . '/inc/tool-admin.php';
require_once get_template_directory() . '/inc/template-functions.php';
