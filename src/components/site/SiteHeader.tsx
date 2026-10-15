"use client";

import { useState } from "react";
import Link from "next/link";
import { Menu, X } from "lucide-react";

const LINKS = [
  { href: "/#sobre", label: "Sobre nós" },
  { href: "/blog", label: "Artigos" },
  { href: "/#contato", label: "Contato" },
];

export function SiteHeader({ name }: { name: string }) {
  const [open, setOpen] = useState(false);
  return (
    <header className="sticky top-0 z-40 border-b border-brand-navy/10 bg-white/95 backdrop-blur">
      <div className="container flex h-16 items-center justify-between">
        <Link href="/" className="flex items-center gap-2 text-xl font-extrabold text-brand-navy" onClick={() => setOpen(false)}>
          <img src="/logo.svg" alt="" className="h-9 w-9" />
          {name}
        </Link>

        <nav className="hidden items-center gap-1 md:flex" aria-label="Principal">
          {LINKS.map((l) => (
            <Link key={l.href} href={l.href} className="rounded-lg px-4 py-2 text-sm font-semibold text-brand-navy hover:bg-brand-mint/50">
              {l.label}
            </Link>
          ))}
          <Link href="/admin" className="btn-secondary ml-2 !min-h-[40px]">Área restrita</Link>
        </nav>

        <button
          type="button"
          className="rounded-lg p-2 text-brand-navy hover:bg-brand-mint/50 md:hidden"
          aria-label={open ? "Fechar menu" : "Abrir menu"}
          aria-expanded={open}
          aria-controls="mobile-menu"
          onClick={() => setOpen((v) => !v)}
        >
          {open ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
        </button>
      </div>

      {open && (
        <nav id="mobile-menu" className="border-t border-brand-navy/10 bg-white md:hidden" aria-label="Principal (mobile)">
          <ul className="container flex flex-col py-2">
            {LINKS.map((l) => (
              <li key={l.href}>
                <Link href={l.href} onClick={() => setOpen(false)} className="flex min-h-[48px] items-center text-base font-semibold text-brand-navy">
                  {l.label}
                </Link>
              </li>
            ))}
            <li>
              <Link href="/admin" onClick={() => setOpen(false)} className="flex min-h-[48px] items-center text-base font-semibold text-brand-blue">
                Área restrita
              </Link>
            </li>
          </ul>
        </nav>
      )}
    </header>
  );
}
