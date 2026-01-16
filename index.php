<?php get_header(); ?>

<main>
  <section class="hero">
    <div class="container">
      <h2><?php _e('Emma, confira as Últimas Notícias da Empresa!', 'intranet'); ?></h2>
      <a href="#" class="btn"><?php _e('Leia Mais', 'intranet'); ?></a>
    </div>
    <div class="hero-image">
      <!-- Placeholder para imagem de Emma -->
    </div>
  </section>

  <section class="atalhos">
    <div class="container icons">
      <div class="icon"><i class="fas fa-headset"></i> <?php _e('Helpdesk', 'intranet'); ?></div>
      <div class="icon"><i class="fas fa-file-alt"></i> <?php _e('Relatórios', 'intranet'); ?></div>
      <div class="icon"><i class="fas fa-users"></i> <?php _e('Diretório', 'intranet'); ?></div>
      <div class="icon"><i class="fas fa-newspaper"></i> <?php _e('Notícias', 'intranet'); ?></div>
      <div class="icon"><i class="fas fa-money-check-dollar"></i> <?php _e('Folha de Pagamento', 'intranet'); ?></div>
      <div class="icon"><i class="fas fa-calendar-alt"></i> <?php _e('Eventos', 'intranet'); ?></div>
      <div class="icon"><i class="fas fa-folder-open"></i> <?php _e('Formulários', 'intranet'); ?></div>
    </div>
  </section>

  <section class="noticias">
    <div class="container">
      <h3><?php _e('Notícias & Eventos', 'intranet'); ?></h3>
      <?php
        if (have_posts()) :
          while (have_posts()) : the_post(); ?>
            <article>
              <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
              <p><?php the_excerpt(); ?></p>
            </article>
          <?php endwhile;
        else :
          echo '<p>' . __('Nenhum post encontrado.', 'intranet') . '</p>';
        endif;
      ?>
    </div>
  </section>
</main>

<?php get_footer(); ?>