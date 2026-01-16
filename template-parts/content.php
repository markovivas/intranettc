<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    <div class="post-meta">
        <span><?php _e('Publicado em:', 'intranet'); ?> <?php echo get_the_date(); ?></span>
    </div>
    <?php if (has_post_thumbnail()) : ?>
        <div class="post-thumbnail">
            <?php the_post_thumbnail('noticia-thumb'); ?>
        </div>
    <?php endif; ?>
    <div class="entry-summary">
        <?php the_excerpt(); ?>
    </div>
</article>