<?php

/**
 * QR Code Generator — markup for the [fth_qr_generator] shortcode
 *
 * @var string $fth_id Unique ID prefix for this instance.
 */

if (!defined('ABSPATH')) {
    exit;
}

$fth_sizes = array(128, 256, 384);
$fth_default_size = 256;

?>

<div class="fth-tool fth-image-tool fth-qr-generator" data-fth-qr-generator>

    <div class="fth-qr-layout">

        <!-- Settings -->
        <div class="fth-qr-controls">

            <label for="<?php echo esc_attr($fth_id . '-text'); ?>" class="fth-tool-label">
                <?php esc_html_e('Text or URL', 'freetoolshub-tools'); ?>
            </label>

            <textarea
                id="<?php echo esc_attr($fth_id . '-text'); ?>"
                class="fth-tool-textarea fth-qr-textarea"
                rows="4"
                maxlength="2000"
                spellcheck="false"
                placeholder="<?php esc_attr_e('https://example.com or any text…', 'freetoolshub-tools'); ?>"
                data-qr-input></textarea>

            <span class="fth-qr-counter" data-qr-counter>0 / 2000</span>

            <fieldset class="fth-qr-fieldset">
                <legend class="fth-tool-label"><?php esc_html_e('Size', 'freetoolshub-tools'); ?></legend>

                <div class="fth-qr-sizes">
                    <?php foreach ($fth_sizes as $fth_size) : ?>
                        <label class="fth-qr-size">
                            <input
                                type="radio"
                                name="<?php echo esc_attr($fth_id . '-size'); ?>"
                                value="<?php echo esc_attr($fth_size); ?>"
                                <?php checked($fth_size, $fth_default_size); ?>
                                data-qr-size>
                            <span><?php echo esc_html($fth_size . 'px'); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <div class="fth-qr-colors">

                <div class="fth-qr-color">
                    <label for="<?php echo esc_attr($fth_id . '-fg'); ?>" class="fth-tool-label">
                        <?php esc_html_e('Foreground', 'freetoolshub-tools'); ?>
                    </label>
                    <div class="fth-qr-color-input">
                        <input type="color" id="<?php echo esc_attr($fth_id . '-fg'); ?>" value="#000000" data-qr-fg>
                        <code data-qr-fg-value>#000000</code>
                    </div>
                </div>

                <div class="fth-qr-color">
                    <label for="<?php echo esc_attr($fth_id . '-bg'); ?>" class="fth-tool-label">
                        <?php esc_html_e('Background', 'freetoolshub-tools'); ?>
                    </label>
                    <div class="fth-qr-color-input">
                        <input type="color" id="<?php echo esc_attr($fth_id . '-bg'); ?>" value="#ffffff" data-qr-bg>
                        <code data-qr-bg-value>#FFFFFF</code>
                    </div>
                </div>

                <button type="button" class="fth-qr-link-btn" data-qr-reset-colors>
                    <?php esc_html_e('Reset colors', 'freetoolshub-tools'); ?>
                </button>

            </div>

            <span class="fth-tool-message is-error" data-qr-contrast role="status" aria-live="polite"></span>

        </div>


        <!-- Preview -->
        <div class="fth-qr-preview">

            <div class="fth-qr-preview-frame">
                <canvas class="fth-qr-canvas" data-qr-canvas hidden role="img" aria-label="<?php esc_attr_e('Generated QR code', 'freetoolshub-tools'); ?>"></canvas>
                <span class="fth-qr-placeholder" data-qr-placeholder>
                    <span aria-hidden="true">🔳</span>
                    <?php esc_html_e('Your QR code will appear here', 'freetoolshub-tools'); ?>
                </span>
            </div>

            <p class="fth-img-preview-meta" data-qr-meta></p>

        </div>

    </div>


    <!-- Actions -->
    <div class="fth-tool-actions">

        <a class="fth-btn fth-btn-primary" download data-qr-download aria-disabled="true">
            <?php esc_html_e('Download PNG', 'freetoolshub-tools'); ?>
        </a>

        <button type="button" class="fth-btn fth-btn-secondary" data-qr-clear disabled>
            <?php esc_html_e('Clear', 'freetoolshub-tools'); ?>
        </button>

        <span class="fth-tool-message" data-qr-message role="status" aria-live="polite"></span>

    </div>

    <p class="fth-tool-note">
        <?php esc_html_e('QR codes are generated in your browser — your text is never sent to our server. Dark codes on light backgrounds scan most reliably.', 'freetoolshub-tools'); ?>
    </p>

</div>
