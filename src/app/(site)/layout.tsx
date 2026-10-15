import type { ReactNode } from "react";
import { getCompanyInfo } from "@/lib/company";
import { SiteHeader } from "@/components/site/SiteHeader";
import { SiteFooter } from "@/components/site/SiteFooter";
import { WhatsAppFloat } from "@/components/site/WhatsAppFloat";
import { CookieBanner } from "@/components/site/CookieBanner";

export const dynamic = "force-dynamic";

export default async function SiteLayout({ children }: { children: ReactNode }) {
  const company = await getCompanyInfo();
  return (
    <>
      <a href="#conteudo" className="sr-only focus:not-sr-only focus:fixed focus:left-2 focus:top-2 focus:z-[100] focus:rounded focus:bg-white focus:px-3 focus:py-2">
        Ir para o conteúdo
      </a>
      <SiteHeader name={company.name} />
      <div id="conteudo">{children}</div>
      <SiteFooter c={company} />
      <WhatsAppFloat number={company.whatsapp} />
      <CookieBanner />
    </>
  );
}
