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
    <h2><?php echo esc_html(get_theme_mod('hero_title', 'Bem-vindo à Intranet')); ?></h2>
    <p><?php echo esc_html(get_theme_mod('hero_description', 'Um espaço pensado para você, servidor público de Três Corações, com acesso rápido a informações, documentos e serviços essenciais.')); ?></p>
    <a href="<?php echo esc_url(get_theme_mod('hero_btn_url', '/intranet')); ?>" class="btn-primario">
        <i class="fas fa-info-circle"></i> <?php echo esc_html(get_theme_mod('hero_btn_text', 'Saiba mais')); ?>
    </a>
</div>

        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/imagem-lateral.png" alt="" class="hero-pattern">
      </div>
    </section>

    <div class="container">
      <!-- Atalhos Rápidos -->
      <section class="atalhos-modernos">
    <div class="atalhos-grid">
        <?php
        $atalhos_defaults = array(
            1  => array('label' => 'Artes',              'url' => '/artes',                          'icon' => 'fa-solid fa-palette'),
            2  => array('label' => 'Empregos',           'url' => '/empregos',                       'icon' => 'fas fa-file-alt'),
            3  => array('label' => 'Eventos',            'url' => '/calendario',                     'icon' => 'fas fa-calendar-alt'),
            4  => array('label' => 'Formulários',        'url' => '/formularios',                    'icon' => 'fas fa-folder-open'),
            5  => array('label' => 'Helpdesk',           'url' => '/helpdesk',                       'icon' => 'fas fa-headset'),
            6  => array('label' => 'Notícias',           'url' => '/noticias',                       'icon' => 'fas fa-newspaper'),
            7  => array('label' => 'Recursos Humanos',   'url' => '/rh-recursos-humanos/',           'icon' => 'fas fa-money-check-dollar'),
            8  => array('label' => 'WebMail',            'url' => 'https://webmail.trescoracoes.mg.gov.br', 'icon' => 'fas fa-envelope'),
            9  => array('label' => 'WhatsApp',           'url' => 'https://web.whatsapp.com/',        'icon' => 'fab fa-whatsapp'),
            10 => array('label' => '',                   'url' => '',                                'icon' => 'fas fa-plus'),
            11 => array('label' => '',                   'url' => '',                                'icon' => 'fas fa-plus'),
            12 => array('label' => '',                   'url' => '',                                'icon' => 'fas fa-plus'),
            13 => array('label' => '',                   'url' => '',                                'icon' => 'fas fa-plus'),
            14 => array('label' => '',                   'url' => '',                                'icon' => 'fas fa-plus'),
            15 => array('label' => '',                   'url' => '',                                'icon' => 'fas fa-plus'),
        );
        for ($i = 1; $i <= 15; $i++) :
            $d = $atalhos_defaults[$i];
            $label = get_theme_mod("atalho_{$i}_label", $d['label']);
            if (empty($label)) continue;
            $url  = get_theme_mod("atalho_{$i}_url", $d['url']);
            $icon = get_theme_mod("atalho_{$i}_icon", $d['icon']);
            $target = strpos($url, 'http') === 0 ? ' target="_blank" rel="noopener noreferrer"' : '';
        ?>
        <a href="<?php echo esc_url($url); ?>" class="atalho-card"<?php echo $target; ?>>
            <i class="<?php echo esc_attr($icon); ?>"></i>
            <span><?php echo esc_html($label); ?></span>
        </a>
        <?php endfor; ?>
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