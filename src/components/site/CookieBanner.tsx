"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { Cookie } from "lucide-react";

const KEY = "dc_cookie_consent";
export const OPEN_COOKIE_PREFS_EVENT = "dc:open-cookie-prefs";

function persist(value: "all" | "essential") {
  localStorage.setItem(KEY, JSON.stringify({ value, at: new Date().toISOString() }));
  document.cookie = `${KEY}=${value}; Max-Age=${60 * 60 * 24 * 180}; Path=/; SameSite=Lax`;
}

export function CookieBanner() {
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    if (!localStorage.getItem(KEY)) setVisible(true);
    const open = () => setVisible(true);
    window.addEventListener(OPEN_COOKIE_PREFS_EVENT, open);
    return () => window.removeEventListener(OPEN_COOKIE_PREFS_EVENT, open);
  }, []);

  if (!visible) return null;

  const choose = (value: "all" | "essential") => {
    persist(value);
    setVisible(false);
  };

  return (
    <div role="dialog" aria-live="polite" aria-label="Aviso de cookies" className="fixed inset-x-0 bottom-0 z-[60] p-3 sm:p-4">
      <div className="container">
        <div className="mx-auto flex max-w-4xl flex-col gap-4 rounded-2xl border border-brand-navy/10 bg-white p-4 shadow-2xl sm:flex-row sm:items-center sm:p-5">
          <Cookie className="hidden h-8 w-8 shrink-0 text-brand-blue sm:block" aria-hidden />
          <p className="flex-1 text-sm leading-relaxed text-brand-navy/90">
            Usamos cookies essenciais para o funcionamento do site e, com o seu consentimento, cookies de desempenho para melhorar sua experiência, conforme a LGPD. Saiba mais na{" "}
            <Link href="/p/politica-de-privacidade" className="font-semibold text-brand-blue underline">Política de Privacidade</Link>.
          </p>
          <div className="flex flex-col gap-2 sm:flex-row">
            <button type="button" onClick={() => choose("essential")} className="btn-secondary">Somente essenciais</button>
            <button type="button" onClick={() => choose("all")} className="btn-primary">Aceitar todos</button>
          </div>
        </div>
      </div>
    </div>
  );
}

export function CookiePrefsButton({ className }: { className?: string }) {
  return (
    <button type="button" className={className} onClick={() => window.dispatchEvent(new Event(OPEN_COOKIE_PREFS_EVENT))}>
      Preferências de cookies
    </button>
  );
}
