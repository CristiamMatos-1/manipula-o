<?php
use App\Core\Embed;
use App\Models\Company;

$c = $company;
$video = Embed::video($c['hero_video_url']);
$years = Company::yearsInBusiness((int) $c['founded_year']);
$photo = safe_image_url($c['pharmacist_photo_url']) ?? asset('img/avatar-placeholder.svg');
$map = Embed::maps($c['maps_embed_url'], $c['address']);
$wa = Embed::whatsapp($c['whatsapp'], site_text('whatsapp_message'));
$btn1 = trim(site_text('hero_btn1_label'));
$btn2 = trim(site_text('hero_btn2_label'));
?>
<main>
  <?php require APP_PATH . '/Views/partials/banner.php'; ?>

  <!-- Hero -->
  <section class="relative overflow-hidden bg-gradient-to-br from-brand-navy via-brand-navy to-brand-blue text-white">
    <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-brand-sky/20 blur-3xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-brand-green/20 blur-3xl" aria-hidden="true"></div>
    <div class="container relative grid items-center gap-8 py-10 sm:py-14 lg:grid-cols-2 lg:gap-12 lg:py-20">
      <div>
        <?php if (trim(site_text('hero_badge')) !== ''): ?><p class="inline-block rounded-full bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-wider text-brand-mint sm:text-sm"><?= e(site_text('hero_badge')) ?></p><?php endif ?>
        <h1 class="mt-4 text-3xl font-extrabold leading-tight sm:text-4xl lg:text-5xl"><?= e(site_text('hero_title')) ?> <span class="text-brand-green"><?= e(site_text('hero_highlight')) ?></span></h1>
        <?php if (trim(site_text('hero_text')) !== ''): ?><p class="mt-4 max-w-xl text-base text-white/85 sm:text-lg"><?= e(site_text('hero_text')) ?></p><?php endif ?>
        <?php $h1 = link_href(site_text('hero_btn1_url')); $h2 = link_href(site_text('hero_btn2_url')); ?>
        <?php if (($btn1 !== '' && $h1) || ($btn2 !== '' && $h2)): ?>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
          <?php if ($btn1 !== '' && $h1): ?><a href="<?= e($h1) ?>" class="btn bg-brand-green text-brand-navy hover:bg-brand-mint"><?= e($btn1) ?> <?= icon('arrow-right', 'h-4 w-4') ?></a><?php endif ?>
          <?php if ($btn2 !== '' && $h2): ?><a href="<?= e($h2) ?>" class="btn border border-white/40 text-white hover:bg-white/10"><?= e($btn2) ?></a><?php endif ?>
        </div>
        <?php endif ?>
      </div>
      <div class="aspect-video w-full overflow-hidden rounded-2xl bg-black/30 shadow-2xl ring-1 ring-white/20">
        <?php if ($video && $video['kind'] === 'youtube'): ?>
          <iframe src="<?= e($video['src']) ?>" title="Vídeo institucional <?= e($c['name']) ?>" class="h-full w-full" loading="lazy"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
        <?php elseif ($video): ?>
          <video class="h-full w-full object-cover" src="<?= e($video['src']) ?>" controls autoplay muted loop playsinline preload="metadata">Seu navegador não suporta vídeo HTML5.</video>
        <?php else: ?>
          <div class="flex h-full w-full flex-col items-center justify-center gap-3 p-6 text-center text-white/80">
            <img src="<?= e(default_cover()) ?>" alt="" class="h-16 w-auto max-w-[60%] object-contain">
            <?php if (trim(site_text('hero_video_placeholder')) !== ''): ?><p class="text-sm"><?= e(site_text('hero_video_placeholder')) ?></p><?php endif ?>
          </div>
        <?php endif ?>
      </div>
    </div>
  </section>

  <!-- Sobre nós -->
  <section id="sobre" class="scroll-mt-16 bg-brand-mint/30 py-12 sm:py-16 lg:py-20">
    <div class="container grid gap-8 lg:grid-cols-5 lg:gap-12">
      <div class="lg:col-span-3">
        <?php if (trim(site_text('about_label')) !== ''): ?><p class="text-sm font-bold uppercase tracking-wider text-brand-blue"><?= e(site_text('about_label')) ?></p><?php endif ?>
        <h2 class="section-title mt-2"><?= e(site_text('about_title')) ?></h2>
        <div class="mt-6 flex items-center gap-4 rounded-2xl bg-brand-navy p-5 text-white shadow-card sm:gap-6 sm:p-6">
          <p class="text-5xl font-extrabold leading-none text-brand-green sm:text-6xl"><?= $years ?>+</p>
          <p class="text-sm font-semibold sm:text-base"><?= e(site_text('about_counter_text')) ?></p>
        </div>
        <?php if (trim(site_text('about_text')) !== ''): ?><p class="mt-6 whitespace-pre-line text-base leading-relaxed text-brand-navy/85 sm:text-lg"><?= e(site_text('about_text')) ?></p><?php endif ?>
        <ul class="mt-6 grid gap-4 sm:grid-cols-3">
          <?php foreach ([['flask-conical', 'about_f1'], ['award', 'about_f2'], ['heart-handshake', 'about_f3']] as [$ic, $fk]): $t = site_text($fk . '_title'); $d = site_text($fk . '_text'); if (trim($t . $d) === '') continue; ?>
            <li class="rounded-xl bg-white p-4 shadow-card"><span class="text-brand-blue"><?= icon($ic, 'h-7 w-7') ?></span><p class="mt-2 font-bold"><?= e($t) ?></p><p class="text-sm text-brand-navy/70"><?= e($d) ?></p></li>
          <?php endforeach ?>
        </ul>
      </div>
      <aside class="lg:col-span-2" aria-label="Farmacêutico Responsável">
        <div class="mx-auto max-w-sm overflow-hidden rounded-2xl bg-white shadow-card">
          <img src="<?= e($photo) ?>" alt="<?= e($c['pharmacist_name'] ? 'Foto de ' . $c['pharmacist_name'] : 'Farmacêutico responsável') ?>" class="aspect-square w-full object-cover" loading="lazy">
          <div class="border-t-4 border-brand-green p-5 text-center">
            <p class="text-xs font-bold uppercase tracking-wider text-brand-forest"><?= e(site_text('pharmacist_title')) ?></p>
            <p class="mt-1 text-xl font-extrabold"><?= e($c['pharmacist_name'] ?: 'A definir') ?></p>
            <?php if ($c['pharmacist_crf']): ?><p class="text-sm font-semibold text-brand-blue"><?= e($c['pharmacist_crf']) ?></p><?php endif ?>
          </div>
        </div>
      </aside>
    </div>
  </section>

  <?php if ($posts): ?>
  <section class="py-12 sm:py-16">
    <div class="container">
      <div class="flex items-end justify-between gap-4">
        <h2 class="section-title"><?= e(site_text('articles_title')) ?></h2>
        <?php if (trim(site_text('articles_link_label')) !== ''): ?><a href="<?= e(url('/blog')) ?>" class="shrink-0 text-sm font-bold text-brand-blue hover:underline"><?= e(site_text('articles_link_label')) ?></a><?php endif ?>
      </div>
      <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($posts as $post) { require APP_PATH . '/Views/partials/post_card.php'; } ?>
      </div>
    </div>
  </section>
  <?php endif ?>

  <!-- Contato -->
  <section id="contato" class="scroll-mt-16 py-12 sm:py-16 lg:py-20">
    <div class="container">
      <?php if (trim(site_text('contact_label')) !== ''): ?><p class="text-sm font-bold uppercase tracking-wider text-brand-blue"><?= e(site_text('contact_label')) ?></p><?php endif ?>
      <h2 class="section-title mt-2"><?= e(site_text('contact_title')) ?></h2>
      <div class="mt-8 grid gap-6 lg:grid-cols-5">
        <ul class="space-y-4 lg:col-span-2">
          <?php if ($c['address']): ?><li class="card flex gap-3"><span class="text-brand-blue"><?= icon('map-pin', 'h-6 w-6 shrink-0') ?></span><div><p class="font-bold">Endereço</p><p class="text-sm"><?= e($c['address']) ?></p></div></li><?php endif ?>
          <?php if ($c['phone']): ?><li class="card flex gap-3"><span class="text-brand-blue"><?= icon('phone', 'h-6 w-6 shrink-0') ?></span><div><p class="font-bold">Telefone</p><a href="tel:<?= e(preg_replace('/[^\d+]/', '', $c['phone'])) ?>" class="text-sm hover:underline"><?= e($c['phone']) ?></a></div></li><?php endif ?>
          <?php if ($c['email']): ?><li class="card flex gap-3"><span class="text-brand-blue"><?= icon('mail', 'h-6 w-6 shrink-0') ?></span><div class="min-w-0"><p class="font-bold">E-mail</p><a href="mailto:<?= e($c['email']) ?>" class="break-all text-sm hover:underline"><?= e($c['email']) ?></a></div></li><?php endif ?>
          <?php if ($wa): ?><li><a href="<?= e($wa) ?>" target="_blank" rel="noopener noreferrer" class="btn w-full bg-[#25D366] text-white hover:opacity-90"><?= icon('message-circle') ?> <?= e(site_text('contact_whatsapp_label')) ?></a></li><?php endif ?>
        </ul>
        <div class="overflow-hidden rounded-2xl border border-brand-navy/10 shadow-card lg:col-span-3">
          <?php if ($map): ?>
            <iframe src="<?= e($map) ?>" title="Mapa de localização da <?= e($c['name']) ?>" class="h-72 w-full sm:h-96 lg:h-full lg:min-h-[420px]" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
          <?php else: ?>
            <div class="flex h-72 items-center justify-center bg-brand-mint/30 p-6 text-center text-sm text-brand-navy/70">O mapa será exibido assim que o endereço for cadastrado.</div>
          <?php endif ?>
        </div>
      </div>
    </div>
  </section>

  <?php if (!empty($feed)): ?>
  <!-- Novidades: cada página publicada vira um cartão aqui, depois do Contato (mais novas primeiro) -->
  <section id="novidades" class="scroll-mt-16 bg-brand-mint/20 py-12 sm:py-16" aria-labelledby="novidades-titulo">
    <div class="container max-w-2xl">
      <h2 id="novidades-titulo" class="section-title"><?= e(site_text('feed_title')) ?></h2>
      <div class="mt-8 space-y-6" data-feed data-feed-url="<?= e(url('/feed')) ?>" data-feed-next="<?= $feedHasMore ? (int) $feedPage + 1 : 0 ?>">
        <?php foreach ($feed as $card) { require APP_PATH . '/Views/partials/feed_card.php'; } ?>
      </div>
      <?php if ($feedHasMore): ?>
        <div class="mt-8 text-center" data-feed-more>
          <a href="<?= e(url('/?pagina=' . ((int) $feedPage + 1) . '#novidades')) ?>" class="btn-secondary" data-feed-link>Carregar mais</a>
        </div>
      <?php endif ?>
    </div>
  </section>
  <?php endif ?>
</main>
