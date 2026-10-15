import type { MetadataRoute } from "next";
import { prisma } from "@/lib/db";
import { env } from "@/lib/env";

export const dynamic = "force-dynamic";

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const [posts, pages] = await Promise.all([
    prisma.post.findMany({ where: { status: "PUBLISHED" }, select: { slug: true, updatedAt: true } }),
    prisma.page.findMany({ where: { status: "PUBLISHED" }, select: { slug: true, updatedAt: true } }),
  ]);
  return [
    { url: env.appUrl, changeFrequency: "weekly", priority: 1 },
    { url: `${env.appUrl}/blog`, changeFrequency: "weekly", priority: 0.8 },
    ...posts.map((p) => ({ url: `${env.appUrl}/blog/${p.slug}`, lastModified: p.updatedAt })),
    ...pages.map((p) => ({ url: `${env.appUrl}/p/${p.slug}`, lastModified: p.updatedAt })),
  ];
}
