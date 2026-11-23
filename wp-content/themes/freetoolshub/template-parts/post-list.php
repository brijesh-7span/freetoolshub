<?php

/**
 * Grid of blog post cards for the main query, with pagination
 * and a friendly empty state. Used by home.php and archive.php.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<section class="tools-section posts-section">

    <div class="container">

        <?php if (have_posts()) : ?>

            <div class="posts-grid">
                <?php
                while (have_posts()) :
                    the_post();
                    get_template_part('template-parts/post-card');
                endwhile;
                ?>
            </div>

            <?php
            the_posts_pagination(array(
                'mid_size'           => 1,
                'prev_text'          => __('‹ Previous', 'freetoolshub'),
                'next_text'          => __('Next ›', 'freetoolshub'),
                'screen_reader_text' => __('Posts navigation', 'freetoolshub'),
            ));
            ?>

        <?php else : ?>

            <div class="posts-empty">
                <h2><?php esc_html_e('New articles are on the way', 'freetoolshub'); ?></h2>
                <p><?php esc_html_e('We haven\'t published any posts here yet. In the meantime, try one of our free tools.', 'freetoolshub'); ?></p>
                <a href="<?php echo esc_url(get_post_type_archive_link('tool')); ?>" class="btn-primary">
                    <?php esc_html_e('Browse all tools', 'freetoolshub'); ?>
                </a>
            </div>

        <?php endif; ?>

    </div>

</section>
