<h1 class="text-xl font-extrabold text-brand-navy sm:text-2xl"><?= $forced ? 'Defina uma nova senha' : 'Alterar senha' ?></h1>
<?php if ($forced): ?><p class="mt-1 text-sm text-brand-navy/70">Por segurança, você precisa trocar a senha provisória antes de continuar.</p><?php endif ?>
<form method="post" action="<?= e(url('/admin/alterar-senha')) ?>" class="mt-6 space-y-4" novalidate>
  <?= csrf_field() ?>
  <?= field('Senha atual', 'current', ['type' => 'password', 'error' => $errors['current'] ?? null, 'attrs' => 'autocomplete="current-password" required']) ?>
  <?= field('Nova senha', 'password', ['type' => 'password', 'error' => $errors['password'] ?? null, 'hint' => 'Mínimo de 10 caracteres, com maiúscula, minúscula e número.', 'attrs' => 'autocomplete="new-password" required']) ?>
  <?= field('Confirmar nova senha', 'confirm', ['type' => 'password', 'error' => $errors['confirm'] ?? null, 'attrs' => 'autocomplete="new-password" required']) ?>
  <?= submit_button('Salvar nova senha', 'btn-primary w-full') ?>
</form>
<?php if ($forced): ?>
  <form method="post" action="<?= e(url('/admin/logout')) ?>" class="mt-4 text-center"><?= csrf_field() ?><button type="submit" class="text-sm font-semibold text-brand-blue hover:underline">Sair</button></form>
<?php else: ?>
  <p class="mt-4 text-center text-sm"><a href="<?= e(url('/admin')) ?>" class="font-semibold text-brand-blue hover:underline">Voltar ao painel</a></p>
<?php endif ?>
