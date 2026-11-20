<?php get_header(); ?>

<main class="single-tool-section">

    <div class="container">

        <?php while (have_posts()) : the_post(); ?>

            <article <?php post_class('single-tool-content'); ?>>

                <?php get_template_part('template-parts/breadcrumbs'); ?>

                <div class="tool-detail-header">

                    <?php

                    $tool_icon = get_post_meta(
                        get_the_ID(),
                        '_fth_tool_icon',
                        true
                    );

                    ?>

                    <div class="tool-detail-icon">
                        <?php echo esc_html($tool_icon ?: '🔧'); ?>
                    </div>

                    <h1><?php the_title(); ?></h1>

                    <div class="tool-description">
                        <?php the_excerpt(); ?>
                    </div>

                </div>

                <?php

                // Tool shortcode from the FreeToolsHub Tools plugin, matched by "Tool Identifier"
                $tool_shortcode = class_exists('FTH_Shortcodes')
                    ? FTH_Shortcodes::get_tool_shortcode(get_post_meta(get_the_ID(), '_fth_tool_slug', true))
                    : '';

                ?>

                <div class="tool-functionality<?php echo $tool_shortcode ? ' has-tool' : ''; ?>">

                    <?php if ($tool_shortcode) : ?>

                        <?php echo do_shortcode('[' . $tool_shortcode . ']'); ?>

                    <?php else : ?>

                        <h2>Tool Coming Soon</h2>

                        <p>
                            The functionality for this tool
                            will be available soon.
                        </p>

                    <?php endif; ?>

                </div>

                <?php
                // About, features, how-to steps and FAQs (content from the FreeToolsHub Tools plugin)
                get_template_part('template-parts/tool-guide');
                ?>

            </article>

            <?php get_template_part('template-parts/related-tools'); ?>

        <?php endwhile; ?>

    </div>

</main>

<?php get_footer(); ?>