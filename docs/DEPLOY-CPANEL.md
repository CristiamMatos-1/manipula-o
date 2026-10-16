# Instalação no cPanel (Gerenciador de Arquivos + Setup Node.js App)

Guia passo a passo para colocar o portal Drogacare no ar em uma hospedagem com **cPanel**.

## Antes de começar: verifique sua hospedagem

O site é uma aplicação **Node.js** (Next.js). Não basta enviar arquivos para a `public_html` como em um site comum. Seu plano precisa ter, no cPanel, o ícone **"Setup Node.js App"** (também chamado "Node.js Selector"), com **Node.js 18.17 ou superior (recomendado 20)**.

- Se o ícone **não existir**, peça ao suporte da hospedagem para habilitar Node.js, ou contrate um plano com suporte (VPS/Cloud). Hospedagens compartilhadas básicas não rodam este projeto.
- Tenha em mãos: acesso ao cPanel, o domínio apontado para a hospedagem e uma conta de e-mail do domínio (para a recuperação de senha).

---

## Parte 1 - Preparar o pacote no seu computador

Requisito: Node.js 20+ instalado no computador.

1. Na pasta do projeto, instale e gere a versão de produção:

   ```bash
   npm install
   npm run build
   ```

2. Crie um arquivo **.zip** com o projeto, **sem** estas pastas/arquivos: `node_modules`, `.git`, `.env`, `prisma/dev.db`.
   - Mac/Linux:

     ```bash
     zip -r drogacare.zip . -x "node_modules/*" ".git/*" ".env" "prisma/*.db" "prisma/*.db-journal"
     ```

   - Windows: selecione todas as pastas e arquivos, **exceto** `node_modules`, `.git` e `.env`, clique com o botão direito e escolha *Enviar para > Pasta compactada*.

   Importante: a pasta **`.next`** (gerada pelo build) **deve ir dentro do zip**, junto com `server.js`, `package.json`, `package-lock.json`, `prisma/`, `public/` e `src/`.

---

## Parte 2 - Enviar os arquivos pelo Gerenciador de Arquivos

1. No cPanel, abra **Gerenciador de Arquivos**.
2. Vá até a pasta raiz da sua conta (`/home/SEU_USUARIO`). **Não use a `public_html`.**
3. Clique em **+ Pasta** e crie a pasta `drogacare`.
4. Entre em `drogacare`, clique em **Carregar** e envie o `drogacare.zip`.
5. Volte ao gerenciador, selecione o zip, clique em **Extrair** e confirme. Os arquivos devem ficar diretamente em `/home/SEU_USUARIO/drogacare/` (ex.: `drogacare/server.js`, e não `drogacare/drogacare/server.js`).
6. Apague o zip depois de extrair.
7. Clique em **Configurações** (canto superior direito) e marque **Mostrar arquivos ocultos**.

---

## Parte 3 - Criar o arquivo de configuração `.env`

1. Dentro de `/home/SEU_USUARIO/drogacare/`, clique em **+ Arquivo** e crie `.env`.
2. Selecione o `.env`, clique em **Editar** e cole o conteúdo abaixo, trocando os valores:

   ```env
   DATABASE_URL="file:./dev.db"
   SESSION_SECRET="COLE_AQUI_UM_TEXTO_ALEATORIO_COM_MAIS_DE_32_CARACTERES_A"
   RESET_TOKEN_SECRET="COLE_AQUI_OUTRO_TEXTO_ALEATORIO_DIFERENTE_COM_MAIS_DE_32_CARACTERES_B"
   APP_URL="https://www.seudominio.com.br"
   SMTP_URL="smtps://contato%40seudominio.com.br:SENHA_DO_EMAIL@mail.seudominio.com.br:465"
   MAIL_FROM="Drogacare <contato@seudominio.com.br>"
   SEED_ADMIN_EMAIL="admin@drogacare.com.br"
   ```

Explicação dos campos:

| Campo | O que colocar |
| --- | --- |
| `DATABASE_URL` | Deixe como está. Cria o arquivo de banco em `drogacare/prisma/dev.db` |
| `SESSION_SECRET` e `RESET_TOKEN_SECRET` | Dois textos longos, aleatórios e **diferentes entre si**. Para gerar, use um gerador de senhas com 48+ caracteres |
| `APP_URL` | Endereço público do site, com `https://` e sem barra no final |
| `SMTP_URL` | Dados do e-mail do domínio (crie em **Contas de E-mail** no cPanel). Formato: `smtps://USUARIO:SENHA@SERVIDOR:465`. Troque `@` do usuário por `%40` e codifique símbolos da senha (`@`=`%40`, `#`=`%23`, `/`=`%2F`, `:`=`%3A`). O servidor SMTP aparece em *Contas de E-mail > Conectar dispositivos* |
| `MAIL_FROM` | Remetente exibido no e-mail |
| `SEED_ADMIN_EMAIL` | E-mail do administrador inicial (troque por um e-mail real se quiser) |

