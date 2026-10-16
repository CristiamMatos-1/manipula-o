# Instalação no cPanel (Gerenciador de Arquivos) — PHP + MySQL

Não é necessário Node.js, Composer nem terminal: o sistema é PHP puro e o CSS já vem compilado.

## Requisitos da hospedagem

- PHP **8.1 ou superior** (cPanel → *Selecionar versão do PHP* / *MultiPHP Manager*).
- Extensões PHP: `pdo_mysql`, `mbstring`, `openssl`, `curl` (opcional), `fileinfo`.
- MySQL 5.7+ ou MariaDB 10.3+.
- Apache com `mod_rewrite` (padrão no cPanel) e certificado SSL (AutoSSL / Let's Encrypt).

## Passo 1 — Criar o banco de dados

1. cPanel → **Bancos de dados MySQL**.
2. Em *Criar novo banco de dados*, informe um nome (ex.: `drogacare`). O cPanel acrescenta o prefixo da conta (ex.: `conta_drogacare`).
3. Em *Adicionar novo usuário*, crie um usuário (ex.: `droga`) com uma senha forte. Anote.
4. Em *Adicionar usuário ao banco de dados*, selecione usuário e banco e marque **TODOS OS PRIVILÉGIOS**.

Anote: nome completo do banco, nome completo do usuário, senha. O servidor é `localhost`.

## Passo 2 — Enviar os arquivos

1. No seu computador, compacte **todo o conteúdo** do projeto em um `.zip` (não inclua `node_modules` nem a pasta `.git`).
2. cPanel → **Gerenciador de Arquivos** → abra `public_html` (ou a pasta do domínio/subdomínio).
3. Clique em **Carregar**, envie o `.zip`, depois botão direito → **Extrair**.
4. Confira que dentro de `public_html` estão: `.htaccess`, `app/`, `config/`, `database/`, `public/`, `storage/`, `bin/`.
   Ative *Mostrar arquivos ocultos* (Configurações) para ver o `.htaccess`.

> O `.htaccess` da raiz redireciona todo o tráfego para `public/` e bloqueia o acesso web às pastas internas.
> **Alternativa mais segura:** colocar o projeto fora de `public_html` (ex.: `/home/conta/drogacare`) e
> apontar o domínio (*Domínios* → *Raiz do documento*) para `/home/conta/drogacare/public`.

## Passo 3 — Permissões de escrita

Pelo Gerenciador de Arquivos (botão direito → *Alterar permissões*), garanta que o PHP consiga gravar em:

- `storage/` e subpastas (`storage/logs`, `storage/sessions`) — permissão **755** (use 775 se der erro).
- `config/` — **755** durante a instalação (o instalador cria `config/config.php`).

## Passo 4 — Rodar o instalador

1. Acesse `https://seu-site.com.br/install`.
2. A tela mostra a verificação do ambiente (versão do PHP, extensões, pastas graváveis). Corrija o que estiver em vermelho.
3. Preencha:
   - **Endereço do site** (ex.: `https://www.drogacare.com.br`);
   - **Servidor do banco**: `localhost`; **porta** `3306`;
   - **Nome do banco**, **usuário** e **senha** do Passo 1;
   - **E-mail do administrador** (padrão `admin@drogacare.com.br`);
   - (opcional) **SMTP** — veja o Passo 6.
4. Clique em **Instalar**. O instalador cria as tabelas, o administrador inicial (senha `admin`), os dados iniciais da empresa e conteúdo de exemplo, grava `config/config.php` e bloqueia o instalador (`storage/installed.lock`).

Depois disso `/install` deixa de existir (retorna 404).

## Passo 5 — Primeiro acesso e troca de senha

1. Acesse `https://seu-site.com.br/admin/login`.
2. E-mail `admin@drogacare.com.br` e senha `admin`.
3. O sistema exige a criação de uma nova senha (mín. 10 caracteres, maiúscula, minúscula e número).
4. Vá em **Empresa** e complete os dados (farmacêutico, CRF, foto, telefone, WhatsApp, mapa, vídeo, redes).
5. Vá em **Usuários** e cadastre a equipe (Editor ou Visualizador).

## Passo 6 — E-mail (recuperação de senha)

Sem SMTP, o envio é **simulado** e gravado em `storage/logs/mail.log`.
Para enviar de verdade, crie uma conta de e-mail no cPanel (ex.: `nao-responda@seu-site.com.br`) e informe no instalador
(ou edite `config/config.php`):

```php
'smtp_url'  => 'smtps://nao-responda%40seu-site.com.br:SENHA@mail.seu-site.com.br:465',
'mail_from' => 'Drogacare <nao-responda@seu-site.com.br>',
```

- `smtps://` = SSL na porta 465; `smtp://` = STARTTLS na porta 587.
- Caracteres especiais no usuário/senha devem ser codificados (`@` → `%40`).

## Passo 7 — HTTPS

cPanel → **SSL/TLS Status** → *Executar AutoSSL*. Com HTTPS ativo o sistema automaticamente marca os cookies como `Secure` e envia o cabeçalho HSTS.
Para forçar HTTPS, descomente/adicione no `.htaccess` da raiz:

```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [L,R=301]
```

## Passo 8 — Testar

- `/` — site público; `/blog`; `/p/politica-de-privacidade`; `/sitemap.xml`.
- `/admin/login` — painel.
- Teste no celular (menu hambúrguer, botão do WhatsApp, banner de cookies).

## Atualizações

1. Faça backup: exporte o banco (phpMyAdmin → Exportar) e copie `config/config.php`.
2. Envie os arquivos novos sobrescrevendo os antigos, **sem apagar** `config/config.php` e `storage/installed.lock`.
3. O esquema do banco é idempotente (`database/schema.sql`); mudanças futuras virão com instruções.

## Reinstalar do zero

Apague `config/config.php` e `storage/installed.lock`, esvazie/recrie o banco e abra `/install` novamente.
**Isso apaga todos os dados** se você recriar o banco.

## Problemas comuns

| Sintoma | Causa provável | Solução |
|---|---|---|
| Erro 500 | PHP < 8.1 ou `.htaccess` incompatível | Selecione PHP 8.1+ no cPanel; veja `storage/logs/php-error.log` |
| Todas as páginas dão 404 (menos a inicial) | `mod_rewrite` / `.htaccess` ausente | Mostre arquivos ocultos e confira se `.htaccess` existe na raiz e em `public/` |
| "Access denied for user" no instalador | Usuário sem privilégios no banco | Refaça "Adicionar usuário ao banco" com todos os privilégios; use o nome completo com prefixo |
| "Could not write config" | Sem permissão em `config/` | Aplique 755/775 na pasta `config` |
| Fica pedindo login toda hora | Sessão sem permissão de escrita | Garanta escrita em `storage/sessions` (ou na pasta de sessões do PHP) |
| E-mail de recuperação não chega | SMTP não configurado | Configure `smtp_url`; enquanto isso consulte `storage/logs/mail.log` |
| Mapa/vídeo não aparecem | URL incorreta | Use o link do YouTube e o `src` do iframe do Google Maps; outros domínios são bloqueados por segurança |
| CSS quebrado | Raiz do documento errada | Domínio deve apontar para a pasta do projeto (com `.htaccess`) ou para `public/` |
| Instalador não abre (404) | Já instalado | Remova `storage/installed.lock` e `config/config.php` para reinstalar |

## Segurança pós-instalação

- Troque a senha do administrador imediatamente (o sistema obriga).
- Mantenha `config/config.php` com permissão 640/600.
- Faça backup periódico do banco.
- Mantenha o PHP atualizado no cPanel.
