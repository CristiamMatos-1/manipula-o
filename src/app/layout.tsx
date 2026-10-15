import type { Metadata, Viewport } from "next";
import { Raleway } from "next/font/google";
import type { ReactNode } from "react";
import { env } from "@/lib/env";
import "./globals.css";

const raleway = Raleway({
  subsets: ["latin"],
  display: "swap",
  variable: "--font-raleway",
  fallback: ["Arial", "Helvetica", "sans-serif"],
});

export const metadata: Metadata = {
  metadataBase: new URL(env.appUrl),
  title: { default: "Drogacare | Farmácia de Manipulação desde 1991", template: "%s | Drogacare" },
  description: "Drogacare: farmácia de manipulação com mais de três décadas de tradição, qualidade e atendimento farmacêutico especializado.",
  icons: { icon: "/logo.svg" },
  openGraph: { type: "website", locale: "pt_BR", siteName: "Drogacare" },
};

export const viewport: Viewport = {
  width: "device-width",
  initialScale: 1,
  themeColor: "#061e3b",
};

export default function RootLayout({ children }: { children: ReactNode }) {
  return (
    <html lang="pt-BR" className={raleway.variable}>
      <body>{children}</body>
    </html>
  );
}
