<?php
use App\Core\Rbac;

$isNew = $target === null;
$isSelf = !$isNew && (int) $target['id'] === (int) $user['id'];
$val = static fn (string $k, $d = '') => $v[$k] ?? ($target[$k] ?? $d);
$role = $val('role', Rbac::VIEWER);
$active = array_key_exists('active', $v) ? (bool) $v['active'] : (bool) ($target['active'] ?? true);
$mustChange = array_key_exists('must_change_password', $v) ? (bool) $v['must_change_password'] : (bool) ($target['must_change_password'] ?? false);
$help = [
    Rbac::SUPERADMIN => 'Acesso total: usuários, conteúdo, empresa e auditoria.',
    Rbac::EDITOR => 'Cria, edita, publica e exclui artigos e páginas; edita dados da empresa. Não gerencia usuários.',
    Rbac::VIEWER => 'Somente leitura: consulta o painel sem alterar nada.',
];
$pageTitle = $isNew ? 'Novo usuário' : $target['name'];
$pageDesc = $isNew ? null : $target['email'];
require __DIR__ . '/_header.php';
?>
<form method="post" action="<?= e(url($isNew ? '/admin/usuarios/novo' : '/admin/usuarios/' . $target['id'])) ?>" class="card max-w-2xl space-y-4" novalidate>
  <?= csrf_field() ?>
  <?= alert_box($error) ?>
  <?= field('Nome completo', 'name', ['value' => $val('name'), 'error' => $errors['name'] ?? null, 'attrs' => 'required']) ?>
  <?= field('E-mail (login)', 'email', ['type' => 'email', 'value' => $val('email'), 'error' => $errors['email'] ?? null, 'attrs' => 'required']) ?>
  <div>
    <label for="role" class="label">Perfil de acesso</label>
    <select id="role" name="role" class="input">
      <?php foreach (Rbac::ROLES as $r): ?><option value="<?= e($r) ?>" <?= $role === $r ? 'selected' : '' ?>><?= e(Rbac::label($r)) ?></option><?php endforeach ?>
    </select>
    <?php if (!empty($errors['role'])): ?><p class="mt-1 text-xs font-medium text-red-600"><?= e($errors['role']) ?></p><?php endif ?>
    <ul class="mt-2 space-y-1 text-xs text-brand-navy/60">
      <?php foreach (Rbac::ROLES as $r): ?><li><strong><?= e(Rbac::label($r)) ?>:</strong> <?= e($help[$r]) ?></li><?php endforeach ?>
    </ul>
  </div>
  <?= field($isNew ? 'Senha provisória' : 'Nova senha (opcional)', 'password', [
      'type' => 'password', 'error' => $errors['password'] ?? null,
      'attrs' => 'autocomplete="new-password" ' . ($isNew ? 'required' : ''),
      'hint' => $isNew ? 'Mínimo de 10 caracteres, com maiúscula, minúscula e número. O usuário deverá trocá-la no 1º login.' : 'Deixe em branco para manter a atual. Ao redefinir, o usuário será obrigado a trocá-la no próximo login.',
  ]) ?>
  <?php if (!$isNew): ?>
    <div class="space-y-2">
      <label class="flex min-h-[44px] items-center gap-3 text-sm font-semibold">
        <input type="checkbox" name="active" <?= $active ? 'checked' : '' ?> <?= $isSelf ? 'disabled' : '' ?> class="h-5 w-5 accent-brand-blue">
        <?php if ($isSelf): ?><input type="hidden" name="active" value="on"><?php endif ?>
        Usuário ativo (pode acessar o painel)
      </label>
      <label class="flex min-h-[44px] items-center gap-3 text-sm font-semibold">
        <input type="checkbox" name="must_change_password" <?= $mustChange ? 'checked' : '' ?> class="h-5 w-5 accent-brand-blue">
        Exigir troca de senha no próximo login
      </label>
    </div>
  <?php endif ?>
  <div class="flex flex-col gap-2 sm:flex-row"><?= submit_button($isNew ? 'Criar usuário' : 'Salvar alterações') ?></div>
</form>

<?php if (!$isNew && !$isSelf): ?>
  <form method="post" action="<?= e(url('/admin/usuarios/' . $target['id'] . '/excluir')) ?>" class="mt-8 max-w-2xl border-t border-brand-navy/10 pt-6" data-confirm="Excluir o usuário <?= e($target['email']) ?>?">
    <?= csrf_field() ?>
    <h2 class="text-sm font-bold text-red-700">Zona de perigo</h2>
    <p class="mb-3 text-xs text-brand-navy/60">O usuário perde o acesso imediatamente. O histórico de auditoria é preservado.</p>
    <button type="submit" class="btn-danger">Excluir usuário</button>
  </form>
<?php endif ?>
