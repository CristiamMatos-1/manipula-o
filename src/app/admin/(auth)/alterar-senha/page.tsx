import { requireUser } from "@/lib/auth/guards";
import { AuthShell } from "@/components/admin/AuthShell";
import { ChangePasswordForm } from "./ChangePasswordForm";

export const metadata = { title: "Alterar senha" };

export default async function ChangePasswordPage() {
  const user = await requireUser({ allowMustChange: true });
  return (
    <AuthShell
      title={user.mustChangePassword ? "Defina uma nova senha" : "Alterar senha"}
      subtitle={user.mustChangePassword ? "Por segurança, você precisa trocar a senha provisória antes de continuar." : undefined}
    >
      <ChangePasswordForm forced={user.mustChangePassword} />
    </AuthShell>
  );
}
