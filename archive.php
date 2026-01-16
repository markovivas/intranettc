<?php get_header(); ?>

<main>
    <div class="container">
        <section class="archive-content">
            <h1>
                <?php
                if (is_category()) {
                    printf(__('Categoria: %s', 'intranet'), single_cat_title('', false));
                } elseif (is_tag()) {
                    printf(__('Tag: %s', 'intranet'), single_tag_title('', false));
                } elseif (is_day()) {
                    printf(__('Arquivo Diário: %s', 'intranet'), get_the_date());
                } elseif (is_month()) {
                    printf(__('Arquivo Mensal: %s', 'intranet'), get_the_date('F Y'));
                } elseif (is_year()) {
                    printf(__('Arquivo Anual: %s', 'intranet'), get_the_date('Y'));
                } else {
                    _e('Arquivo', 'intranet');
                }
                ?>
            </h1>
            <?php
            if (have_posts()) :
                while (have_posts()) : the_post(); ?>
                    <article class="archive-post">
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
                    'mid_size' => 2,
                    'prev_text' => __('Anterior', 'intranet'),
                    'next_text' => __('Próximo', 'intranet'),
                ));
            else :
                echo '<p>' . __('Nenhum post encontrado.', 'intranet') . '</p>';
            endif;
            ?>
        </section>

        <?php get_sidebar(); ?>
    </div>
</main>

<?php get_footer(); ?>