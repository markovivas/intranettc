<aside class="sidebar">
    <div class="container">
        <?php
        if (is_active_sidebar('sidebar-1')) {
            dynamic_sidebar('sidebar-1');
        } else {
            ?>
            <div class="widget">
                <h3><?php _e('Links Úteis', 'intranet'); ?></h3>
                <ul>
                    <li><a href="#"><?php _e('Helpdesk', 'intranet'); ?></a></li>
                    <li><a href="#"><?php _e('Relatórios', 'intranet'); ?></a></li>
                    <li><a href="#"><?php _e('Diretório', 'intranet'); ?></a></li>
                </ul>
            </div>
            <?php
        }
        ?>
    </div>
</aside>