<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add icon / popular columns to the Tools list table
 */
function fth_tool_admin_columns($columns)
{

    $new_columns = array();

    foreach ($columns as $key => $label) {

        if ($key === 'title') {
            $new_columns['fth_icon'] = __('Icon', 'freetoolshub');
        }

        $new_columns[$key] = $label;

        if ($key === 'title') {
            $new_columns['fth_featured'] = __('Featured', 'freetoolshub');
        }
    }

    return $new_columns;
}

add_filter('manage_tool_posts_columns', 'fth_tool_admin_columns');


/**
 * Render custom column values in the Tools list table
 */
function fth_tool_admin_column_content($column, $post_id)
{

    if ($column === 'fth_icon') {

        if (has_post_thumbnail($post_id)) {
            echo get_the_post_thumbnail($post_id, array(40, 40));
        } else {
            echo '<span style="font-size:24px;">' . esc_html(fth_get_tool_icon($post_id)) . '</span>';
        }
    }

    if ($column === 'fth_featured') {
        echo get_post_meta($post_id, '_fth_is_featured', true) === '1' ? '&#9733;' : '&mdash;';
    }
}

add_action('manage_tool_posts_custom_column', 'fth_tool_admin_column_content', 10, 2);


/**
 * Keep the icon column narrow
 */
function fth_tool_admin_column_styles()
{

    $screen = get_current_screen();

    if ($screen && $screen->id === 'edit-tool') {
        echo '<style>.column-fth_icon{width:60px;text-align:center;}.column-fth_featured{width:80px;text-align:center;}</style>';
    }
}

add_action('admin_head', 'fth_tool_admin_column_styles');


/**
 * Category icon field — add screen
 */
function fth_tool_category_add_icon_field()
{
?>

    <div class="form-field">
        <label for="fth_category_icon"><?php esc_html_e('Icon (emoji)', 'freetoolshub'); ?></label>
        <input type="text" id="fth_category_icon" name="fth_category_icon" value="" placeholder="🖼️">
        <p><?php esc_html_e('Shown on the homepage category cards.', 'freetoolshub'); ?></p>
    </div>

<?php
}

add_action('tool_category_add_form_fields', 'fth_tool_category_add_icon_field');


/**
 * Category icon field — edit screen
 */
function fth_tool_category_edit_icon_field($term)
{

    $icon = get_term_meta($term->term_id, 'fth_category_icon', true);
?>

    <tr class="form-field">
        <th scope="row">
            <label for="fth_category_icon"><?php esc_html_e('Icon (emoji)', 'freetoolshub'); ?></label>
        </th>
        <td>
            <input type="text" id="fth_category_icon" name="fth_category_icon" value="<?php echo esc_attr($icon); ?>" placeholder="🖼️">
            <p class="description"><?php esc_html_e('Shown on the homepage category cards.', 'freetoolshub'); ?></p>
        </td>
    </tr>

<?php
}

add_action('tool_category_edit_form_fields', 'fth_tool_category_edit_icon_field');


/**
 * Save category icon
 * (term add/edit forms are already nonce-checked by WordPress core)
 */
function fth_save_tool_category_icon($term_id)
{

    if (!isset($_POST['fth_category_icon']) || !current_user_can('manage_categories')) {
        return;
    }

    $icon = sanitize_text_field(wp_unslash($_POST['fth_category_icon']));

    if ($icon !== '') {
        update_term_meta($term_id, 'fth_category_icon', $icon);
    } else {
        delete_term_meta($term_id, 'fth_category_icon');
    }
}

add_action('created_tool_category', 'fth_save_tool_category_icon');
add_action('edited_tool_category', 'fth_save_tool_category_icon');
