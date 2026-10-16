<?php
use App\Core\Rbac;
use App\Models\Settings;

$canEdit = Rbac::can($user['role'], 'company:edit');
$pageTitle = 'Página inicial';
$pageDesc = 'Edite todos os textos, o banner e as seções da página inicial. Nos textos você pode usar {empresa}, {slogan}, {ano} e {anos}, que são trocados pelos dados da empresa.';
$pageActions = '<a href="' . e(url('/')) . '" target="_blank" rel="noopener" class="btn-secondary">' . icon('external-link', 'h-4 w-4') . ' Ver site</a>';
require __DIR__ . '/_header.php';

$byGroup = [];
foreach (Settings::DEFS as $key => $def) {
    $byGroup[$def['group']][$key] = $def;
}
?>
<form method="post" action="<?= e(url('/admin/pagina-inicial')) ?>" class="space-y-6" enctype="multipart/form-data" novalidate>
  <?= csrf_field() ?>
  <fieldset <?= $canEdit ? '' : 'disabled' ?> class="space-y-6">
    <?php foreach (Settings::GROUPS as $group => [$gTitle, $gDesc]): ?>
      <section class="card space-y-4" id="grupo-<?= e($group) ?>">
        <div>
          <h2 class="text-lg font-bold"><?= e($gTitle) ?></h2>
          <p class="text-sm text-brand-navy/70"><?= e($gDesc) ?></p>
          <?php if ($group === 'banner'): ?>
            <p class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-bold <?= Settings::hasBanner() ? 'bg-brand-green/30 text-brand-forest' : 'bg-brand-navy/10 text-brand-navy/70' ?>">
              <?= Settings::hasBanner() ? 'Banner visível no site' : 'Banner oculto (nada preenchido)' ?>
            </p>
          <?php endif ?>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
          <?php foreach ($byGroup[$group] as $key => $def): ?>
            <?php if ($def['type'] === 'image'): ?>
              <div class="sm:col-span-2"><?= image_field($def['label'], $key, $v[$key] ?? null, $errors[$key] ?? null, 'Recomendado: imagem larga (ex.: 1600x600). JPG, PNG, WebP ou GIF. Para ocultar o banner, remova a imagem e esvazie os textos.') ?></div>
            <?php else: ?>
              <?php $wide = $def['type'] === 'textarea' || in_array($key, ['about_title', 'contact_title', 'home_seo_title'], true); ?>
              <?= field($def['label'], $key, [
                  'type' => $def['type'] === 'textarea' ? 'textarea' : ($def['type'] === 'url' ? 'text' : 'text'),
                  'rows' => 3,
                  'value' => $v[$key] ?? '',
                  'error' => $errors[$key] ?? null,
                  'hint' => $def['hint'] ?? null,
                  'class' => $wide ? 'sm:col-span-2' : '',
                  'attrs' => 'maxlength="' . (int) ($def['max'] ?? 255) . '"',
              ]) ?>
            <?php endif ?>
          <?php endforeach ?>
        </div>
      </section>
    <?php endforeach ?>
    <?php if ($canEdit): ?>
      <div class="flex flex-col gap-2 sm:flex-row">
        <?= submit_button('Salvar página inicial', 'btn-success') ?>
        <button type="submit" name="reset" value="1" class="btn-secondary" formnovalidate data-confirm-click="Restaurar todos os textos ao padrão? O banner não será alterado.">Restaurar textos padrão</button>
      </div>
    <?php endif ?>
  </fieldset>
  <?php if (!$canEdit): ?><p class="text-sm text-brand-navy/60">Seu perfil é somente leitura.</p><?php endif ?>
</form>
