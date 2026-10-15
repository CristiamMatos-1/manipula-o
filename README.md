# Drogacare - Portal Institucional e Painel de Gestão

Site institucional da **Drogacare, farmácia de manipulação** (desde 1991) com painel administrativo para editar conteúdo, dados da empresa e usuários, sem precisar de programador.

> Este documento é o **guia de uso para o cliente**. Detalhes técnicos estão em [`ARCHITECTURE.md`](./ARCHITECTURE.md).

---

## Sumário

1. [O que é o sistema](#1-o-que-é-o-sistema)
2. [Como acessar o painel](#2-como-acessar-o-painel)
3. [Primeiro acesso (troca de senha obrigatória)](#3-primeiro-acesso-troca-de-senha-obrigatória)
4. [Esqueci minha senha](#4-esqueci-minha-senha)
5. [Dashboard](#5-dashboard)
6. [Editar artigos e páginas: salvar rascunho vs. publicar](#6-editar-artigos-e-páginas-salvar-rascunho-vs-publicar)
7. [Dados da empresa](#7-dados-da-empresa)
8. [Gerenciar usuários e permissões](#8-gerenciar-usuários-e-permissões)
9. [Auditoria](#9-auditoria)
10. [Perguntas frequentes](#10-perguntas-frequentes)
11. [Para quem vai instalar (resumo rápido)](#11-para-quem-vai-instalar-resumo-rápido)

---

## 1. O que é o sistema

**Site público** (o que o cliente da farmácia vê):

| Área | O que mostra |
| --- | --- |
| Início | Vídeo institucional, "Sobre nós" com **tempo de mercado** (calculado automaticamente a partir de 1991) e card do **Farmacêutico Responsável**, últimos artigos e **Contato** com mapa do Google |
| Artigos (`/blog`) | Artigos publicados, com botões para **compartilhar** no WhatsApp, Facebook e Instagram, além do link do canal do YouTube |
| Páginas (`/p/nome-da-pagina`) | Páginas institucionais (ex.: Política de Privacidade) |
| Botão do WhatsApp | Botão verde fixo no canto inferior direito, sempre visível ao rolar a página |
| Aviso de cookies | Faixa de consentimento (LGPD) na primeira visita |

O site se adapta a **celulares, tablets e computadores**.

**Painel administrativo** (`/admin`): área restrita, com login e senha, onde a equipe gerencia tudo acima.

---

## 2. Como acessar o painel

1. Abra `https://SEU-SITE/admin` (ou clique em **Área restrita** no topo do site).
2. Informe seu **e-mail** e **senha**.
3. Você será levado ao **Dashboard**.

Para sair, use o botão **Sair** no menu lateral (no celular, abra o menu pelo ícone de três traços).

**Segurança:** após **5 tentativas erradas**, a conta fica bloqueada por 15 minutos. Isso é normal e protege contra invasões.

---

## 3. Primeiro acesso (troca de senha obrigatória)

O sistema vem com um administrador inicial:

| Campo | Valor |
| --- | --- |
| E-mail | `admin@drogacare.com.br` |
| Senha provisória | `admin` |

No **primeiro login**, o sistema **obriga** a troca da senha antes de liberar qualquer tela. A nova senha precisa ter:

- pelo menos **10 caracteres**;
- ao menos uma letra **maiúscula**, uma **minúscula** e um **número**;
- ser diferente da senha atual e não ser algo óbvio (como "admin").

> Recomendação: logo após entrar, vá em **Usuários**, edite o administrador e troque o e-mail por um e-mail real da farmácia. Ele será usado na recuperação de senha.

Quando um administrador **cria um usuário novo** (ou redefine a senha de alguém), essa pessoa também precisará trocar a senha provisória no primeiro acesso.

---

## 4. Esqueci minha senha

1. Na tela de login, clique em **Esqueci minha senha**.
2. Digite o seu e-mail e clique em **Enviar link de redefinição**.
3. Você verá sempre a mesma mensagem, exista ou não o e-mail (isso é proposital, por segurança).
4. Abra o e-mail recebido e clique no link. O link:
   - vale por **30 minutos**;
   - **só pode ser usado uma vez**.
5. Escolha a nova senha (mesmas regras do item 3) e confirme.
6. Faça login com a nova senha.

Se o link expirar, basta repetir o processo. Se o e-mail não chegar, olhe a caixa de spam e confirme com o administrador se o seu e-mail está correto no cadastro.

> Em ambiente de testes, sem servidor de e-mail configurado, o envio é **simulado**: o link aparece apenas no registro (log) do servidor. Veja `ARCHITECTURE.md`.

---

## 5. Dashboard

É a primeira tela após o login e mostra um resumo do sistema:

- **Total de usuários** (e quantos estão ativos);
- **Artigos publicados**;
- **Rascunhos pendentes** (artigos e páginas ainda não publicados);
- **Páginas do site**;
- **Últimos logs de auditoria** (o Super Administrador vê toda a atividade; os demais veem apenas as próprias ações);
- **Atalhos** para criar artigo, criar página e abrir o site.

---

## 6. Editar artigos e páginas: salvar rascunho vs. publicar

Menu **Artigos** (blog) ou **Páginas** (institucionais). Clique em **Novo artigo** / **Nova página**.

### Campos

| Campo | Para que serve |
| --- | --- |
| Título | Nome do conteúdo (obrigatório) |
| Endereço (slug) | Parte final do link. Se deixar em branco, é gerado do título |
| Resumo | Texto curto exibido nas listas e ao compartilhar |
| Imagem de capa (artigos) | Endereço (URL) de uma imagem |
| Conteúdo | Texto principal (veja a formatação abaixo) |
| SEO | Título e descrição para o Google, palavras-chave e imagem de compartilhamento |

### Formatação do texto

| Você digita | Resultado |
| --- | --- |
| `## Subtítulo` | Subtítulo |
| `- item` (um por linha) | Lista com marcadores |
| `**texto**` | **Negrito** |
| `[clique aqui](https://site.com)` | Link |
| Linha em branco | Novo parágrafo |

### Salvar rascunho x Publicar (importante)

| Botão | O que acontece |
| --- | --- |
| **Salvar rascunho** | Guarda o texto, mas o conteúdo **NÃO aparece no site**. Use para continuar depois. |
| **Publicar** | Salva **e coloca no ar** imediatamente. |
| **Salvar alterações (continua publicado)** | Em conteúdo já publicado: atualiza o texto que está no ar. |
| **Despublicar e salvar como rascunho** | Tira do ar, mantendo o texto guardado. |

Dicas:

- Itens **publicados** têm o selo verde; **rascunhos**, o selo amarelo.
- Para ver o resultado, abra **Ver no site** (aparece quando o conteúdo está publicado).
- **Excluir** é definitivo (fica na "Zona de perigo", ao final da tela de edição) e exige confirmação.
- Quem tem perfil **Visualizador** só consulta, não edita.

---

## 7. Dados da empresa

Menu **Dados da empresa**. Tudo o que for salvo aqui é atualizado no site:

- **Ano de fundação** (1991): o "tempo de mercado" é calculado sozinho a cada ano;
- **Farmacêutico(a) Responsável**: nome, **CRF** e **foto** (URL da imagem). Para usar um arquivo próprio, coloque-o na pasta `public/` do projeto e informe `/nome-do-arquivo.jpg`;
- **Contato**: telefone, e-mail, endereço e **WhatsApp** (somente números, com DDI e DDD, ex.: `5511999999999`). Se o WhatsApp ficar vazio, o botão flutuante não é exibido;
- **Mapa**: no Google Maps clique em *Compartilhar > Incorporar um mapa* e copie **apenas o endereço que está dentro de `src="..."`**. Se deixar vazio, o mapa é gerado a partir do endereço;
- **Vídeo institucional**: link do YouTube (`https://www.youtube.com/watch?v=...`) ou arquivo `.mp4`/`.webm`;
- **Redes sociais**: YouTube, Instagram e Facebook.

---

## 8. Gerenciar usuários e permissões

Disponível **somente para o Super Administrador**. Menu **Usuários**.

### Perfis

| Perfil | O que pode fazer |
| --- | --- |
| **Super Administrador** | Tudo: usuários, artigos, páginas, dados da empresa e auditoria |
| **Editor de Conteúdo** | Criar, editar, publicar e excluir artigos e páginas; editar dados da empresa. **Não** acessa Usuários nem Auditoria |
| **Visualizador** | Apenas consulta (somente leitura) |

### Como fazer

- **Criar:** *Novo usuário* → nome, e-mail, perfil e senha provisória. A pessoa troca a senha no 1º acesso.
- **Editar:** clique em **Editar** na lista. Dá para mudar nome, e-mail, perfil, **desativar** o acesso e **redefinir a senha**.
- **Desativar:** a pessoa perde o acesso imediatamente, mas o histórico é mantido (indicado quando alguém sai da equipe).
- **Excluir:** em "Zona de perigo", no fim da tela de edição.

Proteções automáticas: você **não** pode excluir/desativar a si mesmo, e o sistema **sempre mantém ao menos um Super Administrador ativo**.

---

## 9. Auditoria

Menu **Auditoria** (somente Super Administrador). Registra **quem fez o quê e quando**: logins, falhas de login, criação/edição/exclusão de usuários, publicação de conteúdo, alterações de dados da empresa e trocas de senha. Existe busca e paginação. Os registros **não podem ser editados nem apagados** pelo painel. Senhas nunca são gravadas.

Isso apoia o cumprimento da **LGPD** (rastreabilidade do tratamento de dados).

---

## 10. Perguntas frequentes

**Publiquei e não apareceu no site.** Verifique se o selo é "Publicado" e recarregue a página (F5).

**Aparece "Acesso negado".** Seu perfil não tem permissão para aquela área. Peça ao Super Administrador para ajustar.

**Minha conta está bloqueada.** Aguarde 15 minutos ou peça a um Super Administrador para redefinir sua senha.

**O botão de WhatsApp sumiu.** O número está vazio em *Dados da empresa*.

**O vídeo não aparece.** Use um link do YouTube ou um arquivo `.mp4`/`.webm` direto.

**O Instagram não abre uma tela de postagem.** O Instagram não permite compartilhar por link. No celular, o botão abre o menu de compartilhamento do aparelho; no computador, copia o link para você colar no app.

---

## 11. Para quem vai instalar (resumo rápido)

Requisitos: **Node.js 20+** e npm.

```bash
npm install
cp .env.example .env      # ajuste os valores (ver ARCHITECTURE.md)
npm run setup             # gera o client Prisma, cria o banco e o admin inicial
npm run dev               # http://localhost:3000
```

Produção:

```bash
npm run build
npm run start
```

Detalhes completos (PostgreSQL, SMTP, segredos, segurança, RBAC) em [`ARCHITECTURE.md`](./ARCHITECTURE.md).
