import type { Metadata } from "next";
import { prisma } from "@/lib/db";
import { PostCard } from "@/components/site/PostCard";

export const metadata: Metadata = {
  title: "Artigos",
  description: "Dicas e informações sobre saúde, bem-estar e manipulação de medicamentos.",
  alternates: { canonical: "/blog" },
};

export default async function BlogPage() {
  const posts = await prisma.post.findMany({
    where: { status: "PUBLISHED" },
    orderBy: { publishedAt: "desc" },
    select: { slug: true, title: true, excerpt: true, coverUrl: true, publishedAt: true },
  });
  return (
    <main className="container py-10 sm:py-14">
      <h1 className="section-title">Artigos</h1>
      <p className="mt-2 text-brand-navy/70">Informação de qualidade para cuidar da sua saúde.</p>
      {posts.length === 0 ? (
        <p className="mt-10 text-brand-navy/60">Em breve, novos artigos.</p>
      ) : (
        <div className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {posts.map((p) => (
            <PostCard key={p.slug} post={p} />
          ))}
        </div>
      )}
    </main>
  );
}
