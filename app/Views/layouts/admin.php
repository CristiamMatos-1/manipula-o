<?php
use App\Core\Rbac;
$nav = [
    ['/admin', 'Dashboard', 'layout-dashboard', true],
    ['/admin/posts', 'Artigos', 'newspaper', true],
    ['/admin/paginas', 'Páginas', 'file-text', true],
    ['/admin/empresa', 'Dados da empresa', 'building-complex', true],
    ['/admin/usuarios', 'Usuários', 'users', Rbac::can($user['role'], 'users:manage')],
    ['/admin/auditoria', 'Auditoria', 'scroll-text', Rbac::can($user['role'], 'audit:view')],
];
$current = \App\Core\Request::path();
$isActive = static fn (string $href) => $href === '/admin' ? $current === '/admin' : str_starts_with($current, $href);

ob_start(); ?>
<div class="flex h-full flex-col bg-brand-navy text-white">
  <a href="<?= e(url('/admin')) ?>" class="flex items-center gap-3 px-5 py-5">
    <img src="<?= e(asset('img/logo.svg')) ?>" alt="" class="h-9 w-9">
    <div class="leading-tight"><p class="text-lg font-extrabold">Drogacare</p><p class="text-xs text-brand-mint">Painel de gestão</p></div>
  </a>
  <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-2" aria-label="Menu principal">
    <?php foreach ($nav as [$href, $label, $ic, $show]): if (!$show) continue; ?>
      <a href="<?= e(url($href)) ?>" <?= $isActive($href) ? 'aria-current="page"' : '' ?>
         class="flex min-h-[44px] items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold transition <?= $isActive($href) ? 'bg-brand-blue text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' ?>">
        <?= icon($ic, 'h-5 w-5 shrink-0') ?><?= e($label) ?>
      </a>
    <?php endforeach ?>
    <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener" class="flex min-h-[44px] items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold text-white/80 hover:bg-white/10 hover:text-white"><?= icon('external-link') ?> Ver site</a>
  </nav>
  <div class="border-t border-white/10 p-4">
    <p class="truncate text-sm font-bold"><?= e($user['name']) ?></p>
    <p class="truncate text-xs text-white/70"><?= e($user['email']) ?></p>
    <p class="mt-1 inline-block rounded-full bg-brand-green/20 px-2 py-0.5 text-[11px] font-semibold text-brand-mint"><?= e(Rbac::label($user['role'])) ?></p>
    <div class="mt-3 flex gap-2">
      <a href="<?= e(url('/admin/alterar-senha')) ?>" class="btn-ghost !min-h-[40px] flex-1 !px-2 text-xs !text-white hover:!bg-white/10"><?= icon('key-round', 'h-4 w-4') ?> Senha</a>
      <form method="post" action="<?= e(url('/admin/logout')) ?>" class="flex-1">
        <?= csrf_field() ?>
        <button type="submit" class="btn-ghost !min-h-[40px] w-full !px-2 text-xs !text-white hover:!bg-white/10"><?= icon('log-out', 'h-4 w-4') ?> Sair</button>
      </form>
    </div>
  </div>
</div>
<?php $sidebar = ob_get_clean(); ?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e(($title ?? 'Painel') . ' | Painel Drogacare') ?></title>
  <link rel="icon" type="image/svg+xml" href="<?= e(url('favicon.svg')) ?>">
  <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body>
<div class="min-h-screen bg-slate-50 lg:flex">
  <aside class="sticky top-0 hidden h-screen w-64 shrink-0 lg:block"><?= $sidebar ?></aside>

  <header class="sticky top-0 z-30 flex items-center justify-between bg-brand-navy px-4 py-3 text-white lg:hidden">
    <a href="<?= e(url('/admin')) ?>" class="flex items-center gap-2 font-extrabold"><img src="<?= e(asset('img/logo.svg')) ?>" alt="" class="h-8 w-8"> Drogacare</a>
    <button type="button" class="rounded-lg p-2 hover:bg-white/10" aria-label="Abrir menu" aria-expanded="false" data-drawer-open><?= icon('menu', 'h-6 w-6') ?></button>
  </header>

  <div id="admin-drawer" class="fixed inset-0 z-40 hidden lg:hidden" role="dialog" aria-modal="true" aria-label="Menu">
    <button type="button" class="absolute inset-0 bg-black/50" aria-label="Fechar menu" data-drawer-close></button>
    <div class="absolute inset-y-0 left-0 w-72 max-w-[85%] shadow-2xl">
      <button type="button" class="absolute right-2 top-3 z-10 rounded-lg p-2 text-white hover:bg-white/10" aria-label="Fechar menu" data-drawer-close><?= icon('x') ?></button>
      <?= $sidebar ?>
    </div>
  </div>

  <main class="min-w-0 flex-1 p-4 sm:p-6 lg:p-8">
    <?php require APP_PATH . '/Views/partials/flash.php'; ?>
    <?= $_content ?>
  </main>
</div>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
