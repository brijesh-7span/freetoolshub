<?php

/**
 * Tools directory (/tools/) — also used for tool category pages,
 * which open the directory with that category pre-selected.
 */

get_header();

$is_category = is_tax('tool_category');
$term        = $is_category ? get_queried_object() : null;

// /tools/?category=slug — show that category's name in the heading too.
if (!$is_category && !empty($_GET['category'])) {
    $selected = get_term_by('slug', sanitize_title(wp_unslash($_GET['category'])), 'tool_category');
    $term     = $selected ? $selected : null;
}
?>

<main>

    <section class="hero-section archive-hero">

        <div class="container">

            <?php get_template_part('template-parts/breadcrumbs'); ?>

            <span class="hero-label">
                <?php esc_html_e('TOOLS DIRECTORY', 'freetoolshub'); ?>
            </span>

            <h1 data-directory-heading>
                <?php echo $term ? esc_html($term->name) : esc_html__('All Free Tools', 'freetoolshub'); ?>
            </h1>

            <?php $default_intro = __('Search and browse every free tool on FreeToolsHub — no sign-up, no downloads.', 'freetoolshub'); ?>

            <p data-directory-intro data-default-text="<?php echo esc_attr($default_intro); ?>">
                <?php
                echo esc_html($is_category && $term->description
                    ? wp_strip_all_tags($term->description)
                    : $default_intro);
                ?>
            </p>

        </div>

    </section>


    <section class="tools-section directory-section">

        <div class="container">

            <?php
            get_template_part('template-parts/tools-directory', null, array(
                'active_category' => $is_category ? $term->slug : '',
            ));
            ?>

        </div>

    </section>

</main>

<?php get_footer(); ?>
