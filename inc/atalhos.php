<?php

function intranet_atalhos_defaults() {
    return array(
        array('label' => 'Adoção',             'url' => '/adocao',                         'icon' => 'fa-solid fa-dog',               'color' => '#9C27B0'),
        array('label' => 'Artes',              'url' => '/artes',                          'icon' => 'fa-solid fa-palette',           'color' => '#00A6FB'),
        array('label' => 'Contracheque',       'url' => 'https://servicos.cloud.el.com.br/mg-trescoracoes-pm/portal/', 'icon' => 'fa-solid fa-circle-dollar-to-slot', 'color' => '#FF9800'),
        array('label' => 'Empregos',           'url' => '/empregos',                       'icon' => 'fas fa-file-alt',               'color' => '#F44336'),
        array('label' => 'Eventos',            'url' => '/calendario',                     'icon' => 'fas fa-calendar-alt',           'color' => '#003554'),
        array('label' => 'Formulários',        'url' => '/formularios',                    'icon' => 'fas fa-folder-open',            'color' => '#006494'),
        array('label' => 'Helpdesk',           'url' => '/helpdesk',                       'icon' => 'fas fa-headset',                'color' => '#E91E63'),
        array('label' => 'MADV',               'url' => 'https://trescoracoes.mg.gov.br/madv-mapa-de-atividade-diario-do-veiculo', 'icon' => 'fas fa-car', 'color' => '#FFB700'),
        array('label' => 'Notícias',           'url' => '/noticias',                       'icon' => 'fas fa-newspaper',              'color' => '#4CAF50'),
        array('label' => 'Patrimônios',        'url' => '/patrimonios/',                   'icon' => 'fa-regular fa-clipboard',       'color' => '#795548'),
        array('label' => 'Recursos Humanos',   'url' => '/rh-recursos-humanos/',           'icon' => 'fa-solid fa-people-group',      'color' => '#00BCD4'),
        array('label' => 'Site Oficial',       'url' => 'https://www.trescoracoes.mg.gov.br/', 'icon' => 'fa-solid fa-globe',         'color' => '#3F51B5'),
        array('label' => 'WebMail',            'url' => 'https://webmail.trescoracoes.mg.gov.br', 'icon' => 'fas fa-envelope',        'color' => '#FF5722'),
        array('label' => 'WhatsApp',           'url' => 'https://web.whatsapp.com/',        'icon' => 'fab fa-whatsapp',               'color' => '#25D366'),
    );
}

function intranet_get_atalhos() {
    $data = get_theme_mod('atalhos_data');
    if (!empty($data)) {
        $decoded = json_decode($data, true);
        if (is_array($decoded) && !empty($decoded)) {
            return $decoded;
        }
    }
    $migrated = intranet_migrate_old_atalhos();
    if ($migrated !== false) {
        return $migrated;
    }
    return intranet_atalhos_defaults();
}

function intranet_migrate_old_atalhos() {
    $old = get_theme_mod('atalho_1_label');
    if ($old === null) {
        return false;
    }
    $atalhos = array();
    for ($i = 1; $i <= 15; $i++) {
        $label = get_theme_mod("atalho_{$i}_label");
        if (empty($label)) {
            continue;
        }
        $atalhos[] = array(
            'label' => $label,
            'url'   => get_theme_mod("atalho_{$i}_url", ''),
            'icon'  => get_theme_mod("atalho_{$i}_icon", ''),
            'color' => get_theme_mod("atalho_{$i}_color", '#607D8B'),
        );
    }
    if (!empty($atalhos)) {
        set_theme_mod('atalhos_data', json_encode($atalhos));
    }
    return $atalhos;
}

function intranet_render_atalhos() {
    $atalhos = intranet_get_atalhos();
    if (empty($atalhos)) {
        return;
    }
    ?>
    <section class="atalhos-modernos">
        <div class="atalhos-grid">
            <?php foreach ($atalhos as $atalho) :
                $label = $atalho['label'] ?? '';
                if (empty($label)) {
                    continue;
                }
                $url    = $atalho['url'] ?? '';
                $icon   = $atalho['icon'] ?? '';
                $target = strpos($url, 'http') === 0 ? ' target="_blank" rel="noopener noreferrer"' : '';
            ?>
            <a href="<?php echo esc_url($url); ?>" class="atalho-card"<?php echo $target; ?>>
                <?php if ($icon) : ?>
                    <i class="<?php echo esc_attr($icon); ?>"></i>
                <?php endif; ?>
                <span><?php echo esc_html($label); ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
}

