import { notFound } from "next/navigation";
import type { Metadata } from "next";
import { prisma } from "@/lib/db";
import { getCompanyInfo } from "@/lib/company";
import { ArticleView } from "@/components/site/ArticleView";
import { buildMetadata } from "@/lib/seo";

const find = (slug: string) => prisma.post.findFirst({ where: { slug, status: "PUBLISHED" } });

export async function generateMetadata({ params }: { params: { slug: string } }): Promise<Metadata> {
  const post = await find(params.slug);
  return post ? buildMetadata(post, "/blog") : { title: "Artigo não encontrado" };
}

export default async function PostPage({ params }: { params: { slug: string } }) {
  const [post, company] = await Promise.all([find(params.slug), getCompanyInfo()]);
  if (!post) notFound();
  return <ArticleView basePath="/blog" item={post} youtubeUrl={company.youtubeUrl} />;
}
