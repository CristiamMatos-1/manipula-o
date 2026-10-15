import Link from "next/link";
import { Plus } from "lucide-react";
import { prisma } from "@/lib/db";
import { requirePermission } from "@/lib/auth/guards";
import { ROLE_LABELS } from "@/lib/auth/rbac";
import { PageHeader } from "@/components/admin/ui";
import { formatDate } from "@/lib/utils";

export const metadata = { title: "Usuários" };

const MESSAGES: Record<string, { ok: boolean; text: string }> = {
  criado: { ok: true, text: "Usuário criado. Ele deverá trocar a senha no primeiro acesso." },
  excluido: { ok: true, text: "Usuário excluído." },
  self: { ok: false, text: "Você não pode excluir a própria conta." },
  last: { ok: false, text: "Não é possível excluir o único Super Administrador ativo." },
};

export default async function UsersPage({ searchParams }: { searchParams: { ok?: string; erro?: string } }) {
  const me = await requirePermission("users:manage");
  const users = await prisma.user.findMany({ orderBy: [{ role: "asc" }, { name: "asc" }] });
  const msg = MESSAGES[searchParams.ok ?? searchParams.erro ?? ""];

  return (
    <>
      <PageHeader
        title="Usuários"
        description="Crie, edite e remova usuários e defina suas permissões."
        actions={
          <Link href="/admin/usuarios/novo" className="btn-primary">
            <Plus className="h-4 w-4" aria-hidden /> Novo usuário
          </Link>
        }
      />
      {msg && (
        <p
          role="status"
          className={`mb-4 rounded-lg border px-3 py-2 text-sm font-medium ${msg.ok ? "border-brand-green bg-brand-mint/50 text-brand-forest" : "border-red-200 bg-red-50 text-red-700"}`}
        >
          {msg.text}
        </p>
      )}
      <div className="card !p-0 overflow-hidden">
        <ul className="divide-y divide-brand-navy/10 md:hidden">
          {users.map((u) => (
            <li key={u.id} className="space-y-1 p-4">
              <Link href={`/admin/usuarios/${u.id}`} className="font-bold text-brand-blue">{u.name}</Link>
              <p className="text-xs text-brand-navy/60">{u.email}</p>
              <p className="text-xs">
                <span className="badge bg-brand-blue/10 text-brand-blue">{ROLE_LABELS[u.role]}</span>{" "}
                {!u.active && <span className="badge bg-red-100 text-red-700">Inativo</span>}
              </p>
            </li>
          ))}
        </ul>
        <div className="hidden overflow-x-auto md:block">
          <table className="w-full text-left text-sm">
            <thead className="bg-brand-mint/30 text-xs uppercase tracking-wide text-brand-navy/70">
              <tr>
                <th className="px-4 py-3">Nome</th>
                <th className="px-4 py-3">E-mail</th>
                <th className="px-4 py-3">Perfil</th>
                <th className="px-4 py-3">Situação</th>
                <th className="px-4 py-3">Último acesso</th>
                <th className="px-4 py-3 text-right">Ações</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-brand-navy/10">
              {users.map((u) => (
                <tr key={u.id} className="hover:bg-slate-50">
                  <td className="px-4 py-3 font-semibold">{u.name}{u.id === me.id && <span className="ml-2 text-xs text-brand-navy/50">(você)</span>}</td>
                  <td className="px-4 py-3">{u.email}</td>
                  <td className="px-4 py-3"><span className="badge bg-brand-blue/10 text-brand-blue">{ROLE_LABELS[u.role]}</span></td>
                  <td className="px-4 py-3">
                    {u.active ? <span className="badge bg-brand-green/30 text-brand-forest">Ativo</span> : <span className="badge bg-red-100 text-red-700">Inativo</span>}
                    {u.mustChangePassword && <span className="badge ml-1 bg-amber-100 text-amber-800">Troca de senha pendente</span>}
                  </td>
                  <td className="whitespace-nowrap px-4 py-3">{formatDate(u.lastLoginAt, true)}</td>
                  <td className="px-4 py-3 text-right">
                    <Link href={`/admin/usuarios/${u.id}`} className="btn-secondary !min-h-[36px] !px-3">Editar</Link>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </>
  );
}
