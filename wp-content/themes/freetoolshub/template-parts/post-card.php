<?php

/**
 * Blog post card (inside the loop).
 */

if (!defined('ABSPATH')) {
    exit;
}

$fth_categories = get_the_category();
?>

<article <?php post_class('post-card'); ?>>

    <a href="<?php the_permalink(); ?>" class="post-card-image" tabindex="-1" aria-hidden="true">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('medium_large', array('loading' => 'lazy', 'alt' => '')); ?>
        <?php else : ?>
            <span class="post-card-placeholder"><?php echo esc_html(mb_substr(get_the_title(), 0, 1)); ?></span>
        <?php endif; ?>
    </a>

    <div class="post-card-body">

        <?php if ($fth_categories) : ?>
            <span class="post-card-category"><?php echo esc_html($fth_categories[0]->name); ?></span>
        <?php endif; ?>

        <h2 class="post-card-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h2>

        <p class="post-card-excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 22)); ?></p>

        <p class="post-meta">
            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
            <span aria-hidden="true">·</span>
            <?php echo esc_html(fth_reading_time()); ?>
        </p>

    </div>

</article>