Sem `SMTP_URL`, o site funciona, mas **a recuperação de senha não envia e-mail**.

---

## Parte 4 - Criar a aplicação Node.js

1. No cPanel, abra **Setup Node.js App** e clique em **Create Application**.
2. Preencha:
   - **Node.js version:** `20` (ou a mais alta disponível, mínimo 18.17).
   - **Application mode:** `Production`.
   - **Application root:** `drogacare` (a pasta criada na Parte 2).
   - **Application URL:** escolha o domínio (e deixe o caminho em branco, para o site ficar na raiz).
   - **Application startup file:** `server.js`.
3. Clique em **Create**.
4. (Opcional) Em *Environment variables* você pode cadastrar as mesmas variáveis do `.env`. Não é necessário se o `.env` existir.

---

## Parte 5 - Instalar as dependências e preparar o banco

Ainda na tela da aplicação:

1. Clique em **Run NPM Install** e aguarde terminar (pode levar alguns minutos). Isso instala tudo o que o site precisa e gera o cliente do banco.
2. Clique em **Run JS script** e execute o script **`setup`**. Ele cria o banco de dados e o administrador inicial.
   - Se o botão não listar scripts, use o **Terminal** do cPanel (ou SSH). Copie o comando que aparece no topo da tela da aplicação (`source /home/USUARIO/nodevenv/drogacare/20/bin/activate && cd /home/USUARIO/drogacare`) e rode:

     ```bash
     npm run setup
     ```

3. Clique em **Restart** (ou **Start App**).

---

## Parte 6 - Acessar e configurar

1. Abra `https://www.seudominio.com.br` e confira o site.
2. Acesse `https://www.seudominio.com.br/admin`.
3. Entre com **e-mail** `admin@drogacare.com.br` (ou o `SEED_ADMIN_EMAIL`) e **senha** `admin`.
4. O sistema vai **obrigar** a troca da senha (mínimo de 10 caracteres, com maiúscula, minúscula e número).
5. Em **Usuários**, edite o administrador e troque o e-mail por um real (usado na recuperação de senha).
6. Em **Dados da empresa**, preencha farmacêutico, CRF, foto, WhatsApp, endereço, mapa, vídeo e redes sociais.
7. Teste **Esqueci minha senha** para confirmar que o e-mail chega.

---

## Parte 7 - HTTPS (obrigatório)

No cPanel, abra **SSL/TLS Status** e execute **Run AutoSSL** para o domínio. O painel só funciona de forma segura com `https://`, e `APP_URL` deve usar `https://`.

---

## Parte 8 - Atualizar o site no futuro

1. No computador: `npm run build` e gere o zip de novo (Parte 1).
2. No Gerenciador de Arquivos, envie e extraia **sobrescrevendo** os arquivos, **sem apagar** `.env` nem `prisma/dev.db`.
3. Em *Setup Node.js App*: **Run NPM Install** (se `package.json` mudou) e **Restart**.
4. Se houve mudança no banco (`prisma/schema.prisma`), rode `npx prisma db push` no Terminal antes de reiniciar.

## Backup

O banco é o arquivo `/home/SEU_USUARIO/drogacare/prisma/dev.db`. Baixe-o periodicamente pelo Gerenciador de Arquivos (ou ative os backups do cPanel). Guarde também o `.env`.

---

## Problemas comuns

| Sintoma | Causa provável / solução |
| --- | --- |
| Página "503" ou "Application failed to start" | Veja o log na pasta da aplicação (`stderr.log`) ou em *Metrics > Errors*. Em geral faltou **Run NPM Install**, a pasta `.next` não foi enviada ou a versão do Node é menor que 18.17 |
| `Could not find a production build in the '.next' directory` | Envie a pasta `.next` (rode `npm run build` no computador antes de gerar o zip) |
| Erro mencionando `bcrypt` ou `invalid ELF header` | O módulo foi instalado em outro sistema. Apague `node_modules` no servidor e rode **Run NPM Install** de novo. Se persistir, peça ao suporte ou troque `bcrypt` por `bcryptjs` |
| Erro do Prisma ("Query engine", "Unable to open the database file") | Rode **Run NPM Install** e depois `npm run setup`; confira a permissão de escrita da pasta `prisma` (755) e se `DATABASE_URL="file:./dev.db"` |
| Login não mantém a sessão | Acesse por `https://` e confira `APP_URL` e os segredos no `.env` |
| E-mail de recuperação não chega | Confira `SMTP_URL` (usuário com `%40`, senha codificada, servidor e porta 465) e a pasta de spam |
| Mudei o `.env` e nada mudou | Clique em **Restart** na tela do *Setup Node.js App* |
| Site mostra a página padrão da hospedagem | A *Application URL* não está apontando para o domínio certo ou a aplicação está parada |

Detalhes técnicos, variáveis e segurança: [`ARCHITECTURE.md`](../ARCHITECTURE.md).
