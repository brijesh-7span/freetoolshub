<?php

/**
 * Related tools — other tools from the same category, topped up with other tools.
 * Helps visitors (and search engines) discover more of the site.
 */

if (!defined('ABSPATH')) {
    exit;
}

$fth_current_id = get_the_ID();
$fth_limit      = 3;
$fth_terms      = get_the_terms($fth_current_id, 'tool_category');
$fth_term_ids   = ($fth_terms && !is_wp_error($fth_terms)) ? wp_list_pluck($fth_terms, 'term_id') : array();

$fth_related = $fth_term_ids ? get_posts(array(
    'post_type'      => 'tool',
    'post_status'    => 'publish',
    'posts_per_page' => $fth_limit,
    'post__not_in'   => array($fth_current_id),
    'orderby'        => array('menu_order' => 'ASC', 'title' => 'ASC'),
    'tax_query'      => array(array('taxonomy' => 'tool_category', 'terms' => $fth_term_ids)),
)) : array();

if (count($fth_related) < $fth_limit) {
    $fth_related = array_merge($fth_related, get_posts(array(
        'post_type'      => 'tool',
        'post_status'    => 'publish',
        'posts_per_page' => $fth_limit - count($fth_related),
        'post__not_in'   => array_merge(array($fth_current_id), wp_list_pluck($fth_related, 'ID')),
        'orderby'        => array('menu_order' => 'ASC', 'title' => 'ASC'),
    )));
}

if (!$fth_related) {
    return;
}
?>

<section class="related-tools" aria-labelledby="related-tools-title">

    <h2 id="related-tools-title"><?php esc_html_e('More free tools', 'freetoolshub'); ?></h2>

    <div class="tools-grid">
        <?php
        global $post;

        foreach ($fth_related as $post) :
            setup_postdata($post);
            get_template_part('template-parts/tool-card');
        endforeach;

        wp_reset_postdata();
        ?>
    </div>

    <p class="related-tools-all">
        <a href="<?php echo esc_url(get_post_type_archive_link('tool')); ?>"><?php esc_html_e('Browse all tools →', 'freetoolshub'); ?></a>
    </p>

</section>
