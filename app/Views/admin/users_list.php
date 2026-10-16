<?php
use App\Core\Rbac;

$pageTitle = 'Usuários';
$pageDesc = 'Crie, edite e remova usuários e defina suas permissões.';
$pageActions = '<a href="' . e(url('/admin/usuarios/novo')) . '" class="btn-primary">' . icon('plus', 'h-4 w-4') . ' Novo usuário</a>';
require __DIR__ . '/_header.php';
?>
<div class="card !p-0 overflow-hidden">
  <ul class="divide-y divide-brand-navy/10 md:hidden">
    <?php foreach ($users as $u): ?>
      <li class="space-y-1 p-4">
        <a href="<?= e(url('/admin/usuarios/' . $u['id'])) ?>" class="font-bold text-brand-blue"><?= e($u['name']) ?></a>
        <p class="text-xs text-brand-navy/60"><?= e($u['email']) ?></p>
        <p class="text-xs"><span class="badge bg-brand-blue/10 text-brand-blue"><?= e(Rbac::label($u['role'])) ?></span> <?php if (!(int) $u['active']): ?><span class="badge bg-red-100 text-red-700">Inativo</span><?php endif ?></p>
      </li>
    <?php endforeach ?>
  </ul>
  <div class="hidden overflow-x-auto md:block">
    <table class="w-full text-left text-sm">
      <thead class="bg-brand-mint/30 text-xs uppercase tracking-wide text-brand-navy/70">
        <tr><th class="px-4 py-3">Nome</th><th class="px-4 py-3">E-mail</th><th class="px-4 py-3">Perfil</th><th class="px-4 py-3">Situação</th><th class="px-4 py-3">Último acesso</th><th class="px-4 py-3 text-right">Ações</th></tr>
      </thead>
      <tbody class="divide-y divide-brand-navy/10">
        <?php foreach ($users as $u): ?>
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-3 font-semibold"><?= e($u['name']) ?><?php if ((int) $u['id'] === (int) $user['id']): ?><span class="ml-2 text-xs text-brand-navy/50">(você)</span><?php endif ?></td>
            <td class="px-4 py-3"><?= e($u['email']) ?></td>
            <td class="px-4 py-3"><span class="badge bg-brand-blue/10 text-brand-blue"><?= e(Rbac::label($u['role'])) ?></span></td>
            <td class="px-4 py-3">
              <?= (int) $u['active'] ? '<span class="badge bg-brand-green/30 text-brand-forest">Ativo</span>' : '<span class="badge bg-red-100 text-red-700">Inativo</span>' ?>
              <?php if ((int) $u['must_change_password']): ?><span class="badge ml-1 bg-amber-100 text-amber-800">Troca de senha pendente</span><?php endif ?>
            </td>
            <td class="whitespace-nowrap px-4 py-3"><?= e(format_date($u['last_login_at'], true)) ?></td>
            <td class="px-4 py-3 text-right"><a href="<?= e(url('/admin/usuarios/' . $u['id'])) ?>" class="btn-secondary !min-h-[36px] !px-3">Editar</a></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
