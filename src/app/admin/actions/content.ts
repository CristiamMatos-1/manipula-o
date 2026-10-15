"use server";

import { revalidatePath } from "next/cache";
import { redirect } from "next/navigation";
import { z } from "zod";
import { prisma } from "@/lib/db";
import { audit } from "@/lib/audit";
import { requirePermission } from "@/lib/auth/guards";
import { type FormState, str, zodFieldErrors } from "@/lib/form";
import { safeImageUrl, slugify } from "@/lib/utils";

export type ContentKind = "post" | "page";

type Row = { id: string; title: string; slug: string; status: "DRAFT" | "PUBLISHED"; publishedAt: Date | null };
type Delegate = {
  findUnique(args: unknown): Promise<Row | null>;
  create(args: unknown): Promise<Row>;
  update(args: unknown): Promise<Row>;
  delete(args: unknown): Promise<Row>;
};

const delegate = (kind: ContentKind) => (kind === "post" ? prisma.post : prisma.page) as unknown as Delegate;
const ENTITY = { post: "Post", page: "Page" } as const;
const BASE_PATH = { post: "/admin/posts", page: "/admin/paginas" } as const;
const PUBLIC_PATH = { post: "/blog", page: "/p" } as const;

const optional = (max: number) => z.string().max(max).optional().transform((v) => (v ? v : null));

const schema = z.object({
  title: z.string().min(3, "O título precisa de ao menos 3 caracteres.").max(150),
  slug: z.string().max(80).optional(),
  excerpt: optional(300),
  content: z.string().min(1, "O conteúdo é obrigatório.").max(100_000),
  coverUrl: optional(500),
  seoTitle: optional(70),
  seoDescription: optional(160),
  seoKeywords: optional(200),
  ogImageUrl: optional(500),
});

function revalidate(kind: ContentKind, slug?: string) {
  revalidatePath(BASE_PATH[kind]);
  revalidatePath("/admin");
  revalidatePath("/");
  revalidatePath(PUBLIC_PATH[kind]);
  if (slug) revalidatePath(`${PUBLIC_PATH[kind]}/${slug}`);
}

/**
 * "intent" define o ciclo de vida:
 *  - draft:   salva sem publicar (mantém/volta para RASCUNHO)
 *  - publish: salva e publica (registra publishedAt na 1ª publicação)
 */
export async function saveContentAction(kind: ContentKind, id: string | null, _prev: FormState, fd: FormData): Promise<FormState> {
  const user = await requirePermission("content:write");
  const intent = str(fd, "intent") === "publish" ? "publish" : "draft";
  if (intent === "publish") await requirePermission("content:publish");

  const parsed = schema.safeParse({
    title: str(fd, "title"),
    slug: str(fd, "slug"),
    excerpt: str(fd, "excerpt"),
    content: String(fd.get("content") ?? "").trim(),
    coverUrl: str(fd, "coverUrl"),
    seoTitle: str(fd, "seoTitle"),
    seoDescription: str(fd, "seoDescription"),
    seoKeywords: str(fd, "seoKeywords"),
    ogImageUrl: str(fd, "ogImageUrl"),
  });
  if (!parsed.success) return { fieldErrors: zodFieldErrors(parsed.error) };
  const d = parsed.data;

  for (const f of ["coverUrl", "ogImageUrl"] as const) {
    if (d[f] && !safeImageUrl(d[f])) return { fieldErrors: { [f]: "Use uma URL http(s) ou um caminho que comece com /." } };
  }

  const slug = slugify(d.slug || d.title);
  if (!slug) return { fieldErrors: { slug: "Slug inválido." } };

  const dg = delegate(kind);
  const clash = await dg.findUnique({ where: { slug } });
  if (clash && clash.id !== id) return { fieldErrors: { slug: "Já existe um item com este endereço (slug)." } };

  const existing = id ? await dg.findUnique({ where: { id } }) : null;
  if (id && !existing) return { error: "Item não encontrado." };

  const data = {
    title: d.title,
    slug,
    excerpt: d.excerpt,
    content: d.content,
    seoTitle: d.seoTitle,
    seoDescription: d.seoDescription,
    seoKeywords: d.seoKeywords,
    ogImageUrl: d.ogImageUrl,
    ...(kind === "post" ? { coverUrl: d.coverUrl } : {}),
    status: intent === "publish" ? "PUBLISHED" : "DRAFT",
    publishedAt: intent === "publish" ? (existing?.publishedAt ?? new Date()) : (existing?.publishedAt ?? null),
  };

  let saved: Row;
  if (existing) {
    saved = await dg.update({ where: { id: existing.id }, data });
  } else {
    saved = await dg.create({ data: { ...data, authorId: user.id } });
  }

  const wasPublished = existing?.status === "PUBLISHED";
  const action =
    intent === "publish"
      ? wasPublished
        ? `${ENTITY[kind].toUpperCase()}_UPDATE_PUBLISHED`
        : `${ENTITY[kind].toUpperCase()}_PUBLISH`
      : wasPublished
        ? `${ENTITY[kind].toUpperCase()}_UNPUBLISH`
        : existing
          ? `${ENTITY[kind].toUpperCase()}_UPDATE`
          : `${ENTITY[kind].toUpperCase()}_CREATE`;
  await audit({ userId: user.id, userEmail: user.email, action, entity: ENTITY[kind], entityId: saved.id, details: `"${saved.title}" (/${saved.slug})` });

  revalidate(kind, saved.slug);
  if (existing && existing.slug !== saved.slug) revalidate(kind, existing.slug);

  if (!existing) redirect(`${BASE_PATH[kind]}/${saved.id}?saved=${intent}`);
  return {
    success: intent === "publish" ? "Publicado com sucesso. O conteúdo já está no ar." : "Rascunho salvo. O conteúdo NÃO está visível no site.",
  };
}

export async function deleteContentAction(kind: ContentKind, id: string) {
  const user = await requirePermission("content:delete");
  const dg = delegate(kind);
  const row = await dg.findUnique({ where: { id } });
  if (!row) redirect(BASE_PATH[kind]);
  await dg.delete({ where: { id } });
  await audit({ userId: user.id, userEmail: user.email, action: `${ENTITY[kind].toUpperCase()}_DELETE`, entity: ENTITY[kind], entityId: id, details: `"${row.title}" (/${row.slug})` });
  revalidate(kind, row.slug);
  redirect(BASE_PATH[kind]);
}

export async function unpublishContentAction(kind: ContentKind, id: string) {
  const user = await requirePermission("content:publish");
  const row = await delegate(kind).update({ where: { id }, data: { status: "DRAFT" } });
  await audit({ userId: user.id, userEmail: user.email, action: `${ENTITY[kind].toUpperCase()}_UNPUBLISH`, entity: ENTITY[kind], entityId: id, details: `"${row.title}"` });
  revalidate(kind, row.slug);
  redirect(`${BASE_PATH[kind]}/${id}?saved=draft`);
}
