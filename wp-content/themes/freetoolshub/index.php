<?php get_header(); ?>

<main class="container default-content">

    <?php if (have_posts()) : ?>

        <?php while (have_posts()) : the_post(); ?>

            <article <?php post_class(); ?>>

                <h1>
                    <a href="<?php the_permalink(); ?>">
                        <?php the_title(); ?>
                    </a>
                </h1>

                <div class="entry-content">
                    <?php the_content(); ?>
                </div>

            </article>

        <?php endwhile; ?>

    <?php else : ?>

        <h2>No content found.</h2>

    <?php endif; ?>

</main>

<?php get_footer(); ?>