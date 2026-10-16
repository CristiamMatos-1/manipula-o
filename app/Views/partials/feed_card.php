<?php $cover = safe_image_url($card['cover_url'] ?? null); $href = url('/p/' . $card['slug']); ?>
<article class="overflow-hidden rounded-2xl border border-brand-navy/10 bg-white shadow-card transition hover:shadow-xl" data-feed-item>
  <a href="<?= e($href) ?>" class="block">
    <?php if ($cover): ?>
      <img src="<?= e($cover) ?>" alt="" loading="lazy" class="max-h-[28rem] w-full bg-brand-mint/20 object-cover">
    <?php endif ?>
    <div class="p-5 sm:p-6">
      <time class="text-xs font-semibold text-brand-blue" datetime="<?= e(date('c', strtotime((string) $card['published_at']))) ?>"><?= e(format_date($card['published_at'])) ?></time>
      <h3 class="mt-1 text-xl font-extrabold text-brand-navy sm:text-2xl"><?= e($card['title']) ?></h3>
      <?php if (!empty($card['excerpt'])): ?><p class="mt-2 text-sm text-brand-navy/75 sm:text-base"><?= e($card['excerpt']) ?></p><?php endif ?>
      <span class="mt-4 inline-flex items-center gap-1 text-sm font-bold text-brand-blue">Ler mais <?= icon('arrow-right', 'h-4 w-4') ?></span>
    </div>
  </a>
</article>
