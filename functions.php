<?php
/**
 * Intranet TC â€” functions.php
 *
 * OrganizaÃ§Ã£o:
 *  01. Scripts e estilos
 *  02. Menus, thumbnails e traduÃ§Ã£o
 *  03. Atalhos rÃ¡pidos (inc/atalhos.php)
 *  04. Widgets / title-tag / thumbnails
 *  05. MÃ­dias, breadcrumbs, lazy-load e leitura
 *  06. Clima (Open-Meteo + shortcode [temperatura])
 *  07. Banner prÃ³ximo pagamento ([proximo_pagamento])
 *  08. RestriÃ§Ã£o para visitantes
 *  09. CPT MÃ©tricas + meta boxes
 *  10. Login: redirect, admin bar, customizer e tela
 *  11. Perfil: upload, avatar e e-mail
 *
 * PadrÃ£o: 4 espaÃ§os, sem tabs, sem trailing whitespace.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ===================================================================
// 01. Scripts e estilos
// ===================================================================

function intranet_scripts() {
    wp_enqueue_style('intranet-style', get_stylesheet_uri());
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
    wp_enqueue_style('dashicons');

    // Adiciona o script para o menu mobile
    wp_enqueue_script(
        'intranet-mobile-menu',
        get_template_directory_uri() . '/assets/js/mobile-menu.js',
        array(), // dependÃªncias
        '1.0.0', // versÃ£o
        true // carregar no footer
    );
}
add_action('wp_enqueue_scripts', 'intranet_scripts');

function intranet_customizer_scripts() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
}
add_action('customize_controls_enqueue_scripts', 'intranet_customizer_scripts');

// ===================================================================
// 02. Menus, thumbnails e traduÃ§Ã£o
// ===================================================================

function intranet_menus() {
    register_nav_menus(array(
        'menu-principal' => __('Menu Principal', 'intranet')
    ));
}
add_action('after_setup_theme', 'intranet_menus');

// Suporte a thumbnails
add_theme_support('post-thumbnails');

// Suporte a traduÃ§Ã£o
load_theme_textdomain('intranet', get_template_directory() . '/languages');

// ===================================================================
// 03. Atalhos rÃ¡pidos (lÃ³gica centralizada)
// ===================================================================

require_once get_template_directory() . '/inc/atalhos.php';

// ===================================================================
// 04. Widgets, title-tag e tamanhos de imagem
// ===================================================================

// Registrar Ã¡reas de widgets
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
        'name'          => __('RodapÃ©', 'intranet'),
        'id'            => 'footer-1',
        'description'   => __('Adicione widgets ao rodapÃ©.', 'intranet'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'intranet_widgets');

// Adicionar suporte a tÃ­tulo dinÃ¢mico
add_theme_support('title-tag');

// Personalizar o tamanho das thumbnails
add_image_size('noticia-thumb', 300, 200, true);

// ===================================================================
// 05. MÃ­dias, breadcrumbs, lazy-load e tempo de leitura
// ===================================================================

// Suporte a WebP
function webp_upload_mimes($existing_mimes) {
    $existing_mimes['webp'] = 'image/webp';
    return $existing_mimes;
}
add_filter('mime_types', 'webp_upload_mimes');

// Breadcrumbs
function intranet_breadcrumbs() {
    // ImplementaÃ§Ã£o dos breadcrumbs
}

// Lazy loading
add_filter('wp_get_attachment_image_attributes', function($attr) {
    $attr['loading'] = 'lazy';
    return $attr;
});

// Tempo de leitura estimado
if ( ! function_exists('estimated_reading_time')) {
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
        return '<div style="all: initial ! important;">' . $output . '</div>';
    }
    return $output;
}, 10, 2);

// ===================================================================
// 06. Clima (Open-Meteo + shortcode [temperatura])
// ===================================================================

// FunÃ§Ã£o auxiliar para mapear Ã­cones do OpenWeatherMap para Font Awesome
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
    return 'fas fa-question-circle'; // Ãcone padrÃ£o
}

// Shortcode para PrevisÃ£o do Tempo
function intranet_previsao_tempo_shortcode() {
    // --- CONFIGURAÃ‡Ã•ES DA API ---
    $latitude = '-21.79';  // Latitude de TrÃªs CoraÃ§Ãµes, MG
    $longitude = '-45.25'; // Longitude de TrÃªs CoraÃ§Ãµes, MG
    $timezone = 'America/Sao_Paulo';

    // Tenta obter os dados do cache
    $weather_data = get_transient('intranet_weather_data');

    if (false === $weather_data) {
        // Se nÃ£o houver cache, busca na API
        $api_url = "https://api.open-meteo.com/v1/forecast?latitude={$latitude}&longitude={$longitude}&current_weather=true&daily=weathercode,temperature_2m_max,temperature_2m_min&timezone={$timezone}&forecast_days=5";

        $response = wp_remote_get($api_url);

        if (is_wp_error($response)) {
            return '<!-- Erro ao buscar dados do tempo -->';
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        // Verifica se a chave de API foi inserida e se a resposta da API Ã© vÃ¡lida
        if (wp_remote_retrieve_response_code($response) !== 200 || ! isset($data->current_weather)) {
            ob_start();
            ?>
            <section class="previsao-tempo-moderna">
                <div class="section-header"><h3><?php _e('Tempo em TrÃªs CoraÃ§Ãµes', 'intranet'); ?></h3></div>
                <div class="tempo-container" style="background: #f8d7da; color: #721c24; display: block; text-align: center;">
                    <p style="margin: 0; font-weight: 500;">
                        <?php _e('NÃ£o foi possÃ­vel carregar os dados do tempo. Tente novamente mais tarde.', 'intranet'); ?>
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

    // VerificaÃ§Ã£o de seguranÃ§a para garantir que os dados existem
    if ( ! isset($current) || ! isset($daily)) {
        return '<!-- Dados do tempo invÃ¡lidos recebidos da API. -->';
    }

    $temp_atual = round($current->temperature);
    $temp_max = round($daily->temperature_2m_max[0]);
    $temp_min = round($daily->temperature_2m_min[0]);
    $icon_class_atual = intranet_get_open_meteo_icon_class($current->weathercode, $current->is_day);

    // DescriÃ§Ãµes para os cÃ³digos de clima (simplificado)
    $wmo_descriptions = [
        0 => 'CÃ©u limpo', 1 => 'Quase limpo', 2 => 'Parcialmente nublado', 3 => 'Nublado',
        45 => 'Nevoeiro', 48 => 'Nevoeiro',
        51 => 'Garoa leve', 53 => 'Garoa', 55 => 'Garoa forte',
        61 => 'Chuva fraca', 63 => 'Chuva', 65 => 'Chuva forte',
        80 => 'Pancadas de chuva', 81 => 'Pancadas de chuva', 82 => 'Pancadas de chuva',
        71 => 'Neve', 73 => 'Neve', 75 => 'Neve',
        95 => 'Trovoada', 96 => 'Trovoada', 99 => 'Trovoada'
    ];
    $condicao = isset($wmo_descriptions[$current->weathercode]) ? $wmo_descriptions[$current->weathercode] : 'CondiÃ§Ã£o desconhecida';

    // Dias da semana em portuguÃªs
    $dias_semana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'SÃ¡b'];

    ob_start();
    ?>
    <style>
        /* Estilos da PrevisÃ£o do Tempo */
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
        <div class="section-header"><h3><?php _e('Tempo em TrÃªs CoraÃ§Ãµes', 'intranet'); ?></h3></div>
        <div class="tempo-container">
            <div class="tempo-atual">
                <div class="tempo-atual-main">
                    <i class="<?php echo esc_attr($icon_class_atual); ?> tempo-icon-grande"></i>
                    <div class="temperatura-grande"><?php echo esc_html($temp_atual); ?>Â°C</div>
                </div>
                <div class="tempo-atual-detalhes">
                    <div class="condicao-texto"><?php echo esc_html($condicao); ?></div>
                    <div class="min-max">MÃ¡x: <?php echo esc_html($temp_max); ?>Â° / MÃ­n: <?php echo esc_html($temp_min); ?>Â°</div>
                </div>
            </div>
            <div class="tempo-forecast">
                <?php for ($i = 1; $i < 5; $i++) : // ComeÃ§a do dia seguinte (Ã­ndice 1) e pega 4 dias ?>
                    <?php if (isset($daily->time[$i])) : ?>
                        <div class="forecast-dia">
                            <div class="dia-semana"><?php echo esc_html($dias_semana[date('w', strtotime($daily->time[$i]))]); ?></div>
                            <i class="<?php echo esc_attr(intranet_get_open_meteo_icon_class($daily->weathercode[$i])); ?>"></i>
                            <div class="temp-forecast"><?php echo esc_html(round($daily->temperature_2m_max[$i])); ?>Â°/<?php echo esc_html(round($daily->temperature_2m_min[$i])); ?>Â°</div>
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

