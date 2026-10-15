import Link from "next/link";
import { ShieldAlert } from "lucide-react";
import { requireUser } from "@/lib/auth/guards";

export const metadata = { title: "Acesso negado" };

export default async function ForbiddenPage() {
  await requireUser();
  return (
    <div className="mx-auto mt-10 max-w-md text-center">
      <ShieldAlert className="mx-auto h-14 w-14 text-red-500" aria-hidden />
      <h1 className="mt-4 text-2xl font-extrabold">Acesso negado</h1>
      <p className="mt-2 text-sm text-brand-navy/70">Seu perfil não tem permissão para acessar esta área. Se acredita que é um engano, fale com um Super Administrador.</p>
      <Link href="/admin" className="btn-primary mt-6">Voltar ao Dashboard</Link>
    </div>
  );
}
