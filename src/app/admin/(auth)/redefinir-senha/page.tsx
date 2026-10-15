import Link from "next/link";
import { AuthShell } from "@/components/admin/AuthShell";
import { prisma } from "@/lib/db";
import { hashToken, verifyResetToken } from "@/lib/auth/reset-token";
import { ResetForm } from "./ResetForm";

export const metadata = { title: "Redefinir senha" };

async function tokenIsValid(token: string | undefined) {
  if (!token) return false;
  const decoded = verifyResetToken(token);
  if (!decoded) return false;
  const user = await prisma.user.findUnique({ where: { id: decoded.userId } });
  return !!user && user.active && user.resetToken === hashToken(token) && !!user.resetTokenExpiry && user.resetTokenExpiry > new Date();
}

export default async function ResetPasswordPage({ searchParams }: { searchParams: { token?: string } }) {
  const token = searchParams.token;
  const valid = await tokenIsValid(token);

  if (!valid || !token) {
    return (
      <AuthShell title="Link inválido ou expirado">
        <p className="text-sm text-brand-navy/80">Este link de redefinição não é mais válido. Ele expira em 30 minutos e só pode ser usado uma vez.</p>
        <Link href="/admin/esqueci-senha" className="btn-primary mt-6 w-full">
          Solicitar novo link
        </Link>
      </AuthShell>
    );
  }

  return (
    <AuthShell title="Criar nova senha" subtitle="Mínimo de 10 caracteres, com maiúscula, minúscula e número.">
      <ResetForm token={token} />
    </AuthShell>
  );
}
