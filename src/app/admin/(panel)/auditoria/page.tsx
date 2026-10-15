import Link from "next/link";
import { prisma } from "@/lib/db";
import { requirePermission } from "@/lib/auth/guards";
import { PageHeader } from "@/components/admin/ui";
import { auditLabel } from "@/lib/audit-labels";
import { formatDate } from "@/lib/utils";

export const metadata = { title: "Auditoria" };
const PAGE_SIZE = 25;

export default async function AuditPage({ searchParams }: { searchParams: { page?: string; q?: string } }) {
  await requirePermission("audit:view");
  const page = Math.max(1, Number(searchParams.page) || 1);
  const q = (searchParams.q ?? "").trim();
  const where = q
    ? { OR: [{ userEmail: { contains: q } }, { action: { contains: q.toUpperCase() } }, { entity: { contains: q } }, { details: { contains: q } }] }
    : {};

  const [total, logs] = await Promise.all([
    prisma.auditLog.count({ where }),
    prisma.auditLog.findMany({ where, orderBy: { createdAt: "desc" }, skip: (page - 1) * PAGE_SIZE, take: PAGE_SIZE, include: { user: { select: { name: true } } } }),
  ]);
  const pages = Math.max(1, Math.ceil(total / PAGE_SIZE));
  const href = (p: number) => `/admin/auditoria?page=${p}${q ? `&q=${encodeURIComponent(q)}` : ""}`;

  return (
    <>
      <PageHeader title="Auditoria" description="Trilha de rastreabilidade (LGPD): quem fez o quê, e quando. Registros são somente leitura." />
      <form className="mb-4 flex flex-col gap-2 sm:max-w-md sm:flex-row" role="search">
        <input name="q" defaultValue={q} placeholder="Buscar por e-mail, ação, entidade..." className="input" aria-label="Buscar logs" />
        <button className="btn-primary" type="submit">Buscar</button>
      </form>

      <div className="card !p-0 overflow-hidden">
        <ul className="divide-y divide-brand-navy/10 md:hidden">
          {logs.map((l) => (
            <li key={l.id} className="space-y-1 p-4">
              <p className="text-sm font-semibold">{auditLabel(l.action)}</p>
              <p className="text-xs text-brand-navy/60">{l.user?.name ?? l.userEmail ?? "Sistema"} · {formatDate(l.createdAt, true)}</p>
              {l.details && <p className="break-words text-xs">{l.details}</p>}
            </li>
          ))}
        </ul>
        <div className="hidden overflow-x-auto md:block">
          <table className="w-full text-left text-sm">
            <thead className="bg-brand-mint/30 text-xs uppercase tracking-wide text-brand-navy/70">
              <tr>
                <th className="px-4 py-3">Quando</th>
                <th className="px-4 py-3">Quem</th>
                <th className="px-4 py-3">Ação</th>
                <th className="px-4 py-3">Alvo</th>
                <th className="px-4 py-3">Detalhes</th>
                <th className="px-4 py-3">IP</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-brand-navy/10">
              {logs.map((l) => (
                <tr key={l.id} className="align-top">
                  <td className="whitespace-nowrap px-4 py-3">{formatDate(l.createdAt, true)}</td>
                  <td className="px-4 py-3">
                    <p className="font-semibold">{l.user?.name ?? "-"}</p>
                    <p className="text-xs text-brand-navy/50">{l.userEmail ?? ""}</p>
                  </td>
                  <td className="px-4 py-3">{auditLabel(l.action)}<p className="text-[11px] text-brand-navy/40">{l.action}</p></td>
                  <td className="px-4 py-3">{l.entity}</td>
                  <td className="max-w-xs break-words px-4 py-3 text-xs">{l.details ?? "-"}</td>
                  <td className="px-4 py-3 text-xs">{l.ip ?? "-"}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        {logs.length === 0 && <p className="p-6 text-sm text-brand-navy/60">Nenhum registro encontrado.</p>}
      </div>

      <nav className="mt-4 flex items-center justify-between text-sm" aria-label="Paginação">
        {page > 1 ? <Link href={href(page - 1)} className="btn-secondary">Anterior</Link> : <span />}
        <span className="text-brand-navy/60">Página {page} de {pages} · {total} registros</span>
        {page < pages ? <Link href={href(page + 1)} className="btn-secondary">Próxima</Link> : <span />}
      </nav>
    </>
  );
}
