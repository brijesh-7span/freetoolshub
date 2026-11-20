<footer class="site-footer">

    <div class="container footer-inner">
        <div class="footer-top">
            <div class="footer-brand">
                <a href="<?php echo esc_url(home_url('/')); ?>">
                    <img
                        src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo-white.png?ver=' . fth_asset_version('assets/images/logo-white.png')); ?>"
                        alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
                        width="1200"
                        height="152"
                        loading="lazy"
                        decoding="async">
                </a>

                <p>
                    Free online tools to make your everyday work easier.
                </p>
            </div>

            <div class="footer-navigation">

                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer-menu',
                    'container'      => false,
                    'menu_class'     => 'footer-menu',
                    'fallback_cb'    => false,
                ));
                ?>

            </div>
        </div>
        <p class="copyright">
            &copy; <?php echo esc_html(wp_date('Y')); ?>
            FreeToolsHub. All rights reserved.
        </p>

    </div>

</footer>

<?php wp_footer(); ?>

</body>

</html>