<?php
/*
 * Template Name: Full Screen
 * Description: Um modelo de página que ocupa a tela inteira, sem rodapé ou barras laterais do tema, mas com cabeçalho.
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <?php get_header(); ?>
    <?php
    // Inicia o Loop do WordPress para buscar e exibir o conteúdo da página.
    while ( have_posts() ) : the_post();
        the_content();
    endwhile;
    ?>
    <?php wp_footer(); ?>
</body>
</html>