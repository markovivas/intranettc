<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
  <header class="header-moderno">
    <div class="container header-container">
      <div class="logo-container">
  <a href="<?php echo home_url(); ?>">
    <?php if (file_exists(get_template_directory() . '/assets/images/logo.png')): ?>
      <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo.png" alt="<?php echo get_bloginfo('name'); ?>">
    <?php else: ?>
      <h1><?php echo get_bloginfo('name'); ?></h1>
    <?php endif; ?>
  </a>
</div>
      
      <nav id="site-navigation" class="nav-principal">
  <?php
    wp_nav_menu(array(
      'theme_location' => 'menu-principal', // Corrigido para o mesmo nome do functions.php
      'container' => false,
      'items_wrap' => '<ul id="primary-menu" class="menu-items">%3$s</ul>'
    ));
  ?>
</nav>
      <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
        <i class="fas fa-bars" aria-hidden="true"></i>
        <span class="sr-only"><?php _e('Menu', 'intranet'); ?></span>
      </button>
      
      <div class="user-menu">
  <?php if (is_user_logged_in()) : 
    $current_user = wp_get_current_user();
    echo get_avatar($current_user->ID, 40, '', $current_user->display_name, ['class' => 'user-avatar']);
  else : ?>
    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/avatar-default.png" alt="Avatar" class="user-avatar" width="40" height="40">
  <?php endif; ?>
</div>
    </div>
  </header>