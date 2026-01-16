  <footer class="footer-moderno">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-col">
          <h4><?php _e('Sobre Nós', 'intranet'); ?></h4>
          <ul>
            <li><a href="#"><?php _e('Nossa Prefeitura', 'intranet'); ?></a></li>
            <li><a href="#"><?php _e('Administração', 'intranet'); ?></a></li>
            <li><a href="#"><?php _e('Secretárias', 'intranet'); ?></a></li>
          </ul>
        </div>
        
        <div class="footer-col">
          <h4><?php _e('Recursos', 'intranet'); ?></h4>
          <ul>
            <li><a href="#"><?php _e('Documentos', 'intranet'); ?></a></li>
            <li><a href="#"><?php _e('Formulários', 'intranet'); ?></a></li>
            <li><a href="#"><?php _e('Políticas', 'intranet'); ?></a></li>
          </ul>
        </div>
        
        <div class="footer-col">
          <h4><?php _e('Suporte', 'intranet'); ?></h4>
          <ul>
            <li><a href="#"><?php _e('Helpdesk', 'intranet'); ?></a></li>
            <li><a href="#"><?php _e('Perguntas Frequentes', 'intranet'); ?></a></li>
            <li><a href="#"><?php _e('Contato TI', 'intranet'); ?></a></li>
          </ul>
        </div>
      </div>
      
      <div class="footer-bottom">
        &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php _e('Todos os direitos reservados.', 'intranet'); ?>
      </div>
    </div>
  </footer>
  
  <?php wp_footer(); ?>
</body>
</html>