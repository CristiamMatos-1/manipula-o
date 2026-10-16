<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e(($title ?? 'Painel') . ' | Painel Drogacare') ?></title>
  <link rel="icon" type="image/svg+xml" href="<?= e(static_url('favicon.svg')) ?>">
  <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body>
<main class="flex min-h-screen items-center justify-center bg-gradient-to-br from-brand-navy via-brand-blue to-brand-sky px-4 py-10">
  <div class="w-full <?= !empty($wide) ? 'max-w-2xl' : 'max-w-md' ?>">
    <a href="<?= e(url('/')) ?>" class="mb-6 flex items-center justify-center gap-3 text-white">
      <img src="<?= e(asset('img/logo.svg')) ?>" alt="" class="h-10 w-10">
      <span class="text-2xl font-extrabold tracking-tight">Drogacare</span>
    </a>
    <div class="rounded-2xl bg-white p-6 shadow-2xl sm:p-8"><?= $_content ?></div>
    <p class="mt-6 text-center text-xs text-white/80">Área restrita. Acessos são registrados para fins de auditoria (LGPD).</p>
  </div>
</main>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
