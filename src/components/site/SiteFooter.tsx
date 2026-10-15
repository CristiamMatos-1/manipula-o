import Link from "next/link";
import { Mail, MapPin, Phone } from "lucide-react";
import { Facebook, Instagram, Youtube } from "./icons";
import { CookiePrefsButton } from "./CookieBanner";
import { safeUrl } from "@/lib/utils";

type Company = {
  name: string; tagline: string; foundedYear: number; phone: string; email: string; address: string;
  youtubeUrl: string; instagramUrl: string; facebookUrl: string;
};

export function SiteFooter({ c }: { c: Company }) {
  const socials = [
    { href: safeUrl(c.instagramUrl), label: "Instagram", Icon: Instagram },
    { href: safeUrl(c.facebookUrl), label: "Facebook", Icon: Facebook },
    { href: safeUrl(c.youtubeUrl), label: "YouTube", Icon: Youtube },
  ].filter((s): s is { href: string; label: string; Icon: typeof Instagram } => !!s.href);

  return (
    <footer className="bg-brand-navy text-white">
      <div className="container grid gap-8 py-10 sm:grid-cols-2 lg:grid-cols-3">
        <div>
          <p className="flex items-center gap-2 text-xl font-extrabold">
            <img src="/logo.svg" alt="" className="h-9 w-9" /> {c.name}
          </p>
          <p className="mt-2 text-sm text-brand-mint">{c.tagline} · desde {c.foundedYear}</p>
          {socials.length > 0 && (
            <ul className="mt-4 flex gap-3">
              {socials.map(({ href, label, Icon }) => (
                <li key={label}>
                  <a href={href} target="_blank" rel="noopener noreferrer" aria-label={label} className="flex h-11 w-11 items-center justify-center rounded-full bg-white/10 hover:bg-brand-sky">
                    <Icon className="h-5 w-5" />
                  </a>
                </li>
              ))}
            </ul>
          )}
        </div>
        <ul className="space-y-3 text-sm text-white/85">
          {c.address && <li className="flex gap-2"><MapPin className="mt-0.5 h-4 w-4 shrink-0 text-brand-green" aria-hidden />{c.address}</li>}
          {c.phone && <li className="flex gap-2"><Phone className="mt-0.5 h-4 w-4 shrink-0 text-brand-green" aria-hidden />{c.phone}</li>}
          {c.email && <li className="flex gap-2"><Mail className="mt-0.5 h-4 w-4 shrink-0 text-brand-green" aria-hidden /><a href={`mailto:${c.email}`} className="hover:underline">{c.email}</a></li>}
        </ul>
        <ul className="space-y-2 text-sm">
          <li><Link href="/blog" className="hover:underline">Artigos</Link></li>
          <li><Link href="/p/politica-de-privacidade" className="hover:underline">Política de Privacidade</Link></li>
          <li><CookiePrefsButton className="hover:underline" /></li>
        </ul>
      </div>
      <div className="border-t border-white/10 py-4 text-center text-xs text-white/60">
        © {new Date().getFullYear()} {c.name}. Todos os direitos reservados.
      </div>
    </footer>
  );
}
