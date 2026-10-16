<?php
use App\Models\Settings;

if (!Settings::hasBanner()) {
    return;
}
$bImg = safe_image_url(Settings::raw('banner_image'));
$bTitle = trim(site_text('banner_title'));
$bText = trim(site_text('banner_text'));
$bHref = link_href(site_text('banner_link_url'));
$bLabel = trim(site_text('banner_link_label')) ?: 'Saiba mais';
$bHasText = $bTitle !== '' || $bText !== '';
$bBtn = $bHref && $bHasText ? '<a href="' . e($bHref) . '" class="btn mt-4 bg-brand-green text-brand-navy hover:bg-brand-mint">' . e($bLabel) . ' ' . icon('arrow-right', 'h-4 w-4') . '</a>' : '';
?>
<section aria-label="Destaque" class="relative bg-brand-navy">
  <?php if ($bImg): ?>
    <div class="relative mx-auto max-w-[1600px]">
      <?php if ($bHref): ?><a href="<?= e($bHref) ?>" class="block" aria-label="<?= e($bTitle !== '' ? $bTitle : $bLabel) ?>"><?php endif ?>
      <img src="<?= e($bImg) ?>" alt="<?= e($bTitle) ?>" class="max-h-[32rem] min-h-[11rem] w-full object-cover">
      <?php if ($bHref): ?></a><?php endif ?>
      <?php if ($bHasText): ?>
        <div class="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-brand-navy/90 via-brand-navy/60 to-transparent px-4 pb-5 pt-16 text-white sm:px-8 sm:pb-8">
          <div class="container pointer-events-auto">
            <?php if ($bTitle !== ''): ?><p class="text-2xl font-extrabold leading-tight sm:text-4xl"><?= e($bTitle) ?></p><?php endif ?>
            <?php if ($bText !== ''): ?><p class="mt-2 max-w-3xl text-sm text-white/90 sm:text-lg"><?= e($bText) ?></p><?php endif ?>
            <?= $bBtn ?>
          </div>
        </div>
      <?php endif ?>
    </div>
  <?php else: ?>
    <div class="bg-brand-green text-brand-navy">
      <div class="container flex flex-col items-start gap-1 py-6 sm:py-8">
        <?php if ($bTitle !== ''): ?><p class="text-2xl font-extrabold leading-tight sm:text-3xl"><?= e($bTitle) ?></p><?php endif ?>
        <?php if ($bText !== ''): ?><p class="max-w-3xl text-sm sm:text-lg"><?= e($bText) ?></p><?php endif ?>
        <?= str_replace('bg-brand-green text-brand-navy hover:bg-brand-mint', 'bg-brand-navy text-white hover:bg-brand-blue', $bBtn) ?>
      </div>
    </div>
  <?php endif ?>
</section>
