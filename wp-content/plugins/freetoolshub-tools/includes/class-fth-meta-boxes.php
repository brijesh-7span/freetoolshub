<?php

if (!defined('ABSPATH')) {
    exit;
}

class FTH_Meta_Boxes
{

    public function __construct()
    {

        add_action('add_meta_boxes', array($this, 'add_tool_meta_box'));

        add_action('save_post_tool', array($this, 'save_tool_meta'));
    }

    /**
     * Add Tool Information Meta Box
     */
    public function add_tool_meta_box()
    {

        add_meta_box(
            'fth_tool_details',
            __('Tool Information', 'freetoolshub-tools'),
            array($this, 'render_tool_meta_box'),
            'tool',
            'normal',
            'high'
        );
    }


    /**
     * Render Fields
     */
    public function render_tool_meta_box($post)
    {

        wp_nonce_field(
            'fth_save_tool_meta',
            'fth_tool_nonce'
        );

        $tool_icon = get_post_meta(
            $post->ID,
            '_fth_tool_icon',
            true
        );

        $tool_slug = get_post_meta(
            $post->ID,
            '_fth_tool_slug',
            true
        );

        $is_featured = get_post_meta(
            $post->ID,
            '_fth_is_featured',
            true
        );

?>

        <p>
            <label for="fth_tool_icon">
                <strong>Tool Icon (Emoji)</strong>
            </label>
        </p>

        <input
            type="text"
            id="fth_tool_icon"
            name="fth_tool_icon"
            value="<?php echo esc_attr($tool_icon); ?>"
            placeholder="📝"
            style="width:100%;">

        <p>
            <label for="fth_tool_slug">
                <strong>Tool Identifier</strong>
            </label>
        </p>

        <input
            type="text"
            id="fth_tool_slug"
            name="fth_tool_slug"
            value="<?php echo esc_attr($tool_slug); ?>"
            placeholder="word-counter"
            style="width:100%;">

        <p>
            <label>
                <input
                    type="checkbox"
                    name="fth_is_featured"
                    value="1"
                    <?php checked($is_featured, '1'); ?>>

                Featured Tool
            </label>
        </p>

<?php

    }


    /**
     * Save Tool Fields
     */
    public function save_tool_meta($post_id)
    {

        if (
            !isset($_POST['fth_tool_nonce']) ||
            !wp_verify_nonce(
                sanitize_text_field(wp_unslash($_POST['fth_tool_nonce'])),
                'fth_save_tool_meta'
            )
        ) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $icon = isset($_POST['fth_tool_icon'])
            ? sanitize_text_field(wp_unslash($_POST['fth_tool_icon']))
            : '';

        $slug = isset($_POST['fth_tool_slug'])
            ? sanitize_title(wp_unslash($_POST['fth_tool_slug']))
            : '';

        $featured = isset($_POST['fth_is_featured'])
            ? '1'
            : '0';

        update_post_meta($post_id, '_fth_tool_icon', $icon);

        update_post_meta($post_id, '_fth_tool_slug', $slug);

        update_post_meta($post_id, '_fth_is_featured', $featured);
    }
}