// ===================================================================
// 07. Banner prÃ³ximo pagamento ([proximo_pagamento])
// ===================================================================

function intranet_next_payment_defaults() {
    return array(
        'payment_date'        => '2026-08-06',
        'payment_title'       => 'PRÃ“XIMO PAGAMENTO',
        'payment_info_text'   => 'Pagamento / adiantamento dos servidores municipais',
        'payment_icon'        => 'dashicons-calendar-alt',
        'payment_image'       => '',
        'payment_image_width' => 260,
        'message_title'       => '',
        'message_text'        => 'Fique atento Ã  Intranet para saber dos prÃ³ximos pagamentos e adiantamentos.',
        'message_icon'        => 'dashicons-money-alt',
        'bg_start'            => '#0b5fa5',
        'bg_end'              => '#0a2f6b',
        'accent_color'        => '#ffcb3d',
        'text_color'          => '#ffffff',
        'secondary_color'     => '#a9d6ff',
        'message_color'       => '#ffffff',
        'border_radius'       => 24,
        'shadow_blur'         => 32,
        'min_height'          => 220,
    );
}

function intranet_next_payment_theme_mod_keys() {
    return array(
        'payment_date',
        'payment_title',
        'payment_info_text',
        'payment_icon',
        'payment_image',
        'payment_image_width',
        'message_title',
        'message_text',
        'message_icon',
        'bg_start',
        'bg_end',
        'accent_color',
        'text_color',
        'secondary_color',
        'message_color',
        'border_radius',
        'shadow_blur',
        'min_height',
    );
}

function intranet_get_next_payment_settings() {
    $defaults = intranet_next_payment_defaults();
    $legacy_settings = get_option('intranet_next_payment_settings', array());
    $settings = array();

    foreach (intranet_next_payment_theme_mod_keys() as $key) {
        $fallback = $legacy_settings[$key] ?? $defaults[$key];
        $settings[$key] = get_theme_mod('intranet_next_payment_' . $key, $fallback);
    }

    return wp_parse_args($settings, $defaults);
}

function intranet_sanitize_next_payment_settings($input) {
    $defaults = intranet_next_payment_defaults();
    $output = array();

    $output['payment_date'] = sanitize_text_field($input['payment_date'] ?? $defaults['payment_date']);
    if ( ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $output['payment_date'])) {
        $output['payment_date'] = $defaults['payment_date'];
    }

    $output['payment_title'] = sanitize_text_field($input['payment_title'] ?? $defaults['payment_title']);
    $output['payment_info_text'] = sanitize_text_field($input['payment_info_text'] ?? $defaults['payment_info_text']);
    $output['payment_icon'] = sanitize_html_class($input['payment_icon'] ?? $defaults['payment_icon']);
    $output['payment_image'] = esc_url_raw($input['payment_image'] ?? $defaults['payment_image']);
    $output['payment_image_width'] = max(120, min(420, absint($input['payment_image_width'] ?? $defaults['payment_image_width'])));
    $output['message_title'] = sanitize_text_field($input['message_title'] ?? $defaults['message_title']);
    $output['message_text'] = sanitize_textarea_field($input['message_text'] ?? $defaults['message_text']);
    $output['message_icon'] = sanitize_html_class($input['message_icon'] ?? $defaults['message_icon']);

    foreach (array('bg_start', 'bg_end', 'accent_color', 'text_color', 'secondary_color', 'message_color') as $color_key) {
        $output[$color_key] = sanitize_hex_color($input[$color_key] ?? $defaults[$color_key]) ?: $defaults[$color_key];
    }

    $output['border_radius'] = max(8, min(48, absint($input['border_radius'] ?? $defaults['border_radius'])));
    $output['shadow_blur'] = max(0, min(80, absint($input['shadow_blur'] ?? $defaults['shadow_blur'])));
    $output['min_height'] = max(160, min(420, absint($input['min_height'] ?? $defaults['min_height'])));

    return $output;
}

function intranet_register_next_payment_setting() {
    register_setting(
        'intranet_next_payment_group',
        'intranet_next_payment_settings',
        'intranet_sanitize_next_payment_settings'
    );
}
add_action('admin_init', 'intranet_register_next_payment_setting');

