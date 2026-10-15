import { Award, FlaskConical, HeartHandshake } from "lucide-react";
import { yearsInBusiness } from "@/lib/company";
import { safeImageUrl } from "@/lib/utils";

type Props = { name: string; foundedYear: number; pharmacistName: string; pharmacistCrf: string; pharmacistPhotoUrl: string };

export function About({ name, foundedYear, pharmacistName, pharmacistCrf, pharmacistPhotoUrl }: Props) {
  const years = yearsInBusiness(foundedYear);
  const photo = safeImageUrl(pharmacistPhotoUrl) ?? "/avatar-placeholder.svg";
  return (
    <section id="sobre" className="scroll-mt-16 bg-brand-mint/30 py-12 sm:py-16 lg:py-20">
      <div className="container grid gap-8 lg:grid-cols-5 lg:gap-12">
        <div className="lg:col-span-3">
          <p className="text-sm font-bold uppercase tracking-wider text-brand-blue">Sobre nós</p>
          <h2 className="section-title mt-2">Tradição e confiança desde {foundedYear}</h2>

          <div className="mt-6 flex items-center gap-4 rounded-2xl bg-brand-navy p-5 text-white shadow-card sm:gap-6 sm:p-6">
            <p className="text-5xl font-extrabold leading-none text-brand-green sm:text-6xl">{years}+</p>
            <p className="text-sm font-semibold sm:text-base">anos de mercado cuidando da saúde de gerações de famílias.</p>
          </div>

          <p className="mt-6 text-base leading-relaxed text-brand-navy/85 sm:text-lg">
            A {name} é uma farmácia de manipulação que une conhecimento científico, matéria-prima selecionada e atendimento humanizado. Cada fórmula é preparada sob medida, seguindo rigorosos controles de qualidade.
          </p>

          <ul className="mt-6 grid gap-4 sm:grid-cols-3">
            {[
              { Icon: FlaskConical, t: "Fórmulas sob medida", d: "Dose e forma ideais para você." },
              { Icon: Award, t: "Qualidade", d: "Controle em cada etapa." },
              { Icon: HeartHandshake, t: "Atendimento", d: "Orientação farmacêutica próxima." },
            ].map(({ Icon, t, d }) => (
              <li key={t} className="rounded-xl bg-white p-4 shadow-card">
                <Icon className="h-7 w-7 text-brand-blue" aria-hidden />
                <p className="mt-2 font-bold">{t}</p>
                <p className="text-sm text-brand-navy/70">{d}</p>
              </li>
            ))}
          </ul>
        </div>

        <aside className="lg:col-span-2" aria-label="Farmacêutico Responsável">
          <div className="mx-auto max-w-sm overflow-hidden rounded-2xl bg-white shadow-card">
            <img src={photo} alt={pharmacistName ? `Foto de ${pharmacistName}` : "Farmacêutico responsável"} className="aspect-square w-full object-cover" loading="lazy" />
            <div className="border-t-4 border-brand-green p-5 text-center">
              <p className="text-xs font-bold uppercase tracking-wider text-brand-forest">Farmacêutico(a) Responsável</p>
              <p className="mt-1 text-xl font-extrabold">{pharmacistName || "A definir"}</p>
              {pharmacistCrf && <p className="text-sm font-semibold text-brand-blue">{pharmacistCrf}</p>}
            </div>
          </div>
        </aside>
      </div>
    </section>
  );
}
