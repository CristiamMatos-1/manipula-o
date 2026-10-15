import { redirect } from "next/navigation";
import { getCurrentUser } from "@/lib/auth/guards";
import { AuthShell } from "@/components/admin/AuthShell";
import { LoginForm } from "./LoginForm";

export const metadata = { title: "Entrar" };

export default async function LoginPage({ searchParams }: { searchParams: { reset?: string } }) {
  if (await getCurrentUser()) redirect("/admin");
  return (
    <AuthShell title="Acessar o painel" subtitle="Entre com seu e-mail e senha.">
      {searchParams.reset && (
        <p role="status" className="mb-4 rounded-lg border border-brand-green bg-brand-mint/50 px-3 py-2 text-sm font-medium text-brand-forest">
          Senha redefinida com sucesso. Faça login com a nova senha.
        </p>
      )}
      <LoginForm />
    </AuthShell>
  );
}
