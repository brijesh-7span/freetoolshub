<?php

/**
 * Tool guide: About, Key features, How to use and FAQs.
 *
 * Content comes from the FreeToolsHub Tools plugin (tools/{identifier}/content.php).
 * The FAQs here are the same ones described in the page's FAQPage structured data.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('FTH_Tool_Content')) {
    return;
}

$fth_guide = FTH_Tool_Content::get_for_post(get_the_ID());
$fth_name  = get_the_title();

if (!$fth_guide['intro'] && !$fth_guide['steps'] && !$fth_guide['faqs']) {
    return;
}
?>

<div class="tool-guide">

    <?php if ($fth_guide['intro'] || $fth_guide['features']) : ?>
        <section class="tool-guide-section" aria-labelledby="tool-about">

            <h2 id="tool-about">
                <?php
                /* translators: %s: tool name */
                echo esc_html(sprintf(__('About the %s', 'freetoolshub'), $fth_name));
                ?>
            </h2>

            <?php foreach ($fth_guide['intro'] as $fth_paragraph) : ?>
                <p><?php echo esc_html($fth_paragraph); ?></p>
            <?php endforeach; ?>

            <?php if ($fth_guide['features']) : ?>
                <h3><?php esc_html_e('Key features', 'freetoolshub'); ?></h3>
                <ul class="tool-guide-features">
                    <?php foreach ($fth_guide['features'] as $fth_feature) : ?>
                        <li><?php echo esc_html($fth_feature); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

        </section>
    <?php endif; ?>


    <?php if ($fth_guide['steps']) : ?>
        <section class="tool-guide-section" aria-labelledby="tool-how-to">

            <h2 id="tool-how-to">
                <?php
                /* translators: %s: tool name */
                echo esc_html(sprintf(__('How to use the %s', 'freetoolshub'), $fth_name));
                ?>
            </h2>

            <ol class="tool-guide-steps">
                <?php foreach ($fth_guide['steps'] as $fth_step) : ?>
                    <li><?php echo esc_html($fth_step); ?></li>
                <?php endforeach; ?>
            </ol>

        </section>
    <?php endif; ?>


    <?php if ($fth_guide['faqs']) : ?>
        <section class="tool-guide-section" aria-labelledby="tool-faq">

            <h2 id="tool-faq"><?php esc_html_e('Frequently asked questions', 'freetoolshub'); ?></h2>

            <div class="tool-faq">
                <?php foreach ($fth_guide['faqs'] as $fth_i => $fth_faq) : ?>
                    <details class="tool-faq-item" <?php echo $fth_i === 0 ? 'open' : ''; ?>>
                        <summary>
                            <h3><?php echo esc_html($fth_faq['question']); ?></h3>
                        </summary>
                        <p><?php echo esc_html($fth_faq['answer']); ?></p>
                    </details>
                <?php endforeach; ?>
            </div>

        </section>
    <?php endif; ?>

</div>
