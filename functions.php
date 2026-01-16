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
        @media (prefers-color-scheme: dark) {
            .tempo-container {
                background: linear-gradient(135deg, #2d3436, #1e272e);
            }
            .forecast-dia {
                background: rgba(255, 255, 255, 0.05);
            }
        }
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
 * Estilos e Script para o Menu Mobile
 * ===================================================================
 */
function intranet_mobile_menu_styles() {
    ?>
    <style>
        /* Estilos para o botão do menu hambúrguer */
        .menu-toggle {
            display: none; /* Escondido por padrão */
            background: transparent;
            border: none;
            color: var(--preto);
            font-size: 24px;
            cursor: pointer;
            padding: 0 15px;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: block; /* Mostra o botão em telas menores */
                order: 3; /* Muda a ordem para aparecer depois do logo */
            }

            .header-container {
                justify-content: space-between;
            }

            .nav-principal {
                display: none; /* Esconde o menu de navegação */
                position: absolute;
                top: 100%; /* Logo abaixo do header */
                left: 0;
                right: 0;
                background-color: var(--branco);
                box-shadow: var(--sombra);
                width: 100%;
                z-index: 1000;
            }

            .nav-principal.toggled {
                display: block; /* Mostra o menu quando ativado */
            }

            .nav-principal ul {
                display: none; /* Esconde a lista de itens por padrão em mobile */
                flex-direction: column; /* Para quando for exibido */
                width: 100%;
                list-style: none; /* Remove marcadores de lista padrão */
                margin: 0;
                padding: 0;
            }

            .nav-principal.toggled ul.menu-items { /* Garante que a lista de itens seja exibida */
                display: flex;
            }

            .nav-principal ul.menu-items li {
                border-bottom: 1px solid rgba(0, 0, 0, 0.1); /* Separador para itens de menu */
            }
            .nav-principal ul.menu-items li:last-child {
                border-bottom: none;
            }
            .nav-principal ul.menu-items li a {
                padding: 15px 20px; /* Aumenta a área clicável */
                display: block; /* Faz o link ocupar toda a largura do item */
                text-align: left;
                text-decoration: none; /* Remove sublinhado */
                color: var(--preto); /* Define a cor do texto */
            }
        }
    </style>
    <?php
}
add_action('wp_head', 'intranet_mobile_menu_styles');

/*
 * ===================================================================
 * Remover o ícone do WordPress da barra de administração
 * ===================================================================
 */
function remover_logo_wp_admin_bar($wp_admin_bar) {
    $wp_admin_bar->remove_node('wp-logo');
}
add_action('admin_bar_menu', 'remover_logo_wp_admin_bar', 999);

?>
