import { Mail, MapPin, MessageCircle, Phone } from "lucide-react";
import { safeMapsEmbed, whatsappLink } from "@/lib/embed";

type Props = { name: string; phone: string; whatsapp: string; email: string; address: string; mapsEmbedUrl: string };

export function Contact({ name, phone, whatsapp, email, address, mapsEmbedUrl }: Props) {
  const map = safeMapsEmbed(mapsEmbedUrl, address);
  const wa = whatsappLink(whatsapp, "Olá! Gostaria de falar com a Drogacare.");
  return (
    <section id="contato" className="scroll-mt-16 py-12 sm:py-16 lg:py-20">
      <div className="container">
        <p className="text-sm font-bold uppercase tracking-wider text-brand-blue">Contato</p>
        <h2 className="section-title mt-2">Venha nos visitar ou fale conosco</h2>

        <div className="mt-8 grid gap-6 lg:grid-cols-5">
          <ul className="space-y-4 lg:col-span-2">
            {address && (
              <li className="card flex gap-3"><MapPin className="h-6 w-6 shrink-0 text-brand-blue" aria-hidden /><div><p className="font-bold">Endereço</p><p className="text-sm">{address}</p></div></li>
            )}
            {phone && (
              <li className="card flex gap-3"><Phone className="h-6 w-6 shrink-0 text-brand-blue" aria-hidden /><div><p className="font-bold">Telefone</p><a href={`tel:${phone.replace(/[^\d+]/g, "")}`} className="text-sm hover:underline">{phone}</a></div></li>
            )}
            {email && (
              <li className="card flex gap-3"><Mail className="h-6 w-6 shrink-0 text-brand-blue" aria-hidden /><div className="min-w-0"><p className="font-bold">E-mail</p><a href={`mailto:${email}`} className="break-all text-sm hover:underline">{email}</a></div></li>
            )}
            {wa && (
              <li><a href={wa} target="_blank" rel="noopener noreferrer" className="btn bg-[#25D366] text-white hover:opacity-90 w-full"><MessageCircle className="h-5 w-5" aria-hidden /> Chamar no WhatsApp</a></li>
            )}
          </ul>

          <div className="overflow-hidden rounded-2xl border border-brand-navy/10 shadow-card lg:col-span-3">
            {map ? (
              <iframe
                src={map}
                title={`Mapa de localização da ${name}`}
                className="h-72 w-full sm:h-96 lg:h-full lg:min-h-[420px]"
                loading="lazy"
                referrerPolicy="no-referrer-when-downgrade"
                allowFullScreen
              />
            ) : (
              <div className="flex h-72 items-center justify-center bg-brand-mint/30 p-6 text-center text-sm text-brand-navy/70">O mapa será exibido assim que o endereço for cadastrado.</div>
            )}
          </div>
        </div>
      </div>
    </section>
  );
}
