<?php
use App\Core\Rbac;

$canWrite = Rbac::can($user['role'], 'content:write');
$canPublish = Rbac::can($user['role'], 'content:publish');
$canDelete = Rbac::can($user['role'], 'content:delete');
$isNew = $item === null;
$published = ($item['status'] ?? '') === 'PUBLISHED';
$val = static fn (string $k) => $v[$k] ?? ($item[$k] ?? '');
$action = $isNew ? $c['base'] . '/novo' : $c['base'] . '/' . $item['id'];

$pageTitle = $isNew ? 'Novo ' . $c['singular'] : $item['title'];
$pageDesc = $isNew
    ? 'Use “Salvar rascunho” para continuar depois (não vai ao ar) ou “Publicar” para exibir no site.'
    : 'Status: ' . ($published ? 'Publicado' : 'Rascunho') . (!empty($item['published_at']) ? ' · publicado em ' . format_date($item['published_at']) : '');
$pageActions = ($published) ? '<a href="' . e(url($c['public'] . '/' . $item['slug'])) . '" target="_blank" rel="noopener" class="btn-secondary">' . icon('external-link', 'h-4 w-4') . ' Ver no site</a>' : '';
require __DIR__ . '/_header.php';
?>
<form method="post" action="<?= e(url($action)) ?>" class="space-y-6" novalidate>
  <?= csrf_field() ?>
  <fieldset <?= $canWrite ? '' : 'disabled' ?> class="space-y-6">
    <div class="card space-y-4">
      <?= field('Título', 'title', ['value' => $val('title'), 'error' => $errors['title'] ?? null, 'attrs' => 'required maxlength="150"']) ?>
      <?= field('Endereço (slug)', 'slug', ['value' => $val('slug'), 'error' => $errors['slug'] ?? null, 'attrs' => 'maxlength="80"', 'hint' => 'Deixe em branco para gerar automaticamente a partir do título. Ex.: cuidados-com-a-pele']) ?>
      <?= field('Resumo', 'excerpt', ['type' => 'textarea', 'rows' => 2, 'value' => $val('excerpt'), 'error' => $errors['excerpt'] ?? null, 'attrs' => 'maxlength="300"', 'hint' => 'Aparece nas listagens e ao compartilhar.']) ?>
      <?php if ($c['kind'] === 'post'): ?>
        <?= field('Imagem de capa (URL)', 'cover_url', ['value' => $val('cover_url'), 'error' => $errors['cover_url'] ?? null, 'attrs' => 'placeholder="https://..."']) ?>
      <?php endif ?>
      <?= field('Conteúdo', 'content', ['type' => 'textarea', 'rows' => 14, 'value' => $val('content'), 'error' => $errors['content'] ?? null, 'inputClass' => 'font-mono', 'attrs' => 'required', 'hint' => 'Formatação: ## Subtítulo · - item de lista · **negrito** · [texto](https://link). Separe parágrafos com uma linha em branco.']) ?>
    </div>

    <details class="card" <?= ($val('seo_title') || $val('seo_description') || isset($errors['seo_title']) || isset($errors['seo_description'])) ? 'open' : '' ?>>
      <summary class="cursor-pointer text-base font-bold">Configurações de SEO (Google e redes sociais)</summary>
      <div class="mt-4 space-y-4">
        <?= field('Título SEO', 'seo_title', ['value' => $val('seo_title'), 'error' => $errors['seo_title'] ?? null, 'attrs' => 'maxlength="70"', 'hint' => 'Até 70 caracteres. Se vazio, usa o título.']) ?>
        <?= field('Descrição SEO', 'seo_description', ['type' => 'textarea', 'rows' => 2, 'value' => $val('seo_description'), 'error' => $errors['seo_description'] ?? null, 'attrs' => 'maxlength="160"', 'hint' => 'Até 160 caracteres.']) ?>
        <?= field('Palavras-chave', 'seo_keywords', ['value' => $val('seo_keywords'), 'error' => $errors['seo_keywords'] ?? null, 'attrs' => 'maxlength="200"', 'hint' => 'Separadas por vírgula.']) ?>
        <?= field('Imagem de compartilhamento (URL)', 'og_image_url', ['value' => $val('og_image_url'), 'error' => $errors['og_image_url'] ?? null, 'attrs' => 'placeholder="https://..."']) ?>
      </div>
    </details>

    <?php if ($canWrite): ?>
      <div class="sticky bottom-0 -mx-4 flex flex-col gap-2 border-t border-brand-navy/10 bg-white/95 px-4 py-3 backdrop-blur sm:static sm:mx-0 sm:flex-row sm:border-0 sm:bg-transparent sm:p-0">
        <?php if ($published): ?>
          <?php if ($canPublish): ?><button type="submit" name="intent" value="publish" class="btn-success" data-loading>Salvar alterações (continua publicado)</button><?php endif ?>
          <button type="submit" name="intent" value="draft" class="btn-secondary" data-loading>Despublicar e salvar como rascunho</button>
        <?php else: ?>
          <button type="submit" name="intent" value="draft" class="btn-secondary" data-loading>Salvar rascunho</button>
          <?php if ($canPublish): ?><button type="submit" name="intent" value="publish" class="btn-success" data-loading>Publicar</button><?php endif ?>
        <?php endif ?>
      </div>
    <?php endif ?>
  </fieldset>
  <?php if (!$canWrite): ?><p class="text-sm text-brand-navy/60">Seu perfil é somente leitura: não é possível editar este conteúdo.</p><?php endif ?>
</form>

<?php if (!$isNew && $canDelete): ?>
  <form method="post" action="<?= e(url($c['base'] . '/' . $item['id'] . '/excluir')) ?>" class="mt-8 border-t border-brand-navy/10 pt-6" data-confirm="Excluir &quot;<?= e($item['title']) ?>&quot; definitivamente?">
    <?= csrf_field() ?>
    <h2 class="text-sm font-bold text-red-700">Zona de perigo</h2>
    <p class="mb-3 text-xs text-brand-navy/60">A exclusão é definitiva e fica registrada na auditoria.</p>
    <button type="submit" class="btn-danger">Excluir <?= e($c['singular']) ?></button>
  </form>
<?php endif ?>
