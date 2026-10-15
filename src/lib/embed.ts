import { safeUrl } from "./utils";

export type VideoSource =
  | { kind: "youtube"; embedUrl: string }
  | { kind: "file"; src: string }
  | null;

export function parseVideoUrl(raw: string | null | undefined): VideoSource {
  const url = safeUrl(raw);
  if (!url) return null;
  const u = new URL(url);
  const host = u.hostname.replace(/^www\./, "");

  let id: string | null = null;
  if (host === "youtu.be") id = u.pathname.slice(1);
  else if (host === "youtube.com" || host === "m.youtube.com" || host === "youtube-nocookie.com") {
    if (u.pathname === "/watch") id = u.searchParams.get("v");
    else if (u.pathname.startsWith("/embed/") || u.pathname.startsWith("/shorts/")) id = u.pathname.split("/")[2];
  }
  if (id && /^[\w-]{6,20}$/.test(id)) {
    // youtube-nocookie: não grava cookies até o usuário interagir (LGPD).
    return { kind: "youtube", embedUrl: `https://www.youtube-nocookie.com/embed/${id}?rel=0&modestbranding=1` };
  }
  if (/\.(mp4|webm|ogg)$/i.test(u.pathname)) return { kind: "file", src: url };
  return null;
}

/** Só aceitamos incorporação oficial do Google Maps. */
export function safeMapsEmbed(raw: string | null | undefined, address: string): string | null {
  const url = safeUrl(raw);
  if (url) {
    const u = new URL(url);
    if (u.protocol === "https:" && u.hostname.endsWith("google.com") && u.pathname.startsWith("/maps")) return url;
  }
  if (address.trim()) return `https://www.google.com/maps?q=${encodeURIComponent(address)}&output=embed`;
  return null;
}

export function whatsappLink(number: string, text?: string): string | null {
  const digits = number.replace(/\D/g, "");
  if (digits.length < 10) return null;
  return `https://wa.me/${digits}${text ? `?text=${encodeURIComponent(text)}` : ""}`;
}
