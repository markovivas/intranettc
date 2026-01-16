<?php get_header(); ?>

<main>
    <div class="container">
        <section class="error-404">
            <h1><?php _e('Página Não Encontrada', 'intranet'); ?></h1>
            <p><?php _e('Desculpe, a página que você está procurando não existe ou foi movida.', 'intranet'); ?></p>
            <p><a href="<?php echo esc_url(home_url('/')); ?>" class="btn"><?php _e('Voltar para a Página Inicial', 'intranet'); ?></a></p>
        </section>
    </div>
</main>

<?php get_footer(); ?>