function intranet_sanitize_next_payment_date($value) {
    $value = sanitize_text_field($value);
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : intranet_next_payment_defaults()['payment_date'];
}

function intranet_sanitize_next_payment_number($value, $min, $max, $default) {
    $value = absint($value);
    if ($value < $min || $value > $max) {
        return $default;
    }
    return $value;
}

function intranet_next_payment_display_date($date_string) {
    $timezone = function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone(wp_timezone_string() ?: 'America/Sao_Paulo');

    try {
        $date = new DateTimeImmutable($date_string, $timezone);
    } catch (Exception $e) {
        $date = new DateTimeImmutable('2026-08-06', $timezone);
    }

    $months = array(
        1 => 'JANEIRO',
        2 => 'FEVEREIRO',
        3 => 'MARÃ‡O',
        4 => 'ABRIL',
        5 => 'MAIO',
        6 => 'JUNHO',
        7 => 'JULHO',
        8 => 'AGOSTO',
        9 => 'SETEMBRO',
        10 => 'OUTUBRO',
        11 => 'NOVEMBRO',
        12 => 'DEZEMBRO',
    );
    $weekdays = array(
        'Sunday'    => 'DOMINGO',
        'Monday'    => 'SEGUNDA-FEIRA',
        'Tuesday'   => 'TERÃ‡A-FEIRA',
        'Wednesday' => 'QUARTA-FEIRA',
        'Thursday'  => 'QUINTA-FEIRA',
        'Friday'    => 'SEXTA-FEIRA',
        'Saturday'  => 'SÃBADO',
    );

    $month_index = (int) $date->format('n');
    $weekday_key = $date->format('l');

    return array(
        'day' => $date->format('d'),
        'month' => $months[$month_index] ?? strtoupper($date->format('F')),
        'weekday' => $weekdays[$weekday_key] ?? strtoupper($weekday_key),
    );
}

