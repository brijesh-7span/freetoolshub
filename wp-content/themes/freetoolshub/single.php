<?php

/**
 * Single blog post.
 */

get_header();
?>

<main>

    <?php while (have_posts()) : the_post(); ?>

        <?php $fth_categories = get_the_category(); ?>

        <section class="hero-section page-hero">

            <div class="container">

                <?php get_template_part('template-parts/breadcrumbs'); ?>

                <?php if ($fth_categories) : ?>
                    <a href="<?php echo esc_url(get_category_link($fth_categories[0])); ?>" class="hero-label">
                        <?php echo esc_html(mb_strtoupper($fth_categories[0]->name)); ?>
                    </a>
                <?php endif; ?>

                <h1><?php the_title(); ?></h1>

                <p class="post-meta">
                    <?php
                    printf(
                        /* translators: %s: author name */
                        esc_html__('By %s', 'freetoolshub'),
                        esc_html(get_the_author())
                    );
                    ?>
                    <span aria-hidden="true">·</span>
                    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
                    <span aria-hidden="true">·</span>
                    <?php echo esc_html(fth_reading_time()); ?>
                </p>

            </div>

        </section>

        <section class="page-section">

            <div class="container">

                <article <?php post_class('page-article'); ?>>

                    <?php if (has_post_thumbnail()) : ?>
                        <figure class="post-featured-image">
                            <?php the_post_thumbnail('large', array('fetchpriority' => 'high')); ?>
                        </figure>
                    <?php endif; ?>

                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>

                    <?php
                    the_tags(
                        '<p class="post-tags"><span>' . esc_html__('Tags:', 'freetoolshub') . '</span> ',
                        ', ',
                        '</p>'
                    );
                    ?>

                    <aside class="post-cta">
                        <h2><?php esc_html_e('Try our free online tools', 'freetoolshub'); ?></h2>
                        <p><?php esc_html_e('Word counters, image compressors, calculators and more — free, fast and no sign-up.', 'freetoolshub'); ?></p>
                        <a href="<?php echo esc_url(get_post_type_archive_link('tool')); ?>" class="btn-primary">
                            <?php esc_html_e('Browse all tools', 'freetoolshub'); ?>
                        </a>
                    </aside>

                    <?php
                    the_post_navigation(array(
                        'prev_text' => '<span>' . esc_html__('Previous post', 'freetoolshub') . '</span>%title',
                        'next_text' => '<span>' . esc_html__('Next post', 'freetoolshub') . '</span>%title',
                    ));
                    ?>

                </article>

            </div>

        </section>

    <?php endwhile; ?>

</main>

<?php get_footer(); ?>
