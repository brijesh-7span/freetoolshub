<?php

/**
 * Tools directory — search box, category filters and the full list of tools.
 *
 * Every published tool is rendered; non-matching ones get the `hidden` attribute.
 * Filtering works without JavaScript (via ?q= and ?category=), and
 * assets/js/tools-directory.js upgrades it to instant filtering.
 *
 * @param array $args {
 *     @type string $active_category Category slug to pre-select (e.g. on a category page).
 * }
 */

if (!defined('ABSPATH')) {
    exit;
}

$fth_directory_url = get_post_type_archive_link('tool');

$fth_query    = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
$fth_category = isset($_GET['category'])
    ? sanitize_title(wp_unslash($_GET['category']))
    : (isset($args['active_category']) ? $args['active_category'] : '');

$fth_categories = get_terms(array(
    'taxonomy'   => 'tool_category',
    'hide_empty' => true,
));

if (is_wp_error($fth_categories)) {
    $fth_categories = array();
}

// Ignore unknown category slugs.
if ($fth_category && !in_array($fth_category, wp_list_pluck($fth_categories, 'slug'), true)) {
    $fth_category = '';
}

$fth_tools = new WP_Query(array(
    'post_type'      => 'tool',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => array('menu_order' => 'ASC', 'title' => 'ASC'),
    'no_found_rows'  => true,
));

$fth_total   = $fth_tools->post_count;
$fth_visible = 0;

?>

<div class="tools-directory" data-tools-directory data-base-url="<?php echo esc_url($fth_directory_url); ?>">

    <!-- Search + filters (a plain GET form, so it works without JavaScript) -->
    <form class="directory-controls" role="search" method="get" action="<?php echo esc_url($fth_directory_url); ?>" data-directory-form>

        <div class="directory-search">
            <label for="tools-directory-search" class="screen-reader-text"><?php esc_html_e('Search tools', 'freetoolshub'); ?></label>
            <span class="directory-search-icon" aria-hidden="true">🔍</span>
            <input
                type="search"
                id="tools-directory-search"
                name="q"
                value="<?php echo esc_attr($fth_query); ?>"
                placeholder="<?php esc_attr_e('Search tools by name or description…', 'freetoolshub'); ?>"
                autocomplete="off"
                data-directory-search>
            <button type="submit" class="directory-search-submit" data-directory-submit>
                <?php esc_html_e('Search', 'freetoolshub'); ?>
            </button>
        </div>

        <?php if ($fth_categories) : ?>

            <input type="hidden" name="category" value="<?php echo esc_attr($fth_category); ?>" data-directory-category-input>

            <div class="directory-filters" role="group" aria-label="<?php esc_attr_e('Filter by category', 'freetoolshub'); ?>">

                <a
                    href="<?php echo esc_url($fth_query ? add_query_arg('q', $fth_query, $fth_directory_url) : $fth_directory_url); ?>"
                    class="directory-filter<?php echo $fth_category === '' ? ' is-active' : ''; ?>"
                    data-directory-filter=""
                    data-label="<?php esc_attr_e('All Free Tools', 'freetoolshub'); ?>"
                    <?php echo $fth_category === '' ? 'aria-current="true"' : ''; ?>>
                    <?php esc_html_e('All', 'freetoolshub'); ?>
                    <span class="directory-filter-count"><?php echo esc_html($fth_total); ?></span>
                </a>

                <?php foreach ($fth_categories as $fth_term) : ?>
                    <?php
                    $fth_filter_url = add_query_arg(array_filter(array(
                        'q'        => $fth_query,
                        'category' => $fth_term->slug,
                    )), $fth_directory_url);
                    $fth_is_active = $fth_category === $fth_term->slug;
                    ?>
                    <a
                        href="<?php echo esc_url($fth_filter_url); ?>"
                        class="directory-filter<?php echo $fth_is_active ? ' is-active' : ''; ?>"
                        data-directory-filter="<?php echo esc_attr($fth_term->slug); ?>"
                        data-label="<?php echo esc_attr($fth_term->name); ?>"
                        <?php echo $fth_is_active ? 'aria-current="true"' : ''; ?>>
                        <span aria-hidden="true"><?php echo esc_html(fth_get_category_icon($fth_term->term_id)); ?></span>
                        <?php echo esc_html($fth_term->name); ?>
                        <span class="directory-filter-count"><?php echo esc_html($fth_term->count); ?></span>
                    </a>
                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </form>


    <!-- Results -->
    <?php if ($fth_tools->have_posts()) : ?>

        <h2 class="screen-reader-text"><?php esc_html_e('Tools', 'freetoolshub'); ?></h2>

        <div class="tools-grid" data-directory-grid>

            <?php
            while ($fth_tools->have_posts()) :
                $fth_tools->the_post();

                $fth_terms      = get_the_terms(get_the_ID(), 'tool_category');
                $fth_terms      = ($fth_terms && !is_wp_error($fth_terms)) ? $fth_terms : array();
                $fth_term_slugs = wp_list_pluck($fth_terms, 'slug');

                // Text the search matches against: name, description and category names.
                $fth_search_text = fth_normalize_search_text(implode(' ', array_merge(
                    array(get_the_title(), get_the_excerpt()),
                    wp_list_pluck($fth_terms, 'name')
                )));

                $fth_matches = fth_tool_matches($fth_search_text, $fth_term_slugs, $fth_query, $fth_category);

                if ($fth_matches) {
                    $fth_visible++;
                }

                get_template_part('template-parts/tool-card', null, array(
                    'categories' => $fth_terms,
                    'hidden'     => !$fth_matches,
                    'attributes' => array(
                        'data-directory-item' => '',
                        'data-categories'     => implode(' ', $fth_term_slugs),
                        'data-search'         => $fth_search_text,
                    ),
                ));
            endwhile;

            wp_reset_postdata();
            ?>

        </div>

    <?php endif; ?>

    <p class="directory-status" role="status" aria-live="polite" data-directory-status>
        <?php echo esc_html(fth_directory_status_text($fth_visible, $fth_total)); ?>
    </p>

    <!-- No results -->
    <div class="directory-empty" data-directory-empty <?php echo $fth_visible ? 'hidden' : ''; ?>>
        <span class="directory-empty-icon" aria-hidden="true">🔎</span>
        <h2><?php esc_html_e('No tools found', 'freetoolshub'); ?></h2>
        <p data-directory-empty-text>
            <?php
            if ($fth_total === 0) {
                esc_html_e('No tools have been published yet. Please check back soon.', 'freetoolshub');
            } else {
                esc_html_e('No tools match your search. Try a different word or browse all categories.', 'freetoolshub');
            }
            ?>
        </p>
        <?php if ($fth_total > 0) : ?>
            <a href="<?php echo esc_url($fth_directory_url); ?>" class="btn-primary" data-directory-reset>
                <?php esc_html_e('Show all tools', 'freetoolshub'); ?>
            </a>
        <?php endif; ?>
    </div>

</div>
