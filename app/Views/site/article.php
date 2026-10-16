<?php
$shareUrl = abs_url($basePath . '/' . $item['slug']);
$cover = safe_image_url($item['cover_url'] ?? null);
$yt = safe_url($company['youtube_url'] ?? '');
$btn = 'inline-flex min-h-[44px] items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90';
?>
<main class="container max-w-3xl py-8 sm:py-12">
  <article>
    <header>
      <?php if ($isPost): ?><p class="text-sm font-semibold text-brand-blue"><?= e(format_date($item['published_at'])) ?></p><?php endif ?>
      <h1 class="mt-1 text-3xl font-extrabold leading-tight sm:text-4xl"><?= e($item['title']) ?></h1>
      <?php if ($item['excerpt']): ?><p class="mt-3 text-lg text-brand-navy/70"><?= e($item['excerpt']) ?></p><?php endif ?>
    </header>
    <?php if ($cover): ?><img src="<?= e($cover) ?>" alt="" class="mt-6 aspect-video w-full rounded-2xl object-cover"><?php endif ?>
    <div class="mt-8"><?= \App\Core\Content::render($item['content']) ?></div>
  </article>

  <div class="mt-10 rounded-2xl bg-brand-mint/30 p-4 sm:p-6">
    <div aria-label="Compartilhar" class="flex flex-wrap items-center gap-2" data-share data-url="<?= e($shareUrl) ?>" data-title="<?= e($item['title']) ?>">
      <span class="mr-1 text-sm font-bold text-brand-navy">Compartilhar:</span>
      <a href="<?= e('https://wa.me/?text=' . rawurlencode($item['title'] . ' ' . $shareUrl)) ?>" target="_blank" rel="noopener noreferrer" class="<?= $btn ?> bg-[#25D366]"><?= brand_icon('whatsapp') ?> WhatsApp</a>
      <a href="<?= e('https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($shareUrl)) ?>" target="_blank" rel="noopener noreferrer" class="<?= $btn ?> bg-[#1877F2]"><?= brand_icon('facebook') ?> Facebook</a>
      <button type="button" data-share-instagram class="<?= $btn ?> bg-gradient-to-tr from-[#f09433] via-[#dc2743] to-[#bc1888]"><?= brand_icon('instagram') ?> Instagram</button>
      <?php if ($yt): ?><a href="<?= e($yt) ?>" target="_blank" rel="noopener noreferrer" class="<?= $btn ?> bg-[#FF0000]"><?= brand_icon('youtube') ?> YouTube</a><?php endif ?>
      <button type="button" data-share-copy class="btn-secondary !min-h-[44px]"><?= icon('link-2', 'h-4 w-4') ?> <span data-share-copy-label>Copiar link</span></button>
    </div>
  </div>
</main>
