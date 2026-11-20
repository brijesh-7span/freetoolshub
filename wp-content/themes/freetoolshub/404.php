<?php

/**
 * 404 — page not found.
 * Full-screen, centred message with ways back into the site: tool search,
 * homepage / all tools buttons and links to popular tools.
 */

get_header();

$popular_tools = get_posts(array(
    'post_type'      => 'tool',
    'post_status'    => 'publish',
    'posts_per_page' => 6,
    'meta_key'       => '_fth_is_featured',
    'meta_value'     => '1',
    'orderby'        => array('menu_order' => 'ASC', 'title' => 'ASC'),
    'no_found_rows'  => true,
));
?>

<main class="error-404">

    <div class="error-404-inner">

        <p class="error-404-code" aria-hidden="true">
            <span>4</span><span class="error-404-zero">0</span><span>4</span>
        </p>

        <h1><?php esc_html_e('Page not found', 'freetoolshub'); ?></h1>

        <p class="error-404-text">
            <?php esc_html_e('Sorry, the page you were looking for doesn’t exist or has moved. Try searching for a tool, or pick one of the options below.', 'freetoolshub'); ?>
        </p>

        <form class="error-404-search" role="search" method="get" action="<?php echo esc_url(get_post_type_archive_link('tool')); ?>">
            <label for="error-404-search" class="screen-reader-text"><?php esc_html_e('Search tools', 'freetoolshub'); ?></label>
            <input
                type="search"
                id="error-404-search"
                name="q"
                placeholder="<?php esc_attr_e('Search for a tool…', 'freetoolshub'); ?>"
                autocomplete="off">
            <button type="submit"><?php esc_html_e('Search', 'freetoolshub'); ?></button>
        </form>

        <div class="error-404-actions">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-primary">
                <?php esc_html_e('Go to homepage', 'freetoolshub'); ?>
            </a>
            <a href="<?php echo esc_url(get_post_type_archive_link('tool')); ?>" class="btn-secondary">
                <?php esc_html_e('Browse all tools', 'freetoolshub'); ?>
            </a>
        </div>

        <?php if ($popular_tools) : ?>
            <nav class="error-404-popular" aria-label="<?php esc_attr_e('Popular tools', 'freetoolshub'); ?>">
                <h2><?php esc_html_e('Popular tools', 'freetoolshub'); ?></h2>
                <ul>
                    <?php foreach ($popular_tools as $tool) : ?>
                        <li>
                            <a href="<?php echo esc_url(get_permalink($tool)); ?>">
                                <span aria-hidden="true"><?php echo esc_html(fth_get_tool_icon($tool->ID)); ?></span>
                                <?php echo esc_html(get_the_title($tool)); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php endif; ?>

    </div>

</main>

<?php get_footer(); ?>