function intranet_render_next_payment_banner() {
    $settings = intranet_get_next_payment_settings();
    $date = intranet_next_payment_display_date($settings['payment_date']);
    $shadow = '0 16px ' . absint($settings['shadow_blur']) . 'px rgba(4, 27, 61, 0.16)';
    $separator = 'rgba(0, 180, 255, 0.38)';

    ob_start();
    ?>
    <style>
        .intranet-next-payment {
            margin-bottom: 50px;
        }
        .intranet-next-payment-card {
            display: grid;
            grid-template-columns: minmax(220px, 30%) minmax(260px, 40%) minmax(220px, 30%);
            align-items: center;
            width: 100%;
            min-height: <?php echo esc_attr($settings['min_height']); ?>px;
            background: linear-gradient(135deg, <?php echo esc_attr($settings['bg_start']); ?> 0%, <?php echo esc_attr($settings['bg_end']); ?> 100%);
            color: <?php echo esc_attr($settings['text_color']); ?>;
            border-radius: <?php echo esc_attr($settings['border_radius']); ?>px;
            box-shadow: <?php echo esc_attr($shadow); ?>;
            overflow: hidden;
            position: relative;
        }
        .intranet-next-payment-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top left, rgba(255,255,255,0.06), transparent 26%),
                linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0));
            pointer-events: none;
        }
        .intranet-next-payment-pane {
            position: relative;
            z-index: 1;
            padding: 30px 28px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 12px;
        }
        .intranet-next-payment-pane + .intranet-next-payment-pane {
            border-left: 2px solid <?php echo esc_attr($separator); ?>;
        }
        .intranet-next-payment-media {
            align-items: center;
            text-align: center;
        }
        .intranet-next-payment-media img {
            width: 100%;
            max-width: <?php echo esc_attr($settings['payment_image_width']); ?>px;
            height: auto;
            display: block;
            object-fit: contain;
            margin: 0 auto;
        }
        .intranet-next-payment-fallback {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 22px 24px;
            border-radius: 14px;
            background: rgba(255,255,255,0.08);
            color: <?php echo esc_attr($settings['text_color']); ?>;
            font-size: clamp(1.3rem, 2.2vw, 2rem);
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .intranet-next-payment-date {
            display: grid;
            grid-template-columns: 76px 1fr;
            align-items: center;
            column-gap: 20px;
        }
        .intranet-next-payment-badge,
        .intranet-next-payment-message-icon {
            width: 76px;
            height: 76px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, rgba(32, 167, 255, 0.95), rgba(4, 109, 212, 0.95));
            color: #ffffff;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.18);
            font-size: 30px;
            flex: 0 0 auto;
        }
        .intranet-next-payment-badge .dashicons,
        .intranet-next-payment-message-icon .dashicons {
            width: 30px;
            height: 30px;
            font-size: 30px;
        }
        .intranet-next-payment-date-main {
            display: block;
            font-size: clamp(1.7rem, 3vw, 2.9rem);
            font-weight: 800;
            line-height: 0.95;
            letter-spacing: -0.03em;
            text-transform: uppercase;
        }
        .intranet-next-payment-date-main .date-full {
            display: block;
            white-space: nowrap;
        }
        .intranet-next-payment-date-main .date-day {
            margin-right: 10px;
        }
        .intranet-next-payment-date-main small {
            font-size: inherit;
            font-weight: 700;
            letter-spacing: inherit;
        }
        .intranet-next-payment-weekday {
            color: <?php echo esc_attr($settings['accent_color']); ?>;
            font-size: clamp(0.95rem, 1.45vw, 1.3rem);
            font-weight: 800;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }
        .intranet-next-payment-date-copy {
            min-width: 0;
        }
        .intranet-next-payment-message {
            display: grid;
            grid-template-columns: 76px 1fr;
            align-items: center;
            column-gap: 18px;
        }
        .intranet-next-payment-message-title {
            margin: 0;
            color: <?php echo esc_attr($settings['text_color']); ?>;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .intranet-next-payment-message-text {
            margin: 0;
            color: <?php echo esc_attr($settings['message_color']); ?>;
            font-size: clamp(1rem, 1.55vw, 1.08rem);
            line-height: 1.35;
            max-width: 22ch;
            word-break: break-word;
        }
        @media (max-width: 1024px) {
            .intranet-next-payment-card {
                grid-template-columns: 0.95fr 1.25fr 1fr;
            }
            .intranet-next-payment-pane {
                padding: 24px 20px;
            }
            .intranet-next-payment-date-main .date-full {
                white-space: normal;
            }
        }
        @media (max-width: 767px) {
            .intranet-next-payment-card {
                grid-template-columns: 1fr;
            }
            .intranet-next-payment-pane + .intranet-next-payment-pane {
                border-left: 0;
                border-top: 2px solid <?php echo esc_attr($separator); ?>;
            }
            .intranet-next-payment-media,
            .intranet-next-payment-date,
            .intranet-next-payment-message {
                text-align: center;
            }
            .intranet-next-payment-date,
            .intranet-next-payment-message {
                grid-template-columns: 1fr;
                row-gap: 18px;
                justify-items: center;
            }
            .intranet-next-payment-message-text {
                max-width: none;
            }
            .intranet-next-payment-date-main .date-full {
                white-space: normal;
            }
        }
    </style>
    <section class="intranet-next-payment" aria-label="<?php echo esc_attr__('PrÃ³ximo pagamento', 'intranet'); ?>">
        <div class="intranet-next-payment-card">
            <div class="intranet-next-payment-pane intranet-next-payment-media">
                <?php if ( ! empty($settings['payment_image']) ) : ?>
                    <img src="<?php echo esc_url($settings['payment_image']); ?>" alt="<?php echo esc_attr($settings['payment_title']); ?>">
                <?php else : ?>
                    <div class="intranet-next-payment-fallback"><?php echo esc_html($settings['payment_title']); ?></div>
                <?php endif; ?>
            </div>
            <div class="intranet-next-payment-pane intranet-next-payment-date">
                <div class="intranet-next-payment-badge">
                    <span class="dashicons <?php echo esc_attr($settings['payment_icon']); ?>" aria-hidden="true"></span>
                </div>
                <div class="intranet-next-payment-date-copy">
                    <div class="intranet-next-payment-date-main">
                        <span class="date-full">
                            <span class="date-day"><?php echo esc_html($date['day']); ?></span><small><?php echo esc_html('DE ' . $date['month']); ?></small>
                        </span>
                    </div>
                    <div class="intranet-next-payment-weekday"><?php echo esc_html($date['weekday']); ?></div>
                </div>
            </div>
            <div class="intranet-next-payment-pane intranet-next-payment-message">
                <div class="intranet-next-payment-message-icon">
                    <span class="dashicons <?php echo esc_attr($settings['message_icon']); ?>" aria-hidden="true"></span>
                </div>
                <div class="intranet-next-payment-message-copy">
                    <?php if ( ! empty($settings['message_title']) ) : ?>
                        <h3 class="intranet-next-payment-message-title"><?php echo esc_html($settings['message_title']); ?></h3>
                    <?php endif; ?>
                    <p class="intranet-next-payment-message-text"><?php echo esc_html($settings['message_text']); ?></p>
                </div>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

function intranet_next_payment_shortcode() {
    return intranet_render_next_payment_banner();
}
add_shortcode('proximo_pagamento', 'intranet_next_payment_shortcode');

// ===================================================================
// 08. Bloquear site para visitantes nÃ£o logados
// ===================================================================

function bloquear_site_para_visitantes() {
    // Se o usuÃ¡rio nÃ£o estiver logado e nÃ£o estiver em uma pÃ¡gina de administraÃ§Ã£o,
    // redireciona para a tela de login.
    if ( ! is_user_logged_in() && ! is_admin() ) {
        auth_redirect(); // envia para tela de login
    }
}
add_action( 'template_redirect', 'bloquear_site_para_visitantes' );

// ===================================================================
// 09. CPT MÃ©tricas + meta boxes
// ===================================================================

function intranet_cpt_metricas() {
    $labels = array(
        'name'               => _x('MÃ©tricas', 'post type general name', 'intranet'),
        'singular_name'      => _x('MÃ©trica', 'post type singular name', 'intranet'),
        'menu_name'          => _x('MÃ©tricas', 'admin menu', 'intranet'),
        'name_admin_bar'     => _x('MÃ©trica', 'add new on admin bar', 'intranet'),
        'add_new'            => _x('Adicionar Nova', 'metrica', 'intranet'),
        'add_new_item'       => __('Adicionar Nova MÃ©trica', 'intranet'),
        'new_item'           => __('Nova MÃ©trica', 'intranet'),
        'edit_item'          => __('Editar MÃ©trica', 'intranet'),
        'view_item'          => __('Ver MÃ©trica', 'intranet'),
        'all_items'          => __('Todas as MÃ©tricas', 'intranet'),
        'search_items'       => __('Buscar MÃ©tricas', 'intranet'),
        'parent_item_colon'  => __('MÃ©trica Pai:', 'intranet'),
        'not_found'          => __('Nenhuma mÃ©trica encontrada.', 'intranet'),
        'not_found_in_trash' => __('Nenhuma mÃ©trica encontrada na lixeira.', 'intranet')
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false, // NÃ£o serÃ£o visÃ­veis publicamente como posts normais
        'publicly_queryable' => false,
        'show_ui'            => true, // Mostrar no painel de admin
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'metrica'),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 20, // PosiÃ§Ã£o no menu do admin
        'menu_icon'          => 'dashicons-chart-line', // Ãcone
        'supports'           => array('title'), // Suporta apenas tÃ­tulo
    );

    register_post_type('metrica', $args);
}
add_action('init', 'intranet_cpt_metricas');

// Adicionar Meta Boxes (campos personalizados) para as MÃ©tricas
function intranet_metricas_meta_boxes() {
    add_meta_box(
        'intranet_metrica_details',
        __('Detalhes da MÃ©trica', 'intranet'),
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
        <label for="metrica_periodo"><?php _e('PerÃ­odo/DescriÃ§Ã£o (Ex: em julho, nesse mÃªs)', 'intranet'); ?></label><br>
        <input type="text" id="metrica_periodo" name="metrica_periodo" value="<?php echo esc_attr($periodo); ?>" style="width:100%;">
    </p>
    <p>
        <label for="metrica_icone"><?php _e('Ãcone (Classe do Font Awesome, Ex: fas fa-users)', 'intranet'); ?></label><br>
        <input type="text" id="metrica_icone" name="metrica_icone" value="<?php echo esc_attr($icone); ?>" style="width:100%;">
    </p>
    <?php
}

// Salvar os dados da Meta Box
function intranet_save_metrica_details($post_id) {
    if ( ! isset($_POST['intranet_metrica_nonce']) || ! wp_verify_nonce($_POST['intranet_metrica_nonce'], 'intranet_save_metrica_details') ) {
        return;
    }
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can('edit_post', $post_id) ) {
        return;
    }

    update_post_meta($post_id, '_metrica_valor', sanitize_text_field($_POST['metrica_valor'] ?? ''));
    update_post_meta($post_id, '_metrica_periodo', sanitize_text_field($_POST['metrica_periodo'] ?? ''));
    update_post_meta($post_id, '_metrica_icone', sanitize_text_field($_POST['metrica_icone'] ?? 'fas fa-chart-bar'));
}
add_action('save_post_metrica', 'intranet_save_metrica_details');

