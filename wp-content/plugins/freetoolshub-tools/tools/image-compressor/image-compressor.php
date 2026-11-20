<?php

/**
 * Image Compressor — markup for the [fth_image_compressor] shortcode
 *
 * @var string $fth_id Unique ID prefix for this instance.
 */

if (!defined('ABSPATH')) {
    exit;
}

$fth_formats = array(
    'auto'       => __('Auto (recommended)', 'freetoolshub-tools'),
    'image/jpeg' => __('JPG', 'freetoolshub-tools'),
    'image/webp' => __('WebP', 'freetoolshub-tools'),
    'image/png'  => __('PNG (lossless)', 'freetoolshub-tools'),
);

$fth_max_widths = array(
    ''     => __('Keep original size', 'freetoolshub-tools'),
    '3840' => __('3840 px (4K)', 'freetoolshub-tools'),
    '2560' => __('2560 px', 'freetoolshub-tools'),
    '1920' => __('1920 px (Full HD)', 'freetoolshub-tools'),
    '1280' => __('1280 px', 'freetoolshub-tools'),
    '800'  => __('800 px', 'freetoolshub-tools'),
);

?>

<div class="fth-tool fth-image-tool fth-image-compressor" data-fth-image-compressor>

    <!-- Upload -->
    <div class="fth-img-dropzone" data-img-dropzone>

        <input
            type="file"
            id="<?php echo esc_attr($fth_id . '-file'); ?>"
            class="fth-img-file"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
            data-img-file>

        <label for="<?php echo esc_attr($fth_id . '-file'); ?>" class="fth-img-dropzone-label">
            <span class="fth-img-dropzone-icon" aria-hidden="true">🗜️</span>
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
    <div data-img-workspace hidden>

        <!-- Size comparison -->
        <div class="fth-ic-summary" aria-live="polite">

            <div class="fth-ic-stat">
                <span class="fth-ic-stat-label"><?php esc_html_e('Original size', 'freetoolshub-tools'); ?></span>
                <span class="fth-ic-stat-value" data-ic-original-size>—</span>
            </div>

            <div class="fth-ic-stat">
                <span class="fth-ic-stat-label"><?php esc_html_e('Compressed size', 'freetoolshub-tools'); ?></span>
                <span class="fth-ic-stat-value" data-ic-compressed-size>—</span>
            </div>

            <div class="fth-ic-stat fth-ic-stat-highlight" data-ic-saved-card>
                <span class="fth-ic-stat-label" data-ic-saved-label><?php esc_html_e('You saved', 'freetoolshub-tools'); ?></span>
                <span class="fth-ic-stat-value" data-ic-saved>—</span>
            </div>

            <div class="fth-ic-bars" aria-hidden="true">
                <div class="fth-ic-bar">
                    <span class="fth-ic-bar-name"><?php esc_html_e('Original', 'freetoolshub-tools'); ?></span>
                    <span class="fth-ic-bar-track"><span class="fth-ic-bar-fill fth-ic-bar-original" data-ic-bar-original></span></span>
                </div>
                <div class="fth-ic-bar">
                    <span class="fth-ic-bar-name"><?php esc_html_e('Compressed', 'freetoolshub-tools'); ?></span>
                    <span class="fth-ic-bar-track"><span class="fth-ic-bar-fill fth-ic-bar-compressed" data-ic-bar-compressed></span></span>
                </div>
            </div>

        </div>


        <!-- Previews -->
        <div class="fth-img-previews">

            <figure class="fth-img-preview">
                <figcaption class="fth-img-preview-title"><?php esc_html_e('Original', 'freetoolshub-tools'); ?></figcaption>
                <div class="fth-img-preview-frame">
                    <img src="" alt="<?php esc_attr_e('Original image preview', 'freetoolshub-tools'); ?>" data-img-original>
                </div>
                <p class="fth-img-preview-meta" data-img-original-meta></p>
            </figure>

            <figure class="fth-img-preview">
                <figcaption class="fth-img-preview-title"><?php esc_html_e('Compressed', 'freetoolshub-tools'); ?></figcaption>
                <div class="fth-img-preview-frame">
                    <img src="" alt="<?php esc_attr_e('Compressed image preview', 'freetoolshub-tools'); ?>" data-img-result hidden>
                    <span class="fth-img-preview-loading" data-img-result-loading><?php esc_html_e('Compressing…', 'freetoolshub-tools'); ?></span>
                </div>
                <p class="fth-img-preview-meta" data-img-result-meta></p>
            </figure>

        </div>


        <!-- Settings -->
        <div class="fth-img-settings fth-ic-settings">

            <div class="fth-img-field fth-ic-quality" data-img-quality-field>
                <label for="<?php echo esc_attr($fth_id . '-quality'); ?>" class="fth-tool-label">
                    <?php esc_html_e('Compression quality', 'freetoolshub-tools'); ?>
                    <output class="fth-img-quality-value" for="<?php echo esc_attr($fth_id . '-quality'); ?>" data-img-quality-value>75%</output>
                </label>
                <input type="range" id="<?php echo esc_attr($fth_id . '-quality'); ?>" class="fth-img-range" min="10" max="100" step="1" value="75" data-img-quality>
                <span class="fth-ic-range-labels" aria-hidden="true">
                    <span><?php esc_html_e('Smaller file', 'freetoolshub-tools'); ?></span>
                    <span><?php esc_html_e('Better quality', 'freetoolshub-tools'); ?></span>
                </span>
                <span class="fth-img-field-hint" data-img-quality-hint hidden>
                    <?php esc_html_e('PNG is lossless — quality does not apply. Choose WebP or JPG for bigger savings.', 'freetoolshub-tools'); ?>
                </span>
            </div>

            <div class="fth-img-field">
                <label for="<?php echo esc_attr($fth_id . '-format'); ?>" class="fth-tool-label">
                    <?php esc_html_e('Output format', 'freetoolshub-tools'); ?>
                </label>
                <select id="<?php echo esc_attr($fth_id . '-format'); ?>" class="fth-img-input" data-img-format>
                    <?php foreach ($fth_formats as $fth_value => $fth_label) : ?>
                        <option value="<?php echo esc_attr($fth_value); ?>"><?php echo esc_html($fth_label); ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="fth-img-field-hint" data-ic-format-hint></span>
            </div>

            <div class="fth-img-field">
                <label for="<?php echo esc_attr($fth_id . '-max-width'); ?>" class="fth-tool-label">
                    <?php esc_html_e('Max width', 'freetoolshub-tools'); ?>
                </label>
                <select id="<?php echo esc_attr($fth_id . '-max-width'); ?>" class="fth-img-input" data-ic-max-width>
                    <?php foreach ($fth_max_widths as $fth_value => $fth_label) : ?>
                        <option value="<?php echo esc_attr($fth_value); ?>"><?php echo esc_html($fth_label); ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="fth-img-field-hint"><?php esc_html_e('Shrinking large photos saves the most space.', 'freetoolshub-tools'); ?></span>
            </div>

        </div>


        <!-- Actions -->
        <div class="fth-tool-actions">

            <a class="fth-btn fth-btn-primary" download data-img-download aria-disabled="true">
                <?php esc_html_e('Download Compressed Image', 'freetoolshub-tools'); ?>
            </a>

            <button type="button" class="fth-btn fth-btn-secondary" data-img-reset>
                <?php esc_html_e('Choose Another Image', 'freetoolshub-tools'); ?>
            </button>

            <span class="fth-tool-message" data-img-message role="status" aria-live="polite"></span>

        </div>

    </div>

    <p class="fth-tool-note">
        <?php esc_html_e('Images are compressed in your browser and never uploaded to our server. Compression also removes hidden metadata such as camera details and GPS location.', 'freetoolshub-tools'); ?>
    </p>

</div>
