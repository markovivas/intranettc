<?php get_header(); ?>

<main class="single-post-container">
    <div class="container">
        <!-- Breadcrumbs -->
        <div class="breadcrumbs">
            <?php if (function_exists('intranet_breadcrumbs')) intranet_breadcrumbs(); ?>
        </div>

        <article id="post-<?php the_ID(); ?>" <?php post_class('post-content'); ?>>
            <!-- Cabeçalho do Post -->
            <header class="post-header">
                <?php if (has_post_thumbnail()) : ?>
                    <div class="post-featured-image">
                        <?php the_post_thumbnail('full', ['class' => 'featured-img']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="post-meta-wrapper">
                    <div class="post-categories">
                        <?php the_category(', '); ?>
                    </div>
                    <h1 class="post-title"><?php the_title(); ?></h1>
                    <div class="post-meta">
                        <div class="author-avatar">
                            <?php echo get_avatar(get_the_author_meta('ID'), 48); ?>
                        </div>
                        <div class="meta-content">
                            <span class="author-name"><?php the_author(); ?></span>
                            <div class="meta-details">
                                <time datetime="<?php echo get_the_date('c'); ?>" class="post-date">
                                    <i class="far fa-calendar-alt"></i> <?php echo get_the_date('d \d\e F \d\e Y'); ?>
                                </time>
                                <span class="reading-time">
                                    <i class="far fa-clock"></i> <?php echo function_exists('estimated_reading_time') ? estimated_reading_time() : ''; ?> min de leitura
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Conteúdo do Post -->
            <div class="entry-content">
                <?php 
                the_content(); 
                wp_link_pages([
                    'before' => '<div class="page-links">' . __('Páginas:', 'intranet'),
                    'after' => '</div>',
                ]);
                ?>
            </div>
            
            <!-- Rodapé do Post -->
            <footer class="post-footer">
                <div class="post-tags">
                    <?php the_tags('<span class="tags-label"><i class="fas fa-tags"></i> Tags:</span> ', ', ', ''); ?>
                </div>
                <div class="post-share">
                    <span class="share-label"><?php _e('Compartilhar:', 'intranet'); ?></span>
                    <a href="#" class="share-btn facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="share-btn twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="share-btn linkedin"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="share-btn whatsapp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </footer>
        </article>

        <!-- Autor Box -->
        <div class="author-box">
            <div class="author-avatar">
                <?php echo get_avatar(get_the_author_meta('ID'), 96); ?>
            </div>
            <div class="author-info">
                <h4><?php _e('Sobre', 'intranet'); ?> <?php the_author(); ?></h4>
                <p><?php echo get_the_author_meta('description'); ?></p>
                <a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>" class="author-link">
                    <?php _e('Ver todos os posts', 'intranet'); ?> <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Posts Relacionados -->
        <?php get_template_part('template-parts/related-posts'); ?>

        <!-- Comentários -->
        <?php if (comments_open() || get_comments_number()) : ?>
            <div class="comments-section">
                <?php comments_template(); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php get_sidebar(); ?>
</main>
<?php get_footer(); ?>