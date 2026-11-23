<?php

/**
 * Blog posts page (Settings → Reading → Posts page).
 * The Blog page's excerpt, if set, is used as the intro.
 */

get_header();

$fth_blog_page = (int) get_option('page_for_posts');
$fth_intro     = $fth_blog_page && has_excerpt($fth_blog_page)
    ? get_the_excerpt($fth_blog_page)
    : __('Tips, guides and how-tos for getting more done with free online tools.', 'freetoolshub');
?>

<main>

    <section class="hero-section page-hero">

        <div class="container">

            <?php get_template_part('template-parts/breadcrumbs'); ?>

            <span class="hero-label"><?php esc_html_e('BLOG', 'freetoolshub'); ?></span>

            <h1><?php echo esc_html($fth_blog_page ? get_the_title($fth_blog_page) : __('Blog', 'freetoolshub')); ?></h1>

            <p><?php echo esc_html($fth_intro); ?></p>

        </div>

    </section>

    <?php get_template_part('template-parts/post-list'); ?>

</main>

<?php get_footer(); ?>