// ===================================================================
// 10. Login: redirect, admin bar, customizer e tela
// ===================================================================

// --- Redirecionamento pÃ³s-login ---
function intranet_login_redirect( $redirect_to, $request, $user ) {
    // Verifica se o usuÃ¡rio existe e tem uma funÃ§Ã£o (role)
    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
        // Se o usuÃ¡rio for um administrador, redireciona para o painel
        if ( in_array( 'administrator', $user->roles ) ) {
            return admin_url();
        } else {
            // Para todos os outros usuÃ¡rios (ex: Assinantes), redireciona para a pÃ¡gina inicial
            return home_url();
        }
    }
    // Para casos inesperados, retorna o redirecionamento padrÃ£o
    return $redirect_to;
}
add_filter( 'login_redirect', 'intranet_login_redirect', 10, 3 );

// --- Esconder o menu "Painel" para nÃ£o-administradores ---
function hide_dashboard_menu_for_non_admin() {
    if ( ! current_user_can('administrator') ) {

        // Esconde o menu principal "Painel"
        remove_menu_page('index.php');

        // Esconde subpÃ¡ginas relacionadas
        remove_submenu_page('index.php', 'index.php');        // PÃ¡gina inicial
        remove_submenu_page('index.php', 'update-core.php');  // AtualizaÃ§Ãµes
    }
}
add_action('admin_menu', 'hide_dashboard_menu_for_non_admin', 999);

// --- Remover o Ã­cone do WordPress da barra de administraÃ§Ã£o ---
function remover_logo_wp_admin_bar($wp_admin_bar) {
    $wp_admin_bar->remove_node('wp-logo');
}
add_action('admin_bar_menu', 'remover_logo_wp_admin_bar', 999);

// --- PersonalizaÃ§Ã£o da tela de login (Customizer + CSS/JS) ---
function intranet_sanitize_atalhos_json($value) {
    $decoded = json_decode($value, true);
    if ( ! is_array($decoded)) {
        return json_encode(intranet_atalhos_defaults());
    }
    $sanitized = array();
    foreach ($decoded as $item) {
        if ( ! is_array($item)) continue;
        $label = isset($item['label']) ? sanitize_text_field($item['label']) : '';
        $sanitized[] = array(
            'label' => $label,
            'url'   => isset($item['url']) ? esc_url_raw($item['url']) : '',
            'icon'  => isset($item['icon']) ? sanitize_text_field($item['icon']) : '',
            'color' => isset($item['color']) ? sanitize_hex_color($item['color']) : '#607D8B',
        );
    }
    if (empty($sanitized)) {
        return json_encode(intranet_atalhos_defaults());
    }
    return json_encode($sanitized);
}

