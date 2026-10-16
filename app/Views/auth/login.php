<h1 class="text-xl font-extrabold text-brand-navy sm:text-2xl">Acessar o painel</h1>
<p class="mt-1 text-sm text-brand-navy/70">Entre com seu e-mail e senha.</p>
<form method="post" action="<?= e(url('/admin/login')) ?>" class="mt-6 space-y-4" novalidate>
  <?= csrf_field() ?>
  <?= alert_box(!empty($reset) ? 'Senha redefinida com sucesso. Faça login com a nova senha.' : null, 'success') ?>
  <?= alert_box($error) ?>
  <?= field('E-mail', 'email', ['type' => 'email', 'value' => $email, 'error' => $errors['email'] ?? null, 'attrs' => 'autocomplete="username" required']) ?>
  <?= field('Senha', 'password', ['type' => 'password', 'error' => $errors['password'] ?? null, 'attrs' => 'autocomplete="current-password" required']) ?>
  <?= submit_button('Entrar', 'btn-primary w-full') ?>
  <p class="text-center text-sm"><a href="<?= e(url('/admin/esqueci-senha')) ?>" class="font-semibold text-brand-blue hover:underline">Esqueci minha senha</a></p>
</form>
