<?php $cover = safe_image_url($post['cover_url'] ?? null); ?>
<article class="group overflow-hidden rounded-2xl border border-brand-navy/10 bg-white shadow-card transition hover:-translate-y-1 hover:shadow-xl">
  <a href="<?= e(url('/blog/' . $post['slug'])) ?>" class="block">
    <?php if ($cover): ?>
      <img src="<?= e($cover) ?>" alt="" loading="lazy" class="aspect-video w-full object-cover">
    <?php else: ?>
      <div class="flex aspect-video w-full items-center justify-center bg-brand-mint/30 p-6"><img src="<?= e(default_cover()) ?>" alt="" loading="lazy" class="max-h-full max-w-[70%] object-contain" data-default-cover></div>
    <?php endif ?>
    <div class="p-5">
      <time class="text-xs font-semibold text-brand-blue" datetime="<?= e(date('c', strtotime((string) $post['published_at']))) ?>"><?= e(format_date($post['published_at'])) ?></time>
      <h3 class="mt-1 text-lg font-extrabold group-hover:text-brand-blue"><?= e($post['title']) ?></h3>
      <?php if (!empty($post['excerpt'])): ?><p class="mt-2 line-clamp-3 text-sm text-brand-navy/75"><?= e($post['excerpt']) ?></p><?php endif ?>
    </div>
  </a>
</article>
