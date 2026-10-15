import { notFound } from "next/navigation";
import type { Metadata } from "next";
import { prisma } from "@/lib/db";
import { getCompanyInfo } from "@/lib/company";
import { ArticleView } from "@/components/site/ArticleView";
import { buildMetadata } from "@/lib/seo";

const find = (slug: string) => prisma.page.findFirst({ where: { slug, status: "PUBLISHED" } });

export async function generateMetadata({ params }: { params: { slug: string } }): Promise<Metadata> {
  const page = await find(params.slug);
  return page ? buildMetadata(page, "/p") : { title: "Página não encontrada" };
}

export default async function InstitutionalPage({ params }: { params: { slug: string } }) {
  const [page, company] = await Promise.all([find(params.slug), getCompanyInfo()]);
  if (!page) notFound();
  return <ArticleView basePath="/p" item={page} youtubeUrl={company.youtubeUrl} />;
}
