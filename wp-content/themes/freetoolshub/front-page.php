<?php get_header(); ?>

<main>

    <!-- Hero Section -->
    <section class="hero-section">

        <div class="container">

            <span class="hero-label">
                SIMPLE. FREE. ONLINE.
            </span>

            <h1>
                Free Online Tools to Make Your Work Easier
            </h1>

            <p>
                Discover simple and powerful online tools
                for images, text, developers and everyday tasks.
            </p>

        </div>

    </section>


    <!-- Popular Tools -->
    <section class="tools-section">

        <div class="container">

            <div class="section-heading">

                <span>EXPLORE OUR TOOLS</span>

                <h2>Popular Free Tools</h2>

                <p>
                    Everything you need, all in one place.
                </p>

            </div>

            <div class="tools-grid">

                <?php

                $tools_query = new WP_Query(array(

                    'post_type'      => 'tool',

                    'posts_per_page' => 8,

                    'post_status'    => 'publish',

                    'meta_key'       => '_fth_is_featured',

                    'meta_value'     => '1',

                    'orderby'        => 'title',

                    'order'          => 'ASC',

                ));

                if ($tools_query->have_posts()) :

                    while ($tools_query->have_posts()) :

                        $tools_query->the_post();

                        $tool_icon = get_post_meta(
                            get_the_ID(),
                            '_fth_tool_icon',
                            true
                        );

                ?>

                        <div class="tool-card">

                            <div class="tool-icon">
                                <?php echo esc_html($tool_icon ?: '🔧'); ?>
                            </div>

                            <h3>
                                <?php the_title(); ?>
                            </h3>

                            <p>
                                <?php echo esc_html(
                                    get_the_excerpt()
                                ); ?>
                            </p>

                            <a href="<?php the_permalink(); ?>">
                                Explore Tool →
                            </a>

                        </div>

                    <?php

                    endwhile;

                    wp_reset_postdata();

                else :

                    ?>

                    <p>No featured tools available yet.</p>

                <?php endif; ?>

            </div>

        </div>

    </section>

</main>

<?php get_footer(); ?>