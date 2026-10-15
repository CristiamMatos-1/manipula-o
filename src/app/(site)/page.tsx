import Link from "next/link";
import { prisma } from "@/lib/db";
import { getCompanyInfo } from "@/lib/company";
import { Hero } from "@/components/site/Hero";
import { About } from "@/components/site/About";
import { Contact } from "@/components/site/Contact";
import { PostCard } from "@/components/site/PostCard";

export default async function HomePage() {
  const [company, posts] = await Promise.all([
    getCompanyInfo(),
    prisma.post.findMany({
      where: { status: "PUBLISHED" },
      orderBy: { publishedAt: "desc" },
      take: 3,
      select: { slug: true, title: true, excerpt: true, coverUrl: true, publishedAt: true },
    }),
  ]);

  return (
    <main>
      <Hero name={company.name} tagline={company.tagline} videoUrl={company.heroVideoUrl} />
      <About {...company} />
      {posts.length > 0 && (
        <section className="py-12 sm:py-16">
          <div className="container">
            <div className="flex items-end justify-between gap-4">
              <h2 className="section-title">Últimos artigos</h2>
              <Link href="/blog" className="shrink-0 text-sm font-bold text-brand-blue hover:underline">Ver todos</Link>
            </div>
            <div className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {posts.map((p) => (
                <PostCard key={p.slug} post={p} />
              ))}
            </div>
          </div>
        </section>
      )}
      <Contact {...company} />
    </main>
  );
}
