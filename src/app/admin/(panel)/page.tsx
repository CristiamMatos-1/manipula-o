import Link from "next/link";
import { FileEdit, FileText, Newspaper, ShieldCheck, UserCheck, Users } from "lucide-react";
import { prisma } from "@/lib/db";
import { requirePermission } from "@/lib/auth/guards";
import { can } from "@/lib/auth/rbac";
import { PageHeader, StatCard } from "@/components/admin/ui";
import { auditLabel } from "@/lib/audit-labels";
import { formatDate } from "@/lib/utils";

export const metadata = { title: "Dashboard" };

export default async function DashboardPage() {
  const user = await requirePermission("dashboard:view");
  const isSuper = can(user.role, "audit:view");

  const [totalUsers, activeUsers, publishedPosts, publishedPages, draftPosts, draftPages, logs] = await Promise.all([
    prisma.user.count(),
    prisma.user.count({ where: { active: true } }),
    prisma.post.count({ where: { status: "PUBLISHED" } }),
    prisma.page.count({ where: { status: "PUBLISHED" } }),
    prisma.post.count({ where: { status: "DRAFT" } }),
    prisma.page.count({ where: { status: "DRAFT" } }),
    prisma.auditLog.findMany({
      where: isSuper ? {} : { userId: user.id },
      orderBy: { createdAt: "desc" },
      take: 8,
      include: { user: { select: { name: true } } },
    }),
  ]);

  const drafts = draftPosts + draftPages;

  return (
    <>
      <PageHeader title={`Olá, ${user.name.split(" ")[0]}!`} description="Resumo geral do portal Drogacare." />

      <section aria-label="Resumo do sistema" className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {can(user.role, "users:manage") ? (
          <StatCard label="Total de usuários" value={totalUsers} icon={Users} tone="blue" href="/admin/usuarios" hint={`${activeUsers} ativos`} />
        ) : (
          <StatCard label="Usuários ativos" value={activeUsers} icon={UserCheck} tone="blue" />
        )}
        <StatCard label="Artigos publicados" value={publishedPosts} icon={Newspaper} tone="green" href="/admin/posts" hint={`+ ${publishedPages} páginas publicadas`} />
        <StatCard label="Rascunhos pendentes" value={drafts} icon={FileEdit} tone="sky" href="/admin/posts" hint={`${draftPosts} artigos · ${draftPages} páginas`} />
        <StatCard label="Páginas do site" value={publishedPages + draftPages} icon={FileText} tone="navy" href="/admin/paginas" />
      </section>

      <section className="mt-6 grid gap-6 lg:grid-cols-3" aria-label="Atividade e atalhos">
        <div className="card lg:col-span-2">
          <div className="mb-4 flex items-center justify-between gap-2">
            <h2 className="flex items-center gap-2 text-lg font-bold">
              <ShieldCheck className="h-5 w-5 text-brand-blue" aria-hidden />
              {isSuper ? "Últimos logs de auditoria" : "Minhas atividades recentes"}
            </h2>
            {isSuper && (
              <Link href="/admin/auditoria" className="text-sm font-semibold text-brand-blue hover:underline">
                Ver todos
              </Link>
            )}
          </div>

          {logs.length === 0 ? (
            <p className="text-sm text-brand-navy/60">Nenhuma atividade registrada ainda.</p>
          ) : (
            <ul className="divide-y divide-brand-navy/10">
              {logs.map((log) => (
                <li key={log.id} className="flex flex-col gap-1 py-3 sm:flex-row sm:items-center sm:justify-between">
                  <div className="min-w-0">
                    <p className="text-sm font-semibold">{auditLabel(log.action)}</p>
                    <p className="truncate text-xs text-brand-navy/60">
                      {log.user?.name ?? log.userEmail ?? "Sistema"}
                      {log.details ? ` · ${log.details}` : ""}
                    </p>
                  </div>
                  <time className="shrink-0 text-xs text-brand-navy/50" dateTime={log.createdAt.toISOString()}>
                    {formatDate(log.createdAt, true)}
                  </time>
                </li>
              ))}
            </ul>
          )}
        </div>

        <div className="card">
          <h2 className="mb-4 text-lg font-bold">Atalhos</h2>
          <div className="grid gap-2">
            {can(user.role, "content:write") && (
              <>
                <Link href="/admin/posts/novo" className="btn-primary">Novo artigo</Link>
                <Link href="/admin/paginas/novo" className="btn-secondary">Nova página</Link>
              </>
            )}
            <Link href="/admin/empresa" className="btn-secondary">Dados da empresa</Link>
            <a href="/" target="_blank" rel="noopener" className="btn-ghost border border-brand-navy/10">Abrir o site</a>
          </div>
          {!can(user.role, "content:write") && (
            <p className="mt-4 text-xs text-brand-navy/60">Seu perfil é somente leitura. Peça a um administrador para ajustar suas permissões.</p>
          )}
        </div>
      </section>
    </>
  );
}
