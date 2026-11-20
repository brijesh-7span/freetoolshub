<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

    <?php wp_body_open(); ?>

    <header class="site-header">

        <div class="container header-inner">

            <div class="site-logo">
                <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                    <img
                        src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo-dark.png?ver=' . fth_asset_version('assets/images/logo-dark.png')); ?>"
                        alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
                        width="1200"
                        height="152"
                        fetchpriority="high">
                </a>
            </div>

            <nav class="main-navigation">

                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary-menu',
                    'container'      => false,
                    'menu_class'     => 'nav-menu',
                    'fallback_cb'    => false,
                ));
                ?>

            </nav>

        </div>

    </header>