<?php get_header(); ?>

<main class="grid-layout">
  <!-- Sidebar -->
  <aside class="sidebar-moderno">
    <nav>
      <ul class="menu-vertical">
        <li class="current-menu-item"><a href="#"><i class="fas fa-home"></i> <?php _e('Início', 'intranet'); ?></a></li>
        <li><a href="<?php echo site_url('/wp-admin/profile.php'); ?>"><i class="fas fa-user"></i> <?php _e('Meu Perfil', 'intranet'); ?></a></li>
        <li><a href="<?php echo site_url('/calendario'); ?>"><i class="fas fa-calendar-alt"></i> <?php _e('Calendário', 'intranet'); ?></a></li>
        <li><a href="<?php echo site_url('/documentos'); ?>"><i class="fas fa-file-alt"></i> <?php _e('Documentos', 'intranet'); ?></a></li>
        <li><a href="<?php echo site_url('/formularios'); ?>"><i class="fas fa-folder-open"></i> <?php _e('Formulários', 'intranet'); ?></a></li>
        <li><a href="<?php echo site_url('/empregos'); ?>"><i class="fas fa-briefcase"></i> <?php _e('Empregos', 'intranet'); ?></a></li>
        <li><a href="<?php echo site_url('/equipe'); ?>"><i class="fas fa-users"></i> <?php _e('Equipe', 'intranet'); ?></a></li>
        <li><a href="<?php echo site_url('/ramais'); ?>"><i class="fas fa-phone-alt"></i> <?php _e('Ramais', 'intranet'); ?></a></li>
        <li><a href="<?php echo site_url('/configuracoes'); ?>"><i class="fas fa-cog"></i> <?php _e('Configurações', 'intranet'); ?></a></li>
      </ul>
    </nav>
  </aside>

  <!-- Conteúdo Principal -->
  <div class="main-content">
    <section class="hero-moderno">
      <div class="container">
        <div class="hero-content">
    <h2><?php _e('Bem-vindo à Intranet', 'intranet'); ?></h2>
    <p><?php _e('Um espaço pensado para você, servidor público de Três Corações, com acesso rápido a informações, documentos e serviços essenciais.', 'intranet'); ?></p>
    <a href="/intranet" class="btn-primario">
        <i class="fas fa-info-circle"></i> <?php _e('Saiba mais', 'intranet'); ?>
    </a>
</div>

        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/imagem-lateral.png" alt="" class="hero-pattern">
      </div>
    </section>

    <div class="container">
      <!-- Atalhos Rápidos -->
      <section class="atalhos-modernos">
    <div class="atalhos-grid">
        <a href="/artes" class="atalho-card">
            <i class="fa-solid fa-palette"></i>
            <span><?php _e('Artes', 'intranet'); ?></span>
        </a>

        <a href="/empregos" class="atalho-card">
            <i class="fas fa-file-alt"></i>
            <span><?php _e('Empregos', 'intranet'); ?></span>
        </a>

        <a href="/calendario" class="atalho-card">
            <i class="fas fa-calendar-alt"></i>
            <span><?php _e('Eventos', 'intranet'); ?></span>
        </a>

        <a href="/formularios" class="atalho-card">
            <i class="fas fa-folder-open"></i>
            <span><?php _e('Formulários', 'intranet'); ?></span>
        </a>

        <a href="/helpdesk" class="atalho-card">
            <i class="fas fa-headset"></i>
            <span><?php _e('Helpdesk', 'intranet'); ?></span>
        </a>

        <a href="/noticias" class="atalho-card">
            <i class="fas fa-newspaper"></i>
            <span><?php _e('Notícias', 'intranet'); ?></span>
        </a>

        <a href="/rh-recursos-humanos/" class="atalho-card">
            <i class="fas fa-money-check-dollar"></i>
            <span><?php _e('Recursos Humanos', 'intranet'); ?></span>
        </a>

        <a href="https://webmail.trescoracoes.mg.gov.br" class="atalho-card" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-envelope"></i>
            <span><?php _e('WebMail', 'intranet'); ?></span>
        </a>

        <a href="https://web.whatsapp.com/" class="atalho-card" target="_blank" rel="noopener noreferrer">
            <i class="fab fa-whatsapp"></i>
            <span><?php _e('WhatsApp', 'intranet'); ?></span>
        </a>

    </div>
</section>


      <!-- Notícias -->
      <section class="noticias-modernas">
        <div class="section-header">
          <h3><?php _e('Últimas Notícias', 'intranet'); ?></h3>
          <a href="<?php echo get_permalink(get_page_by_path('noticias')); ?>" class="ver-tudo">
  <?php _e('Ver tudo', 'intranet'); ?> <i class="fas fa-arrow-right"></i>
</a>
        </div>
        <div class="noticias-eventos-wrapper">
          <div class="noticias-col">
            <div class="noticias-grid">
              <?php
                $args = array(
                  'post_type' => 'post',
                  'posts_per_page' => 4,
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
                  wp_reset_postdata();
                else :
                  echo '<p>' . __('Nenhuma notícia encontrada.', 'intranet') . '</p>';
                endif;
              ?>
            </div>
          </div>
          <div class="eventos-col">
            <div class="calendario-box">
              <?php echo do_shortcode('[mostra-calendario-widget]'); // Use um plugin ou shortcode de calendário ?>
            </div>
            <div class="proximos-eventos-box">
              
             <?php echo do_shortcode('[mostra-prox-eventos]'); // Use um plugin ou shortcode de calendário ?>
            </div>
          </div>
        </div>
      </section>

      <!-- Estatísticas -->
      <section class="estatisticas-modernas">
        <div class="section-header">
          <h3><?php _e('Métricas Importantes', 'intranet'); ?></h3>
        </div>

        <div class="stats-grid">
          <?php
            $args = array(
              'post_type' => 'metrica',
              'posts_per_page' => 5, // Limita a 5 métricas na página inicial
              'post_status' => 'publish',
              'orderby' => 'date',
              'order' => 'DESC'
            );
            $metricas_query = new WP_Query($args);
            if ($metricas_query->have_posts()) :
              while ($metricas_query->have_posts()) : $metricas_query->the_post();
                $valor = get_post_meta(get_the_ID(), '_metrica_valor', true);
                $periodo = get_post_meta(get_the_ID(), '_metrica_periodo', true);
                $icone = get_post_meta(get_the_ID(), '_metrica_icone', true);
          ?>
            <div class="stats-card">
              <div class="stats-icon-wrapper"><i class="<?php echo esc_attr($icone ?: 'fas fa-chart-bar'); ?>"></i></div>
              <h4><?php the_title(); ?></h4>
              <div class="stats-value"><?php echo esc_html($valor); ?></div>
              <div class="stats-period"><?php echo esc_html($periodo); ?></div>
            </div>
          <?php endwhile; wp_reset_postdata(); endif; ?>
        </div>
      </section>
    
      <?php echo do_shortcode('[temperatura]'); ?>

      <!-- Aniversariantes do Dia -->
      <section class="aniversariantes-dia">
        <div class="section-header">
          <h3><?php _e('Aniversariantes do Dia', 'intranet'); ?></h3>
        </div>
        <div class="aniversariantes-wrapper">
          <?php echo do_shortcode('[aniversariantes_do_dia]'); ?>
        </div>
      </section>
    </div>
  </div>
</main>
<?php get_footer(); ?>