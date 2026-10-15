# ARCHITECTURE - Drogacare Portal & CMS

Documento técnico para engenheiros. O guia de uso do cliente está no [`README.md`](./README.md).

## Sumário

1. [Stack](#1-stack)
2. [Inicialização e comandos](#2-inicialização-e-comandos)
3. [Estrutura de pastas](#3-estrutura-de-pastas)
4. [Variáveis de ambiente](#4-variáveis-de-ambiente)
5. [Modelo de dados](#5-modelo-de-dados)
6. [Autenticação e sessão](#6-autenticação-e-sessão)
7. [RBAC - matriz de permissões](#7-rbac---matriz-de-permissões)
8. [Fluxos de segurança](#8-fluxos-de-segurança)
9. [LGPD e auditoria](#9-lgpd-e-auditoria)
10. [Frontend, design system e responsividade](#10-frontend-design-system-e-responsividade)
11. [CMS: conteúdo e SEO](#11-cms-conteúdo-e-seo)
12. [Migrando de SQLite para PostgreSQL](#12-migrando-de-sqlite-para-postgresql)
13. [Deploy e operação](#13-deploy-e-operação)
14. [Decisões de projeto e limitações conhecidas](#14-decisões-de-projeto-e-limitações-conhecidas)

---

## 1. Stack

| Camada | Tecnologia | Observação |
| --- | --- | --- |
| Framework | **Next.js 14** (App Router, Server Components, Server Actions) | Mutations via Server Actions (checagem de `Origin` nativa contra CSRF) |
| Linguagem | **TypeScript** (strict) | |
| Estilo | **Tailwind CSS 3** | Tokens da marca em `tailwind.config.ts`; mobile-first |
| ORM / BD | **Prisma 6** + **SQLite** (dev) / **PostgreSQL** (produção) | Enums nativos |
| Senhas | **bcrypt** (custo 12) | |
| Sessão | **jose** (JWT HS256 em cookie `httpOnly`) | `jose` é compatível com Edge (middleware) |
| Token de recuperação | **jsonwebtoken** | Só roda em Node (rotas/ações), nunca no middleware |
| E-mail | **nodemailer** (`SMTP_URL`) | Simulado no console se `SMTP_URL` vazio |
| Validação | **zod** | Toda entrada de formulário é validada no servidor |
| Ícones | **lucide-react** | Ícones de redes sociais são SVGs próprios (`components/site/icons.tsx`) |
| Fonte | **Raleway** via `next/font/google` (fallback Arial) | Self-host automático pelo Next (sem requisição ao Google em runtime) |

## 2. Inicialização e comandos

Como o projeto foi criado (referência):

```bash
npx create-next-app@14 . --typescript --tailwind --eslint --app --src-dir --import-alias "@/*" --use-npm
npm i @prisma/client@6 lucide-react bcrypt jsonwebtoken jose nodemailer zod server-only
npm i -D prisma@6 tsx @types/bcrypt @types/jsonwebtoken @types/nodemailer
```

Rodar localmente:

```bash
npm install
cp .env.example .env     # edite os segredos
npm run setup            # prisma generate + db push + seed
npm run dev
```

| Script | Função |
| --- | --- |
| `npm run dev` / `build` / `start` | Next.js |
| `npm run lint` / `typecheck` | ESLint / `tsc --noEmit` |
| `npm run setup` | `prisma generate && prisma db push && prisma db seed` |
| `npm run db:push` | Sincroniza o schema com o banco (sem histórico de migrations) |
| `npm run db:migrate` | `prisma migrate dev` (gera migrations versionadas; recomendado em produção) |
| `npm run db:seed` | Executa `prisma/seed.ts` (idempotente) |
| `npm run db:reset` | Apaga e recria o banco (**destrutivo**) |
| `npm run db:studio` | Prisma Studio |

## 3. Estrutura de pastas

```
.
├── prisma/
│   ├── schema.prisma            # Modelo de dados completo
│   └── seed.ts                  # Admin inicial (admin/admin + troca obrigatória), CompanyInfo, conteúdo de exemplo
├── public/                      # logo.svg, avatar-placeholder.svg (+ fotos/arquivos estáticos)
├── src/
│   ├── middleware.ts            # 1ª barreira de /admin/*: sessão + troca de senha pendente
│   ├── app/
│   │   ├── layout.tsx           # <html>, fonte Raleway, metadata global
│   │   ├── globals.css          # Tailwind + componentes (.btn, .input, .card...)
│   │   ├── robots.ts / sitemap.ts
│   │   ├── (site)/              # Site público (header, footer, WhatsApp, cookies)
│   │   │   ├── page.tsx         # Home: Hero, Sobre, Artigos, Contato
│   │   │   ├── blog/            # Listagem e artigo
│   │   │   └── p/[slug]/        # Páginas institucionais
│   │   └── admin/
│   │       ├── actions/         # Server Actions: auth, content, users, company
│   │       ├── (auth)/          # login, esqueci-senha, redefinir-senha, alterar-senha
│   │       └── (panel)/         # Área logada (layout com sidebar responsiva)
│   │           ├── page.tsx     # Dashboard
│   │           ├── posts/ paginas/ empresa/
│   │           ├── usuarios/    # SUPERADMIN
│   │           └── auditoria/   # SUPERADMIN
│   ├── components/
│   │   ├── site/                # Hero, About, Contact, ShareButtons, CookieBanner, WhatsAppFloat...
│   │   ├── admin/               # AdminShell, ContentForm, UserForm, CompanyForm, ui (StatCard...)
│   │   └── ui/form.tsx          # Field, TextArea, SubmitButton, ConfirmButton, FormMessage
│   └── lib/
│       ├── auth/                # rbac.ts, session.ts, guards.ts, password.ts, reset-token.ts
│       ├── audit.ts             # Trilha de auditoria
│       ├── mail.ts              # nodemailer (+ modo simulado)
│       ├── content.tsx          # Renderizador seguro de conteúdo (sem HTML bruto)
│       ├── embed.ts             # Validação de YouTube / Google Maps / WhatsApp
│       ├── company.ts, seo.ts, env.ts, db.ts, utils.ts, form.ts
└── .env.example
```

## 4. Variáveis de ambiente

Veja `.env.example`. Em `NODE_ENV=production`, segredos ausentes ou curtos (< 32 caracteres) **impedem a aplicação de funcionar** (falha rápida, `src/lib/env.ts`).

| Variável | Obrigatória | Descrição |
| --- | --- | --- |
| `DATABASE_URL` | Sim | SQLite: `file:./dev.db` (relativo a `prisma/`). PostgreSQL: `postgresql://user:pass@host:5432/drogacare?schema=public` |
| `SESSION_SECRET` | Sim (prod) | Assina o JWT de sessão. ≥ 32 chars. `openssl rand -base64 48` |
| `RESET_TOKEN_SECRET` | Sim (prod) | Assina o JWT de recuperação de senha. **Diferente** de `SESSION_SECRET` |
| `APP_URL` | Sim (prod) | URL pública (links nos e-mails, `metadataBase`, sitemap, links de compartilhamento) |
| `SMTP_URL` | Não* | Ex.: `smtps://usuario:senha@smtp.provedor.com:465`. *Sem ela, o e-mail é simulado (link impresso no console do servidor). **Obrigatória em produção** para a recuperação de senha funcionar |
| `MAIL_FROM` | Não | Remetente. Padrão: `Drogacare <nao-responda@drogacare.com.br>` |
| `SEED_ADMIN_EMAIL` | Não | E-mail do admin criado pelo seed. Padrão `admin@drogacare.com.br` |

Nunca versione `.env` (já está no `.gitignore`).

## 5. Modelo de dados

Definido em `prisma/schema.prisma`.

| Modelo | Finalidade | Pontos-chave |
| --- | --- | --- |
| `User` | Usuários do painel | `role` (enum `Role`), `active`, `mustChangePassword`, `resetToken` (**hash sha256**) + `resetTokenExpiry`, `failedLoginAttempts`, `lockedUntil`, `lastLoginAt` |
| `AuditLog` | Trilha de auditoria (LGPD) | `userId` (FK, `onDelete: SetNull`), `userEmail` (snapshot), `action`, `entity`, `entityId`, `details`, `ip`, `createdAt` |
| `Post` | Artigos | `slug` único, `status` (`DRAFT`/`PUBLISHED`), `publishedAt`, SEO (`seoTitle`, `seoDescription`, `seoKeywords`, `ogImageUrl`), `coverUrl` |
| `Page` | Páginas institucionais | Mesma estrutura de `Post` (sem capa) |
| `CompanyInfo` | **Singleton** (`id = 1`) | `foundedYear` (1991), farmacêutico (`pharmacistName`, `pharmacistCrf`, `pharmacistPhotoUrl`), contato, `whatsapp`, `mapsEmbedUrl`, `heroVideoUrl`, `youtubeUrl`, `instagramUrl`, `facebookUrl` |

Enums: `Role { SUPERADMIN, EDITOR_CONTEUDO, VISUALIZADOR }`, `ContentStatus { DRAFT, PUBLISHED }`.

O singleton é garantido por `id Int @id @default(1)` e acessado sempre via `upsert({ where: { id: 1 } })` (`lib/company.ts`).

## 6. Autenticação e sessão

- **Login** (`actions/auth.ts#loginAction`): valida com zod, busca o usuário, compara com bcrypt e emite o cookie `dc_session`.
- **Cookie**: JWT HS256 (8 h), `httpOnly`, `sameSite=lax`, `secure` em produção. Payload mínimo: `uid`, `role`, `mcp` (troca de senha pendente).
- **Duas camadas de proteção:**
  1. `middleware.ts` (Edge, sem banco): rejeita requisições a `/admin/*` sem JWT válido e **força `/admin/alterar-senha`** enquanto `mcp = true`. Adiciona `Cache-Control: no-store` e `X-Robots-Tag: noindex`.
  2. `lib/auth/guards.ts` (Node, com banco): `requireUser()` / `requirePermission()` recarregam o usuário **do banco a cada requisição**. Usuário desativado, excluído, com perfil alterado ou com troca de senha pendente é tratado imediatamente, mesmo com um JWT ainda válido. **O JWT nunca é a fonte da verdade do perfil.**
- Toda Server Action chama `requirePermission(...)` no início (não confiar em UI escondida).

## 7. RBAC - matriz de permissões

Fonte única da verdade: `src/lib/auth/rbac.ts` (`PERMISSIONS`).

| Permissão | Descrição | SUPERADMIN | EDITOR_CONTEUDO | VISUALIZADOR |
| --- | --- | :-: | :-: | :-: |
| `dashboard:view` | Ver o Dashboard | ✅ | ✅ | ✅ |
| `content:view` | Listar/abrir artigos e páginas | ✅ | ✅ | ✅ |
| `content:write` | Criar/editar (rascunho) | ✅ | ✅ | ❌ |
| `content:publish` | Publicar/despublicar | ✅ | ✅ | ❌ |
| `content:delete` | Excluir artigos/páginas | ✅ | ✅ | ❌ |
| `company:view` | Ver Dados da empresa | ✅ | ✅ | ✅ |
| `company:edit` | Editar Dados da empresa | ✅ | ✅ | ❌ |
| `users:manage` | Criar/editar/excluir usuários e perfis | ✅ | ❌ | ❌ |
| `audit:view` | Tela de Auditoria / logs globais no Dashboard | ✅ | ❌ | ❌ |

Comportamento ao violar: páginas redirecionam para `/admin/acesso-negado`; sem sessão, para `/admin/login`. Não-superadmins veem no Dashboard apenas as **próprias** atividades.

Para adicionar uma permissão: inclua em `PERMISSIONS`, use `requirePermission("x:y")` na página **e** na Server Action, e esconda o item de menu com `can(role, "x:y")`.

Invariantes de usuários (`actions/users.ts`): nunca remover/desativar/rebaixar o **último SUPERADMIN ativo**; ninguém pode excluir ou desativar a **própria** conta.

## 8. Fluxos de segurança

### 8.1 Admin inicial e troca obrigatória

`prisma/seed.ts` cria `admin@drogacare.com.br` com senha `admin` (bcrypt) e `mustChangePassword = true`. O seed é **idempotente** e **nunca sobrescreve** um admin existente. Enquanto a flag estiver ativa:

- o cookie leva `mcp = true` e o middleware redireciona qualquer `/admin/*` para `/admin/alterar-senha`;
- `requireUser()` também redireciona (caso o cookie esteja desatualizado);
- `changePasswordAction` exige a senha atual, aplica a política, grava o hash, limpa a flag e reemite a sessão.

Usuários criados no painel e senhas redefinidas por um administrador também recebem `mustChangePassword = true`.

> **Em produção:** altere a senha e o e-mail do admin imediatamente após o primeiro deploy.

### 8.2 Política de senha

Mínimo de 10 caracteres, maiúscula, minúscula e número; máximo 128; lista de senhas óbvias bloqueada; nova senha ≠ atual (`lib/auth/password.ts`).

### 8.3 Recuperação de senha

1. `forgotPasswordAction` recebe o e-mail e **sempre** responde com a mesma mensagem (sem enumeração de usuários).
2. Se o usuário existe e está ativo: gera um JWT (`purpose: "pwd-reset"`, `jti` aleatório, 30 min, segredo `RESET_TOKEN_SECRET`), grava **apenas o hash sha256** em `resetToken` e a expiração em `resetTokenExpiry`, e envia o link `APP_URL/admin/redefinir-senha?token=...` por e-mail.
3. `resetPasswordAction` valida: assinatura/expiração do JWT **e** igualdade com o hash armazenado **e** `resetTokenExpiry`. Em caso de sucesso grava a nova senha, **apaga o token (uso único)**, zera bloqueios e encerra a sessão.
4. Um novo pedido invalida o anterior (o hash é sobrescrito). Desativar o usuário também limpa o token.

### 8.4 Proteção contra força bruta

5 falhas consecutivas → `lockedUntil = agora + 15 min` (mesmo com a senha correta, o login é recusado). Mensagem de erro de credenciais é genérica; há um `bcrypt.compare` fictício quando o e-mail não existe para igualar o tempo de resposta.

### 8.5 Demais controles

| Risco | Mitigação |
| --- | --- |
| XSS no conteúdo do CMS | Sem HTML bruto: `lib/content.tsx` renderiza uma sintaxe restrita como elementos React; links só `http(s)` com `rel="noopener noreferrer nofollow"` |
| Embeds maliciosos | `lib/embed.ts`: vídeo só YouTube (`youtube-nocookie.com`) ou arquivo `.mp4/.webm`; mapa só `google.com/maps`; URLs de imagem só `http(s)` ou caminho local |
| CSRF | Server Actions (verificação de `Origin`) + cookie `SameSite=Lax` |
| Clickjacking / sniffing | `X-Frame-Options: SAMEORIGIN`, `frame-ancestors 'self'`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Permissions-Policy` (`next.config.mjs`) |
| SQL injection | Prisma (consultas parametrizadas) |
| Indexação do painel | `robots.txt` bloqueia `/admin`; `X-Robots-Tag: noindex` e `robots` meta no layout do admin |
| Cache de páginas privadas | `Cache-Control: no-store` e `force-dynamic` |
| Segredos | Validados no boot em produção; nunca no repositório |

## 9. LGPD e auditoria

**Auditoria** (`lib/audit.ts`, modelo `AuditLog`): toda ação relevante grava *quem* (`userId` + snapshot do e-mail), *o quê* (`action`, `entity`, `entityId`, `details`), *quando* (`createdAt`) e *de onde* (`ip`).

Eventos registrados: `LOGIN`, `LOGIN_FAILED`, `LOGIN_LOCKED`, `LOGIN_BLOCKED`, `LOGOUT`, `PASSWORD_RESET_REQUESTED/COMPLETED`, `PASSWORD_CHANGED(_FORCED)`, `USER_CREATE/UPDATE/DELETE`, `POST_*`, `PAGE_*` (create, update, publish, unpublish, delete), `COMPANY_UPDATE` (lista os campos alterados, não os valores) e `SEED_ADMIN`.

Regras:

- **Nunca** se grava senha, hash ou token em `details`.
- Se um usuário é excluído, os logs permanecem (`onDelete: SetNull` + `userEmail`), preservando a rastreabilidade.
- Não existe interface para editar/apagar logs.
- A tela `/admin/auditoria` é restrita a `audit:view` (SUPERADMIN).

**Boas práticas LGPD aplicadas / a cumprir:**

| Item | Situação |
| --- | --- |
| Banner de consentimento de cookies | Implementado (`CookieBanner`): "Aceitar todos" / "Somente essenciais"; escolha em `localStorage` + cookie `dc_cookie_consent` (180 dias); reabrível pelo rodapé |
| Cookies | Apenas o essencial (`dc_session`, `dc_cookie_consent`); sem trackers. Vídeo do YouTube via `youtube-nocookie.com` |
| Minimização | O sistema só guarda nome, e-mail e IP de usuários internos |
| Segurança do tratamento | bcrypt, JWT httpOnly, RBAC, auditoria, bloqueio de força bruta |
| Política de Privacidade | Página-modelo criada pelo seed (`/p/politica-de-privacidade`). **Deve ser revisada pelo jurídico** |
| Retenção de logs / IP | O IP é dado pessoal: defina prazo de retenção (ex.: 6 meses a 1 ano) e rotina de expurgo conforme a política da empresa (pendência operacional) |
| Direitos do titular | Atendimento via e-mail de contato; exclusão/edição de usuários pelo painel |

Se for adicionar analytics/pixels no futuro, carregue-os **somente** após o consentimento `all` (`localStorage["dc_cookie_consent"]`).

## 10. Frontend, design system e responsividade

**Cores oficiais** (`tailwind.config.ts` → `brand.*`):

| Token | Hex | Uso típico |
| --- | --- | --- |
| `brand-navy` | `#061e3b` | Texto, rodapé, sidebar |
| `brand-blue` | `#2775bb` | Botões primários, links |
| `brand-sky` | `#1ea8e0` | Destaques, foco |
| `brand-green` | `#7bc58f` | Acentos, sucesso |
| `brand-mint` | `#c2e4d2` | Fundos suaves |
| `brand-forest` | `#224e41` | Botão de publicar, textos de sucesso |

**Tipografia:** Raleway (`--font-raleway`, `next/font/google`) com fallback Arial (`font-sans`).

**Responsividade (mobile-first):** tudo é desenhado para telas pequenas e ampliado com `sm:` (640), `md:` (768), `lg:` (1024) e `xl:`. Exemplos: menu hambúrguer no site e drawer lateral no painel (< `lg`); tabelas viram **cartões** no mobile (< `md`); alvos de toque ≥ 44px; barra de ações do editor fixa na base no celular.

**Componentes do site:** `Hero` (vídeo YouTube/arquivo, com placeholder), `About` (tempo de mercado = `ano atual − foundedYear` + card do farmacêutico), `Contact` (iframe Google Maps), `WhatsAppFloat` (`fixed bottom-4 right-4`, oculto sem número), `ShareButtons` (WhatsApp, Facebook, Instagram, YouTube, copiar link), `CookieBanner`.

Nota sobre o Instagram: não existe URL pública de compartilhamento. O botão usa a **Web Share API** (menu nativo do celular) e, sem suporte, copia o link e abre o Instagram.

**Acessibilidade:** link "pular para o conteúdo", `aria-*` em menus/diálogos, foco visível, mensagens de erro com `role="alert"`.

## 11. CMS: conteúdo e SEO

- **Ciclo de vida:** `DRAFT` (invisível no site público; 404) ↔ `PUBLISHED`. `publishedAt` é gravado na **primeira** publicação e preservado depois. Botões do formulário enviam `intent=draft|publish` (`actions/content.ts#saveContentAction`).
- **Slug:** gerado do título (sem acentos) se vazio, com checagem de unicidade.
- **SEO:** `generateMetadata` usa `seoTitle`, `seoDescription`, `seoKeywords`, `ogImageUrl` (Open Graph/Twitter), com canonical. `sitemap.xml` lista apenas conteúdo publicado.
- **Editor:** texto simples com sintaxe restrita (`##`, `-`, `**`, `[txt](url)`). Intencional (segurança). Para um editor rico (TipTap/Markdown), sanitize a saída (ex.: `rehype-sanitize`) antes de renderizar.
- **Cache:** páginas públicas são dinâmicas; as ações chamam `revalidatePath` para rotas relacionadas.

## 12. Migrando de SQLite para PostgreSQL

1. Em `prisma/schema.prisma`, altere `provider = "sqlite"` para `provider = "postgresql"`.
2. Defina `DATABASE_URL="postgresql://..."`.
3. Remova `prisma/dev.db` e quaisquer migrations antigas (se existirem) e gere as migrations do novo provider:

   ```bash
   npx prisma migrate dev --name init
   npm run db:seed
   ```

4. Em produção: `npx prisma migrate deploy` (nunca `db push`/`reset`).

O schema não usa recursos específicos do SQLite. Para buscas na Auditoria sem diferenciar maiúsculas/minúsculas no PostgreSQL, adicione `mode: "insensitive"` aos filtros `contains` em `auditoria/page.tsx`.

## 13. Deploy e operação

Checklist de produção:

- [ ] `NODE_ENV=production`, `SESSION_SECRET` e `RESET_TOKEN_SECRET` fortes e **distintos**.
- [ ] `APP_URL` correto (https) e **HTTPS** obrigatório (cookie `secure`).
- [ ] `SMTP_URL` configurado e testado (senão ninguém recupera a senha).
- [ ] PostgreSQL com backup automático.
- [ ] Trocar a senha e o e-mail do admin inicial no primeiro acesso.
- [ ] Revisar e publicar a Política de Privacidade; definir retenção de logs.
- [ ] Atrás de proxy/CDN: repassar `X-Forwarded-For` (usado para registrar o IP na auditoria) e considerar rate limiting por IP no proxy.
- [ ] Cadastrar em *Dados da empresa*: WhatsApp, farmacêutico, CRF, foto, mapa, vídeo, redes sociais.

Plataformas: qualquer host Node (Vercel, Railway, Render, VPS com `npm run start` + Nginx). Em Vercel/serverless use PostgreSQL gerenciado (SQLite não persiste).

## 14. Decisões de projeto e limitações conhecidas

- **Login por e-mail** (sem campo "usuário"): o admin inicial é `admin@drogacare.com.br` / `admin`. Necessário para a recuperação de senha por e-mail.
- **Bloqueio por conta, não por IP:** protege contas, mas um atacante pode bloquear uma conta alvo de propósito (DoS de conta). Mitigação: rate limit por IP no proxy/WAF; um SUPERADMIN pode redefinir a senha para desbloquear.
- **Upload de imagens:** não há upload; fotos/capas são por URL ou arquivos em `public/`. Um provedor (S3/Cloudinary) pode ser integrado futuramente.
- **CSP restrita:** apenas `frame-ancestors/base-uri/form-action`. Uma CSP completa com nonces é um endurecimento recomendado.
- **Aviso do build:** o `next build` pode emitir um aviso do `jose` sobre `CompressionStream` no Edge Runtime; é inofensivo (não usamos JWE).
- **Sem testes automatizados** nesta primeira entrega; o fluxo foi validado manualmente de ponta a ponta (login forçado, RBAC dos 3 perfis, rascunho vs. publicação, recuperação de senha de uso único, bloqueio de força bruta, drawer mobile).
