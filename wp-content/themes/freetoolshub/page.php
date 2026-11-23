<?php

/**
 * Default page template (About, Contact, Privacy Policy, Terms, Disclaimer…).
 * The page excerpt, if set, is shown as the intro under the title.
 */

get_header();
?>

<main>

    <?php while (have_posts()) : the_post(); ?>

        <section class="hero-section page-hero">

            <div class="container">

                <?php get_template_part('template-parts/breadcrumbs'); ?>

                <h1><?php the_title(); ?></h1>

                <?php if (has_excerpt()) : ?>
                    <p><?php echo esc_html(get_the_excerpt()); ?></p>
                <?php endif; ?>

            </div>

        </section>

        <section class="page-section">

            <div class="container">

                <article <?php post_class('page-article entry-content'); ?>>
                    <?php the_content(); ?>
                </article>

            </div>

        </section>

    <?php endwhile; ?>

</main>

<?php get_footer(); ?>