function intranet_atalhos_dynamic_css() {
    if (!is_front_page()) {
        return;
    }
    $atalhos = intranet_get_atalhos();
    $css = '';
    $n = 1;
    foreach ($atalhos as $atalho) {
        $color = $atalho['color'] ?? '';
        if (empty($color)) {
            $n++;
            continue;
        }
        $hex = ltrim($color, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $rgba = "rgba({$r}, {$g}, {$b}, 0.1)";
        $css .= ".atalho-card:nth-child({$n}) i { background: {$rgba}; color: {$color}; }";
        $css .= ".atalho-card:hover:nth-child({$n}) { border-color: {$color}; }";
        $n++;
    }
    if (!empty($css)) {
        echo '<style id="intranet-atalhos-dynamic">' . $css . '</style>';
    }
}
add_action('wp_head', 'intranet_atalhos_dynamic_css');

function intranet_fa_icons_list() {
    return array(
        'fas fa-home', 'fas fa-user', 'fas fa-users', 'fas fa-user-tie', 'fas fa-user-cog',
        'fa-solid fa-people-group', 'fas fa-people-arrows', 'fas fa-user-friends',
        'fas fa-envelope', 'fas fa-envelope-open', 'fas fa-phone', 'fas fa-phone-alt',
        'fas fa-phone-square-alt', 'fas fa-headset', 'fas fa-fax', 'fas fa-mobile-alt',
        'fas fa-comment', 'fas fa-comments', 'fas fa-comment-dots', 'fab fa-whatsapp',
        'fab fa-telegram', 'fab fa-facebook', 'fab fa-instagram', 'fab fa-youtube',
        'fab fa-twitter', 'fab fa-linkedin', 'fab fa-github', 'fab fa-wordpress',
        'fa-solid fa-globe', 'fas fa-globe-americas', 'fas fa-globe-africa',
        'fas fa-link', 'fas fa-external-link-alt',
        'fas fa-file', 'fas fa-file-alt', 'fas fa-file-pdf', 'fas fa-file-word',
        'fas fa-file-excel', 'fas fa-file-image', 'fas fa-file-archive',
        'fas fa-folder', 'fas fa-folder-open', 'fas fa-folder-plus', 'fas fa-folder-minus',
        'fas fa-newspaper', 'fas fa-calendar', 'fas fa-calendar-alt', 'fas fa-calendar-check',
        'fas fa-calendar-plus', 'fas fa-calendar-minus', 'fas fa-calendar-day',
        'fas fa-clock', 'fas fa-bell', 'fas fa-bell-slash', 'fas fa-cog', 'fas fa-cogs',
        'fas fa-tools', 'fas fa-wrench', 'fas fa-sliders-h', 'fas fa-chart-bar',
        'fas fa-chart-line', 'fas fa-chart-pie', 'fas fa-chart-simple',
        'fas fa-chart-column', 'fas fa-table', 'fas fa-database', 'fas fa-cloud',
        'fas fa-upload', 'fas fa-download', 'fas fa-print', 'fas fa-search',
        'fas fa-search-plus', 'fas fa-search-minus', 'fas fa-filter', 'fas fa-sort',
        'fas fa-sort-alpha-down', 'fas fa-sort-alpha-up', 'fas fa-sort-numeric-down',
        'fas fa-sort-numeric-up', 'fa-solid fa-circle-dollar-to-slot',
        'fas fa-dollar-sign', 'fas fa-euro-sign', 'fas fa-coins', 'fas fa-wallet',
        'fas fa-money-check', 'fas fa-money-check-alt', 'fas fa-credit-card',
        'fas fa-car', 'fas fa-bus', 'fas fa-truck', 'fas fa-taxi', 'fas fa-bicycle',
        'fas fa-motorcycle', 'fas fa-traffic-light', 'fas fa-road',
        'fas fa-train', 'fas fa-train-subway', 'fas fa-ship', 'fas fa-plane',
        'fas fa-helicopter', 'fas fa-building', 'fas fa-hospital', 'fas fa-school',
        'fas fa-university', 'fas fa-church', 'fas fa-store', 'fas fa-warehouse',
        'fas fa-heart', 'fas fa-heartbeat', 'fas fa-star',
        'fas fa-star-half-alt', 'fas fa-thumbs-up', 'fas fa-thumbs-down',
        'fas fa-check', 'fas fa-check-circle', 'fas fa-check-square',
        'fas fa-times', 'fas fa-times-circle', 'fas fa-info', 'fas fa-info-circle',
        'fas fa-exclamation', 'fas fa-exclamation-circle', 'fas fa-exclamation-triangle',
        'fas fa-question', 'fas fa-question-circle',
        'fas fa-plus', 'fas fa-plus-circle', 'fas fa-minus', 'fas fa-minus-circle',
        'fas fa-edit', 'fas fa-pen', 'fas fa-pen-alt', 'fas fa-pen-fancy',
        'fas fa-pencil-alt', 'fas fa-eraser', 'fas fa-trash', 'fas fa-trash-alt',
        'fas fa-eye', 'fas fa-eye-slash', 'fas fa-lock', 'fas fa-lock-open',
        'fas fa-key', 'fas fa-shield-alt', 'fas fa-shield-halved',
        'fas fa-map', 'fas fa-map-marker-alt', 'fas fa-map-pin', 'fas fa-map-signs',
        'fas fa-route', 'fas fa-location-dot', 'fas fa-location-arrow',
        'fas fa-flag', 'fas fa-flag-checkered', 'fas fa-certificate',
        'fas fa-award', 'fas fa-medal', 'fas fa-trophy', 'fas fa-crown',
        'fas fa-gem', 'fas fa-diamond', 'fas fa-fire', 'fas fa-water',
        'fas fa-sun', 'fas fa-moon', 'fas fa-cloud-sun', 'fas fa-cloud-rain',
        'fas fa-cloud-sun-rain', 'fas fa-snowflake', 'fas fa-bolt', 'fas fa-wind',
        'fas fa-umbrella', 'fas fa-thermometer-half', 'fas fa-temperature-high',
        'fas fa-temperature-low', 'fas fa-box', 'fas fa-boxes',
        'fa-regular fa-clipboard', 'fas fa-clipboard-list', 'fas fa-clipboard-check',
        'fas fa-handshake', 'fas fa-hand-holding-heart', 'fas fa-hands-helping',
        'fas fa-donate', 'fas fa-gift', 'fas fa-birthday-cake', 'fas fa-balance-scale',
        'fas fa-gavel', 'fas fa-briefcase', 'fas fa-briefcase-medical',
        'fas fa-suitcase', 'fas fa-shopping-cart', 'fas fa-shopping-bag',
        'fas fa-shopping-basket', 'fas fa-tag', 'fas fa-tags', 'fas fa-qrcode',
        'fas fa-barcode', 'fas fa-ticket', 'fas fa-ticket-alt',
        'fas fa-film', 'fas fa-video', 'fas fa-camera', 'fas fa-camera-retro',
        'fas fa-image', 'fas fa-images', 'fas fa-paint-brush',
        'fa-solid fa-palette', 'fas fa-paint-roller', 'fas fa-palette',
        'fas fa-music', 'fas fa-headphones', 'fas fa-microphone',
        'fas fa-play', 'fas fa-pause', 'fas fa-stop', 'fas fa-forward',
        'fas fa-backward', 'fas fa-volume-up', 'fas fa-volume-down',
        'fas fa-volume-mute', 'fas fa-gamepad', 'fas fa-dice', 'fas fa-puzzle-piece',
        'fas fa-robot', 'fas fa-microchip', 'fas fa-laptop', 'fas fa-laptop-code',
        'fas fa-desktop', 'fas fa-tablet-alt', 'fas fa-mobile',
        'fas fa-plug', 'fas fa-battery-full', 'fas fa-battery-three-quarters',
        'fas fa-wifi', 'fas fa-signal', 'fas fa-broadcast-tower',
        'fas fa-paw', 'fa-solid fa-dog',
        'fas fa-cat', 'fas fa-horse', 'fas fa-horse-head', 'fas fa-tree',
        'fas fa-seedling', 'fas fa-leaf', 'fas fa-recycle',
        'fas fa-book', 'fas fa-book-open', 'fas fa-bookmark', 'fas fa-archive',
        'fas fa-graduation-cap', 'fas fa-chalkboard-teacher',
        'fas fa-address-book', 'fas fa-address-card', 'fas fa-id-card',
        'fas fa-phone-volume', 'fas fa-tty', 'fas fa-sign-language',
        'fas fa-accessible-icon', 'fas fa-universal-access', 'fas fa-wheelchair',
        'fas fa-couch', 'fas fa-bed', 'fas fa-stethoscope', 'fas fa-syringe',
        'fas fa-ambulance', 'fas fa-first-aid',
        'fas fa-medkit', 'fas fa-prescription', 'fas fa-pills',
        'fas fa-utensils', 'fas fa-coffee', 'fas fa-mug-hot', 'fas fa-cookie-bite',
        'fas fa-apple-alt', 'fas fa-carrot', 'fas fa-drumstick-bite',
    );
}

if (class_exists('WP_Customize_Control')) {

class Intranet_Atalhos_Repeater_Control extends WP_Customize_Control {
    public $type = 'atalhos_repeater';

    public function enqueue() {
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script(
            'intranet-customizer-atalhos',
            get_template_directory_uri() . '/assets/js/customizer-atalhos.js',
            array('jquery', 'jquery-ui-sortable'),
            '1.0',
            true
        );
        wp_localize_script('intranet-customizer-atalhos', 'intranetAtalhos', array(
            'icons'    => intranet_fa_icons_list(),
            'defaults' => intranet_atalhos_defaults(),
        ));
        wp_enqueue_style(
            'intranet-customizer-atalhos',
            get_template_directory_uri() . '/assets/css/customizer-atalhos.css',
            array(),
            '1.0'
        );
    }

    public function render_content() {
        ?>
        <label>
            <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <?php if (!empty($this->description)) : ?>
                <span class="description customize-control-description"><?php echo esc_html($this->description); ?></span>
            <?php endif; ?>
        </label>
        <div class="atalhos-repeater">
            <ul class="atalhos-repeater-list"></ul>
            <button type="button" class="button atalhos-add-item">+ Adicionar Atalho</button>
        </div>
        <textarea class="atalhos-repeater-value" <?php $this->link(); ?> hidden></textarea>
        <?php
    }
}

}
