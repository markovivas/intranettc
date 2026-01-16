# Intranet Corporativa Moderna

Tema WordPress moderno para intranet corporativa, focado em usabilidade, integração de notícias, atalhos rápidos, estatísticas, eventos e recursos internos para equipes municipais.

## Recursos Principais

- Layout responsivo e moderno
- Menu lateral personalizável
- Seção de destaques com integração ao WhatsApp
- Atalhos rápidos para Helpdesk, Artes, Diretório, Notícias, Folha de Pagamento, Eventos e Relatórios
- Listagem de notícias e eventos com paginação
- Previsão do tempo integrada (Open-Meteo API)
- Controle de acesso (Redirecionamento para login se não autenticado)
- Estatísticas e métricas importantes
- Integração com calendário e eventos via shortcode
- Feed de redes sociais (Instagram)
- Suporte a widgets na barra lateral e rodapé
- Suporte a imagens destacadas e thumbnails customizadas
- Tradução pronta para pt_BR
- Suporte a WebP
- SEO básico (title-tag)
- Lazy loading para imagens
- Tempo de leitura estimado nos posts

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

- Compatível com plugins de calendário e eventos.
- Suporte a widgets padrão do WordPress.

## Shortcodes Personalizados

*   `[temperatura]`: Exibe a previsão do tempo atual e para os próximos dias (configurado para Três Corações, MG).

## Controle de Acesso

*   **Bloqueio de Visitantes:** Usuários não logados são redirecionados automaticamente para a tela de login.
*   **Restrição de Painel:** Usuários sem permissão de administrador não têm acesso ao painel do WordPress e são redirecionados para a home após o login.
*   **Barra de Admin:** O logo do WordPress é removido da barra de administração.

## Plugins de terceiros

*   Login Designer (https://br.wordpress.org/plugins/login-designer/)

## Créditos

Desenvolvido por Marco Antonio Vivas
https://marcovivas.com/

---