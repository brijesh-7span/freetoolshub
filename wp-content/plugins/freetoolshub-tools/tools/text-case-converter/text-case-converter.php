<?php

/**
 * Text Case Converter — markup for the [fth_text_case_converter] shortcode
 *
 * @var string $fth_textarea_id Unique textarea ID for this instance.
 */

if (!defined('ABSPATH')) {
    exit;
}

$fth_cases = array(
    'upper'    => __('UPPERCASE', 'freetoolshub-tools'),
    'lower'    => __('lowercase', 'freetoolshub-tools'),
    'title'    => __('Title Case', 'freetoolshub-tools'),
    'sentence' => __('Sentence case', 'freetoolshub-tools'),
);

?>

<div class="fth-tool fth-text-case-converter" data-fth-text-case>

    <label for="<?php echo esc_attr($fth_textarea_id); ?>" class="fth-tool-label">
        <?php esc_html_e('Enter or paste your text', 'freetoolshub-tools'); ?>
    </label>

    <textarea
        id="<?php echo esc_attr($fth_textarea_id); ?>"
        class="fth-tool-textarea"
        data-tc-input
        rows="10"
        spellcheck="true"
        placeholder="<?php esc_attr_e('Type or paste your text here, then choose a case...', 'freetoolshub-tools'); ?>"></textarea>

    <div class="fth-tc-cases" role="group" aria-label="<?php esc_attr_e('Convert text to', 'freetoolshub-tools'); ?>">

        <?php foreach ($fth_cases as $fth_case => $fth_label) : ?>

            <button
                type="button"
                class="fth-tc-case-btn"
                data-case="<?php echo esc_attr($fth_case); ?>"
                aria-pressed="false"
                disabled>
                <?php echo esc_html($fth_label); ?>
            </button>

        <?php endforeach; ?>

    </div>

    <div class="fth-tool-actions">

        <button type="button" class="fth-btn fth-btn-primary" data-fth-copy disabled>
            <?php esc_html_e('Copy Text', 'freetoolshub-tools'); ?>
        </button>

        <button type="button" class="fth-btn fth-btn-secondary" data-fth-clear disabled>
            <?php esc_html_e('Clear Text', 'freetoolshub-tools'); ?>
        </button>

        <span class="fth-tool-message" data-fth-message role="status" aria-live="polite"></span>

    </div>

    <p class="fth-tool-note">
        <?php esc_html_e('Tip: press Ctrl+Z (Cmd+Z on Mac) to undo a conversion. Your text never leaves your browser.', 'freetoolshub-tools'); ?>
    </p>

</div>
