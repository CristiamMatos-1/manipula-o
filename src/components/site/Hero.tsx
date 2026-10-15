import Link from "next/link";
import { ArrowRight } from "lucide-react";
import { parseVideoUrl } from "@/lib/embed";

export function Hero({ name, tagline, videoUrl }: { name: string; tagline: string; videoUrl: string }) {
  const video = parseVideoUrl(videoUrl);
  return (
    <section className="relative overflow-hidden bg-gradient-to-br from-brand-navy via-brand-navy to-brand-blue text-white">
      <div className="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-brand-sky/20 blur-3xl" aria-hidden />
      <div className="pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-brand-green/20 blur-3xl" aria-hidden />
      <div className="container relative grid items-center gap-8 py-10 sm:py-14 lg:grid-cols-2 lg:gap-12 lg:py-20">
        <div>
          <p className="inline-block rounded-full bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-wider text-brand-mint sm:text-sm">{tagline}</p>
          <h1 className="mt-4 text-3xl font-extrabold leading-tight sm:text-4xl lg:text-5xl">
            Cuidado sob medida, <span className="text-brand-green">fórmula por fórmula</span>.
          </h1>
          <p className="mt-4 max-w-xl text-base text-white/85 sm:text-lg">
            Na {name}, cada medicamento é manipulado de forma individualizada, com rigor técnico e atendimento farmacêutico próximo.
          </p>
          <div className="mt-6 flex flex-col gap-3 sm:flex-row">
            <Link href="/#contato" className="btn bg-brand-green text-brand-navy hover:bg-brand-mint">
              Fale conosco <ArrowRight className="h-4 w-4" aria-hidden />
            </Link>
            <Link href="/#sobre" className="btn border border-white/40 text-white hover:bg-white/10">Conheça a Drogacare</Link>
          </div>
        </div>

        <div className="aspect-video w-full overflow-hidden rounded-2xl bg-black/30 shadow-2xl ring-1 ring-white/20">
          {video?.kind === "youtube" ? (
            <iframe
              src={video.embedUrl}
              title={`Vídeo institucional ${name}`}
              className="h-full w-full"
              loading="lazy"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
              allowFullScreen
              referrerPolicy="strict-origin-when-cross-origin"
            />
          ) : video?.kind === "file" ? (
            <video className="h-full w-full object-cover" src={video.src} controls autoPlay muted loop playsInline preload="metadata">
              Seu navegador não suporta vídeo HTML5.
            </video>
          ) : (
            <div className="flex h-full w-full flex-col items-center justify-center gap-3 p-6 text-center text-white/80">
              <img src="/logo.svg" alt="" className="h-16 w-16" />
              <p className="text-sm">O vídeo institucional será exibido aqui.</p>
            </div>
          )}
        </div>
      </div>
    </section>
  );
}
