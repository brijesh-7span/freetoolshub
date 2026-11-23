<?php

/**
 * Blog archives: categories, tags, authors and dates.
 * (Tools have their own archive-tool.php / taxonomy-tool_category.php.)
 */

get_header();
?>

<main>

    <section class="hero-section page-hero">

        <div class="container">

            <?php get_template_part('template-parts/breadcrumbs'); ?>

            <span class="hero-label"><?php esc_html_e('BLOG', 'freetoolshub'); ?></span>

            <h1><?php echo esc_html(wp_strip_all_tags(get_the_archive_title())); ?></h1>

            <?php if (get_the_archive_description()) : ?>
                <p><?php echo esc_html(wp_strip_all_tags(get_the_archive_description())); ?></p>
            <?php endif; ?>

        </div>

    </section>

    <?php get_template_part('template-parts/post-list'); ?>

</main>

<?php get_footer(); ?>
