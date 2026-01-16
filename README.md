# Intranet Corporativa Moderna

Tema WordPress moderno para intranet corporativa, focado em usabilidade, integração de notícias, atalhos rápidos, estatísticas, eventos e recursos internos para equipes municipais.

## Recursos Principais

- Layout responsivo e moderno
- Menu lateral personalizável
- Seção de destaques com integração ao WhatsApp
- Atalhos rápidos para Helpdesk, Artes, Diretório, Notícias, Folha de Pagamento, Eventos e Relatórios
- Listagem de notícias e eventos com paginação
- Estatísticas e métricas importantes
- Integração com calendário e eventos via shortcode
- Feed de redes sociais (Instagram)
- Suporte a widgets na barra lateral e rodapé
- Suporte a imagens destacadas e thumbnails customizadas
- Tradução pronta para pt_BR
- Suporte a WebP
- SEO básico (title-tag)
- Lazy loading para imagens

## Estrutura de Pastas

```
wp-content/themes/intranet/
├── assets/
│   └── images/
├── languages/
│   ├── pt_BR.po
│   └── pt_BR.mo
├── template-parts/
│   └── content.php
├── 404.php
├── all-news.php
├── archive.php
├── content-noticia.php
├── footer.php
├── front-page.php
├── functions.php
├── header.php
├── index.php
├── page.php
├── search.php
├── sidebar.php
├── single.php
├── style.css
├── README.md
└── screenshot.png
```

## Instalação

1. Faça upload da pasta do tema para `wp-content/themes/intranet`.
2. Ative o tema pelo painel do WordPress.
3. Certifique-se de que as dependências externas (Font Awesome) estão carregando corretamente.
4. Configure os menus e widgets pelo painel.
5. Para atalhos e eventos, utilize os shortcodes sugeridos ou plugins compatíveis.

## Personalização

- Edite o arquivo `front-page.php` para alterar textos, atalhos e integrações.
- As cores principais podem ser ajustadas no CSS, especialmente para adaptar ao branding da organização.
- Para alterar ícones, utilize as classes do Font Awesome.
- Traduções podem ser ajustadas nos arquivos `.po` e `.mo` em `languages/`.

## Suporte a Plugins

- Compatível com plugins de calendário e eventos via shortcode.
- Suporte a widgets padrão do WordPress.

## Plugins de terceiros

*   Login Designer (https://br.wordpress.org/plugins/login-designer/)

## Integração com Active Directory (AD/LDAP)

Este tema inclui funcionalidades customizadas para autenticação e sincronização de usuários com o Active Directory (AD) via LDAP.

### 1. Configuração do Ambiente Docker

Para que a integração com o AD funcione, a extensão `php-ldap` precisa estar instalada no container do WordPress.

*   **Crie um `Dockerfile`** na mesma pasta do seu `docker-compose.yml` com o seguinte conteúdo:

    ```dockerfile
    FROM wordpress:latest
    RUN apt-get update && apt-get install -y libldap2-dev \
        && docker-php-ext-install ldap \
        && rm -rf /var/lib/apt/lists/*
    ```

*   **Modifique seu `docker-compose.yml`** para usar este `Dockerfile` para o serviço `wordpress_app`:

    ```yaml
    # ... (outros serviços)
    wordpress_app:
      depends_on:
        - wordpress_db
      build: . # Altere 'image: wordpress:latest' para 'build: .'
      container_name: wordpress_app
      # ... (outras configurações)
    ```

*   **Reconstrua e suba os containers:**

    ```bash
    docker-compose up --build -d
    ```

### 2. Configuração das Credenciais do AD no `wp-config.php`

Para a conexão com o AD, é utilizada uma conta de serviço com permissões de leitura. **Não use uma conta de administrador.**

Adicione as seguintes linhas ao seu `y/data/wordpress/wp-config.php` (antes de `/* That's all, stop editing! Happy publishing. */`), substituindo pelos seus dados reais:

```php
/**
 * Configurações de Autenticação do Active Directory (LDAP)
 * Credenciais da conta de serviço usada para ler o AD.
 */
define('AD_SERVICE_USER_DN', 'intranet.pmtc@PMTCAD.LOCAL.BR'); // UPN ou DN completo da conta de serviço
define('AD_SERVICE_USER_PASS', 'SenhaRealDaContaDeServico'); // Senha da conta de serviço
```

### 3. Funcionalidades no `functions.php`

O arquivo `y/data/wordpress/wp-content/themes/intranet/functions.php` contém o código para:

*   **Autenticação de Usuários:** Permite que usuários do AD façam login no WordPress usando suas credenciais do AD. Se o usuário não existir no WordPress, ele será criado automaticamente com a função de "Assinante".
*   **Sincronização de Usuários:** Uma ferramenta no painel administrativo para importar e atualizar usuários do AD em massa.

### 4. Utilizando a Ferramenta de Sincronização

Após configurar o Docker e o `wp-config.php`:

1.  Acesse o painel administrativo do WordPress.
2.  Vá para **Ferramentas > Sincronizar Usuários AD**.
3.  Clique no botão "Iniciar Sincronização de Usuários" para importar ou atualizar os usuários do seu Active Directory no WordPress.

**Importante:** A conta de serviço (`AD_SERVICE_USER_DN`) deve ter apenas as permissões mínimas necessárias para ler o diretório.


## Créditos

Desenvolvido por Marco Antonio Vivas
https://marcovivas.com/

---

## CODIGO PARA O LoginÉ possive
```json
{"login_designer":{"template":"01","bg_image":"https://intranet.local/wp-content/uploads/2025/09/maria-fumaca.jpg","bg_image_gallery":"bg_09","bg_color":"#ffffff","form_shadow":0,"form_shadow_opacity":0,"form_side_padding":40,"form_width":0,"field_bg":"#ffffff","field_border":2,"field_padding_top":6,"field_padding_bottom":6,"field_radius":3,"field_shadow":0,"field_shadow_opacity":0,"logo":"https://intranet.local/wp-content/uploads/2025/09/logo_tc.png","logo_width":160,"logo_height":160},"settings":{"login_designer_page":7,"branding_color":"#000000","branding_icon_color":"#000000"},"language_translator":{"translation":false}}
```

Para dúvidas ou sugestões, utilize o painel de administração do WordPress ou entre em contato com o desenvolvedor.