"use client";

import { useFormState } from "react-dom";
import { saveCompanyAction } from "@/app/admin/actions/company";
import { Field, FormMessage, SubmitButton } from "@/components/ui/form";
import { initialFormState } from "@/lib/form";

type Company = {
  name: string; tagline: string; foundedYear: number;
  pharmacistName: string; pharmacistCrf: string; pharmacistPhotoUrl: string;
  phone: string; whatsapp: string; email: string; address: string; mapsEmbedUrl: string;
  heroVideoUrl: string; youtubeUrl: string; instagramUrl: string; facebookUrl: string;
};

export function CompanyForm({ company, canEdit }: { company: Company; canEdit: boolean }) {
  const [state, action] = useFormState(saveCompanyAction, initialFormState);
  const e = state.fieldErrors ?? {};
  const f = (name: keyof Company, label: string, extra: Record<string, unknown> = {}) => (
    <Field label={label} name={name} defaultValue={String(company[name])} error={e[name]} {...extra} />
  );

  return (
    <form action={action} className="space-y-6" noValidate>
      <FormMessage state={state} />
      <fieldset disabled={!canEdit} className="space-y-6">
        <section className="card space-y-4">
          <h2 className="text-lg font-bold">Identidade</h2>
          <div className="grid gap-4 sm:grid-cols-2">
            {f("name", "Nome da empresa", { required: true })}
            {f("tagline", "Slogan")}
            {f("foundedYear", "Ano de fundação", { type: "number", inputMode: "numeric", required: true, hint: "Usado para calcular o tempo de mercado automaticamente." })}
          </div>
        </section>

        <section className="card space-y-4">
          <h2 className="text-lg font-bold">Farmacêutico(a) Responsável</h2>
          <div className="grid gap-4 sm:grid-cols-2">
            {f("pharmacistName", "Nome")}
            {f("pharmacistCrf", "CRF", { placeholder: "CRF-SP 12345" })}
            {f("pharmacistPhotoUrl", "URL da foto", { className: "sm:col-span-2", placeholder: "https://... ou /foto.jpg", hint: "Dica: coloque o arquivo na pasta public/ e use /nome-do-arquivo.jpg" })}
          </div>
        </section>

        <section className="card space-y-4">
          <h2 className="text-lg font-bold">Contato e localização</h2>
          <div className="grid gap-4 sm:grid-cols-2">
            {f("phone", "Telefone")}
            {f("whatsapp", "WhatsApp (somente números)", { placeholder: "5511999999999", inputMode: "numeric", hint: "DDI + DDD + número. Se vazio, o botão flutuante não aparece." })}
            {f("email", "E-mail de contato", { type: "email" })}
            {f("address", "Endereço")}
            {f("mapsEmbedUrl", "Link de incorporação do Google Maps", {
              className: "sm:col-span-2",
              placeholder: "https://www.google.com/maps/embed?pb=...",
              hint: "Google Maps > Compartilhar > Incorporar um mapa > copie apenas o endereço do src. Se vazio, o mapa é gerado a partir do endereço.",
            })}
          </div>
        </section>

        <section className="card space-y-4">
          <h2 className="text-lg font-bold">Vídeo e redes sociais</h2>
          <div className="grid gap-4 sm:grid-cols-2">
            {f("heroVideoUrl", "Vídeo institucional (Hero)", { className: "sm:col-span-2", placeholder: "https://www.youtube.com/watch?v=... ou https://.../video.mp4", hint: "Link do YouTube ou arquivo .mp4/.webm." })}
            {f("youtubeUrl", "Canal do YouTube", { type: "url" })}
            {f("instagramUrl", "Instagram", { type: "url" })}
            {f("facebookUrl", "Facebook", { type: "url" })}
          </div>
        </section>

        {canEdit && <SubmitButton className="btn-success" pendingText="Salvando...">Salvar dados da empresa</SubmitButton>}
      </fieldset>
      {!canEdit && <p className="text-sm text-brand-navy/60">Seu perfil é somente leitura.</p>}
    </form>
  );
}
