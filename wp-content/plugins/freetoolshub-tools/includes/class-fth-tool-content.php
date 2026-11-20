<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Tool page content: SEO title/description, intro, features, how-to steps and FAQs.
 *
 * Each tool keeps its content next to its code in tools/{identifier}/content.php,
 * which returns an array:
 *
 *     array(
 *         'seo_title'       => 'Short title without the site name',
 *         'seo_description' => 'Meta description, ~150 characters',
 *         'intro'           => array('Paragraph', ...),
 *         'features'        => array('Feature', ...),
 *         'steps'           => array('Step', ...),
 *         'faqs'            => array(array('question' => '...', 'answer' => '...'), ...),
 *         'app_category'    => 'UtilitiesApplication', // schema.org applicationCategory
 *     )
 *
 * The result can be adjusted with the `fth_tool_content` filter.
 */
class FTH_Tool_Content
{

    private static $cache = array();

    private static $defaults = array(
        'seo_title'       => '',
        'seo_description' => '',
        'intro'           => array(),
        'features'        => array(),
        'steps'           => array(),
        'faqs'            => array(),
        'app_category'    => 'UtilitiesApplication',
    );


    /**
     * Content for a tool identifier (e.g. "word-counter"). Missing keys get defaults.
     */
    public static function get($tool_slug)
    {

        $tool_slug = sanitize_key($tool_slug);

        if (!isset(self::$cache[$tool_slug])) {

            $content = array();
            $file    = FTH_PLUGIN_PATH . 'tools/' . $tool_slug . '/content.php';

            if ($tool_slug !== '' && is_readable($file)) {
                $loaded  = include $file;
                $content = is_array($loaded) ? $loaded : array();
            }

            self::$cache[$tool_slug] = apply_filters(
                'fth_tool_content',
                array_merge(self::$defaults, $content),
                $tool_slug
            );
        }

        return self::$cache[$tool_slug];
    }


    /**
     * Content for a tool post (looked up by its "Tool Identifier").
     */
    public static function get_for_post($post_id)
    {

        return self::get(get_post_meta($post_id, '_fth_tool_slug', true));
    }
}
