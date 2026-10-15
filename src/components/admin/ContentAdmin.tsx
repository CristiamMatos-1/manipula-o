import Link from "next/link";
import { notFound } from "next/navigation";
import { ExternalLink, Pencil, Plus } from "lucide-react";
import { prisma } from "@/lib/db";
import { requirePermission } from "@/lib/auth/guards";
import { can } from "@/lib/auth/rbac";
import { deleteContentAction, type ContentKind } from "@/app/admin/actions/content";
import { ConfirmButton } from "@/components/ui/form";
import { ContentForm, type ContentFormItem } from "@/components/admin/ContentForm";
import { PageHeader, StatusBadge } from "@/components/admin/ui";
import { formatDate } from "@/lib/utils";

const CFG = {
  post: { base: "/admin/posts", plural: "Artigos", singular: "artigo", pub: "/blog", desc: "Artigos do blog exibidos em /blog." },
  page: { base: "/admin/paginas", plural: "Páginas", singular: "página", pub: "/p", desc: "Páginas institucionais exibidas em /p/slug." },
} as const;

async function list(kind: ContentKind) {
  const select = { id: true, title: true, slug: true, status: true, updatedAt: true, publishedAt: true, author: { select: { name: true } } } as const;
  const args = { orderBy: { updatedAt: "desc" as const }, select };
  return kind === "post" ? prisma.post.findMany(args) : prisma.page.findMany(args);
}

export async function ContentListPage({ kind }: { kind: ContentKind }) {
  const user = await requirePermission("content:view");
  const cfg = CFG[kind];
  const items = await list(kind);
  const canWrite = can(user.role, "content:write");

  return (
    <>
      <PageHeader
        title={cfg.plural}
        description={cfg.desc}
        actions={
          canWrite && (
            <Link href={`${cfg.base}/novo`} className="btn-primary">
              <Plus className="h-4 w-4" aria-hidden /> Novo {cfg.singular}
            </Link>
          )
        }
      />
      <div className="card !p-0 overflow-hidden">
        {items.length === 0 ? (
          <p className="p-6 text-sm text-brand-navy/60">Nenhum item cadastrado.</p>
        ) : (
          <>
            {/* Mobile: cartões */}
            <ul className="divide-y divide-brand-navy/10 md:hidden">
              {items.map((it) => (
                <li key={it.id} className="space-y-2 p-4">
                  <div className="flex items-start justify-between gap-2">
                    <Link href={`${cfg.base}/${it.id}`} className="font-bold text-brand-blue">{it.title}</Link>
                    <StatusBadge status={it.status} />
                  </div>
                  <p className="text-xs text-brand-navy/60">/{it.slug} · {it.author?.name ?? "-"} · {formatDate(it.updatedAt, true)}</p>
                </li>
              ))}
            </ul>
            {/* Tablet/desktop: tabela */}
            <div className="hidden overflow-x-auto md:block">
              <table className="w-full text-left text-sm">
                <thead className="bg-brand-mint/30 text-xs uppercase tracking-wide text-brand-navy/70">
                  <tr>
                    <th className="px-4 py-3">Título</th>
                    <th className="px-4 py-3">Status</th>
                    <th className="px-4 py-3">Autor</th>
                    <th className="px-4 py-3">Atualizado</th>
                    <th className="px-4 py-3 text-right">Ações</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-brand-navy/10">
                  {items.map((it) => (
                    <tr key={it.id} className="hover:bg-slate-50">
                      <td className="px-4 py-3">
                        <p className="font-semibold">{it.title}</p>
                        <p className="text-xs text-brand-navy/50">/{it.slug}</p>
                      </td>
                      <td className="px-4 py-3"><StatusBadge status={it.status} /></td>
                      <td className="px-4 py-3">{it.author?.name ?? "-"}</td>
                      <td className="whitespace-nowrap px-4 py-3">{formatDate(it.updatedAt, true)}</td>
                      <td className="px-4 py-3">
                        <div className="flex justify-end gap-2">
                          {it.status === "PUBLISHED" && (
                            <a href={`${cfg.pub}/${it.slug}`} target="_blank" rel="noopener" className="btn-ghost !min-h-[36px] !px-3" aria-label={`Ver ${it.title} no site`}>
                              <ExternalLink className="h-4 w-4" aria-hidden />
                            </a>
                          )}
                          <Link href={`${cfg.base}/${it.id}`} className="btn-secondary !min-h-[36px] !px-3">
                            <Pencil className="h-4 w-4" aria-hidden /> {canWrite ? "Editar" : "Ver"}
                          </Link>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </>
        )}
      </div>
    </>
  );
}

export async function ContentNewPage({ kind }: { kind: ContentKind }) {
  const user = await requirePermission("content:write");
  const cfg = CFG[kind];
  return (
    <>
      <PageHeader title={`Novo ${cfg.singular}`} description="Use “Salvar rascunho” para continuar depois (não vai ao ar) ou “Publicar” para exibir no site." />
      <ContentForm kind={kind} canWrite canPublish={can(user.role, "content:publish")} />
    </>
  );
}

export async function ContentEditPage({ kind, id, saved }: { kind: ContentKind; id: string; saved?: string }) {
  const user = await requirePermission("content:view");
  const cfg = CFG[kind];
  const item = (kind === "post" ? await prisma.post.findUnique({ where: { id } }) : await prisma.page.findUnique({ where: { id } })) as
    | (ContentFormItem & { publishedAt: Date | null })
    | null;
  if (!item) notFound();

  const canWrite = can(user.role, "content:write");
  const canDelete = can(user.role, "content:delete");

  return (
    <>
      <PageHeader
        title={item.title}
        description={`Status: ${item.status === "PUBLISHED" ? "Publicado" : "Rascunho"}${item.publishedAt ? ` · publicado em ${formatDate(item.publishedAt)}` : ""}`}
        actions={
          item.status === "PUBLISHED" && (
            <a href={`${cfg.pub}/${item.slug}`} target="_blank" rel="noopener" className="btn-secondary">
              <ExternalLink className="h-4 w-4" aria-hidden /> Ver no site
            </a>
          )
        }
      />
      {saved && (
        <p role="status" className="mb-4 rounded-lg border border-brand-green bg-brand-mint/50 px-3 py-2 text-sm font-medium text-brand-forest">
          {saved === "publish" ? "Publicado com sucesso." : "Rascunho salvo."}
        </p>
      )}
      <ContentForm kind={kind} item={item} canWrite={canWrite} canPublish={can(user.role, "content:publish")} />
      {canDelete && (
        <form action={deleteContentAction.bind(null, kind, item.id)} className="mt-8 border-t border-brand-navy/10 pt-6">
          <h2 className="text-sm font-bold text-red-700">Zona de perigo</h2>
          <p className="mb-3 text-xs text-brand-navy/60">A exclusão é definitiva e fica registrada na auditoria.</p>
          <ConfirmButton message={`Excluir "${item.title}" definitivamente?`}>Excluir {cfg.singular}</ConfirmButton>
        </form>
      )}
    </>
  );
}