// Registrar seÃ§Ãµes e campos no Personalizador do WordPress
function intranet_customize_register($wp_customize) {

    // === SeÃ§Ã£o: Tela de Login ===
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

    // --- RÃ³tulo UsuÃ¡rio ---
    $wp_customize->add_setting('login_label_username', array(
        'default'           => 'Digite sua matrÃ­cula',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('login_label_username', array(
        'label'   => 'RÃ³tulo do Campo UsuÃ¡rio',
        'section' => 'intranet_login_section',
        'type'    => 'text',
    ));

    // --- RÃ³tulo Senha ---
    $wp_customize->add_setting('login_label_password', array(
        'default'           => 'Senha Ã© a data de nascimento (somente nÃºmeros)',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('login_label_password', array(
        'label'   => 'RÃ³tulo do Campo Senha',
        'section' => 'intranet_login_section',
        'type'    => 'text',
    ));

    // --- Texto da Mensagem ---
    $wp_customize->add_setting('login_message_text', array(
        'default'           => 'VocÃª estÃ¡ desconectado agora.',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('login_message_text', array(
        'label'   => 'Mensagem Acima do FormulÃ¡rio',
        'section' => 'intranet_login_section',
        'type'    => 'text',
    ));

    // --- Texto do BotÃ£o ---
    $wp_customize->add_setting('login_button_text', array(
        'default'           => 'Acessar',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('login_button_text', array(
        'label'   => 'Texto do BotÃ£o de Envio',
        'section' => 'intranet_login_section',
        'type'    => 'text',
    ));

    // --- Cor do BotÃ£o ---
    $wp_customize->add_setting('login_button_color', array(
        'default'           => '#2196F3',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'login_button_color', array(
        'label'   => 'Cor do BotÃ£o',
        'section' => 'intranet_login_section',
    )));

    // --- Cor de Fundo do FormulÃ¡rio ---
    $wp_customize->add_setting('login_form_bg', array(
        'default'           => '#ffffff',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'login_form_bg', array(
        'label'   => 'Cor de Fundo do FormulÃ¡rio',
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

    // --- Cor de Fundo da PÃ¡gina ---
    $wp_customize->add_setting('login_page_bg', array(
        'default'           => '#f0f2f5',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'login_page_bg', array(
        'label'   => 'Cor de Fundo da PÃ¡gina',
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

    // === SeÃ§Ã£o: Hero Moderno ===
    $wp_customize->add_section('intranet_hero_section', array(
        'title'    => 'Hero Moderno',
        'priority' => 35,
    ));

    // --- TÃ­tulo do Hero ---
    $wp_customize->add_setting('hero_title', array(
        'default'           => 'Bem-vindo Ã  Intranet',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('hero_title', array(
        'label'   => 'TÃ­tulo',
        'section' => 'intranet_hero_section',
        'type'    => 'text',
    ));

    // --- DescriÃ§Ã£o do Hero ---
    $wp_customize->add_setting('hero_description', array(
        'default'           => 'Um espaÃ§o pensado para vocÃª, servidor pÃºblico de TrÃªs CoraÃ§Ãµes, com acesso rÃ¡pido a informaÃ§Ãµes, documentos e serviÃ§os essenciais.',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('hero_description', array(
        'label'   => 'DescriÃ§Ã£o',
        'section' => 'intranet_hero_section',
        'type'    => 'textarea',
    ));

    // --- Texto do BotÃ£o ---
    $wp_customize->add_setting('hero_btn_text', array(
        'default'           => 'Saiba mais',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('hero_btn_text', array(
        'label'   => 'Texto do BotÃ£o',
        'section' => 'intranet_hero_section',
        'type'    => 'text',
    ));

    // --- URL do BotÃ£o ---
    $wp_customize->add_setting('hero_btn_url', array(
        'default'           => '/intranet',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control('hero_btn_url', array(
        'label'   => 'Link do BotÃ£o',
        'section' => 'intranet_hero_section',
        'type'    => 'url',
    ));

    // --- Cor Inicial do DegradÃª ---
    $wp_customize->add_setting('hero_gradient_start', array(
        'default'           => '#006494',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'hero_gradient_start', array(
        'label'   => 'Cor Inicial do DegradÃª',
        'section' => 'intranet_hero_section',
    )));

    // --- Cor Final do DegradÃª ---
    $wp_customize->add_setting('hero_gradient_end', array(
        'default'           => '#003554',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'hero_gradient_end', array(
        'label'   => 'Cor Final do DegradÃª',
        'section' => 'intranet_hero_section',
    )));

    // === SeÃ§Ã£o: Atalhos RÃ¡pidos ===
    $wp_customize->add_section('intranet_atalhos_section', array(
        'title'    => 'Atalhos RÃ¡pidos',
        'priority' => 40,
    ));

    $wp_customize->add_setting('atalhos_data', array(
        'default'           => json_encode(intranet_atalhos_defaults()),
        'sanitize_callback' => 'intranet_sanitize_atalhos_json',
        'transport'         => 'refresh',
    ));

    $wp_customize->add_control(new Intranet_Atalhos_Repeater_Control($wp_customize, 'atalhos_data', array(
        'label'       => 'Atalhos',
        'description' => 'Adicione, remova e reordene os atalhos. Deixe o campo "Nome" vazio para ocultar.',
        'section'     => 'intranet_atalhos_section',
    )));

    // === SeÃ§Ã£o: PrÃ³ximo Pagamento ===
    $next_payment_defaults = intranet_next_payment_defaults();

    $wp_customize->add_section('intranet_next_payment_section', array(
        'title'       => 'PrÃ³ximo Pagamento',
        'priority'    => 45,
        'description' => 'Configura o banner institucional exibido acima de MÃ©tricas Importantes na pÃ¡gina inicial.',
    ));

    $wp_customize->add_setting('intranet_next_payment_payment_date', array(
        'default'           => $next_payment_defaults['payment_date'],
        'sanitize_callback' => 'intranet_sanitize_next_payment_date',
    ));
    $wp_customize->add_control('intranet_next_payment_payment_date', array(
        'label'   => 'Data do pagamento',
        'section' => 'intranet_next_payment_section',
        'type'    => 'date',
    ));

    $wp_customize->add_setting('intranet_next_payment_payment_title', array(
        'default'           => $next_payment_defaults['payment_title'],
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('intranet_next_payment_payment_title', array(
        'label'   => 'TÃ­tulo do PNG / fallback',
        'section' => 'intranet_next_payment_section',
        'type'    => 'text',
    ));

    $wp_customize->add_setting('intranet_next_payment_payment_icon', array(
        'default'           => $next_payment_defaults['payment_icon'],
        'sanitize_callback' => 'sanitize_html_class',
    ));
    $wp_customize->add_control('intranet_next_payment_payment_icon', array(
        'label'       => 'Ãcone da Ã¡rea da data',
        'description' => 'Use uma classe Dashicons, por exemplo: dashicons-calendar-alt.',
        'section'     => 'intranet_next_payment_section',
        'type'        => 'text',
    ));

    $wp_customize->add_setting('intranet_next_payment_payment_image', array(
        'default'           => $next_payment_defaults['payment_image'],
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'intranet_next_payment_payment_image', array(
        'label'       => 'Imagem PNG personalizada',
        'description' => 'FaÃ§a upload de um PNG com fundo transparente.',
        'section'     => 'intranet_next_payment_section',
    )));

    $wp_customize->add_setting('intranet_next_payment_payment_image_width', array(
        'default'           => $next_payment_defaults['payment_image_width'],
        'sanitize_callback' => function($value) use ($next_payment_defaults) {
            return intranet_sanitize_next_payment_number($value, 120, 420, $next_payment_defaults['payment_image_width']);
        },
    ));
    $wp_customize->add_control('intranet_next_payment_payment_image_width', array(
        'label'       => 'Largura mÃ¡xima da imagem (px)',
        'section'     => 'intranet_next_payment_section',
        'type'        => 'number',
        'input_attrs' => array('min' => 120, 'max' => 420, 'step' => 10),
    ));

    $wp_customize->add_setting('intranet_next_payment_message_title', array(
        'default'           => $next_payment_defaults['message_title'],
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('intranet_next_payment_message_title', array(
        'label'   => 'TÃ­tulo opcional da mensagem',
        'section' => 'intranet_next_payment_section',
        'type'    => 'text',
    ));

    $wp_customize->add_setting('intranet_next_payment_message_text', array(
        'default'           => $next_payment_defaults['message_text'],
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('intranet_next_payment_message_text', array(
        'label'   => 'Texto da mensagem',
        'section' => 'intranet_next_payment_section',
        'type'    => 'textarea',
    ));

    $wp_customize->add_setting('intranet_next_payment_message_icon', array(
        'default'           => $next_payment_defaults['message_icon'],
        'sanitize_callback' => 'sanitize_html_class',
    ));
    $wp_customize->add_control('intranet_next_payment_message_icon', array(
        'label'       => 'Ãcone da mensagem',
        'description' => 'Exemplos: dashicons-money-alt, dashicons-groups, dashicons-businessperson.',
        'section'     => 'intranet_next_payment_section',
        'type'        => 'text',
    ));

    $wp_customize->add_setting('intranet_next_payment_bg_start', array(
        'default'           => $next_payment_defaults['bg_start'],
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'intranet_next_payment_bg_start', array(
        'label'   => 'Cor principal do fundo',
        'section' => 'intranet_next_payment_section',
    )));

    $wp_customize->add_setting('intranet_next_payment_bg_end', array(
        'default'           => $next_payment_defaults['bg_end'],
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'intranet_next_payment_bg_end', array(
        'label'   => 'Cor secundÃ¡ria do fundo',
        'section' => 'intranet_next_payment_section',
    )));

    $wp_customize->add_setting('intranet_next_payment_accent_color', array(
        'default'           => $next_payment_defaults['accent_color'],
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'intranet_next_payment_accent_color', array(
        'label'   => 'Cor do destaque',
        'section' => 'intranet_next_payment_section',
    )));

    $wp_customize->add_setting('intranet_next_payment_text_color', array(
        'default'           => $next_payment_defaults['text_color'],
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'intranet_next_payment_text_color', array(
        'label'   => 'Cor do texto principal',
        'section' => 'intranet_next_payment_section',
    )));

    $wp_customize->add_setting('intranet_next_payment_secondary_color', array(
        'default'           => $next_payment_defaults['secondary_color'],
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'intranet_next_payment_secondary_color', array(
        'label'   => 'Cor dos elementos secundÃ¡rios',
        'section' => 'intranet_next_payment_section',
    )));

    $wp_customize->add_setting('intranet_next_payment_message_color', array(
        'default'           => $next_payment_defaults['message_color'],
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'intranet_next_payment_message_color', array(
        'label'   => 'Cor do texto da mensagem',
        'section' => 'intranet_next_payment_section',
    )));

    $wp_customize->add_setting('intranet_next_payment_border_radius', array(
        'default'           => $next_payment_defaults['border_radius'],
        'sanitize_callback' => function($value) use ($next_payment_defaults) {
            return intranet_sanitize_next_payment_number($value, 8, 48, $next_payment_defaults['border_radius']);
        },
    ));
    $wp_customize->add_control('intranet_next_payment_border_radius', array(
        'label'       => 'Raio das bordas',
        'section'     => 'intranet_next_payment_section',
        'type'        => 'number',
        'input_attrs' => array('min' => 8, 'max' => 48, 'step' => 1),
    ));

    $wp_customize->add_setting('intranet_next_payment_shadow_blur', array(
        'default'           => $next_payment_defaults['shadow_blur'],
        'sanitize_callback' => function($value) use ($next_payment_defaults) {
            return intranet_sanitize_next_payment_number($value, 0, 80, $next_payment_defaults['shadow_blur']);
        },
    ));
    $wp_customize->add_control('intranet_next_payment_shadow_blur', array(
        'label'       => 'Intensidade da sombra',
        'section'     => 'intranet_next_payment_section',
        'type'        => 'number',
        'input_attrs' => array('min' => 0, 'max' => 80, 'step' => 1),
    ));

    $wp_customize->add_setting('intranet_next_payment_min_height', array(
        'default'           => $next_payment_defaults['min_height'],
        'sanitize_callback' => function($value) use ($next_payment_defaults) {
            return intranet_sanitize_next_payment_number($value, 160, 420, $next_payment_defaults['min_height']);
        },
    ));
    $wp_customize->add_control('intranet_next_payment_min_height', array(
        'label'       => 'Altura mÃ­nima do banner',
        'section'     => 'intranet_next_payment_section',
        'type'        => 'number',
        'input_attrs' => array('min' => 160, 'max' => 420, 'step' => 1),
    ));
}
add_action('customize_register', 'intranet_customize_register');

// Carregar CSS e scripts customizados na tela de login
function intranet_login_enqueue_scripts() {
    wp_enqueue_style('intranet-login-custom', get_template_directory_uri() . '/assets/css/login-custom.css', array(), '1.0.0');
}
add_action('login_enqueue_scripts', 'intranet_login_enqueue_scripts');

// Injetar CSS dinÃ¢mico baseado nas configuraÃ§Ãµes do Personalizador
function intranet_login_dynamic_css() {
    $button_color = get_theme_mod('login_button_color', '#2196F3');
    $form_bg      = get_theme_mod('login_form_bg', '#ffffff');
    $text_color   = get_theme_mod('login_text_color', '#333333');
    $page_bg      = get_theme_mod('login_page_bg', '#f0f2f5');

    // Calcula versÃ£o mais escura do botÃ£o para o hover
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
        .login .language-switcher { display: none ! important; }
    </style>';
}
add_action('login_head', 'intranet_login_dynamic_css');

// Injetar CSS dinÃ¢mico do Hero Moderno
function intranet_hero_dynamic_css() {
    if ( ! is_front_page()) return;

    $gradient_start = get_theme_mod('hero_gradient_start', '#006494');
    $gradient_end   = get_theme_mod('hero_gradient_end', '#003554');

    echo '<style id="intranet-hero-dynamic">
        .hero-moderno {
            background: linear-gradient(135deg, ' . esc_attr($gradient_start) . ', ' . esc_attr($gradient_end) . ') ! important;
        }
    </style>';
}
add_action('wp_head', 'intranet_hero_dynamic_css');

// FunÃ§Ã£o auxiliar para escurecer uma cor hex
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

// Personalizar o logo da tela de login (com tamanho configurÃ¡vel)
function intranet_login_logo() {
    $logo_url = get_theme_mod('login_logo', get_template_directory_uri() . '/assets/images/logo.png');
    $logo_width = get_theme_mod('login_logo_width', '180');
    echo '<style>#login h1 a { background-image: url(' . esc_url($logo_url) . ') ! important; background-size: contain ! important; width: ' . esc_attr($logo_width) . 'px ! important; height: auto ! important; }</style>';
}
add_action('login_head', 'intranet_login_logo');

// Traduzir textos da tela de login via filtro gettext
function intranet_login_translations($translated, $text, $domain) {
    if ($domain !== 'default') return $translated;

    // Detecta se estÃ¡ na tela de login
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    $is_login_page = (isset($_GET['action']) && $_GET['action'] === 'logout')
        || (isset($_GET['loggedout']) && $_GET['loggedout'] === 'true')
        || (function_exists('get_current_screen') && $screen && isset($screen->id) && $screen->id === 'login')
        || (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'wp-login.php');
    if ( ! $is_login_page) return $translated;

    $label_username = get_theme_mod('login_label_username', '');
    $label_password = get_theme_mod('login_label_password', '');
    $button_text    = get_theme_mod('login_button_text', '');
    $lost_pw_text   = get_theme_mod('login_lost_password_text', '');

    $replacements = array();

    if ( ! empty($label_username)) {
        $replacements['Username or Email Address'] = $label_username;
        $replacements['Username'] = $label_username;
    }
    if ( ! empty($label_password)) {
        $replacements['Password'] = $label_password;
    }
    if ( ! empty($button_text)) {
        $replacements['Log In'] = $button_text;
        $replacements['Log in'] = $button_text;
    }
    if ( ! empty($lost_pw_text)) {
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
    $message = get_theme_mod('login_message_text', 'VocÃª estÃ¡ desconectado agora.');
    return '<p class="login-message">' . esc_html($message) . '</p>';
}
add_filter('login_message', 'intranet_login_message');

// Adicionar imagem lateral e painel do formulÃ¡rio na tela de login
function intranet_login_side_image() {
    $side_image_url = esc_url(get_theme_mod('login_side_image', get_template_directory_uri() . '/assets/images/maria-fumaca.jpg'));
    $back_text = get_theme_mod('login_back_text', '');
    $home_url = esc_url(home_url());
    $script = '<script>document.addEventListener("DOMContentLoaded",function(){'
        . 'var l=document.getElementById("login");if(! l)return;'
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

// ===================================================================
// 11. Perfil: upload, avatar, e-mail e mÃ­dia
// ===================================================================

// Conceder permissÃ£o de upload para Subscribers
function intranet_allow_subscriber_uploads() {
    $subscriber = get_role('subscriber');
    if ($subscriber && ! $subscriber->has_cap('upload_files')) {
        $subscriber->add_cap('upload_files');
    }
}
add_action('init', 'intranet_allow_subscriber_uploads');

// Permitir upload de mÃ­dia via AJAX para todos os logados
function intranet_allow_upload_for_profile($response, $handler, $action) {
    if ($action === 'upload-attachment') {
        if ( ! current_user_can('upload_files') ) {
            $user = wp_get_current_user();
            if ($user->exists()) {
                $user->add_cap('upload_files');
            }
        }
    }
    return $response;
}
add_filter('wp_prepare_attachment_for_js', 'intranet_allow_upload_for_profile', 10, 3);

// Carregar CSS na pÃ¡gina de perfil (somente para nÃ£o-admins)
function intranet_admin_profile_styles($hook) {
    if ($hook !== 'profile.php' && $hook !== 'user-edit.php') {
        return;
    }
    if (current_user_can('administrator')) {
        return;
    }
    wp_enqueue_style('intranet-admin-profile', get_template_directory_uri() . '/assets/css/admin-profile.css', array(), '1.0.0');
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'intranet_admin_profile_styles');

// BotÃ£o "Voltar para o InÃ­cio" (somente para nÃ£o-admins)
function intranet_profile_back_button() {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if ( ! $screen || $screen->id !== 'profile' ) {
        return;
    }
    if (current_user_can('administrator')) {
        return;
    }
    echo '<div class="intranet-back-home-wrap"><a href="' . esc_url(home_url()) . '" class="button intranet-back-home-btn"><i class="dashicons dashicons-admin-home"></i> Voltar para o InÃ­cio</a></div>';
}
add_action('admin_notices', 'intranet_profile_back_button');

// Adicionar campo de foto do perfil (somente para nÃ£o-admins)
function intranet_profile_picture_field($user) {
    if (current_user_can('administrator')) {
        return;
    }
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
    if ( ! isset($_POST['intranet_profile_nonce']) || ! wp_verify_nonce($_POST['intranet_profile_nonce'], 'intranet_save_profile') ) {
        return;
    }
    if (isset($_POST['intranet_profile_photo'])) {
        $photo_url = esc_url_raw($_POST['intranet_profile_photo']);
        update_user_meta($user_id, 'intranet_profile_photo', $photo_url);
    }
}
add_action('profile_update', 'intranet_save_profile_picture');

// Adicionar nonce de seguranÃ§a (somente para nÃ£o-admins)
function intranet_profile_nonce_field($user) {
    if (current_user_can('administrator')) {
        return;
    }
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
        if ($user) {
            $user_id = $user->ID;
        }
    } elseif (is_object($id_or_email)) {
        $user_id = (int) $id_or_email->user_id;
    }
    if ($user_id > 0) {
        $custom_photo = get_user_meta($user_id, 'intranet_profile_photo', true);
        if ( ! empty($custom_photo) ) {
            $size = isset($args['size']) ? $args['size'] : 96;
            $class = isset($args['class']) ? $args['class'] : 'avatar avatar-' . $size . ' photo';
            $avatar = '<img alt="" src="' . esc_url($custom_photo) . '" class="' . esc_attr($class) . '" height="' . esc_attr($size) . '" width="' . esc_attr($size) . '" loading="lazy">';
        }
    }
    return $avatar;
}
add_filter('get_avatar', 'intranet_custom_avatar', 10, 3);

// Injetar JavaScript para upload de avatar no perfil (somente para nÃ£o-admins)
function intranet_profile_upload_script() {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if ( ! $screen || $screen->id !== 'profile' ) {
        return;
    }
    if (current_user_can('administrator')) {
        return;
    }
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

// Ocultar seÃ§Ã£o "Foto de Perfil Personalizada" do plugin Aniversariantes do Dia
function intranet_hide_plugin_avatar_field() {
    if ( ! class_exists('AniversariantesDoDia') ) {
        return;
    }
    $screen = get_current_screen();
    if ( ! $screen || $screen->id !== 'profile' ) {
        return;
    }
    ?>
    <script>
        jQuery(document).ready(function($) {
            $('h3:contains("Foto de Perfil Personalizada")').next('table.form-table').addBack().hide();
        });
    </script>
    <?php
}
add_action('admin_footer', 'intranet_hide_plugin_avatar_field');
remove_action( 'personal_options_update', 'send_confirmation_on_profile_email' );

// Atualizar e-mail diretamente sem confirmaÃ§Ã£o
function intranet_update_email_directly( $user_id ) {
    if ( ! isset( $_POST['email'] ) ) {
        return;
    }

    $new_email = trim( sanitize_email( $_POST['email'] ) );
    $user      = get_userdata( $user_id );

    if ( ! $user || $user->user_email === $new_email ) {
        return;
    }
    if ( ! is_email( $new_email ) ) {
        return;
    }

    $email_owner = email_exists( $new_email );
    if ( $email_owner && $email_owner !== $user_id ) {
        return;
    }

    wp_update_user( array( 'ID' => $user_id, 'user_email' => $new_email ) );
}
add_action( 'personal_options_update', 'intranet_update_email_directly', 20 );
add_action( 'edit_user_profile_update', 'intranet_update_email_directly', 20 );

// Restringe a biblioteca de mÃ­dia para mostrar apenas os uploads do prÃ³prio usuÃ¡rio
add_filter( 'ajax_query_attachments_args', 'filtrar_midia_por_usuario' );

function filtrar_midia_por_usuario( $query ) {
    // Se o usuÃ¡rio nÃ£o puder editar posts de outros (Ex: Assinantes), filtra as mÃ­dias
    if ( ! current_user_can( 'edit_others_posts' ) ) {
        $query['author'] = get_current_user_id();
    }
    return $query;
}
