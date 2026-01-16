<article class="noticia-item">
    <?php if (has_post_thumbnail()) : ?>
        <div class="noticia-imagem">
            <a href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail('noticia-thumb'); ?>
            </a>
        </div>
    <?php endif; ?>
    
    <div class="noticia-conteudo">
        <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
        <div class="noticia-meta">
            <span><?php echo get_the_date(); ?></span>
            <span><?php the_author(); ?></span>
        </div>
        <div class="noticia-resumo"><?php the_excerpt(); ?></div>
        <a href="<?php the_permalink(); ?>" class="leia-mais"><?php _e('Leia mais', 'intranet'); ?></a>
    </div>
</article>