<?php
use App\Core\Audit;

$pageTitle = 'Auditoria';
$pageDesc = 'Trilha de rastreabilidade (LGPD): quem fez o quê, e quando. Registros são somente leitura.';
require __DIR__ . '/_header.php';
$href = static fn (int $p) => url('/admin/auditoria') . '?page=' . $p . ($q !== '' ? '&q=' . rawurlencode($q) : '');
?>
<form method="get" action="<?= e(url('/admin/auditoria')) ?>" class="mb-4 flex flex-col gap-2 sm:max-w-md sm:flex-row" role="search">
  <input name="q" value="<?= e($q) ?>" placeholder="Buscar por e-mail, ação, entidade..." class="input" aria-label="Buscar logs">
  <button class="btn-primary" type="submit">Buscar</button>
</form>
<div class="card !p-0 overflow-hidden">
  <ul class="divide-y divide-brand-navy/10 md:hidden">
    <?php foreach ($logs as $l): ?>
      <li class="space-y-1 p-4">
        <p class="text-sm font-semibold"><?= e(Audit::label($l['action'])) ?></p>
        <p class="text-xs text-brand-navy/60"><?= e($l['user_name'] ?? $l['user_email'] ?? 'Sistema') ?> · <?= e(format_date($l['created_at'], true)) ?></p>
        <?php if ($l['details']): ?><p class="break-words text-xs"><?= e($l['details']) ?></p><?php endif ?>
      </li>
    <?php endforeach ?>
  </ul>
  <div class="hidden overflow-x-auto md:block">
    <table class="w-full text-left text-sm">
      <thead class="bg-brand-mint/30 text-xs uppercase tracking-wide text-brand-navy/70">
        <tr><th class="px-4 py-3">Quando</th><th class="px-4 py-3">Quem</th><th class="px-4 py-3">Ação</th><th class="px-4 py-3">Alvo</th><th class="px-4 py-3">Detalhes</th><th class="px-4 py-3">IP</th></tr>
      </thead>
      <tbody class="divide-y divide-brand-navy/10">
        <?php foreach ($logs as $l): ?>
          <tr class="align-top">
            <td class="whitespace-nowrap px-4 py-3"><?= e(format_date($l['created_at'], true)) ?></td>
            <td class="px-4 py-3"><p class="font-semibold"><?= e($l['user_name'] ?? '-') ?></p><p class="text-xs text-brand-navy/50"><?= e($l['user_email'] ?? '') ?></p></td>
            <td class="px-4 py-3"><?= e(Audit::label($l['action'])) ?><p class="text-[11px] text-brand-navy/40"><?= e($l['action']) ?></p></td>
            <td class="px-4 py-3"><?= e($l['entity']) ?></td>
            <td class="max-w-xs break-words px-4 py-3 text-xs"><?= e($l['details'] ?? '-') ?></td>
            <td class="px-4 py-3 text-xs"><?= e($l['ip'] ?? '-') ?></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
  <?php if (!$logs): ?><p class="p-6 text-sm text-brand-navy/60">Nenhum registro encontrado.</p><?php endif ?>
</div>
<nav class="mt-4 flex items-center justify-between text-sm" aria-label="Paginação">
  <?php if ($page > 1): ?><a href="<?= e($href($page - 1)) ?>" class="btn-secondary">Anterior</a><?php else: ?><span></span><?php endif ?>
  <span class="text-brand-navy/60">Página <?= $page ?> de <?= $pages ?> · <?= $total ?> registros</span>
  <?php if ($page < $pages): ?><a href="<?= e($href($page + 1)) ?>" class="btn-secondary">Próxima</a><?php else: ?><span></span><?php endif ?>
</nav>
