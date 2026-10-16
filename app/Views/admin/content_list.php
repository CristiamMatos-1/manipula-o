<?php
use App\Core\Rbac;

$canWrite = Rbac::can($user['role'], 'content:write');
$pageTitle = $c['plural'];
$pageDesc = $c['kind'] === 'post' ? 'Artigos do blog exibidos em /blog.' : 'Páginas institucionais exibidas em /p/slug.';
$pageActions = $canWrite ? '<a href="' . e(url($c['base'] . '/novo')) . '" class="btn-primary">' . icon('plus', 'h-4 w-4') . ' Novo ' . e($c['singular']) . '</a>' : '';
require __DIR__ . '/_header.php';

$badge = static fn (string $s) => $s === 'PUBLISHED'
    ? '<span class="badge bg-brand-green/30 text-brand-forest">Publicado</span>'
    : '<span class="badge bg-amber-100 text-amber-800">Rascunho</span>';
?>
<div class="card !p-0 overflow-hidden">
  <?php if (!$items): ?>
    <p class="p-6 text-sm text-brand-navy/60">Nenhum item cadastrado.</p>
  <?php else: ?>
    <ul class="divide-y divide-brand-navy/10 md:hidden">
      <?php foreach ($items as $it): ?>
        <li class="space-y-2 p-4">
          <div class="flex items-start justify-between gap-2">
            <a href="<?= e(url($c['base'] . '/' . $it['id'])) ?>" class="font-bold text-brand-blue"><?= e($it['title']) ?></a>
            <?= $badge($it['status']) ?>
          </div>
          <p class="text-xs text-brand-navy/60">/<?= e($it['slug']) ?> · <?= e($it['author_name'] ?? '-') ?> · <?= e(format_date($it['updated_at'], true)) ?></p>
        </li>
      <?php endforeach ?>
    </ul>
    <div class="hidden overflow-x-auto md:block">
      <table class="w-full text-left text-sm">
        <thead class="bg-brand-mint/30 text-xs uppercase tracking-wide text-brand-navy/70">
          <tr><th class="px-4 py-3">Título</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Autor</th><th class="px-4 py-3">Atualizado</th><th class="px-4 py-3 text-right">Ações</th></tr>
        </thead>
        <tbody class="divide-y divide-brand-navy/10">
          <?php foreach ($items as $it): ?>
            <tr class="hover:bg-slate-50">
              <td class="px-4 py-3"><p class="font-semibold"><?= e($it['title']) ?></p><p class="text-xs text-brand-navy/50">/<?= e($it['slug']) ?></p></td>
              <td class="px-4 py-3"><?= $badge($it['status']) ?></td>
              <td class="px-4 py-3"><?= e($it['author_name'] ?? '-') ?></td>
              <td class="whitespace-nowrap px-4 py-3"><?= e(format_date($it['updated_at'], true)) ?></td>
              <td class="px-4 py-3">
                <div class="flex justify-end gap-2">
                  <?php if ($it['status'] === 'PUBLISHED'): ?>
                    <a href="<?= e(url($c['public'] . '/' . $it['slug'])) ?>" target="_blank" rel="noopener" class="btn-ghost !min-h-[36px] !px-3" aria-label="Ver <?= e($it['title']) ?> no site"><?= icon('external-link', 'h-4 w-4') ?></a>
                  <?php endif ?>
                  <a href="<?= e(url($c['base'] . '/' . $it['id'])) ?>" class="btn-secondary !min-h-[36px] !px-3"><?= icon('pencil', 'h-4 w-4') ?> <?= $canWrite ? 'Editar' : 'Ver' ?></a>
                </div>
              </td>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  <?php endif ?>
</div>
