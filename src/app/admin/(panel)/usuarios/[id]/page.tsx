import { notFound } from "next/navigation";
import { prisma } from "@/lib/db";
import { requirePermission } from "@/lib/auth/guards";
import { deleteUserAction } from "@/app/admin/actions/users";
import { ConfirmButton } from "@/components/ui/form";
import { PageHeader } from "@/components/admin/ui";
import { UserForm } from "@/components/admin/UserForm";

export const metadata = { title: "Editar usuário" };

export default async function EditUserPage({ params }: { params: { id: string } }) {
  const me = await requirePermission("users:manage");
  const user = await prisma.user.findUnique({
    where: { id: params.id },
    select: { id: true, name: true, email: true, role: true, active: true, mustChangePassword: true },
  });
  if (!user) notFound();

  return (
    <>
      <PageHeader title={user.name} description={user.email} />
      <UserForm user={user} isSelf={user.id === me.id} />
      {user.id !== me.id && (
        <form action={deleteUserAction.bind(null, user.id)} className="mt-8 max-w-2xl border-t border-brand-navy/10 pt-6">
          <h2 className="text-sm font-bold text-red-700">Zona de perigo</h2>
          <p className="mb-3 text-xs text-brand-navy/60">O usuário perde o acesso imediatamente. O histórico de auditoria é preservado.</p>
          <ConfirmButton message={`Excluir o usuário ${user.email}?`}>Excluir usuário</ConfirmButton>
        </form>
      )}
    </>
  );
}
