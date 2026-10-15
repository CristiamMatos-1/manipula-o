import type { ReactNode } from "react";
import Link from "next/link";

export function AuthShell({ title, subtitle, children }: { title: string; subtitle?: string; children: ReactNode }) {
  return (
    <main className="flex min-h-screen items-center justify-center bg-gradient-to-br from-brand-navy via-brand-blue to-brand-sky px-4 py-10">
      <div className="w-full max-w-md">
        <Link href="/" className="mb-6 flex items-center justify-center gap-3 text-white">
          <img src="/logo.svg" alt="" className="h-10 w-10" />
          <span className="text-2xl font-extrabold tracking-tight">Drogacare</span>
        </Link>
        <div className="rounded-2xl bg-white p-6 shadow-2xl sm:p-8">
          <h1 className="text-xl font-extrabold text-brand-navy sm:text-2xl">{title}</h1>
          {subtitle && <p className="mt-1 text-sm text-brand-navy/70">{subtitle}</p>}
          <div className="mt-6">{children}</div>
        </div>
        <p className="mt-6 text-center text-xs text-white/80">Área restrita. Acessos são registrados para fins de auditoria (LGPD).</p>
      </div>
    </main>
  );
}
