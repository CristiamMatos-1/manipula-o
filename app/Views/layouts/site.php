<?php
$c = $company ?? [];
$meta = $meta ?? [];
$fullTitle = !empty($meta['title']) ? $meta['title'] . ' | Drogacare' : 'Drogacare | Farmácia de Manipulação desde 1991';
$desc = $meta['description'] ?? 'Drogacare: farmácia de manipulação com mais de três décadas de tradição, qualidade e atendimento farmacêutico especializado.';
$canonical = abs_url($meta['canonical'] ?? '/');
$ogImage = !empty($meta['image']) ? (preg_match('#^https?://#', $meta['image']) ? $meta['image'] : abs_url($meta['image'])) : null;
$wa = \App\Core\Embed::whatsapp($c['whatsapp'] ?? '', 'Olá! Gostaria de falar com a Drogacare.');
$nav = [['/#sobre', 'Sobre nós'], ['/blog', 'Artigos'], ['/#contato', 'Contato']];
?><!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#061e3b">
  <title><?= e($fullTitle) ?></title>
  <meta name="description" content="<?= e($desc) ?>">
  <?php if (!empty($meta['keywords'])): ?><meta name="keywords" content="<?= e($meta['keywords']) ?>"><?php endif ?>
  <link rel="canonical" href="<?= e($canonical) ?>">
  <meta property="og:site_name" content="Drogacare">
  <meta property="og:locale" content="pt_BR">
  <meta property="og:type" content="<?= e($meta['type'] ?? 'website') ?>">
  <meta property="og:title" content="<?= e($meta['title'] ?? 'Drogacare | Farmácia de Manipulação') ?>">
  <meta property="og:description" content="<?= e($desc) ?>">
  <meta property="og:url" content="<?= e($canonical) ?>">
  <?php if ($ogImage): ?><meta property="og:image" content="<?= e($ogImage) ?>"><?php endif ?>
  <meta name="twitter:card" content="<?= $ogImage ? 'summary_large_image' : 'summary' ?>">
  <link rel="icon" type="image/svg+xml" href="<?= e(url('favicon.svg')) ?>">
  <link rel="preload" href="<?= e(asset('fonts/raleway-latin.woff2')) ?>" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body>
<a href="#conteudo" class="sr-only focus:not-sr-only focus:fixed focus:left-2 focus:top-2 focus:z-[100] focus:rounded focus:bg-white focus:px-3 focus:py-2">Ir para o conteúdo</a>

<header class="sticky top-0 z-40 border-b border-brand-navy/10 bg-white/95 backdrop-blur">
  <div class="container flex h-16 items-center justify-between">
    <a href="<?= e(url('/')) ?>" class="flex items-center gap-2 text-xl font-extrabold text-brand-navy">
      <img src="<?= e(asset('img/logo.svg')) ?>" alt="" class="h-9 w-9"> <?= e($c['name'] ?? 'Drogacare') ?>
    </a>
    <nav class="hidden items-center gap-1 md:flex" aria-label="Principal">
      <?php foreach ($nav as [$href, $label]): ?>
        <a href="<?= e(url($href)) ?>" class="rounded-lg px-4 py-2 text-sm font-semibold text-brand-navy hover:bg-brand-mint/50"><?= e($label) ?></a>
      <?php endforeach ?>
      <a href="<?= e(url('/admin')) ?>" class="btn-secondary ml-2 !min-h-[40px]">Área restrita</a>
    </nav>
    <button type="button" class="rounded-lg p-2 text-brand-navy hover:bg-brand-mint/50 md:hidden" aria-label="Abrir menu" aria-expanded="false" aria-controls="mobile-menu" data-toggle="mobile-menu">
      <span data-icon-open><?= icon('menu', 'h-6 w-6') ?></span>
      <span data-icon-close class="hidden"><?= icon('x', 'h-6 w-6') ?></span>
    </button>
  </div>
  <nav id="mobile-menu" class="hidden border-t border-brand-navy/10 bg-white md:hidden" aria-label="Principal (mobile)">
    <ul class="container flex flex-col py-2">
      <?php foreach ($nav as [$href, $label]): ?>
        <li><a href="<?= e(url($href)) ?>" class="flex min-h-[48px] items-center text-base font-semibold text-brand-navy"><?= e($label) ?></a></li>
      <?php endforeach ?>
      <li><a href="<?= e(url('/admin')) ?>" class="flex min-h-[48px] items-center text-base font-semibold text-brand-blue">Área restrita</a></li>
    </ul>
  </nav>
</header>

