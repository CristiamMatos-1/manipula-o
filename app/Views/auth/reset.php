<h1 class="text-xl font-extrabold text-brand-navy sm:text-2xl">Criar nova senha</h1>
<p class="mt-1 text-sm text-brand-navy/70">Mínimo de 10 caracteres, com maiúscula, minúscula e número.</p>
<form method="post" action="<?= e(url('/admin/redefinir-senha')) ?>" class="mt-6 space-y-4" novalidate>
  <?= csrf_field() ?>
  <input type="hidden" name="token" value="<?= e($token) ?>">
  <?= field('Nova senha', 'password', ['type' => 'password', 'error' => $errors['password'] ?? null, 'attrs' => 'autocomplete="new-password" required']) ?>
  <?= field('Confirmar nova senha', 'confirm', ['type' => 'password', 'error' => $errors['confirm'] ?? null, 'attrs' => 'autocomplete="new-password" required']) ?>
  <?= submit_button('Redefinir senha', 'btn-primary w-full') ?>
</form>
