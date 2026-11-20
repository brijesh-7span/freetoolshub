<?php

/**
 * Image Resizer — markup for the [fth_image_resizer] shortcode
 *
 * @var string $fth_id Unique ID prefix for this instance.
 */

if (!defined('ABSPATH')) {
    exit;
}

$fth_formats = array(
    'original'   => __('Same as original', 'freetoolshub-tools'),
    'image/jpeg' => __('JPG', 'freetoolshub-tools'),
    'image/png'  => __('PNG', 'freetoolshub-tools'),
    'image/webp' => __('WebP', 'freetoolshub-tools'),
);

$fth_presets = array(25, 50, 75, 100);

?>

<div class="fth-tool fth-image-tool fth-image-resizer" data-fth-image-resizer>

    <!-- Upload -->
    <div class="fth-img-dropzone" data-img-dropzone>

        <input
            type="file"
            id="<?php echo esc_attr($fth_id . '-file'); ?>"
            class="fth-img-file"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
            data-img-file>

        <label for="<?php echo esc_attr($fth_id . '-file'); ?>" class="fth-img-dropzone-label">
            <span class="fth-img-dropzone-icon" aria-hidden="true">🖼️</span>
            <span class="fth-img-dropzone-title">
                <?php esc_html_e('Drag & drop an image here, or click to choose', 'freetoolshub-tools'); ?>
            </span>
            <span class="fth-img-dropzone-hint">
                <?php esc_html_e('JPG, JPEG, PNG or WebP — up to 25 MB', 'freetoolshub-tools'); ?>
            </span>
        </label>

    </div>

    <span class="fth-tool-message" data-img-upload-message role="alert"></span>


    <!-- Workspace (shown after an image is loaded) -->
    <div class="fth-ir-workspace" data-img-workspace hidden>

        <div class="fth-img-previews">

            <figure class="fth-img-preview">
                <figcaption class="fth-img-preview-title"><?php esc_html_e('Original', 'freetoolshub-tools'); ?></figcaption>
                <div class="fth-img-preview-frame">
                    <img src="" alt="<?php esc_attr_e('Original image preview', 'freetoolshub-tools'); ?>" data-img-original>
                </div>
                <p class="fth-img-preview-meta" data-img-original-meta></p>
            </figure>

            <figure class="fth-img-preview">
                <figcaption class="fth-img-preview-title"><?php esc_html_e('Resized', 'freetoolshub-tools'); ?></figcaption>
                <div class="fth-img-preview-frame">
                    <img src="" alt="<?php esc_attr_e('Resized image preview', 'freetoolshub-tools'); ?>" data-img-result hidden>
                    <span class="fth-img-preview-loading" data-img-result-loading><?php esc_html_e('Processing…', 'freetoolshub-tools'); ?></span>
                </div>
                <p class="fth-img-preview-meta" data-img-result-meta></p>
            </figure>

        </div>


        <!-- Settings -->
        <div class="fth-img-settings">

            <div class="fth-ir-dimensions">

                <div class="fth-img-field">
                    <label for="<?php echo esc_attr($fth_id . '-width'); ?>" class="fth-tool-label">
                        <?php esc_html_e('Width (px)', 'freetoolshub-tools'); ?>
                    </label>
                    <input type="number" id="<?php echo esc_attr($fth_id . '-width'); ?>" class="fth-img-input" min="1" max="10000" step="1" inputmode="numeric" data-ir-width>
                </div>

                <span class="fth-ir-times" aria-hidden="true">×</span>

                <div class="fth-img-field">
                    <label for="<?php echo esc_attr($fth_id . '-height'); ?>" class="fth-tool-label">
                        <?php esc_html_e('Height (px)', 'freetoolshub-tools'); ?>
                    </label>
                    <input type="number" id="<?php echo esc_attr($fth_id . '-height'); ?>" class="fth-img-input" min="1" max="10000" step="1" inputmode="numeric" data-ir-height>
                </div>

            </div>

            <label class="fth-ir-checkbox">
                <input type="checkbox" checked data-ir-lock>
                <?php esc_html_e('Maintain aspect ratio', 'freetoolshub-tools'); ?>
            </label>

            <div class="fth-ir-presets" role="group" aria-label="<?php esc_attr_e('Resize by percentage', 'freetoolshub-tools'); ?>">
                <?php foreach ($fth_presets as $fth_preset) : ?>
                    <button type="button" class="fth-ir-preset" data-ir-preset="<?php echo esc_attr($fth_preset); ?>">
                        <?php echo esc_html($fth_preset . '%'); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="fth-ir-output">

                <div class="fth-img-field">
                    <label for="<?php echo esc_attr($fth_id . '-format'); ?>" class="fth-tool-label">
                        <?php esc_html_e('Output format', 'freetoolshub-tools'); ?>
                    </label>
                    <select id="<?php echo esc_attr($fth_id . '-format'); ?>" class="fth-img-input" data-img-format>
                        <?php foreach ($fth_formats as $fth_value => $fth_label) : ?>
                            <option value="<?php echo esc_attr($fth_value); ?>"><?php echo esc_html($fth_label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="fth-img-field" data-img-quality-field>
                    <label for="<?php echo esc_attr($fth_id . '-quality'); ?>" class="fth-tool-label">
                        <?php esc_html_e('Quality', 'freetoolshub-tools'); ?>
                        <output class="fth-img-quality-value" for="<?php echo esc_attr($fth_id . '-quality'); ?>" data-img-quality-value>90%</output>
                    </label>
                    <input type="range" id="<?php echo esc_attr($fth_id . '-quality'); ?>" class="fth-img-range" min="10" max="100" step="1" value="90" data-img-quality>
                    <span class="fth-img-field-hint" data-img-quality-hint hidden>
                        <?php esc_html_e('PNG is lossless — quality does not apply.', 'freetoolshub-tools'); ?>
                    </span>
                </div>

            </div>

        </div>


        <!-- Actions -->
        <div class="fth-tool-actions">

            <a href="#" class="fth-btn fth-btn-primary" download data-img-download aria-disabled="true">
                <?php esc_html_e('Download Image', 'freetoolshub-tools'); ?>
            </a>

            <button type="button" class="fth-btn fth-btn-secondary" data-img-reset>
                <?php esc_html_e('Choose Another Image', 'freetoolshub-tools'); ?>
            </button>

            <span class="fth-tool-message" data-img-message role="status" aria-live="polite"></span>

        </div>

    </div>

    <p class="fth-tool-note">
        <?php esc_html_e('Images are resized in your browser and never uploaded to our server.', 'freetoolshub-tools'); ?>
    </p>

</div>
