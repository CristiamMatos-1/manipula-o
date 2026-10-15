"use client";

import { useState } from "react";
import { Check, Link2 } from "lucide-react";
import { Facebook, Instagram, Youtube } from "./icons";

const wa = (url: string, title: string) => `https://wa.me/?text=${encodeURIComponent(`${title} ${url}`)}`;
const fb = (url: string) => `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;

function WhatsAppIcon() {
  return (
    <svg viewBox="0 0 32 32" className="h-5 w-5" fill="currentColor" aria-hidden="true">
      <path d="M16.04 3C9.4 3 4 8.4 4 15.03c0 2.12.55 4.18 1.6 6L4 28l7.15-1.87a12.03 12.03 0 0 0 4.89 1.03h.01C22.7 27.16 28 21.76 28 15.13 28 11.9 26.74 8.88 24.46 6.6A11.9 11.9 0 0 0 16.04 3Z" />
    </svg>
  );
}

export function ShareButtons({ url, title, youtubeUrl }: { url: string; title: string; youtubeUrl?: string }) {
  const [copied, setCopied] = useState(false);

  async function copy() {
    try {
      await navigator.clipboard.writeText(url);
      setCopied(true);
      setTimeout(() => setCopied(false), 2500);
      return true;
    } catch {
      return false;
    }
  }

  // O Instagram não possui URL de compartilhamento pela web: usamos o menu nativo
  // do aparelho (Web Share API) ou copiamos o link para colar no app.
  async function shareInstagram() {
    if (navigator.share) {
      try {
        await navigator.share({ title, url });
        return;
      } catch {
        return;
      }
    }
    await copy();
    window.open("https://www.instagram.com/", "_blank", "noopener,noreferrer");
  }

  const base = "inline-flex min-h-[44px] items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90";

  return (
    <div aria-label="Compartilhar" className="flex flex-wrap items-center gap-2">
      <span className="mr-1 text-sm font-bold text-brand-navy">Compartilhar:</span>
      <a href={wa(url, title)} target="_blank" rel="noopener noreferrer" className={`${base} bg-[#25D366]`}>
        <WhatsAppIcon /> WhatsApp
      </a>
      <a href={fb(url)} target="_blank" rel="noopener noreferrer" className={`${base} bg-[#1877F2]`}>
        <Facebook className="h-5 w-5" /> Facebook
      </a>
      <button type="button" onClick={shareInstagram} className={`${base} bg-gradient-to-tr from-[#f09433] via-[#dc2743] to-[#bc1888]`}>
        <Instagram className="h-5 w-5" aria-hidden /> Instagram
      </button>
      {youtubeUrl && (
        <a href={youtubeUrl} target="_blank" rel="noopener noreferrer" className={`${base} bg-[#FF0000]`}>
          <Youtube className="h-5 w-5" aria-hidden /> YouTube
        </a>
      )}
      <button type="button" onClick={copy} className="btn-secondary !min-h-[44px]">
        {copied ? <Check className="h-4 w-4" aria-hidden /> : <Link2 className="h-4 w-4" aria-hidden />}
        {copied ? "Link copiado!" : "Copiar link"}
      </button>
    </div>
  );
}
