import { PrismaClient } from "@prisma/client";
import bcrypt from "bcrypt";

const prisma = new PrismaClient();

async function main() {
  const email = (process.env.SEED_ADMIN_EMAIL || "admin@drogacare.com.br").toLowerCase();

  // Admin inicial: senha "admin" + troca OBRIGATÓRIA no primeiro login.
  // Nunca sobrescrevemos um admin existente (evita resetar senha em novo seed).
  const existing = await prisma.user.findUnique({ where: { email } });
  if (!existing) {
    const admin = await prisma.user.create({
      data: {
        name: "Administrador",
        email,
        passwordHash: await bcrypt.hash("admin", 12),
        role: "SUPERADMIN",
        mustChangePassword: true,
      },
    });
    await prisma.auditLog.create({
      data: {
        userId: admin.id,
        userEmail: admin.email,
        action: "SEED_ADMIN",
        entity: "User",
        entityId: admin.id,
        details: "Administrador inicial criado pelo seed (troca de senha obrigatória).",
      },
    });
    console.log(`Admin criado: ${email} / admin  (a senha deverá ser trocada no 1º login)`);
  } else {
    console.log(`Admin já existe (${email}); nada alterado.`);
  }

  await prisma.companyInfo.upsert({
    where: { id: 1 },
    update: {},
    create: {
      id: 1,
      name: "Drogacare",
      tagline: "Farmácia de Manipulação",
      foundedYear: 1991,
      pharmacistName: "Nome do Farmacêutico Responsável",
      pharmacistCrf: "CRF-XX 00000",
      pharmacistPhotoUrl: "/avatar-placeholder.svg",
      phone: "(00) 0000-0000",
      whatsapp: "",
      email: "contato@drogacare.com.br",
      address: "Endereço da farmácia - editar em Painel > Dados da empresa",
    },
  });

  const author = await prisma.user.findUnique({ where: { email } });

  await prisma.page.upsert({
    where: { slug: "politica-de-privacidade" },
    update: {},
    create: {
      title: "Política de Privacidade",
      slug: "politica-de-privacidade",
      excerpt: "Como a Drogacare trata dados pessoais conforme a LGPD (Lei 13.709/2018).",
      content:
        "## Quais dados coletamos\n\nEste site utiliza apenas cookies essenciais ao funcionamento e, mediante seu consentimento, cookies de medição. Dados enviados por formulários ou WhatsApp são usados somente para atendê-lo.\n\n## Seus direitos\n\nVocê pode solicitar acesso, correção ou exclusão de seus dados a qualquer momento pelo e-mail de contato informado no rodapé.\n\n## Revise este texto\n\nEste é um texto-modelo. Peça à assessoria jurídica para revisá-lo antes da publicação definitiva.",
      status: "PUBLISHED",
      publishedAt: new Date(),
      authorId: author?.id,
      seoTitle: "Política de Privacidade | Drogacare",
    },
  });

  await prisma.post.upsert({
    where: { slug: "bem-vindo-ao-novo-site-da-drogacare" },
    update: {},
    create: {
      title: "Bem-vindo ao novo site da Drogacare",
      slug: "bem-vindo-ao-novo-site-da-drogacare",
      excerpt: "Conheça nosso novo canal e saiba como a manipulação personalizada cuida de você.",
      content:
        "Desde 1991 a Drogacare cuida da sua saúde com **medicamentos manipulados sob medida**.\n\n## O que é manipulação?\n\nÉ a preparação de fórmulas individualizadas, com dose e forma farmacêutica ajustadas à prescrição do seu médico.\n\n- Atendimento farmacêutico especializado\n- Qualidade e rastreabilidade em cada fórmula\n- Mais de três décadas de tradição",
      status: "PUBLISHED",
      publishedAt: new Date(),
      authorId: author?.id,
      seoDescription: "Conheça o novo site da Drogacare, farmácia de manipulação desde 1991.",
    },
  });

  await prisma.post.upsert({
    where: { slug: "rascunho-cuidados-no-verao" },
    update: {},
    create: {
      title: "Cuidados com a pele no verão (rascunho)",
      slug: "rascunho-cuidados-no-verao",
      excerpt: "Rascunho de exemplo: visível apenas no painel até ser publicado.",
      content: "Escreva aqui o conteúdo do artigo.",
      status: "DRAFT",
      authorId: author?.id,
    },
  });
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(() => prisma.$disconnect());
