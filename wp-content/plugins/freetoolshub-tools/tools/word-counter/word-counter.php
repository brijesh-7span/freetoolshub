<?php

/**
 * Word Counter — markup for the [fth_word_counter] shortcode
 *
 * @var string $fth_textarea_id Unique textarea ID for this instance.
 */

if (!defined('ABSPATH')) {
    exit;
}

$fth_stats = array(
    'words'                    => __('Words', 'freetoolshub-tools'),
    'characters'               => __('Characters', 'freetoolshub-tools'),
    'characters-no-spaces'     => __('Characters (no spaces)', 'freetoolshub-tools'),
    'sentences'                => __('Sentences', 'freetoolshub-tools'),
    'paragraphs'               => __('Paragraphs', 'freetoolshub-tools'),
    'reading-time'             => __('Reading Time', 'freetoolshub-tools'),
);

?>

<div class="fth-tool fth-word-counter" data-fth-word-counter>

    <div class="fth-wc-stats">

        <?php foreach ($fth_stats as $fth_key => $fth_label) : ?>

            <div class="fth-wc-stat">
                <span class="fth-wc-stat-value" data-stat="<?php echo esc_attr($fth_key); ?>">
                    <?php echo $fth_key === 'reading-time' ? esc_html__('0 sec', 'freetoolshub-tools') : '0'; ?>
                </span>
                <span class="fth-wc-stat-label"><?php echo esc_html($fth_label); ?></span>
            </div>

        <?php endforeach; ?>

    </div>

    <label for="<?php echo esc_attr($fth_textarea_id); ?>" class="fth-tool-label">
        <?php esc_html_e('Enter or paste your text', 'freetoolshub-tools'); ?>
    </label>

    <textarea
        id="<?php echo esc_attr($fth_textarea_id); ?>"
        class="fth-tool-textarea"
        data-wc-input
        rows="12"
        spellcheck="true"
        placeholder="<?php esc_attr_e('Start typing or paste your text here...', 'freetoolshub-tools'); ?>"></textarea>

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
        <?php esc_html_e('Reading time is based on an average reading speed of 200 words per minute. Your text never leaves your browser.', 'freetoolshub-tools'); ?>
    </p>

</div>
