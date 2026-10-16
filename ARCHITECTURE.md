# ARCHITECTURE — Drogacare (PHP + MySQL)

Documento para engenheiros. Para o guia do cliente veja o [`README.md`](README.md) e para instalação no cPanel
[`docs/DEPLOY-CPANEL.md`](docs/DEPLOY-CPANEL.md).

## 1. Visão geral

Monólito server-side em **PHP 8.1+ puro** (sem framework e sem Composer em produção), banco **MySQL 5.7+/MariaDB 10.3+**
via **PDO**. Site público e painel administrativo vivem na mesma aplicação.

| Camada | Tecnologia |
|---|---|
| Linguagem | PHP 8.1+ (`strict_types`), extensões `pdo_mysql`, `mbstring`, `openssl` |
| Banco | MySQL/MariaDB, InnoDB, `utf8mb4_unicode_ci`, prepared statements |
| UI | Templates PHP + Tailwind CSS (compilado e versionado em `public/assets/css/app.css`) + JS vanilla |
| Fonte | Raleway (woff2 hospedado em `public/assets/fonts`), fallback Arial |
| Ícones | SVGs Lucide embutidos (`app/Core/icons.php`) + marcas (Facebook/Instagram/YouTube/WhatsApp) |
| E-mail | Cliente SMTP próprio (`app/Core/Mailer.php`) ou simulação em log |
| Testes | Script E2E HTTP (`tests/e2e.php`) |

Node.js é necessário **apenas** para recompilar o CSS (ver §10).

## 2. Estrutura de pastas

```
.htaccess                 # (raiz) encaminha para public/ e bloqueia pastas privadas
index.php, install.php    # entradas na raiz (sem reescrita de URL); delegam a public/index.php
public/                   # única pasta que deve ser exposta pelo servidor web
  index.php               # front controller
  .htaccess               # rewrite para index.php + cache de estáticos
  assets/{css,js,fonts,img}
app/
  bootstrap.php           # autoload (App\ -> app/), config, timezone, log de erros
  routes.php              # tabela de rotas
  Core/                   # App, Router, Request, View, Config, Database, Session, Csrf, Auth,
                          # Rbac, Password, Audit, Mailer, Content, Embed, Installer, helpers, icons
  Models/                 # Company (singleton), ContentRepo (posts/pages)
  Controllers/            # SiteController, InstallController, Admin/*
  Views/                  # layouts/, site/, auth/, admin/, install/, partials/
config/
  config.example.php      # modelo; config.php é gerado pelo instalador (gitignored)
database/schema.sql       # esquema idempotente
storage/                  # logs, sessões, installed.lock (privado)
bin/install.php           # instalador CLI
assets-src/input.css, tailwind.config.js, package.json   # build do CSS
tests/e2e.php
```

## 3. Fluxo de requisição

`public/index.php` → `bootstrap.php` → `App::run()`:

1. Envia cabeçalhos de segurança (§7).
2. Se não instalado (`Config::installed()` = `config.php` válido **e** `storage/installed.lock`), só existe a rota `/install`; qualquer outra URL redireciona para ela.
3. `Router::dispatch()` casa método + caminho (regex; `{id}` numérico, demais `{param}` = `[^/]+`).
4. Para `/admin` e `/install`: inicia sessão (lazy), `Cache-Control: no-store` e `noindex`.
5. POSTs passam por verificação **CSRF central** (419 em falha).
6. Controller autentica/autoriza (`Auth::require`, `Auth::requirePermission`) e renderiza com `View::render(view, data, layout)`.
7. Exceções não tratadas são logadas e geram 500 genérico (detalhes só com `debug` = true).

O site público **não inicia sessão** e portanto não grava cookies.

Subpastas: `Config::basePath()` deriva o prefixo de `SCRIPT_NAME`. Quando a página é servida por `index.php`/`install.php` da raiz
(sem reescrita), os estáticos são servidos de `<base>/public/...` (`Config::publicBase()` / `static_url()`); com reescrita ou com a raiz do
domínio em `public/`, de `<base>/assets/...`.

## 4. Configuração (substitui variáveis de ambiente)

`config/config.php` (gerado pelo instalador, permissão 640, fora do Git):

| Chave | Descrição |
|---|---|
| `app_url` | URL pública, usada em sitemap, links de e-mail e canonical |
| `debug` | `false` em produção |
| `db.host/port/name/user/pass` | Conexão MySQL |
| `smtp_url` | `smtps://user:pass@host:465` (SSL) ou `smtp://user:pass@host:587` (STARTTLS). Vazio = simulação |
| `mail_from` | Remetente, ex.: `Drogacare <nao-responda@dominio>` |

## 5. Modelo de dados

Tabelas em `database/schema.sql` (idempotente):

