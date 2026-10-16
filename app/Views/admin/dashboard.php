<?php
use App\Core\Audit;
use App\Core\Rbac;

$pageTitle = 'Olá, ' . explode(' ', $user['name'])[0] . '!';
$pageDesc = 'Resumo geral do portal Drogacare.';
require __DIR__ . '/_header.php';

$tones = ['blue' => 'bg-brand-blue/10 text-brand-blue', 'green' => 'bg-brand-green/25 text-brand-forest', 'sky' => 'bg-brand-sky/15 text-brand-sky', 'navy' => 'bg-brand-navy/10 text-brand-navy'];
$drafts = $draftPosts + $draftPages;
$cards = [];
$cards[] = Rbac::can($user['role'], 'users:manage')
    ? ['Total de usuários', $totalUsers, 'users', 'blue', '/admin/usuarios', $activeUsers . ' ativos']
    : ['Usuários ativos', $activeUsers, 'user-check', 'blue', null, null];
$cards[] = ['Artigos publicados', $publishedPosts, 'newspaper', 'green', '/admin/posts', '+ ' . $publishedPages . ' páginas publicadas'];
$cards[] = ['Rascunhos pendentes', $drafts, 'file-pen', 'sky', '/admin/posts', $draftPosts . ' artigos · ' . $draftPages . ' páginas'];
$cards[] = ['Páginas do site', $publishedPages + $draftPages, 'file-text', 'navy', '/admin/paginas', null];
?>
<section aria-label="Resumo do sistema" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
  <?php foreach ($cards as [$label, $value, $ic, $tone, $href, $hint]): ?>
    <?php if ($href): ?><a href="<?= e(url($href)) ?>"><?php endif ?>
    <div class="card flex items-center gap-4 <?= $href ? 'transition hover:-translate-y-0.5 hover:shadow-lg' : '' ?>">
      <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl <?= $tones[$tone] ?>"><?= icon($ic, 'h-6 w-6') ?></div>
      <div class="min-w-0">
        <p class="text-3xl font-extrabold leading-none text-brand-navy"><?= e($value) ?></p>
        <p class="mt-1 truncate text-sm font-semibold text-brand-navy/70"><?= e($label) ?></p>
        <?php if ($hint): ?><p class="truncate text-xs text-brand-navy/50"><?= e($hint) ?></p><?php endif ?>
      </div>
    </div>
    <?php if ($href): ?></a><?php endif ?>
  <?php endforeach ?>
</section>

<section class="mt-6 grid gap-6 lg:grid-cols-3" aria-label="Atividade e atalhos">
  <div class="card lg:col-span-2">
    <div class="mb-4 flex items-center justify-between gap-2">
      <h2 class="flex items-center gap-2 text-lg font-bold"><span class="text-brand-blue"><?= icon('shield-check') ?></span><?= $isSuper ? 'Últimos logs de auditoria' : 'Minhas atividades recentes' ?></h2>
      <?php if ($isSuper): ?><a href="<?= e(url('/admin/auditoria')) ?>" class="text-sm font-semibold text-brand-blue hover:underline">Ver todos</a><?php endif ?>
    </div>
    <?php if (!$logs): ?>
      <p class="text-sm text-brand-navy/60">Nenhuma atividade registrada ainda.</p>
    <?php else: ?>
      <ul class="divide-y divide-brand-navy/10">
        <?php foreach ($logs as $log): ?>
          <li class="flex flex-col gap-1 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
              <p class="text-sm font-semibold"><?= e(Audit::label($log['action'])) ?></p>
              <p class="truncate text-xs text-brand-navy/60"><?= e($log['user_name'] ?? $log['user_email'] ?? 'Sistema') ?><?= $log['details'] ? ' · ' . e($log['details']) : '' ?></p>
            </div>
            <time class="shrink-0 text-xs text-brand-navy/50" datetime="<?= e(date('c', strtotime($log['created_at']))) ?>"><?= e(format_date($log['created_at'], true)) ?></time>
          </li>
        <?php endforeach ?>
      </ul>
    <?php endif ?>
  </div>

  <div class="card">
    <h2 class="mb-4 text-lg font-bold">Atalhos</h2>
    <div class="grid gap-2">
      <?php if (Rbac::can($user['role'], 'content:write')): ?>
        <a href="<?= e(url('/admin/posts/novo')) ?>" class="btn-primary">Novo artigo</a>
        <a href="<?= e(url('/admin/paginas/novo')) ?>" class="btn-secondary">Nova página</a>
      <?php endif ?>
      <a href="<?= e(url('/admin/empresa')) ?>" class="btn-secondary">Dados da empresa</a>
      <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener" class="btn-ghost border border-brand-navy/10">Abrir o site</a>
    </div>
    <?php if (!Rbac::can($user['role'], 'content:write')): ?>
      <p class="mt-4 text-xs text-brand-navy/60">Seu perfil é somente leitura. Peça a um administrador para ajustar suas permissões.</p>
    <?php endif ?>
  </div>
</section>
