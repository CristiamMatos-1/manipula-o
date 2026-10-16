<h1 class="text-xl font-extrabold text-brand-navy sm:text-2xl">Esqueci minha senha</h1>
<p class="mt-1 text-sm text-brand-navy/70">Informe seu e-mail e enviaremos um link seguro para criar uma nova senha.</p>
<form method="post" action="<?= e(url('/admin/esqueci-senha')) ?>" class="mt-6 space-y-4" novalidate>
  <?= csrf_field() ?>
  <?= alert_box($sent ? 'Se o e-mail estiver cadastrado, enviaremos um link de redefinição válido por 30 minutos. Verifique também a caixa de spam.' : null, 'success') ?>
  <?= field('E-mail', 'email', ['type' => 'email', 'value' => $email, 'error' => $errors['email'] ?? null, 'attrs' => 'autocomplete="email" required']) ?>
  <?= submit_button('Enviar link de redefinição', 'btn-primary w-full') ?>
  <p class="text-center text-sm"><a href="<?= e(url('/admin/login')) ?>" class="font-semibold text-brand-blue hover:underline">Voltar ao login</a></p>
</form>
