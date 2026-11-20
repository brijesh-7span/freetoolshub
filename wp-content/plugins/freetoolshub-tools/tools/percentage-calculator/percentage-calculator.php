<?php

/**
 * Percentage Calculator — markup for the [fth_percentage_calculator] shortcode
 *
 * @var string $fth_id Unique ID prefix for this instance.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render a number input for a calculator sentence.
 */
$fth_pc_input = function ($name, $label, $placeholder) use ($fth_id) {
    $input_id = $fth_id . '-' . $name;
?>
    <label class="screen-reader-text fth-pc-sr" for="<?php echo esc_attr($input_id); ?>"><?php echo esc_html($label); ?></label>
    <input
        type="number"
        id="<?php echo esc_attr($input_id); ?>"
        class="fth-pc-input"
        step="any"
        inputmode="decimal"
        placeholder="<?php echo esc_attr($placeholder); ?>"
        data-pc-field="<?php echo esc_attr($name); ?>">
<?php
};

/**
 * Render a calculator's result area and buttons.
 */
$fth_pc_footer = function () {
?>
    <div class="fth-pc-result" role="status" aria-live="polite">
        <span class="fth-pc-result-label"><?php esc_html_e('Result', 'freetoolshub-tools'); ?></span>
        <output class="fth-pc-result-value" data-pc-result>—</output>
        <span class="fth-pc-formula" data-pc-formula></span>
    </div>

    <div class="fth-pc-actions">
        <button type="button" class="fth-btn fth-btn-primary fth-pc-btn" data-pc-copy disabled>
            <?php esc_html_e('Copy Result', 'freetoolshub-tools'); ?>
        </button>
        <button type="button" class="fth-btn fth-btn-secondary fth-pc-btn" data-pc-clear disabled>
            <?php esc_html_e('Clear', 'freetoolshub-tools'); ?>
        </button>
        <span class="fth-tool-message" data-pc-message role="status" aria-live="polite"></span>
    </div>
<?php
};

?>

<div class="fth-tool fth-percentage-calculator" data-fth-percentage-calculator>

    <div class="fth-pc-grid">

        <!-- 1. Percentage of a number -->
        <section class="fth-pc-card" data-pc-calc="of" aria-labelledby="<?php echo esc_attr($fth_id . '-of-title'); ?>">

            <h2 class="fth-pc-title" id="<?php echo esc_attr($fth_id . '-of-title'); ?>">
                <?php esc_html_e('Percentage of a number', 'freetoolshub-tools'); ?>
            </h2>

            <p class="fth-pc-sentence">
                <span><?php esc_html_e('What is', 'freetoolshub-tools'); ?></span>
                <span class="fth-pc-field fth-pc-field-percent">
                    <?php $fth_pc_input('of-percent', __('Percentage', 'freetoolshub-tools'), '20'); ?>
                    <span class="fth-pc-unit" aria-hidden="true">%</span>
                </span>
                <span><?php esc_html_e('of', 'freetoolshub-tools'); ?></span>
                <span class="fth-pc-field">
                    <?php $fth_pc_input('of-number', __('Number', 'freetoolshub-tools'), '500'); ?>
                </span>
                <span>?</span>
            </p>

            <?php $fth_pc_footer(); ?>

        </section>


        <!-- 2. Find percentage -->
        <section class="fth-pc-card" data-pc-calc="what" aria-labelledby="<?php echo esc_attr($fth_id . '-what-title'); ?>">

            <h2 class="fth-pc-title" id="<?php echo esc_attr($fth_id . '-what-title'); ?>">
                <?php esc_html_e('Find the percentage', 'freetoolshub-tools'); ?>
            </h2>

            <p class="fth-pc-sentence">
                <span class="fth-pc-field">
                    <?php $fth_pc_input('what-part', __('Part', 'freetoolshub-tools'), '50'); ?>
                </span>
                <span><?php esc_html_e('is what % of', 'freetoolshub-tools'); ?></span>
                <span class="fth-pc-field">
                    <?php $fth_pc_input('what-total', __('Total', 'freetoolshub-tools'), '200'); ?>
                </span>
                <span>?</span>
            </p>

            <?php $fth_pc_footer(); ?>

        </section>


        <!-- 3. Percentage change -->
        <section class="fth-pc-card" data-pc-calc="change" aria-labelledby="<?php echo esc_attr($fth_id . '-change-title'); ?>">

            <h2 class="fth-pc-title" id="<?php echo esc_attr($fth_id . '-change-title'); ?>">
                <?php esc_html_e('Percentage change', 'freetoolshub-tools'); ?>
            </h2>

            <p class="fth-pc-sentence">
                <span><?php esc_html_e('From', 'freetoolshub-tools'); ?></span>
                <span class="fth-pc-field">
                    <?php $fth_pc_input('change-from', __('Original value', 'freetoolshub-tools'), '100'); ?>
                </span>
                <span><?php esc_html_e('to', 'freetoolshub-tools'); ?></span>
                <span class="fth-pc-field">
                    <?php $fth_pc_input('change-to', __('New value', 'freetoolshub-tools'), '150'); ?>
                </span>
            </p>

            <?php $fth_pc_footer(); ?>

        </section>

    </div>

    <p class="fth-tool-note">
        <?php esc_html_e('Results update as you type. All calculations happen in your browser.', 'freetoolshub-tools'); ?>
    </p>

</div>
