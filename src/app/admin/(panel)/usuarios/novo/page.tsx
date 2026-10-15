import { requirePermission } from "@/lib/auth/guards";
import { PageHeader } from "@/components/admin/ui";
import { UserForm } from "@/components/admin/UserForm";

export const metadata = { title: "Novo usuário" };

export default async function NewUserPage() {
  await requirePermission("users:manage");
  return (
    <>
      <PageHeader title="Novo usuário" />
      <UserForm />
    </>
  );
}
