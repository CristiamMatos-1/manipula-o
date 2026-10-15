"use client";

import { useEffect, useState, type ReactNode } from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { Building2, FileText, LayoutDashboard, LogOut, Menu, Newspaper, ScrollText, Users, X, ExternalLink, KeyRound } from "lucide-react";
import { logoutAction } from "@/app/admin/actions/auth";
import { cn } from "@/lib/utils";

const ICONS = { dashboard: LayoutDashboard, posts: Newspaper, pages: FileText, company: Building2, users: Users, audit: ScrollText };
export type NavItem = { href: string; label: string; icon: keyof typeof ICONS };

export function AdminShell({
  nav,
  user,
  children,
}: {
  nav: NavItem[];
  user: { name: string; email: string; roleLabel: string };
  children: ReactNode;
}) {
  const [open, setOpen] = useState(false);
  const pathname = usePathname();

  useEffect(() => setOpen(false), [pathname]);
  useEffect(() => {
    document.body.style.overflow = open ? "hidden" : "";
    return () => {
      document.body.style.overflow = "";
    };
  }, [open]);

  const isActive = (href: string) => (href === "/admin" ? pathname === "/admin" : pathname.startsWith(href));

  const sidebar = (
    <div className="flex h-full flex-col bg-brand-navy text-white">
      <Link href="/admin" className="flex items-center gap-3 px-5 py-5">
        <img src="/logo.svg" alt="" className="h-9 w-9" />
        <div className="leading-tight">
          <p className="text-lg font-extrabold">Drogacare</p>
          <p className="text-xs text-brand-mint">Painel de gestão</p>
        </div>
      </Link>
      <nav className="flex-1 space-y-1 overflow-y-auto px-3 py-2" aria-label="Menu principal">
        {nav.map(({ href, label, icon }) => {
          const Icon = ICONS[icon];
          return (
            <Link
              key={href}
              href={href}
              aria-current={isActive(href) ? "page" : undefined}
              className={cn(
                "flex min-h-[44px] items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold transition",
                isActive(href) ? "bg-brand-blue text-white" : "text-white/80 hover:bg-white/10 hover:text-white",
              )}
            >
              <Icon className="h-5 w-5 shrink-0" aria-hidden />
              {label}
            </Link>
          );
        })}
        <a
          href="/"
          target="_blank"
          rel="noopener"
          className="flex min-h-[44px] items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold text-white/80 hover:bg-white/10 hover:text-white"
        >
          <ExternalLink className="h-5 w-5" aria-hidden /> Ver site
        </a>
      </nav>
      <div className="border-t border-white/10 p-4">
        <p className="truncate text-sm font-bold">{user.name}</p>
        <p className="truncate text-xs text-white/70">{user.email}</p>
        <p className="mt-1 inline-block rounded-full bg-brand-green/20 px-2 py-0.5 text-[11px] font-semibold text-brand-mint">{user.roleLabel}</p>
        <div className="mt-3 flex gap-2">
          <Link href="/admin/alterar-senha" className="btn-ghost !min-h-[40px] flex-1 !px-2 text-xs !text-white hover:!bg-white/10">
            <KeyRound className="h-4 w-4" aria-hidden /> Senha
          </Link>
          <form action={logoutAction} className="flex-1">
            <button type="submit" className="btn-ghost !min-h-[40px] w-full !px-2 text-xs !text-white hover:!bg-white/10">
              <LogOut className="h-4 w-4" aria-hidden /> Sair
            </button>
          </form>
        </div>
      </div>
    </div>
  );

  return (
    <div className="min-h-screen bg-slate-50 lg:flex">
      <aside className="sticky top-0 hidden h-screen w-64 shrink-0 lg:block">{sidebar}</aside>

      <header className="sticky top-0 z-30 flex items-center justify-between bg-brand-navy px-4 py-3 text-white lg:hidden">
        <Link href="/admin" className="flex items-center gap-2 font-extrabold">
          <img src="/logo.svg" alt="" className="h-8 w-8" /> Drogacare
        </Link>
        <button type="button" onClick={() => setOpen(true)} className="rounded-lg p-2 hover:bg-white/10" aria-label="Abrir menu" aria-expanded={open}>
          <Menu className="h-6 w-6" />
        </button>
      </header>

      {open && (
        <div className="fixed inset-0 z-40 lg:hidden" role="dialog" aria-modal="true" aria-label="Menu">
          <button type="button" className="absolute inset-0 bg-black/50" onClick={() => setOpen(false)} aria-label="Fechar menu" />
          <div className="absolute inset-y-0 left-0 w-72 max-w-[85%] shadow-2xl">
            <button type="button" onClick={() => setOpen(false)} className="absolute right-2 top-3 z-10 rounded-lg p-2 text-white hover:bg-white/10" aria-label="Fechar menu">
              <X className="h-5 w-5" />
            </button>
            {sidebar}
          </div>
        </div>
      )}

      <main className="min-w-0 flex-1 p-4 sm:p-6 lg:p-8">{children}</main>
    </div>
  );
}
