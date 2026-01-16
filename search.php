<?php get_header(); ?>

<main>
    <div class="container">
        <section class="search-content">
            <h1>
                <?php
                /* Translators: %s is the search query */
                printf(__('Resultados da Busca por: %s', 'intranet'), '<span>' . esc_html(get_search_query()) . '</span>');
                ?>
            </h1>
            <?php
            if (have_posts()) :
                while (have_posts()) : the_post(); ?>
                    <article class="search-result">
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <div class="post-meta">
                            <span><?php _e('Publicado em:', 'intranet'); ?> <?php echo get_the_date(); ?></span>
                        </div>
                        <div class="entry-summary">
                            <?php the_excerpt(); ?>
                        </div>
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="post-thumbnail">
                                <?php the_post_thumbnail('noticia-thumb'); ?>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endwhile;
                the_posts_pagination(array(
                    'mid_size'  => 2,
                    'prev_text' => __('Anterior', 'intranet'),
                    'next_text' => __('Próximo', 'intranet'),
                ));
            else :
                echo '<p>' . __('Nenhum resultado encontrado. Tente outra busca.', 'intranet') . '</p>';
            endif;
            ?>
        </section>

        <?php get_sidebar(); ?>
    </div>
</main>

<?php get_footer(); ?>