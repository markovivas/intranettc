<?php
/* Template Name: Todas as Notícias */
get_header(); ?>

<main>
  <div class="container">
    <section class="noticias-modernas">
      <div class="section-header">
        <h3><?php _e('Todas as Notícias', 'intranet'); ?></h3>
      </div>
      <div class="noticias-grid">
        <?php
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $args = array(
          'post_type' => 'post',
          'posts_per_page' => 9,
          'paged' => $paged,
          'post_status' => 'publish'
        );
        $query = new WP_Query($args);

        if ($query->have_posts()) :
          while ($query->have_posts()) : $query->the_post(); ?>
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
          <?php endwhile;
          the_posts_pagination(array(
            'mid_size' => 2,
            'prev_text' => __('Anterior', 'intranet'),
            'next_text' => __('Próximo', 'intranet'),
          ));
          wp_reset_postdata();
        else :
          echo '<p>' . __('Nenhuma notícia encontrada.', 'intranet') . '</p>';
        endif;
        ?>
      </div>
    </section>
  </div>
</main>

<?php get_footer(); ?>