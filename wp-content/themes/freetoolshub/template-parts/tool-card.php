<?php

/**
 * Tool card — used inside a tools loop
 *
 * @param array $args {
 *     Optional.
 *     @type WP_Term[] $categories Categories to show as labels on the card.
 *     @type bool      $hidden     Render the card hidden (used by the tools directory filters).
 *     @type array     $attributes Extra HTML attributes for the card element (name => value).
 * }
 */

if (!defined('ABSPATH')) {
    exit;
}

$fth_card_categories = isset($args['categories']) ? $args['categories'] : array();
$fth_card_attributes = isset($args['attributes']) ? $args['attributes'] : array();
?>

<article
    <?php post_class('tool-card'); ?>
    <?php foreach ($fth_card_attributes as $fth_name => $fth_value) : ?>
        <?php echo esc_attr($fth_name); ?>="<?php echo esc_attr($fth_value); ?>"
    <?php endforeach; ?>
    <?php echo !empty($args['hidden']) ? 'hidden' : ''; ?>>

    <a href="<?php the_permalink(); ?>" class="tool-icon" aria-hidden="true" tabindex="-1">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('thumbnail', array('class' => 'tool-image', 'alt' => '')); ?>
        <?php else : ?>
            <?php echo esc_html(fth_get_tool_icon()); ?>
        <?php endif; ?>
    </a>

    <?php if ($fth_card_categories) : ?>
        <div class="tool-card-categories">
            <?php foreach ($fth_card_categories as $fth_term) : ?>
                <span class="tool-card-category"><?php echo esc_html($fth_term->name); ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h3>
        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
    </h3>

    <?php if (has_excerpt()) : ?>
        <p><?php echo esc_html(get_the_excerpt()); ?></p>
    <?php else : ?>
        <p><?php echo esc_html(wp_trim_words(get_the_content(), 12)); ?></p>
    <?php endif; ?>

    <a href="<?php the_permalink(); ?>" class="tool-link">
        <?php esc_html_e('Explore Tool →', 'freetoolshub'); ?>
    </a>

</article>
