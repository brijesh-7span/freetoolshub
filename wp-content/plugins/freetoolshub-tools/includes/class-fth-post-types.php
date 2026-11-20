<?php

if (!defined('ABSPATH')) {
    exit;
}

class FTH_Post_Types
{

    public function __construct()
    {

        add_action('init', array($this, 'register_tool_post_type'));

        add_action('init', array($this, 'register_tool_taxonomy'));
    }

    /**
     * Register Tools Custom Post Type
     */
    public function register_tool_post_type()
    {

        $labels = array(

            'name'               => __('Tools', 'freetoolshub-tools'),

            'singular_name'      => __('Tool', 'freetoolshub-tools'),

            'menu_name'          => __('Tools', 'freetoolshub-tools'),

            'add_new'            => __('Add New Tool', 'freetoolshub-tools'),

            'add_new_item'       => __('Add New Tool', 'freetoolshub-tools'),

            'edit_item'          => __('Edit Tool', 'freetoolshub-tools'),

            'new_item'           => __('New Tool', 'freetoolshub-tools'),

            'view_item'          => __('View Tool', 'freetoolshub-tools'),

            'search_items'       => __('Search Tools', 'freetoolshub-tools'),

            'not_found'          => __('No tools found', 'freetoolshub-tools'),

            'not_found_in_trash' => __('No tools found in Trash', 'freetoolshub-tools'),

        );

        $args = array(

            'labels'        => $labels,

            'public'        => true,

            'show_ui'       => true,

            'show_in_menu'  => true,

            'show_in_rest'  => true,

            'has_archive'   => true,

            'rewrite'       => array(
                'slug' => 'tools'
            ),

            'menu_icon'     => 'dashicons-admin-tools',

            'supports'      => array(
                'title',
                'editor',
                'thumbnail',
                'excerpt',
                'revisions'
            ),

        );

        register_post_type('tool', $args);
    }


    /**
     * Register Tool Categories Taxonomy
     */
    public function register_tool_taxonomy()
    {

        $labels = array(

            'name'              => __('Tool Categories', 'freetoolshub-tools'),

            'singular_name'     => __('Tool Category', 'freetoolshub-tools'),

            'search_items'      => __('Search Categories', 'freetoolshub-tools'),

            'all_items'         => __('All Categories', 'freetoolshub-tools'),

            'parent_item'       => __('Parent Category', 'freetoolshub-tools'),

            'edit_item'         => __('Edit Category', 'freetoolshub-tools'),

            'add_new_item'      => __('Add New Category', 'freetoolshub-tools'),

            'menu_name'         => __('Tool Categories', 'freetoolshub-tools'),

        );

        $args = array(

            'labels'            => $labels,

            'public'            => true,

            'hierarchical'      => true,

            'show_ui'           => true,

            'show_admin_column' => true,

            'show_in_rest'      => true,

            'rewrite'           => array(
                'slug' => 'tool-category'
            ),

        );

        register_taxonomy(
            'tool_category',
            array('tool'),
            $args
        );
    }
}
