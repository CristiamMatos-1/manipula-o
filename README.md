# Drogacare — Portal Institucional + Painel Administrativo (PHP + MySQL)

Site institucional e painel de gestão de conteúdo (CMS) da **Drogacare**, farmácia de manipulação
fundada em 1991. Feito em **PHP puro (8.1+)** com banco de dados **MySQL/MariaDB**, sem frameworks
e sem dependências para instalar em produção — basta enviar os arquivos para a hospedagem
(cPanel, por exemplo) e abrir o instalador no navegador.

- Para quem **usa** o sistema: leia este arquivo.
- Para engenheiros: leia [`ARCHITECTURE.md`](ARCHITECTURE.md).
- Instalação passo a passo no cPanel: [`docs/DEPLOY-CPANEL.md`](docs/DEPLOY-CPANEL.md).

---

## 1. O que o sistema tem

**Site público (responsivo, mobile-first)**

- Banner principal (Hero) com o vídeo institucional.
- "Sobre nós": tempo de mercado calculado automaticamente a partir de 1991 + cartão do farmacêutico responsável (nome, CRF e foto).
- Contato com mapa do Google Maps, telefone, e-mail e endereço.
- Botão flutuante do WhatsApp em todas as páginas.
- Botões de compartilhamento (WhatsApp, Facebook, Instagram) e link para o YouTube.
- Blog (artigos) e páginas institucionais (ex.: Política de Privacidade).
- Aviso de cookies (LGPD) com opção de aceitar/recusar.
- SEO: título e descrição por página, `sitemap.xml` e `robots.txt`.
- Fonte Raleway (hospedada no próprio site, sem enviar dados ao Google) com Arial como reserva.

**Painel administrativo (`/admin`)**

- Login e "Esqueci minha senha" (link com validade de 30 minutos, uso único).
- Painel (Dashboard) com cartões-resumo.
- Artigos e Páginas: rascunho ou publicado, com campos de SEO.
- Dados da empresa: ano de fundação, farmacêutico responsável, telefone, WhatsApp, e-mail, endereço, mapa, vídeo e redes sociais.
- Usuários (somente o Administrador): criar, editar, ativar/desativar, excluir.
- Auditoria (somente o Administrador): quem fez o quê e quando (LGPD).

## 2. Perfis de acesso

| Ação | Administrador (`SUPERADMIN`) | Editor (`EDITOR_CONTEUDO`) | Visualizador (`VISUALIZADOR`) |
|---|:--:|:--:|:--:|
| Ver painel, artigos, páginas e dados da empresa | sim | sim | sim |
| Criar/editar/publicar/excluir artigos e páginas | sim | sim | não |
| Editar dados da empresa | sim | sim | não |
| Gerenciar usuários | sim | **não** | não |
| Ver auditoria | sim | não | não |

O bloqueio é feito no servidor: mesmo que alguém digite o endereço de uma tela proibida, o sistema nega o acesso.

## 3. Primeiro acesso

Na instalação é criado o administrador inicial:

- **E-mail:** `admin@drogacare.com.br` (pode ser alterado no instalador)
- **Senha:** `admin`

No primeiro login o sistema **obriga a troca da senha**. A nova senha precisa ter no mínimo 10 caracteres,
com letra maiúscula, minúscula e número. Depois, entre em **Usuários** para cadastrar sua equipe.

## 4. Instalação (resumo)

1. Crie um banco MySQL e um usuário no cPanel (Bancos de dados MySQL) e dê todos os privilégios.
2. Envie o conteúdo do projeto para a pasta `public_html` (o `.htaccess` da raiz já direciona tudo para a pasta `public/`; ative "mostrar arquivos ocultos" para enviá-lo). O instalador é o arquivo `install.php`, não uma pasta.
3. Abra `https://seu-site.com.br/install.php` (em subpasta: `https://seu-site.com.br/manipula-o/install.php`), preencha os dados do banco e o endereço do site e clique em instalar.
4. Entre em `https://seu-site.com.br/admin/login` com `admin@drogacare.com.br` / `admin` e troque a senha.

Guia completo passo a passo e solução de problemas: [`docs/DEPLOY-CPANEL.md`](docs/DEPLOY-CPANEL.md).

Instalação por linha de comando (opcional):

```bash
php bin/install.php --db=NOME_DO_BANCO --user=USUARIO --pass=SENHA \
  --url=https://seu-site.com.br --email=admin@drogacare.com.br
```

## 5. Uso do dia a dia

**Publicar um artigo**
1. Painel → **Artigos** → **Novo artigo**.
2. Preencha título, resumo e conteúdo. Preencha também os campos de SEO (título e descrição para o Google).
3. Clique em **Salvar rascunho** (não aparece no site) ou **Publicar** (aparece em `/blog`).

**Formatação do texto**
Por segurança o editor aceita apenas: `# Título`, `## Subtítulo`, listas com `- item`, `**negrito**` e links `[texto](https://endereco)`.
Qualquer outro código HTML é exibido como texto puro (não executa).

**Atualizar dados da empresa**
Painel → **Empresa**. O ano de fundação (1991) alimenta o "tempo de mercado". Para o mapa, cole o endereço do
*iframe* do Google Maps (Compartilhar → Incorporar um mapa). Para o vídeo, cole o link do YouTube.

**Esqueci minha senha**
Na tela de login, clique em **Esqueci minha senha**, informe o e-mail e use o link recebido (válido por 30 minutos, uma única vez).
Se o envio de e-mails (SMTP) não estiver configurado, o e-mail é apenas *simulado*: o conteúdo fica gravado em
`storage/logs/mail.log` (acessível só pelo servidor/gerenciador de arquivos).

## 6. Segurança e LGPD (resumo)

- Senhas com bcrypt; bloqueio temporário após 5 tentativas erradas; mensagens de erro genéricas.
- Proteção contra CSRF, XSS e SQL Injection; cabeçalhos de segurança e CSP restritiva.
- Site público não grava cookies de sessão; o aviso de cookies registra apenas a sua escolha.
- Trilha de auditoria (quem, o quê, quando e IP) sem armazenar senhas ou tokens.
- Pastas internas (`app`, `config`, `database`, `storage`, `bin`) bloqueadas para acesso web.

## 7. Backup

Faça regularmente a exportação do banco (cPanel → phpMyAdmin → Exportar) e uma cópia do arquivo
`config/config.php`. O código do site pode ser restaurado reenviando os arquivos.

## 8. Suporte técnico

Em caso de erro, veja `storage/logs/php-error.log` e a seção "Problemas comuns" do
[`docs/DEPLOY-CPANEL.md`](docs/DEPLOY-CPANEL.md).
