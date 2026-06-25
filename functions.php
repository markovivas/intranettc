<?php
function intranet_scripts() {
    wp_enqueue_style('intranet-style', get_stylesheet_uri());
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');

    // Adiciona o script para o menu mobile
    wp_enqueue_script(
        'intranet-mobile-menu',
        get_template_directory_uri() . '/assets/js/mobile-menu.js',
        array(), // dependências
        '1.0.0', // versão
        true // carregar no footer
    );
}
add_action('wp_enqueue_scripts', 'intranet_scripts');

function intranet_menus() {
    register_nav_menus(array(
        'menu-principal' => __('Menu Principal', 'intranet')
    ));
}
add_action('after_setup_theme', 'intranet_menus');

// Suporte a thumbnails
add_theme_support('post-thumbnails');

// Suporte a tradução
load_theme_textdomain('intranet', get_template_directory() . '/languages');

// Registrar áreas de widgets
function intranet_widgets() {
    register_sidebar(array(
        'name'          => __('Barra Lateral', 'intranet'),
        'id'            => 'sidebar-1',
        'description'   => __('Adicione widgets para a barra lateral.', 'intranet'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
    register_sidebar(array(
        'name'          => __('Rodapé', 'intranet'),
        'id'            => 'footer-1',
        'description'   => __('Adicione widgets ao rodapé.', 'intranet'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'intranet_widgets');

// Adicionar suporte a título dinâmico
add_theme_support('title-tag');

// Personalizar o tamanho das thumbnails
add_image_size('noticia-thumb', 300, 200, true);

// Suporte a WebP
function webp_upload_mimes($existing_mimes) {
    $existing_mimes['webp'] = 'image/webp';
    return $existing_mimes;
}
add_filter('mime_types', 'webp_upload_mimes');

// Breadcrumbs
function intranet_breadcrumbs() {
    // Implementação dos breadcrumbs
}

// Lazy loading
add_filter('wp_get_attachment_image_attributes', function($attr) {
    $attr['loading'] = 'lazy';
    return $attr;
});

// Tempo de leitura estimado
if (!function_exists('estimated_reading_time')) {
    function estimated_reading_time() {
        $content = get_post_field('post_content', get_the_ID());
        $word_count = str_word_count(strip_tags($content));
        $readingtime = ceil($word_count / 200);
        return ($readingtime == 0) ? 'Menos de 1' : $readingtime;
    }
}

// Reset de CSS - Shortcode
add_filter('do_shortcode_tag', function($output, $tag) {
    if ($tag === 'mostra-calendario') {
        return '<div style="all: initial !important;">' . $output . '</div>';
    }
    return $output;
}, 10, 2);

// Função auxiliar para mapear ícones do OpenWeatherMap para Font Awesome
function intranet_get_open_meteo_icon_class($wmo_code, $is_day = 1) {
    $icon_map = [
        0 => ['day' => 'fas fa-sun', 'night' => 'fas fa-moon'], // Clear sky
        1 => ['day' => 'fas fa-cloud-sun', 'night' => 'fas fa-cloud-moon'], // Mainly clear
        2 => ['day' => 'fas fa-cloud', 'night' => 'fas fa-cloud'], // partly cloudy
        3 => ['day' => 'fas fa-cloud', 'night' => 'fas fa-cloud'], // overcast
        45 => ['day' => 'fas fa-smog', 'night' => 'fas fa-smog'], // Fog
        48 => ['day' => 'fas fa-smog', 'night' => 'fas fa-smog'], // depositing rime fog
        51 => ['day' => 'fas fa-cloud-rain', 'night' => 'fas fa-cloud-rain'], // Drizzle: Light
        53 => ['day' => 'fas fa-cloud-rain', 'night' => 'fas fa-cloud-rain'], // Drizzle: moderate
        55 => ['day' => 'fas fa-cloud-rain', 'night' => 'fas fa-cloud-rain'], // Drizzle: dense
        61 => ['day' => 'fas fa-cloud-showers-heavy', 'night' => 'fas fa-cloud-showers-heavy'], // Rain: Slight
        63 => ['day' => 'fas fa-cloud-showers-heavy', 'night' => 'fas fa-cloud-showers-heavy'], // Rain: moderate
        65 => ['day' => 'fas fa-cloud-showers-heavy', 'night' => 'fas fa-cloud-showers-heavy'], // Rain: heavy
        80 => ['day' => 'fas fa-cloud-showers-heavy', 'night' => 'fas fa-cloud-showers-heavy'], // Rain showers: Slight
        81 => ['day' => 'fas fa-cloud-showers-heavy', 'night' => 'fas fa-cloud-showers-heavy'], // Rain showers: moderate
        82 => ['day' => 'fas fa-cloud-showers-heavy', 'night' => 'fas fa-cloud-showers-heavy'], // Rain showers: violent
        71 => ['day' => 'fas fa-snowflake', 'night' => 'fas fa-snowflake'], // Snow fall: Slight
        73 => ['day' => 'fas fa-snowflake', 'night' => 'fas fa-snowflake'], // Snow fall: moderate
        75 => ['day' => 'fas fa-snowflake', 'night' => 'fas fa-snowflake'], // Snow fall: heavy
        95 => ['day' => 'fas fa-bolt', 'night' => 'fas fa-bolt'], // Thunderstorm: Slight or moderate
        96 => ['day' => 'fas fa-bolt', 'night' => 'fas fa-bolt'], // Thunderstorm with slight hail
        99 => ['day' => 'fas fa-bolt', 'night' => 'fas fa-bolt'], // Thunderstorm with heavy hail
    ];

    if (isset($icon_map[$wmo_code])) {
        return $is_day ? $icon_map[$wmo_code]['day'] : $icon_map[$wmo_code]['night'];
    }
    return 'fas fa-question-circle'; // Ícone padrão
}

// Shortcode para Previsão do Tempo
function intranet_previsao_tempo_shortcode() {
    // --- CONFIGURAÇÕES DA API ---
    $latitude = '-21.79';  // Latitude de Três Corações, MG
    $longitude = '-45.25'; // Longitude de Três Corações, MG
    $timezone = 'America/Sao_Paulo';

    // Tenta obter os dados do cache
    $weather_data = get_transient('intranet_weather_data');

    if (false === $weather_data) {
        // Se não houver cache, busca na API
        $api_url = "https://api.open-meteo.com/v1/forecast?latitude={$latitude}&longitude={$longitude}&current_weather=true&daily=weathercode,temperature_2m_max,temperature_2m_min&timezone={$timezone}&forecast_days=5";

        $response = wp_remote_get($api_url);

        if (is_wp_error($response)) {
            return '<!-- Erro ao buscar dados do tempo -->';
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        // Verifica se a chave de API foi inserida e se a resposta da API é válida
        if (wp_remote_retrieve_response_code($response) !== 200 || !isset($data->current_weather)) {
            ob_start();
            ?>
            <section class="previsao-tempo-moderna">
                <div class="section-header"><h3><?php _e('Tempo em Três Corações', 'intranet'); ?></h3></div>
                <div class="tempo-container" style="background: #f8d7da; color: #721c24; display: block; text-align: center;">
                    <p style="margin: 0; font-weight: 500;">
                        <?php _e('Não foi possível carregar os dados do tempo. Tente novamente mais tarde.', 'intranet'); ?>
                    </p>
                </div>
            </section>
            <?php
            return ob_get_clean();
        }

        $weather_data = $data;

        // Salva os dados no cache por 1 hora (3600 segundos)
        set_transient('intranet_weather_data', $weather_data, 3600);
    }

    // Extrai os dados para usar no HTML
    $current = $weather_data->current_weather;
    $daily = $weather_data->daily;

    // Verificação de segurança para garantir que os dados existem
    if (!isset($current) || !isset($daily)) {
        return '<!-- Dados do tempo inválidos recebidos da API. -->';
    }

    $temp_atual = round($current->temperature);
    $temp_max = round($daily->temperature_2m_max[0]);
    $temp_min = round($daily->temperature_2m_min[0]);
    $icon_class_atual = intranet_get_open_meteo_icon_class($current->weathercode, $current->is_day);
    
    // Descrições para os códigos de clima (simplificado)
    $wmo_descriptions = [
        0 => 'Céu limpo', 1 => 'Quase limpo', 2 => 'Parcialmente nublado', 3 => 'Nublado',
        45 => 'Nevoeiro', 48 => 'Nevoeiro',
        51 => 'Garoa leve', 53 => 'Garoa', 55 => 'Garoa forte',
        61 => 'Chuva fraca', 63 => 'Chuva', 65 => 'Chuva forte',
        80 => 'Pancadas de chuva', 81 => 'Pancadas de chuva', 82 => 'Pancadas de chuva',
        71 => 'Neve', 73 => 'Neve', 75 => 'Neve',
        95 => 'Trovoada', 96 => 'Trovoada', 99 => 'Trovoada'
    ];
    $condicao = isset($wmo_descriptions[$current->weathercode]) ? $wmo_descriptions[$current->weathercode] : 'Condição desconhecida';

    // Dias da semana em português
    $dias_semana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

    ob_start();
    ?>
    <style>
        /* Estilos da Previsão do Tempo */
        .previsao-tempo-moderna {
            margin-bottom: 50px;
        }
        .tempo-container {
            display: flex;
            gap: 20px;
            background: linear-gradient(135deg, var(--azul-claro), var(--azul-brilhante));
            color: var(--branco);
            padding: 30px;
            border-radius: var(--borda-radius);
            box-shadow: var(--sombra);
        }
        .tempo-atual {
            flex-basis: 40%;
            border-right: 1px solid rgba(255, 255, 255, 0.3);
            padding-right: 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .tempo-atual-main {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 10px;
        }
        .tempo-icon-grande {
            font-size: 4rem;
            color: var(--amarelo);
        }
        .temperatura-grande {
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1;
        }
        .tempo-atual-detalhes {
            padding-left: 76px; /* Alinha com a temperatura */
        }
        .condicao-texto {
            font-size: 1.2rem;
            font-weight: 500;
            margin-bottom: 5px;
        }
        .min-max {
            font-size: 1rem;
            opacity: 0.9;
        }
        .tempo-forecast {
            flex-basis: 60%;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            align-items: center;
        }
        .forecast-dia {
            text-align: center;
            background: rgba(255, 255, 255, 0.15);
            padding: 15px 10px;
            border-radius: var(--borda-radius);
            transition: var(--transicao);
        }
        .forecast-dia:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-5px);
        }
        .dia-semana {
            font-weight: 600;
            margin-bottom: 10px;
        }
        .forecast-dia i {
            font-size: 1.8rem;
            margin-bottom: 10px;
            color: var(--amarelo);
        }
        .forecast-dia .fa-cloud-sun { color: #f8f9fa; }
        .forecast-dia .fa-cloud-showers-heavy { color: #e9ecef; }
        .temp-forecast {
            font-weight: 500;
        }
        @media (max-width: 768px) {
            .tempo-container { flex-direction: column; }
            .tempo-atual { border-right: none; border-bottom: 1px solid rgba(255, 255, 255, 0.3); padding-right: 0; padding-bottom: 20px; }
            .tempo-forecast { grid-template-columns: repeat(2, 1fr); }
        }
        /* Dark Mode desabilitado */
    </style>
    <section class="previsao-tempo-moderna">
      <div class="section-header"><h3><?php _e('Tempo em Três Corações', 'intranet'); ?></h3></div>
        <div class="tempo-container">
            <div class="tempo-atual">
                <div class="tempo-atual-main">
                    <i class="<?php echo esc_attr($icon_class_atual); ?> tempo-icon-grande"></i>
                    <div class="temperatura-grande"><?php echo esc_html($temp_atual); ?>°C</div>
                </div>
                <div class="tempo-atual-detalhes">
                    <div class="condicao-texto"><?php echo esc_html($condicao); ?></div>
                    <div class="min-max">Máx: <?php echo esc_html($temp_max); ?>° / Mín: <?php echo esc_html($temp_min); ?>°</div>
                </div>
            </div>
            <div class="tempo-forecast">
                <?php for ($i = 1; $i < 5; $i++) : // Começa do dia seguinte (índice 1) e pega 4 dias ?>
                <?php if(isset($daily->time[$i])): ?>
                    <div class="forecast-dia">
                        <div class="dia-semana"><?php echo esc_html($dias_semana[date('w', strtotime($daily->time[$i]))]); ?></div>
                        <i class="<?php echo esc_attr(intranet_get_open_meteo_icon_class($daily->weathercode[$i])); ?>"></i>
                        <div class="temp-forecast"><?php echo esc_html(round($daily->temperature_2m_max[$i])); ?>°/<?php echo esc_html(round($daily->temperature_2m_min[$i])); ?>°</div>
                    </div>
                <?php endif; ?>
                <?php endfor; ?>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('temperatura', 'intranet_previsao_tempo_shortcode');

/*
 * ===================================================================
 * Bloquear site para visitantes não logados
 * ===================================================================
 */

function bloquear_site_para_visitantes() {
    // Se o usuário não estiver logado e não estiver em uma página de administração,
    // redireciona para a tela de login.
    if ( !is_user_logged_in() && !is_admin() ) {
        auth_redirect(); // envia para tela de login
    }
}
add_action( 'template_redirect', 'bloquear_site_para_visitantes' );

/*
 * ===================================================================
 * Custom Post Type: Métricas Importantes
 * ===================================================================
 */

function intranet_cpt_metricas() {
    $labels = array(
        'name'               => _x('Métricas', 'post type general name', 'intranet'),
        'singular_name'      => _x('Métrica', 'post type singular name', 'intranet'),
        'menu_name'          => _x('Métricas', 'admin menu', 'intranet'),
        'name_admin_bar'     => _x('Métrica', 'add new on admin bar', 'intranet'),
        'add_new'            => _x('Adicionar Nova', 'metrica', 'intranet'),
        'add_new_item'       => __('Adicionar Nova Métrica', 'intranet'),
        'new_item'           => __('Nova Métrica', 'intranet'),
        'edit_item'          => __('Editar Métrica', 'intranet'),
        'view_item'          => __('Ver Métrica', 'intranet'),
        'all_items'          => __('Todas as Métricas', 'intranet'),
        'search_items'       => __('Buscar Métricas', 'intranet'),
        'parent_item_colon'  => __('Métrica Pai:', 'intranet'),
        'not_found'          => __('Nenhuma métrica encontrada.', 'intranet'),
        'not_found_in_trash' => __('Nenhuma métrica encontrada na lixeira.', 'intranet')
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false, // Não serão visíveis publicamente como posts normais
        'publicly_queryable' => false,
        'show_ui'            => true, // Mostrar no painel de admin
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'metrica'),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 20, // Posição no menu do admin
        'menu_icon'          => 'dashicons-chart-line', // Ícone
        'supports'           => array('title'), // Suporta apenas título
    );

    register_post_type('metrica', $args);
}
add_action('init', 'intranet_cpt_metricas');

// Adicionar Meta Boxes (campos personalizados) para as Métricas
function intranet_metricas_meta_boxes() {
    add_meta_box(
        'intranet_metrica_details',
        __('Detalhes da Métrica', 'intranet'),
        'intranet_metrica_details_callback',
        'metrica', // Adicionar ao CPT 'metrica'
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'intranet_metricas_meta_boxes');

// Callback para renderizar os campos da Meta Box
function intranet_metrica_details_callback($post) {
    wp_nonce_field('intranet_save_metrica_details', 'intranet_metrica_nonce');
    $valor = get_post_meta($post->ID, '_metrica_valor', true);
    $periodo = get_post_meta($post->ID, '_metrica_periodo', true);
    $icone = get_post_meta($post->ID, '_metrica_icone', true);
    ?>
    <p>
        <label for="metrica_valor"><?php _e('Valor (Ex: 22 toneladas, 487, 5)', 'intranet'); ?></label><br>
        <input type="text" id="metrica_valor" name="metrica_valor" value="<?php echo esc_attr($valor); ?>" style="width:100%;">
    </p>
    <p>
        <label for="metrica_periodo"><?php _e('Período/Descrição (Ex: em julho, nesse mês)', 'intranet'); ?></label><br>
        <input type="text" id="metrica_periodo" name="metrica_periodo" value="<?php echo esc_attr($periodo); ?>" style="width:100%;">
    </p>
    <p>
        <label for="metrica_icone"><?php _e('Ícone (Classe do Font Awesome, Ex: fas fa-users)', 'intranet'); ?></label><br>
        <input type="text" id="metrica_icone" name="metrica_icone" value="<?php echo esc_attr($icone); ?>" style="width:100%;">
    </p>
    <?php
}

// Salvar os dados da Meta Box
function intranet_save_metrica_details($post_id) {
    if (!isset($_POST['intranet_metrica_nonce']) || !wp_verify_nonce($_POST['intranet_metrica_nonce'], 'intranet_save_metrica_details')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    update_post_meta($post_id, '_metrica_valor', sanitize_text_field($_POST['metrica_valor'] ?? ''));
    update_post_meta($post_id, '_metrica_periodo', sanitize_text_field($_POST['metrica_periodo'] ?? ''));
    update_post_meta($post_id, '_metrica_icone', sanitize_text_field($_POST['metrica_icone'] ?? 'fas fa-chart-bar'));
}
add_action('save_post_metrica', 'intranet_save_metrica_details');

/*
 * ===================================================================
 * Redirecionamento Pós-Login
 * ===================================================================
 */

function intranet_login_redirect( $redirect_to, $request, $user ) {
    // Verifica se o usuário existe e tem uma função (role)
    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
        // Se o usuário for um administrador, redireciona para o painel
        if ( in_array( 'administrator', $user->roles ) ) {
            return admin_url();
        } else {
            // Para todos os outros usuários (ex: Assinantes), redireciona para a página inicial
            return home_url();
        }
    }
    // Para casos inesperados, retorna o redirecionamento padrão
    return $redirect_to;
}
add_filter( 'login_redirect', 'intranet_login_redirect', 10, 3 );
// add_filter( 'login_redirect', 'intranet_login_redirect', 10, 3 );

/*
 * ===================================================================
 * Esconder o menu "Painel" para não-administradores
 * ===================================================================
 */

function hide_dashboard_menu_for_non_admin() {
    if ( ! current_user_can('administrator') ) {

        // Esconde o menu principal "Painel"
        remove_menu_page('index.php');

        // Esconde subpáginas relacionadas
        remove_submenu_page('index.php', 'index.php');        // Página inicial
        remove_submenu_page('index.php', 'update-core.php');  // Atualizações
    }
}
add_action('admin_menu', 'hide_dashboard_menu_for_non_admin', 999);

/*
 * ===================================================================
 * Remover o ícone do WordPress da barra de administração
 * ===================================================================
 */
function remover_logo_wp_admin_bar($wp_admin_bar) {
    $wp_admin_bar->remove_node('wp-logo');
}
add_action('admin_bar_menu', 'remover_logo_wp_admin_bar', 999);

/*
 * ===================================================================
 * Personalização da Tela de Login
 * ===================================================================
 */

// Registrar seções e campos no Personalizador do WordPress
function intranet_customize_register($wp_customize) {

    // === Seção: Tela de Login ===
    $wp_customize->add_section('intranet_login_section', array(
        'title'    => 'Tela de Login',
        'priority' => 30,
    ));

    // --- Logo ---
    $wp_customize->add_setting('login_logo', array(
        'default'           => get_template_directory_uri() . '/assets/images/logo.png',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'login_logo', array(
        'label'   => 'Logo da Tela de Login',
        'section' => 'intranet_login_section',
    )));

    // --- Imagem Lateral ---
    $wp_customize->add_setting('login_side_image', array(
        'default'           => get_template_directory_uri() . '/assets/images/maria-fumaca.jpg',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'login_side_image', array(
        'label'   => 'Imagem Lateral (60% da tela)',
        'section' => 'intranet_login_section',
    )));

    // --- Rótulo Usuário ---
    $wp_customize->add_setting('login_label_username', array(
        'default'           => 'Digite sua matrícula',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('login_label_username', array(
        'label'   => 'Rótulo do Campo Usuário',
        'section' => 'intranet_login_section',
        'type'    => 'text',
    ));

    // --- Rótulo Senha ---
    $wp_customize->add_setting('login_label_password', array(
        'default'           => 'Senha é a data de nascimento (somente números)',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('login_label_password', array(
        'label'   => 'Rótulo do Campo Senha',
        'section' => 'intranet_login_section',
        'type'    => 'text',
    ));

    // --- Texto da Mensagem ---
    $wp_customize->add_setting('login_message_text', array(
        'default'           => 'Você está desconectado agora.',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('login_message_text', array(
        'label'   => 'Mensagem Acima do Formulário',
        'section' => 'intranet_login_section',
        'type'    => 'text',
    ));

    // --- Texto do Botão ---
    $wp_customize->add_setting('login_button_text', array(
        'default'           => 'Acessar',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('login_button_text', array(
        'label'   => 'Texto do Botão de Envio',
        'section' => 'intranet_login_section',
        'type'    => 'text',
    ));

    // --- Cor do Botão ---
    $wp_customize->add_setting('login_button_color', array(
        'default'           => '#2196F3',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'login_button_color', array(
        'label'   => 'Cor do Botão',
        'section' => 'intranet_login_section',
    )));

    // --- Cor de Fundo do Formulário ---
    $wp_customize->add_setting('login_form_bg', array(
        'default'           => '#ffffff',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'login_form_bg', array(
        'label'   => 'Cor de Fundo do Formulário',
        'section' => 'intranet_login_section',
    )));

    // --- Cor do Texto ---
    $wp_customize->add_setting('login_text_color', array(
        'default'           => '#333333',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'login_text_color', array(
        'label'   => 'Cor do Texto',
        'section' => 'intranet_login_section',
    )));

    // --- Cor de Fundo da Página ---
    $wp_customize->add_setting('login_page_bg', array(
        'default'           => '#f0f2f5',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'login_page_bg', array(
        'label'   => 'Cor de Fundo da Página',
        'section' => 'intranet_login_section',
    )));

    // --- Texto do Link "Perdeu a senha?" ---
    $wp_customize->add_setting('login_lost_password_text', array(
        'default'           => 'Perdeu a senha?',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('login_lost_password_text', array(
        'label'   => 'Texto "Perdeu a senha?"',
        'section' => 'intranet_login_section',
        'type'    => 'text',
    ));

    // --- Texto do Link "Ir para..." ---
    $wp_customize->add_setting('login_back_text', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('login_back_text', array(
        'label'       => 'Texto do Link "Voltar" (deixe vazio para ocultar)',
        'description' => 'Ex: Ir para Intranet PMTC',
        'section'     => 'intranet_login_section',
        'type'        => 'text',
    ));

    // --- Tamanho do Logo ---
    $wp_customize->add_setting('login_logo_width', array(
        'default'           => '180',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('login_logo_width', array(
        'label'       => 'Largura do Logo (px)',
        'description' => 'Largura em pixels. Altura ajustada automaticamente.',
        'section'     => 'intranet_login_section',
        'type'        => 'number',
        'input_attrs' => array('min' => 60, 'max' => 500, 'step' => 10),
    ));

    // === Seção: Hero Moderno ===
    $wp_customize->add_section('intranet_hero_section', array(
        'title'    => 'Hero Moderno',
        'priority' => 35,
    ));

    // --- Título do Hero ---
    $wp_customize->add_setting('hero_title', array(
        'default'           => 'Bem-vindo à Intranet',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('hero_title', array(
        'label'   => 'Título',
        'section' => 'intranet_hero_section',
        'type'    => 'text',
    ));

    // --- Descrição do Hero ---
    $wp_customize->add_setting('hero_description', array(
        'default'           => 'Um espaço pensado para você, servidor público de Três Corações, com acesso rápido a informações, documentos e serviços essenciais.',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('hero_description', array(
        'label'   => 'Descrição',
        'section' => 'intranet_hero_section',
        'type'    => 'textarea',
    ));

    // --- Texto do Botão ---
    $wp_customize->add_setting('hero_btn_text', array(
        'default'           => 'Saiba mais',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('hero_btn_text', array(
        'label'   => 'Texto do Botão',
        'section' => 'intranet_hero_section',
        'type'    => 'text',
    ));

    // --- URL do Botão ---
    $wp_customize->add_setting('hero_btn_url', array(
        'default'           => '/intranet',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control('hero_btn_url', array(
        'label'   => 'Link do Botão',
        'section' => 'intranet_hero_section',
        'type'    => 'url',
    ));

    // --- Cor Inicial do Degradê ---
    $wp_customize->add_setting('hero_gradient_start', array(
        'default'           => '#006494',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'hero_gradient_start', array(
        'label'   => 'Cor Inicial do Degradê',
        'section' => 'intranet_hero_section',
    )));

    // --- Cor Final do Degradê ---
    $wp_customize->add_setting('hero_gradient_end', array(
        'default'           => '#003554',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'hero_gradient_end', array(
        'label'   => 'Cor Final do Degradê',
        'section' => 'intranet_hero_section',
    )));

    // === Seção: Atalhos Rápidos ===
    $wp_customize->add_section('intranet_atalhos_section', array(
        'title'    => 'Atalhos Rápidos',
        'priority' => 40,
    ));

    $atalhos_defaults = array(
        1 => array('label' => 'Artes',              'url' => '/artes',                          'icon' => 'fa-solid fa-palette',           'color' => '#9C27B0'),
        2 => array('label' => 'Empregos',           'url' => '/empregos',                       'icon' => 'fas fa-file-alt',               'color' => '#00a6fb'),
        3 => array('label' => 'Eventos',            'url' => '/calendario',                     'icon' => 'fas fa-calendar-alt',           'color' => '#FF9800'),
        4 => array('label' => 'Formulários',        'url' => '/formularios',                    'icon' => 'fas fa-folder-open',            'color' => '#F44336'),
        5 => array('label' => 'Helpdesk',           'url' => '/helpdesk',                       'icon' => 'fas fa-headset',                'color' => '#003554'),
        6 => array('label' => 'Notícias',           'url' => '/noticias',                       'icon' => 'fas fa-newspaper',              'color' => '#9C27B0'),
        7 => array('label' => 'Recursos Humanos',   'url' => '/rh-recursos-humanos/',           'icon' => 'fas fa-money-check-dollar',     'color' => '#006494'),
        8 => array('label' => 'WebMail',            'url' => 'https://webmail.trescoracoes.mg.gov.br', 'icon' => 'fas fa-envelope',        'color' => '#ffb700'),
        9 => array('label' => 'WhatsApp',           'url' => 'https://web.whatsapp.com/',        'icon' => 'fab fa-whatsapp',               'color' => '#4CAF50'),
        10 => array('label' => '',                   'url' => '',                                'icon' => '',                              'color' => '#607D8B'),
        11 => array('label' => '',                   'url' => '',                                'icon' => '',                              'color' => '#607D8B'),
        12 => array('label' => '',                   'url' => '',                                'icon' => '',                              'color' => '#607D8B'),
        13 => array('label' => '',                   'url' => '',                                'icon' => '',                              'color' => '#607D8B'),
        14 => array('label' => '',                   'url' => '',                                'icon' => '',                              'color' => '#607D8B'),
        15 => array('label' => '',                   'url' => '',                                'icon' => '',                              'color' => '#607D8B'),
    );

    for ($i = 1; $i <= 15; $i++) {
        $d = $atalhos_defaults[$i];

        $wp_customize->add_setting("atalho_{$i}_label", array(
            'default'           => $d['label'],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control("atalho_{$i}_label", array(
            'label'       => "Atalho {$i} — Nome",
            'description' => 'Deixe vazio para ocultar este atalho.',
            'section'     => 'intranet_atalhos_section',
            'type'        => 'text',
        ));

        $wp_customize->add_setting("atalho_{$i}_url", array(
            'default'           => $d['url'],
            'sanitize_callback' => 'esc_url_raw',
        ));
        $wp_customize->add_control("atalho_{$i}_url", array(
            'label'   => "Atalho {$i} — URL",
            'section' => 'intranet_atalhos_section',
            'type'    => 'url',
        ));

        $wp_customize->add_setting("atalho_{$i}_icon", array(
            'default'           => $d['icon'],
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control("atalho_{$i}_icon", array(
            'label'       => "Atalho {$i} — Classe do Ícone",
            'description' => 'Ex: fas fa-home, fab fa-whatsapp',
            'section'     => 'intranet_atalhos_section',
            'type'        => 'text',
        ));

        $wp_customize->add_setting("atalho_{$i}_color", array(
            'default'           => $d['color'],
            'sanitize_callback' => 'sanitize_hex_color',
        ));
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, "atalho_{$i}_color", array(
            'label'   => "Atalho {$i} — Cor",
            'section' => 'intranet_atalhos_section',
        )));
    }
}
add_action('customize_register', 'intranet_customize_register');

// Carregar CSS e scripts customizados na tela de login
function intranet_login_enqueue_scripts() {
    wp_enqueue_style('intranet-login-custom', get_template_directory_uri() . '/assets/css/login-custom.css', array(), '1.0.0');
}
add_action('login_enqueue_scripts', 'intranet_login_enqueue_scripts');

// Injetar CSS dinâmico baseado nas configurações do Personalizador
function intranet_login_dynamic_css() {
    $button_color = get_theme_mod('login_button_color', '#2196F3');
    $form_bg      = get_theme_mod('login_form_bg', '#ffffff');
    $text_color   = get_theme_mod('login_text_color', '#333333');
    $page_bg      = get_theme_mod('login_page_bg', '#f0f2f5');

    // Calcula versão mais escura do botão para o hover
    $button_hover = esc_attr(sanitize_hex_color(darken_hex($button_color, 15)));

    echo '<style id="intranet-login-dynamic">
        body.login { background-color: ' . esc_attr($page_bg) . '; }
        .login-form-panel { background-color: ' . esc_attr($form_bg) . '; }
        .login-form-panel,
        .login-form-panel label { color: ' . esc_attr($text_color) . '; }
        .login-message { color: ' . esc_attr($text_color) . '99; }
        #loginform input[type="text"],
        #loginform input[type="password"] {
            border-color: ' . esc_attr($text_color) . '18;
            background: ' . esc_attr($form_bg) . 'f5;
        }
        #loginform input[type="text"]:focus,
        #loginform input[type="password"]:focus {
            border-color: ' . esc_attr($button_color) . ';
            box-shadow: 0 0 0 3px ' . esc_attr($button_color) . '1a;
            background: ' . esc_attr($form_bg) . ';
        }
        #loginform .submit input[type="submit"] {
            background-color: ' . esc_attr($button_color) . ';
        }
        #loginform .submit input[type="submit"]:hover {
            background-color: ' . esc_attr($button_hover) . ';
            box-shadow: 0 4px 16px ' . esc_attr($button_color) . '59;
        }
        .login .rememberme label { color: ' . esc_attr($text_color) . '99; }
        .login .rememberme input[type="checkbox"] { accent-color: ' . esc_attr($button_color) . '; }
        #nav a { color: ' . esc_attr($text_color) . '88; }
        #nav a:hover { color: ' . esc_attr($button_color) . '; }
        #backtoblog a { color: ' . esc_attr($text_color) . '66; }
        #backtoblog a:hover { color: ' . esc_attr($button_color) . '; }
    </style>';
}
add_action('login_head', 'intranet_login_dynamic_css');

// Injetar CSS dinâmico do Hero Moderno
function intranet_hero_dynamic_css() {
    if (!is_front_page()) return;

    $gradient_start = get_theme_mod('hero_gradient_start', '#006494');
    $gradient_end   = get_theme_mod('hero_gradient_end', '#003554');

    echo '<style id="intranet-hero-dynamic">
        .hero-moderno {
            background: linear-gradient(135deg, ' . esc_attr($gradient_start) . ', ' . esc_attr($gradient_end) . ') !important;
        }
    </style>';
}
add_action('wp_head', 'intranet_hero_dynamic_css');

// Injetar CSS dinâmico dos Atalhos Rápidos
function intranet_atalhos_dynamic_css() {
    if (!is_front_page()) return;

    $default_colors = array('#9C27B0', '#00a6fb', '#FF9800', '#F44336', '#003554', '#9C27B0', '#006494', '#ffb700', '#4CAF50', '#607D8B', '#607D8B', '#607D8B', '#607D8B', '#607D8B', '#607D8B');
    $css = '';
    for ($i = 1; $i <= 15; $i++) {
        $color = get_theme_mod("atalho_{$i}_color", $default_colors[$i - 1]);
        if (empty($color)) continue;
        $hex = ltrim($color, '#');
        if (strlen($hex) === 3) $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $rgba = "rgba({$r}, {$g}, {$b}, 0.1)";
        $css .= ".atalho-card:nth-child({$i}) i { background: {$rgba}; color: {$color}; }";
        $css .= ".atalho-card:hover:nth-child({$i}) { border-color: {$color}; }";
    }

    if (!empty($css)) {
        echo '<style id="intranet-atalhos-dynamic">' . $css . '</style>';
    }
}
add_action('wp_head', 'intranet_atalhos_dynamic_css');

// Função auxiliar para escurecer uma cor hex
function darken_hex($hex, $percent) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    $r = max(0, round(hexdec(substr($hex, 0, 2)) * (1 - $percent / 100)));
    $g = max(0, round(hexdec(substr($hex, 2, 2)) * (1 - $percent / 100)));
    $b = max(0, round(hexdec(substr($hex, 4, 2)) * (1 - $percent / 100)));
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

// Personalizar o logo da tela de login (com tamanho configurável)
function intranet_login_logo() {
    $logo_url = get_theme_mod('login_logo', get_template_directory_uri() . '/assets/images/logo.png');
    $logo_width = get_theme_mod('login_logo_width', '180');
    echo '<style>#login h1 a { background-image: url(' . esc_url($logo_url) . ') !important; background-size: contain !important; width: ' . esc_attr($logo_width) . 'px !important; height: auto !important; }</style>';
}
add_action('login_head', 'intranet_login_logo');

// Traduzir textos da tela de login via filtro gettext
function intranet_login_translations($translated, $text, $domain) {
    if ($domain !== 'default') return $translated;

    // Detecta se está na tela de login
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    $is_login_page = (isset($_GET['action']) && $_GET['action'] === 'logout')
        || (isset($_GET['loggedout']) && $_GET['loggedout'] === 'true')
        || (function_exists('get_current_screen') && $screen && isset($screen->id) && $screen->id === 'login')
        || (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'wp-login.php');
    if (!$is_login_page) return $translated;

    $label_username = get_theme_mod('login_label_username', '');
    $label_password = get_theme_mod('login_label_password', '');
    $button_text    = get_theme_mod('login_button_text', '');
    $lost_pw_text   = get_theme_mod('login_lost_password_text', '');

    $replacements = array();

    if (!empty($label_username)) {
        $replacements['Username or Email Address'] = $label_username;
        $replacements['Username'] = $label_username;
    }
    if (!empty($label_password)) {
        $replacements['Password'] = $label_password;
    }
    if (!empty($button_text)) {
        $replacements['Log In'] = $button_text;
        $replacements['Log in'] = $button_text;
    }
    if (!empty($lost_pw_text)) {
        $replacements['Lost your password?'] = $lost_pw_text;
    }

    if (isset($replacements[$text])) {
        return $replacements[$text];
    }

    return $translated;
}
add_filter('gettext', 'intranet_login_translations', 10, 3);

// Adicionar mensagem personalizada abaixo do logo
function intranet_login_message() {
    $message = get_theme_mod('login_message_text', 'Você está desconectado agora.');
    return '<p class="login-message">' . esc_html($message) . '</p>';
}
add_filter('login_message', 'intranet_login_message');

// Adicionar imagem lateral e painel do formulário na tela de login
function intranet_login_side_image() {
    $side_image_url = esc_url(get_theme_mod('login_side_image', get_template_directory_uri() . '/assets/images/maria-fumaca.jpg'));
    $back_text = get_theme_mod('login_back_text', '');
    $home_url = esc_url(home_url());
    $script = '<script>document.addEventListener("DOMContentLoaded",function(){'
        . 'var l=document.getElementById("login");if(!l)return;'
        . 'var s=document.createElement("div");s.className="login-side-image";s.style.backgroundImage="url(' . $side_image_url . ')";l.appendChild(s);'
        . 'var p=document.createElement("div");p.className="login-form-panel";'
        . 'var h=l.querySelector("h1"),n=l.querySelector("#nav"),b=l.querySelector("#backtoblog");'
        . 'if(h)p.appendChild(h);if(n)n.style.display="none";if(b)b.remove();'
        . 'var c=l.querySelectorAll("#loginform,.message,#login_error");for(var i=0;i<c.length;i++)p.appendChild(c[i]);'
        . 'var m=document.querySelector(".login-message");if(m)p.insertBefore(m,p.firstChild);'
        . 'l.insertBefore(p,l.firstChild);'
        . (empty($back_text) ? '' : 'var bk=document.createElement("div");bk.id="backtoblog";bk.innerHTML="<a href=\"' . $home_url . '\">&larr; ' . esc_js($back_text) . '</a>";p.appendChild(bk);')
        . '});</script>';
    echo $script;
}
add_action('login_head', 'intranet_login_side_image');

/*
 * ===================================================================
 * Personalização da Página de Perfil do Admin
 * ===================================================================
 */

// Conceder permissão de upload para Subscribers
function intranet_allow_subscriber_uploads() {
    $subscriber = get_role('subscriber');
    if ($subscriber && !$subscriber->has_cap('upload_files')) {
        $subscriber->add_cap('upload_files');
    }
}
add_action('init', 'intranet_allow_subscriber_uploads');

// Permitir upload de mídia via AJAX para todos os logados
function intranet_allow_upload_for_profile($response, $handler, $action) {
    if ($action === 'upload-attachment') {
        if (!current_user_can('upload_files')) {
            $user = wp_get_current_user();
            if ($user->exists()) {
                $user->add_cap('upload_files');
            }
        }
    }
    return $response;
}
add_filter('wp_prepare_attachment_for_js', 'intranet_allow_upload_for_profile', 10, 3);

// Carregar CSS na página de perfil (somente para não-admins)
function intranet_admin_profile_styles($hook) {
    if ($hook !== 'profile.php' && $hook !== 'user-edit.php') return;
    if (current_user_can('administrator')) return;
    wp_enqueue_style('intranet-admin-profile', get_template_directory_uri() . '/assets/css/admin-profile.css', array(), '1.0.0');
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'intranet_admin_profile_styles');

// Botão "Voltar para o Início" (somente para não-admins)
function intranet_profile_back_button() {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->id !== 'profile') return;
    if (current_user_can('administrator')) return;
    echo '<div class="intranet-back-home-wrap"><a href="' . esc_url(home_url()) . '" class="button intranet-back-home-btn"><i class="dashicons dashicons-admin-home"></i> Voltar para o Início</a></div>';
}
add_action('admin_notices', 'intranet_profile_back_button');

// Adicionar campo de foto do perfil (somente para não-admins)
function intranet_profile_picture_field($user) {
    if (current_user_can('administrator')) return;
    $avatar_url = get_user_meta($user->ID, 'intranet_profile_photo', true);
    if (empty($avatar_url)) {
        $avatar_url = get_avatar_url($user->ID, array('size' => 200));
    }
    ?>
    <tr class="user-profile-picture-section-wrap">
        <th><label><?php _e('Foto do Perfil', 'intranet'); ?></label></th>
        <td>
            <div class="user-profile-picture-section">
                <div class="avatar-wrapper">
                    <img id="intranet-avatar-preview" src="<?php echo esc_url($avatar_url); ?>" alt="Avatar">
                </div>
                <div class="profile-picture-info">
                    <h3><?php echo esc_html($user->display_name); ?></h3>
                    <p><?php _e('Envie uma foto do seu computador para personalizar seu perfil.', 'intranet'); ?></p>
                    <div class="profile-picture-actions">
                        <button type="button" class="button" id="intranet-upload-avatar">
                            <i class="dashicons dashicons-upload"></i>
                            <?php _e('Enviar foto', 'intranet'); ?>
                        </button>
                        <button type="button" class="button button-secondary" id="intranet-remove-avatar">
                            <i class="dashicons dashicons-trash"></i>
                            <?php _e('Remover', 'intranet'); ?>
                        </button>
                    </div>
                    <input type="hidden" name="intranet_profile_photo" id="intranet-profile-photo" value="<?php echo esc_attr($avatar_url); ?>">
                    <p class="avatar-change-tip"><?php _e('Formatos aceitos: JPG, PNG, GIF. Tamanho recomendado: 300x300px.', 'intranet'); ?></p>
                </div>
            </div>
        </td>
    </tr>
    <?php
}
add_action('show_user_profile', 'intranet_profile_picture_field');
add_action('edit_user_profile', 'intranet_profile_picture_field');

// Salvar foto do perfil
function intranet_save_profile_picture($user_id) {
    if (!isset($_POST['intranet_profile_nonce']) || !wp_verify_nonce($_POST['intranet_profile_nonce'], 'intranet_save_profile')) return;
    if (isset($_POST['intranet_profile_photo'])) {
        $photo_url = esc_url_raw($_POST['intranet_profile_photo']);
        update_user_meta($user_id, 'intranet_profile_photo', $photo_url);
    }
}
add_action('profile_update', 'intranet_save_profile_picture');

// Adicionar nonce de segurança (somente para não-admins)
function intranet_profile_nonce_field($user) {
    if (current_user_can('administrator')) return;
    wp_nonce_field('intranet_save_profile', 'intranet_profile_nonce');
}
add_action('show_user_profile', 'intranet_profile_nonce_field');
add_action('edit_user_profile', 'intranet_profile_nonce_field');

// Usar foto personalizada no avatar (substitui Gravatar)
function intranet_custom_avatar($avatar, $id_or_email, $args) {
    $user_id = 0;
    if (is_numeric($id_or_email)) {
        $user_id = (int) $id_or_email;
    } elseif (is_string($id_or_email)) {
        $user = get_user_by('email', $id_or_email);
        if ($user) $user_id = $user->ID;
    } elseif (is_object($id_or_email)) {
        $user_id = (int) $id_or_email->user_id;
    }
    if ($user_id > 0) {
        $custom_photo = get_user_meta($user_id, 'intranet_profile_photo', true);
        if (!empty($custom_photo)) {
            $size = isset($args['size']) ? $args['size'] : 96;
            $class = isset($args['class']) ? $args['class'] : 'avatar avatar-' . $size . ' photo';
            $avatar = '<img alt="" src="' . esc_url($custom_photo) . '" class="' . esc_attr($class) . '" height="' . esc_attr($size) . '" width="' . esc_attr($size) . '" loading="lazy">';
        }
    }
    return $avatar;
}
add_filter('get_avatar', 'intranet_custom_avatar', 10, 3);

// Injetar JavaScript para upload de avatar no perfil (somente para não-admins)
function intranet_profile_upload_script() {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->id !== 'profile') return;
    if (current_user_can('administrator')) return;
    ?>
    <script>
    jQuery(document).ready(function($) {
        var frame;
        $('#intranet-upload-avatar').on('click', function(e) {
            e.preventDefault();
            if (frame) { frame.open(); return; }
            frame = wp.media({
                title: 'Selecionar Foto do Perfil',
                button: { text: 'Usar esta foto' },
                multiple: false,
                library: { type: 'image' }
            });
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                var url = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
                $('#intranet-avatar-preview').attr('src', url);
                $('#intranet-profile-photo').val(attachment.url);
            });
            frame.open();
        });
        $('#intranet-remove-avatar').on('click', function(e) {
            e.preventDefault();
            var defaultUrl = '<?php echo esc_js(get_avatar_url(0, array("size" => 200))); ?>';
            $('#intranet-avatar-preview').attr('src', defaultUrl);
            $('#intranet-profile-photo').val('');
        });
    });
    </script>
    <?php
}
add_action('admin_footer', 'intranet_profile_upload_script');

?>
