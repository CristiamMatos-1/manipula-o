import type { Metadata } from "next";
import { safeImageUrl } from "./utils";

type Seo = {
  title: string; slug: string; excerpt: string | null;
  seoTitle: string | null; seoDescription: string | null; seoKeywords: string | null; ogImageUrl: string | null;
};

export function buildMetadata(item: Seo, basePath: string): Metadata {
  const title = item.seoTitle || item.title;
  const description = item.seoDescription || item.excerpt || undefined;
  const image = safeImageUrl(item.ogImageUrl);
  return {
    title,
    description,
    keywords: item.seoKeywords?.split(",").map((k) => k.trim()).filter(Boolean),
    alternates: { canonical: `${basePath}/${item.slug}` },
    openGraph: { title, description, type: "article", url: `${basePath}/${item.slug}`, ...(image ? { images: [image] } : {}) },
    twitter: { card: image ? "summary_large_image" : "summary", title, description },
  };
}
