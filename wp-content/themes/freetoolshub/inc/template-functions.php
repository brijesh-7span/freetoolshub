<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get a tool's emoji icon (falls back to a default)
 */
function fth_get_tool_icon($post_id = null)
{

    $post_id = $post_id ? $post_id : get_the_ID();
    $icon    = get_post_meta($post_id, '_fth_tool_icon', true);

    return $icon !== '' ? $icon : '🛠️';
}


/**
 * Get a tool category's emoji icon (falls back to a default)
 */
function fth_get_category_icon($term_id)
{

    $icon = get_term_meta($term_id, 'fth_category_icon', true);

    return $icon !== '' ? $icon : '📁';
}


/**
 * Get tools for the homepage "Popular Free Tools" section.
 * Uses tools marked as popular; falls back to the latest tools.
 */
function fth_get_popular_tools($limit = 6)
{

    $args = array(
        'post_type'           => 'tool',
        'post_status'         => 'publish',
        'posts_per_page'      => $limit,
        'orderby'             => array('menu_order' => 'ASC', 'date' => 'DESC'),
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    );

    $popular = new WP_Query(array_merge($args, array(
        'meta_key'   => '_fth_is_featured',
        'meta_value' => '1',
    )));

    if ($popular->have_posts()) {
        return $popular;
    }

    return new WP_Query($args);
}


/**
 * Normalize text for directory search: lowercase, accents removed, single spaces.
 * Mirrors normalize() in assets/js/tools-directory.js so both match the same way.
 */
function fth_normalize_search_text($text)
{

    $text = remove_accents(wp_strip_all_tags($text));
    $text = function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);

    return trim(preg_replace('/\s+/u', ' ', $text));
}


/**
 * Whether a tool matches the directory search + category filter.
 * Every word of the search must appear somewhere in the tool's search text.
 */
function fth_tool_matches($search_text, $category_slugs, $query, $category)
{

    if ($category !== '' && !in_array($category, $category_slugs, true)) {
        return false;
    }

    foreach (array_filter(explode(' ', fth_normalize_search_text($query))) as $word) {
        if (strpos($search_text, $word) === false) {
            return false;
        }
    }

    return true;
}


/**
 * Directory results summary, e.g. "Showing all 8 tools" / "Showing 3 of 8 tools".
 */
function fth_directory_status_text($visible, $total)
{

    if ($visible === $total) {
        /* translators: %d: number of tools */
        return sprintf(_n('Showing %d tool', 'Showing all %d tools', $total, 'freetoolshub'), $total);
    }

    /* translators: 1: number of matching tools, 2: total number of tools */
    return sprintf(_n('Showing %1$d of %2$d tool', 'Showing %1$d of %2$d tools', $total, 'freetoolshub'), $visible, $total);
}


/**
 * Estimated reading time for a post, e.g. "4 min read" (at ~220 words per minute).
 */
function fth_reading_time($post = null)
{

    $words   = str_word_count(wp_strip_all_tags(get_post_field('post_content', $post)));
    $minutes = max(1, (int) ceil($words / 220));

    /* translators: %d: number of minutes */
    return sprintf(_n('%d min read', '%d min read', $minutes, 'freetoolshub'), $minutes);
}
