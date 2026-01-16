<?php get_header(); ?>

<main>
    <div class="container">
        <section class="page-content">
            <?php
            if (have_posts()) :
                while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <h1><?php the_title(); ?></h1>
                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div>
                    </article>
                <?php endwhile;
            else :
                echo '<p>' . __('Nenhuma página encontrada.', 'intranet') . '</p>';
            endif;
            ?>
        </section>

        <?php get_sidebar(); ?>
    </div>
</main>

<?php get_footer(); ?>