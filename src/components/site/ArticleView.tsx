import { RenderContent } from "@/lib/content";
import { ShareButtons } from "@/components/site/ShareButtons";
import { formatDate, safeImageUrl, safeUrl } from "@/lib/utils";
import { env } from "@/lib/env";

export function ArticleView({
  basePath,
  item,
  youtubeUrl,
}: {
  basePath: "/blog" | "/p";
  item: { slug: string; title: string; excerpt: string | null; content: string; publishedAt: Date | null; coverUrl?: string | null };
  youtubeUrl: string;
}) {
  const url = `${env.appUrl}${basePath}/${item.slug}`;
  const cover = safeImageUrl(item.coverUrl);
  return (
    <main className="container max-w-3xl py-8 sm:py-12">
      <article>
        <header>
          {basePath === "/blog" && <p className="text-sm font-semibold text-brand-blue">{formatDate(item.publishedAt)}</p>}
          <h1 className="mt-1 text-3xl font-extrabold leading-tight sm:text-4xl">{item.title}</h1>
          {item.excerpt && <p className="mt-3 text-lg text-brand-navy/70">{item.excerpt}</p>}
        </header>
        {cover && <img src={cover} alt="" className="mt-6 aspect-video w-full rounded-2xl object-cover" />}
        <div className="mt-8"><RenderContent content={item.content} /></div>
      </article>
      <div className="mt-10 rounded-2xl bg-brand-mint/30 p-4 sm:p-6">
        <ShareButtons url={url} title={item.title} youtubeUrl={safeUrl(youtubeUrl) ?? undefined} />
      </div>
    </main>
  );
}