<div id="conteudo"><?= $_content ?></div>

<footer class="bg-brand-navy text-white">
  <div class="container grid gap-8 py-10 sm:grid-cols-2 lg:grid-cols-3">
    <div>
      <p class="flex items-center gap-2 text-xl font-extrabold"><img src="<?= e(asset('img/logo.svg')) ?>" alt="" class="h-9 w-9"> <?= e($c['name'] ?? 'Drogacare') ?></p>
      <p class="mt-2 text-sm text-brand-mint"><?= e($c['tagline'] ?? '') ?> · desde <?= e($c['founded_year'] ?? 1991) ?></p>
      <ul class="mt-4 flex gap-3">
        <?php foreach (['instagram_url' => ['instagram', 'Instagram'], 'facebook_url' => ['facebook', 'Facebook'], 'youtube_url' => ['youtube', 'YouTube']] as $key => [$ic, $label]): ?>
          <?php if ($href = safe_url($c[$key] ?? '')): ?>
            <li><a href="<?= e($href) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e($label) ?>" class="flex h-11 w-11 items-center justify-center rounded-full bg-white/10 hover:bg-brand-sky"><?= brand_icon($ic) ?></a></li>
          <?php endif ?>
        <?php endforeach ?>
      </ul>
    </div>
    <ul class="space-y-3 text-sm text-white/85">
      <?php if (!empty($c['address'])): ?><li class="flex gap-2"><?= icon('map-pin', 'mt-0.5 h-4 w-4 shrink-0 text-brand-green') ?><?= e($c['address']) ?></li><?php endif ?>
      <?php if (!empty($c['phone'])): ?><li class="flex gap-2"><?= icon('phone', 'mt-0.5 h-4 w-4 shrink-0 text-brand-green') ?><?= e($c['phone']) ?></li><?php endif ?>
      <?php if (!empty($c['email'])): ?><li class="flex gap-2"><?= icon('mail', 'mt-0.5 h-4 w-4 shrink-0 text-brand-green') ?><a href="mailto:<?= e($c['email']) ?>" class="hover:underline"><?= e($c['email']) ?></a></li><?php endif ?>
    </ul>
    <ul class="space-y-2 text-sm">
      <li><a href="<?= e(url('/blog')) ?>" class="hover:underline">Artigos</a></li>
      <li><a href="<?= e(url('/p/politica-de-privacidade')) ?>" class="hover:underline">Política de Privacidade</a></li>
      <li><button type="button" class="hover:underline" data-cookie-prefs>Preferências de cookies</button></li>
    </ul>
  </div>
  <div class="border-t border-white/10 py-4 text-center text-xs text-white/60">© <?= date('Y') ?> <?= e($c['name'] ?? 'Drogacare') ?>. Todos os direitos reservados.</div>
</footer>

<?php if ($wa): ?>
<a href="<?= e($wa) ?>" target="_blank" rel="noopener noreferrer" aria-label="Falar com a Drogacare pelo WhatsApp"
   class="fixed bottom-4 right-4 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-xl transition hover:scale-105 hover:shadow-2xl sm:bottom-6 sm:right-6 sm:h-16 sm:w-16">
  <?= brand_icon('whatsapp', 'h-8 w-8 sm:h-9 sm:w-9') ?>
</a>
<?php endif ?>

<div id="cookie-banner" role="dialog" aria-live="polite" aria-label="Aviso de cookies" class="fixed inset-x-0 bottom-0 z-[60] hidden p-3 sm:p-4">
  <div class="container">
    <div class="mx-auto flex max-w-4xl flex-col gap-4 rounded-2xl border border-brand-navy/10 bg-white p-4 shadow-2xl sm:flex-row sm:items-center sm:p-5">
      <span class="hidden text-brand-blue sm:block"><?= icon('cookie', 'h-8 w-8') ?></span>
      <p class="flex-1 text-sm leading-relaxed text-brand-navy/90">
        Usamos cookies essenciais para o funcionamento do site e, com o seu consentimento, cookies de desempenho para melhorar sua experiência, conforme a LGPD. Saiba mais na
        <a href="<?= e(url('/p/politica-de-privacidade')) ?>" class="font-semibold text-brand-blue underline">Política de Privacidade</a>.
      </p>
      <div class="flex flex-col gap-2 sm:flex-row">
        <button type="button" class="btn-secondary" data-cookie-choice="essential">Somente essenciais</button>
        <button type="button" class="btn-primary" data-cookie-choice="all">Aceitar todos</button>
      </div>
    </div>
  </div>
</div>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
