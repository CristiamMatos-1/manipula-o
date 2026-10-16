<?php
use App\Core\Embed;
use App\Models\Company;

$c = $company;
$video = Embed::video($c['hero_video_url']);
$years = Company::yearsInBusiness((int) $c['founded_year']);
$photo = safe_image_url($c['pharmacist_photo_url']) ?? asset('img/avatar-placeholder.svg');
$map = Embed::maps($c['maps_embed_url'], $c['address']);
$wa = Embed::whatsapp($c['whatsapp'], 'Olá! Gostaria de falar com a Drogacare.');
?>
<main>
  <!-- Hero -->
  <section class="relative overflow-hidden bg-gradient-to-br from-brand-navy via-brand-navy to-brand-blue text-white">
    <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-brand-sky/20 blur-3xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-brand-green/20 blur-3xl" aria-hidden="true"></div>
    <div class="container relative grid items-center gap-8 py-10 sm:py-14 lg:grid-cols-2 lg:gap-12 lg:py-20">
      <div>
        <p class="inline-block rounded-full bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-wider text-brand-mint sm:text-sm"><?= e($c['tagline']) ?></p>
        <h1 class="mt-4 text-3xl font-extrabold leading-tight sm:text-4xl lg:text-5xl">Cuidado sob medida, <span class="text-brand-green">fórmula por fórmula</span>.</h1>
        <p class="mt-4 max-w-xl text-base text-white/85 sm:text-lg">Na <?= e($c['name']) ?>, cada medicamento é manipulado de forma individualizada, com rigor técnico e atendimento farmacêutico próximo.</p>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
          <a href="<?= e(url('/#contato')) ?>" class="btn bg-brand-green text-brand-navy hover:bg-brand-mint">Fale conosco <?= icon('arrow-right', 'h-4 w-4') ?></a>
          <a href="<?= e(url('/#sobre')) ?>" class="btn border border-white/40 text-white hover:bg-white/10">Conheça a Drogacare</a>
        </div>
      </div>
      <div class="aspect-video w-full overflow-hidden rounded-2xl bg-black/30 shadow-2xl ring-1 ring-white/20">
        <?php if ($video && $video['kind'] === 'youtube'): ?>
          <iframe src="<?= e($video['src']) ?>" title="Vídeo institucional <?= e($c['name']) ?>" class="h-full w-full" loading="lazy"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
        <?php elseif ($video): ?>
          <video class="h-full w-full object-cover" src="<?= e($video['src']) ?>" controls autoplay muted loop playsinline preload="metadata">Seu navegador não suporta vídeo HTML5.</video>
        <?php else: ?>
          <div class="flex h-full w-full flex-col items-center justify-center gap-3 p-6 text-center text-white/80">
            <img src="<?= e(asset('img/logo.svg')) ?>" alt="" class="h-16 w-16">
            <p class="text-sm">O vídeo institucional será exibido aqui.</p>
          </div>
        <?php endif ?>
      </div>
    </div>
  </section>

  <!-- Sobre nós -->
  <section id="sobre" class="scroll-mt-16 bg-brand-mint/30 py-12 sm:py-16 lg:py-20">
    <div class="container grid gap-8 lg:grid-cols-5 lg:gap-12">
      <div class="lg:col-span-3">
        <p class="text-sm font-bold uppercase tracking-wider text-brand-blue">Sobre nós</p>
        <h2 class="section-title mt-2">Tradição e confiança desde <?= e($c['founded_year']) ?></h2>
        <div class="mt-6 flex items-center gap-4 rounded-2xl bg-brand-navy p-5 text-white shadow-card sm:gap-6 sm:p-6">
          <p class="text-5xl font-extrabold leading-none text-brand-green sm:text-6xl"><?= $years ?>+</p>
          <p class="text-sm font-semibold sm:text-base">anos de mercado cuidando da saúde de gerações de famílias.</p>
        </div>
        <p class="mt-6 text-base leading-relaxed text-brand-navy/85 sm:text-lg">A <?= e($c['name']) ?> é uma farmácia de manipulação que une conhecimento científico, matéria-prima selecionada e atendimento humanizado. Cada fórmula é preparada sob medida, seguindo rigorosos controles de qualidade.</p>
        <ul class="mt-6 grid gap-4 sm:grid-cols-3">
          <?php foreach ([['flask-conical', 'Fórmulas sob medida', 'Dose e forma ideais para você.'], ['award', 'Qualidade', 'Controle em cada etapa.'], ['heart-handshake', 'Atendimento', 'Orientação farmacêutica próxima.']] as [$ic, $t, $d]): ?>
            <li class="rounded-xl bg-white p-4 shadow-card"><span class="text-brand-blue"><?= icon($ic, 'h-7 w-7') ?></span><p class="mt-2 font-bold"><?= e($t) ?></p><p class="text-sm text-brand-navy/70"><?= e($d) ?></p></li>
          <?php endforeach ?>
        </ul>
      </div>
      <aside class="lg:col-span-2" aria-label="Farmacêutico Responsável">
        <div class="mx-auto max-w-sm overflow-hidden rounded-2xl bg-white shadow-card">
          <img src="<?= e($photo) ?>" alt="<?= e($c['pharmacist_name'] ? 'Foto de ' . $c['pharmacist_name'] : 'Farmacêutico responsável') ?>" class="aspect-square w-full object-cover" loading="lazy">
          <div class="border-t-4 border-brand-green p-5 text-center">
            <p class="text-xs font-bold uppercase tracking-wider text-brand-forest">Farmacêutico(a) Responsável</p>
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
        <h2 class="section-title">Últimos artigos</h2>
        <a href="<?= e(url('/blog')) ?>" class="shrink-0 text-sm font-bold text-brand-blue hover:underline">Ver todos</a>
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
      <p class="text-sm font-bold uppercase tracking-wider text-brand-blue">Contato</p>
      <h2 class="section-title mt-2">Venha nos visitar ou fale conosco</h2>
      <div class="mt-8 grid gap-6 lg:grid-cols-5">
        <ul class="space-y-4 lg:col-span-2">
          <?php if ($c['address']): ?><li class="card flex gap-3"><span class="text-brand-blue"><?= icon('map-pin', 'h-6 w-6 shrink-0') ?></span><div><p class="font-bold">Endereço</p><p class="text-sm"><?= e($c['address']) ?></p></div></li><?php endif ?>
          <?php if ($c['phone']): ?><li class="card flex gap-3"><span class="text-brand-blue"><?= icon('phone', 'h-6 w-6 shrink-0') ?></span><div><p class="font-bold">Telefone</p><a href="tel:<?= e(preg_replace('/[^\d+]/', '', $c['phone'])) ?>" class="text-sm hover:underline"><?= e($c['phone']) ?></a></div></li><?php endif ?>
          <?php if ($c['email']): ?><li class="card flex gap-3"><span class="text-brand-blue"><?= icon('mail', 'h-6 w-6 shrink-0') ?></span><div class="min-w-0"><p class="font-bold">E-mail</p><a href="mailto:<?= e($c['email']) ?>" class="break-all text-sm hover:underline"><?= e($c['email']) ?></a></div></li><?php endif ?>
          <?php if ($wa): ?><li><a href="<?= e($wa) ?>" target="_blank" rel="noopener noreferrer" class="btn w-full bg-[#25D366] text-white hover:opacity-90"><?= icon('message-circle') ?> Chamar no WhatsApp</a></li><?php endif ?>
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
      <h2 id="novidades-titulo" class="section-title">Novidades</h2>
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
