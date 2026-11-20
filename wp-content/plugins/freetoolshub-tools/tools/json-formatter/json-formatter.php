<?php

/**
 * JSON Formatter — markup for the [fth_json_formatter] shortcode
 *
 * @var string $fth_id Unique ID prefix for this instance.
 */

if (!defined('ABSPATH')) {
    exit;
}

$fth_indents = array(
    '2'   => __('2 spaces', 'freetoolshub-tools'),
    '4'   => __('4 spaces', 'freetoolshub-tools'),
    'tab' => __('Tab', 'freetoolshub-tools'),
);

?>

<div class="fth-tool fth-json-formatter" data-fth-json-formatter>

    <div class="fth-json-toolbar">

        <label for="<?php echo esc_attr($fth_id . '-input'); ?>" class="fth-tool-label">
            <?php esc_html_e('Paste your JSON', 'freetoolshub-tools'); ?>
        </label>

        <div class="fth-json-indent">
            <label for="<?php echo esc_attr($fth_id . '-indent'); ?>"><?php esc_html_e('Indent', 'freetoolshub-tools'); ?></label>
            <select id="<?php echo esc_attr($fth_id . '-indent'); ?>" data-json-indent>
                <?php foreach ($fth_indents as $fth_value => $fth_label) : ?>
                    <option value="<?php echo esc_attr($fth_value); ?>"><?php echo esc_html($fth_label); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

    </div>

    <textarea
        id="<?php echo esc_attr($fth_id . '-input'); ?>"
        class="fth-tool-textarea fth-json-textarea"
        rows="16"
        spellcheck="false"
        autocomplete="off"
        autocapitalize="off"
        wrap="off"
        placeholder='{"name": "FreeToolsHub", "tools": ["JSON Formatter", "Word Counter"], "free": true}'
        aria-describedby="<?php echo esc_attr($fth_id . '-status'); ?>"
        data-json-input></textarea>

    <div class="fth-json-status" id="<?php echo esc_attr($fth_id . '-status'); ?>" role="status" aria-live="polite" data-json-status>
        <strong data-json-status-title></strong>
        <span data-json-status-detail><?php esc_html_e('Paste or type JSON to validate it.', 'freetoolshub-tools'); ?></span>
    </div>

    <div class="fth-tool-actions">

        <button type="button" class="fth-btn fth-btn-primary" data-json-format disabled>
            <?php esc_html_e('Format', 'freetoolshub-tools'); ?>
        </button>

        <button type="button" class="fth-btn fth-btn-secondary" data-json-minify disabled>
            <?php esc_html_e('Minify', 'freetoolshub-tools'); ?>
        </button>

        <button type="button" class="fth-btn fth-btn-secondary" data-fth-copy disabled>
            <?php esc_html_e('Copy', 'freetoolshub-tools'); ?>
        </button>

        <button type="button" class="fth-btn fth-btn-secondary" data-fth-clear disabled>
            <?php esc_html_e('Clear', 'freetoolshub-tools'); ?>
        </button>

        <span class="fth-tool-message" data-fth-message role="status" aria-live="polite"></span>

    </div>

    <p class="fth-tool-note">
        <?php esc_html_e('JSON is checked as you type. Numbers and text are kept exactly as written, so large IDs never lose precision. Your data never leaves your browser.', 'freetoolshub-tools'); ?>
    </p>

</div>