- **users** — `role ENUM('SUPERADMIN','EDITOR_CONTEUDO','VISUALIZADOR')`, `active`, `must_change_password`, `reset_token` (sha256 do token), `reset_token_expiry`, `failed_login_attempts`, `locked_until`, `last_login_at`; `email` único.
- **audit_logs** — `user_id` (FK `ON DELETE SET NULL`), `user_email` (snapshot), `action`, `entity`, `entity_id`, `details`, `ip`, `created_at`; índices por data, usuário, entidade e (ip, action, created_at) usado no throttling.
- **pages**/**posts** `cover_url`: imagem enviada; **pages** também tem `show_on_home` (entra no feed da home).
- **posts** — `title`, `slug` único, `excerpt`, `content`, `cover_url`, `meta_title`, `meta_description`, `status ENUM('DRAFT','PUBLISHED')`, `published_at`, `author_id`.
- **pages** — igual a `posts` sem capa/resumo.
- **company_info** — singleton (inclui `logo_url` e `logo_display` ENUM `both|image|name`) (`id = 1` com `CHECK`): nome, slogan, `founded_year` (1991), farmacêutico (nome, CRF, foto), telefone, WhatsApp, e-mail, endereço, `maps_embed_url`, `hero_video_url`, `youtube_url`, `instagram_url`, `facebook_url`.

Todas as consultas usam prepared statements; nomes de tabela nunca vêm do usuário (`ContentRepo` fixa os nomes).

### Uploads de imagem
`App\Core\Upload::image()`: valida conteúdo real (`getimagesize` + `finfo`, só JPG/PNG/WebP/GIF), limite 8 MB e 12000 px, rejeita arquivos com `<?php`,
nome aleatório (`public/uploads/AAAA/<24 hex>.<ext>`), e com GD regrava a imagem (remove metadados/conteúdo embutido, corrige orientação EXIF e reduz para 1600 px).
O caminho é gravado como `/uploads/...`. `public/uploads/.htaccess` desativa scripts, bloqueia `php/html/svg/js` e envia CSP restritiva. SVG não é aceito.
Imagens antigas são apagadas ao trocar/remover. `public/.user.ini` (e na raiz) eleva `upload_max_filesize` para 8M quando o host permite.

### Feed da página inicial
Páginas `PUBLISHED` com `show_on_home = 1` aparecem na seção `#novidades` da home, **depois do Contato**, `ORDER BY published_at DESC, id DESC`, 6 por vez.
`GET /feed?pagina=N` devolve o fragmento HTML (rolagem infinita via `IntersectionObserver`); sem JS, `/?pagina=N` renderiza N×6 cartões.

### Migrações
`App\Core\Migrator` (versão em `storage/schema.version`) adiciona colunas novas em bancos já instalados no primeiro acesso após atualizar.

## 6. RBAC

Fonte única: `App\Core\Rbac::PERMISSIONS`.

| Permissão | SUPERADMIN | EDITOR_CONTEUDO | VISUALIZADOR |
|---|:--:|:--:|:--:|
| `dashboard:view` | sim | sim | sim |
| `content:view` | sim | sim | sim |
| `content:write` / `publish` / `delete` | sim | sim | não |
| `company:view` | sim | sim | sim |
| `company:edit` | sim | sim | não |
| `users:manage` | sim | **não** | não |
| `audit:view` | sim | não | não |

Regras adicionais: o usuário é relido do banco **a cada requisição** (desativação/mudança de papel valem imediatamente);
não é possível excluir/desativar a si mesmo nem remover/rebaixar o **último SUPERADMIN**.
O menu esconde itens, mas a autorização real é sempre no servidor (testado com POSTs diretos no E2E).

## 7. Segurança

**Autenticação e senhas**
- bcrypt custo 12 (`password_hash`), política: ≥10 caracteres, maiúscula, minúscula, número, lista de senhas fracas.
- Admin inicial `admin@drogacare.com.br` / `admin` com `must_change_password = 1`: nenhuma rota do painel funciona antes da troca.
- Bloqueio da conta após 5 falhas (15 min) + limite por IP (20 falhas / 15 min, calculado a partir de `audit_logs`).
- Erro de login genérico e verificação de hash “dummy” quando o e-mail não existe (sem enumeração por mensagem ou tempo).
- Recuperação: token `bin2hex(random_bytes(32))`, armazenado como sha256, validade 30 min, uso único; resposta idêntica exista ou não o e-mail.
- Sessão: ID regenerado no login, cookie `HttpOnly`, `SameSite=Lax`, `Secure` em HTTPS, 8 h de vida e 2 h de inatividade, `use_strict_mode`.

**Aplicação**
- CSRF: token por sessão verificado centralmente em todo POST.
- XSS: toda saída passa por `e()`; conteúdo de artigos/páginas usa `Content::render` (escapa tudo e só aceita `#`, `##`, `-`, `**negrito**`, links http/https).
- URLs: `safe_url`/`safe_image_url` (apenas http/https ou caminho local); embeds validados em `Embed` (YouTube via `youtube-nocookie.com`, Google Maps; qualquer outro domínio é descartado).
- SQLi: PDO com prepared statements (`ATTR_EMULATE_PREPARES=false`; só o instalador usa emulação para executar o schema).
- Cabeçalhos: CSP estrita (`default-src 'self'`; sem scripts/estilos inline; `frame-src` só YouTube/Google), `X-Content-Type-Options`, `X-Frame-Options`/`frame-ancestors`, `Referrer-Policy`, `Permissions-Policy`, HSTS em HTTPS.
- `.htaccess` nega acesso web a `app/`, `config/`, `database/`, `storage/`, `bin/` e arquivos `*.sql|*.md|*.lock|*.log`.
- Instalador trava após o uso (`storage/installed.lock`).

## 8. LGPD e auditoria

- `Audit::log()` grava ator (id + e-mail snapshot), ação, entidade, detalhe curto, IP e data. **Nunca** grava senhas ou tokens.
- Ações cobertas: login/falha/bloqueio/logout, recuperação e troca de senha, CRUD de usuários, criar/editar/publicar/despublicar/excluir artigos e páginas, edição da empresa.
- Tela `/admin/auditoria` (somente SUPERADMIN) com busca e paginação.
- Site público sem cookies de sessão; banner de consentimento guarda só a escolha (`localStorage` + cookie `dc_cookie_consent`).
- Fonte hospedada localmente (nenhuma requisição ao Google Fonts); YouTube em modo sem cookies; página de Política de Privacidade semeada.
- Retenção/expurgo de logs: não automatizado; definir política e rotina conforme o jurídico.

## 9. Rotas

Público: `/`, `/feed?pagina=N`, `/blog`, `/blog/{slug}`, `/p/{slug}`, `/sitemap.xml`, `/robots.txt`.
Autenticação: `/admin/login`, `/admin/logout`, `/admin/esqueci-senha`, `/admin/redefinir-senha?token=`, `/admin/alterar-senha`, `/admin/acesso-negado`.
Painel: `/admin`, `/admin/posts[/novo|/{id}|/{id}/excluir]`, `/admin/paginas[...]`, `/admin/empresa`, `/admin/usuarios[...]`, `/admin/auditoria`.
Instalação (somente enquanto não instalado): `/install.php` (arquivo físico na raiz, funciona sem `mod_rewrite`) e `/install`.

Artigos/páginas só são públicos com `status = 'PUBLISHED'`; rascunhos retornam 404 ao público.

## 10. Build do CSS

O CSS compilado está versionado; só recompile se alterar classes Tailwind/`assets-src/input.css`:

```bash
npm install
npm run build:css     # gera public/assets/css/app.css (minificado)
npm run watch:css     # desenvolvimento
```

## 11. Desenvolvimento local e testes

```bash
mysql -e "CREATE DATABASE drogacare CHARACTER SET utf8mb4; CREATE USER 'droga'@'localhost' IDENTIFIED BY 'senha'; GRANT ALL ON drogacare.* TO 'droga'@'localhost';"
php bin/install.php --db=drogacare --user=droga --pass=senha --url=http://localhost:8000
php -S localhost:8000 -t public public/index.php
php tests/e2e.php http://localhost:8000     # em instalação nova
```

O E2E (curl + cookie jars) cobre: site público, troca obrigatória de senha, dashboard, usuários, rascunho × publicado,
escape de XSS, dados da empresa, RBAC de Editor/Visualizador (inclusive POSTs diretos), recuperação de senha com token de uso único,
bloqueio por tentativas e auditoria. Deve ser rodado em uma instalação nova (cria usuários e conteúdo de teste).

## 12. Decisões e limitações

- **Sem framework/Composer**: reduz superfície, facilita hospedagem compartilhada; o custo é manter roteamento/validação próprios.
- **SMTP próprio** simples (AUTH LOGIN); para volume ou entregabilidade avançada, trocar por um provedor transacional.
- **Uploads em disco local** (`public/uploads`): em múltiplos servidores usar armazenamento compartilhado/objeto; não há biblioteca de mídia (cada imagem pertence a um registro).
- **Sem migrações versionadas**: o `schema.sql` é idempotente para criação; alterações futuras exigem scripts incrementais.
- **Sem 2FA** no painel; recomendável para o SUPERADMIN em evolução futura.
- **Sessões em arquivos** (`storage/sessions`); em múltiplos servidores usar handler compartilhado.
