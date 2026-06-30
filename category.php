<?php get_header(); ?>

<main>
    <div class="container">
        <?php if (have_posts()) : ?>
            <section class="category-header">
                <div class="category-info">
                    <h1><?php single_cat_title(); ?></h1>
                    <?php if (category_description()) : ?>
                        <p class="category-description"><?php echo category_description(); ?></p>
                    <?php endif; ?>
                    <span class="category-count"><?php printf(__('%d %s', 'intranet'), $wp_query->found_posts, _n('artigo', 'artigos', $wp_query->found_posts, 'intranet')); ?></span>
                </div>
                <?php
                $cat_id = get_queried_object_id();
                $subcategories = wp_list_categories(array(
                    'child_of' => $cat_id,
                    'hide_empty' => true,
                    'title_li' => '',
                    'echo' => false,
                    'style' => 'none',
                    'separator' => '',
                ));
                if (!empty(trim($subcategories))) : ?>
                    <div class="category-subcategories">
                        <span class="subcats-label"><?php _e('Subcategorias:', 'intranet'); ?></span>
                        <?php wp_list_categories(array(
                            'child_of' => $cat_id,
                            'hide_empty' => true,
                            'title_li' => '',
                            'style' => 'list',
                            'depth' => 1,
                        )); ?>
                    </div>
                <?php endif; ?>
            </section>

            <div class="archive-layout">
                <section class="posts-grid-section">
                    <div class="noticias-grid">
                        <?php while (have_posts()) : the_post(); ?>
                            <article class="noticia-card">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="noticia-imagem">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_post_thumbnail('noticia-thumb'); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                <div class="noticia-conteudo">
                                    <div class="noticia-meta">
                                        <span><?php echo get_the_date(); ?></span>
                                        <span><?php the_author(); ?></span>
                                    </div>
                                    <h3 class="noticia-titulo"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                    <div class="noticia-resumo"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></div>
                                    <a href="<?php the_permalink(); ?>" class="leia-mais">
                                        <?php _e('Leia mais', 'intranet'); ?> <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>

                    <?php the_posts_pagination(array(
                        'mid_size' => 2,
                        'prev_text' => '<i class="fas fa-chevron-left"></i> ' . __('Anterior', 'intranet'),
                        'next_text' => __('Próximo', 'intranet') . ' <i class="fas fa-chevron-right"></i>',
                    )); ?>
                </section>

                <?php get_sidebar(); ?>
            </div>

        <?php else : ?>
            <section class="category-header">
                <h1><?php single_cat_title(); ?></h1>
                <p class="no-posts"><?php _e('Nenhum artigo encontrado nesta categoria.', 'intranet'); ?></p>
            </section>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
