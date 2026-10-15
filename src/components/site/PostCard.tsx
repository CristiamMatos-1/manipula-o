import Link from "next/link";
import { formatDate, safeImageUrl } from "@/lib/utils";

export function PostCard({ post }: { post: { slug: string; title: string; excerpt: string | null; coverUrl: string | null; publishedAt: Date | null } }) {
  const cover = safeImageUrl(post.coverUrl);
  return (
    <article className="group overflow-hidden rounded-2xl border border-brand-navy/10 bg-white shadow-card transition hover:-translate-y-1 hover:shadow-xl">
      <Link href={`/blog/${post.slug}`} className="block">
        {cover ? (
          <img src={cover} alt="" loading="lazy" className="aspect-video w-full object-cover" />
        ) : (
          <div className="flex aspect-video w-full items-center justify-center bg-gradient-to-br from-brand-blue to-brand-sky">
            <img src="/logo.svg" alt="" className="h-14 w-14 opacity-90" />
          </div>
        )}
        <div className="p-5">
          <time className="text-xs font-semibold text-brand-blue" dateTime={post.publishedAt?.toISOString()}>{formatDate(post.publishedAt)}</time>
          <h3 className="mt-1 text-lg font-extrabold group-hover:text-brand-blue">{post.title}</h3>
          {post.excerpt && <p className="mt-2 line-clamp-3 text-sm text-brand-navy/75">{post.excerpt}</p>}
        </div>
      </Link>
    </article>
  );
}
