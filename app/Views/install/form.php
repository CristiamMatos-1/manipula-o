<h1 class="text-xl font-extrabold text-brand-navy sm:text-2xl">Instalação do portal Drogacare</h1>
<p class="mt-1 text-sm text-brand-navy/70">Informe os dados do banco MySQL criado no cPanel. O instalador cria as tabelas e o administrador inicial.</p>

<ul class="mt-4 grid gap-1 text-sm sm:grid-cols-2">
  <?php foreach ($checks as $label => $ok): ?>
    <li class="flex items-center gap-2 <?= $ok ? 'text-brand-forest' : 'font-semibold text-red-600' ?>"><?= $ok ? icon('check', 'h-4 w-4') : icon('x', 'h-4 w-4') ?> <?= e($label) ?></li>
  <?php endforeach ?>
</ul>

<form method="post" action="<?= e(url('/install.php')) ?>" class="mt-6 space-y-4" novalidate>
  <?= csrf_field() ?>
  <?= alert_box($error ?? null) ?>
  <fieldset class="space-y-4 rounded-xl border border-brand-navy/10 p-4">
    <legend class="px-2 text-sm font-bold">Banco de dados</legend>
    <div class="grid gap-4 sm:grid-cols-3">
      <?= field('Servidor', 'db_host', ['value' => $v['db_host'], 'error' => $errors['db_host'] ?? null, 'class' => 'sm:col-span-2', 'attrs' => 'required', 'hint' => 'No cPanel, geralmente localhost.']) ?>
      <?= field('Porta', 'db_port', ['value' => $v['db_port'], 'error' => $errors['db_port'] ?? null, 'attrs' => 'required']) ?>
      <?= field('Nome do banco', 'db_name', ['value' => $v['db_name'], 'error' => $errors['db_name'] ?? null, 'attrs' => 'required', 'hint' => 'Com prefixo do cPanel, ex.: usuario_drogacare']) ?>
      <?= field('Usuário', 'db_user', ['value' => $v['db_user'], 'error' => $errors['db_user'] ?? null, 'attrs' => 'required autocomplete="off"']) ?>
      <?= field('Senha', 'db_pass', ['type' => 'password', 'attrs' => 'autocomplete="new-password"']) ?>
    </div>
  </fieldset>
  <fieldset class="space-y-4 rounded-xl border border-brand-navy/10 p-4">
    <legend class="px-2 text-sm font-bold">Site e administrador</legend>
    <?= field('URL pública do site', 'app_url', ['value' => $v['app_url'], 'error' => $errors['app_url'] ?? null, 'attrs' => 'required', 'hint' => 'Com https:// e sem barra no final.']) ?>
    <?= field('E-mail do administrador inicial', 'admin_email', ['type' => 'email', 'value' => $v['admin_email'], 'error' => $errors['admin_email'] ?? null, 'attrs' => 'required', 'hint' => 'Senha inicial: admin (será obrigatório trocar no primeiro login).']) ?>
  </fieldset>
  <fieldset class="space-y-4 rounded-xl border border-brand-navy/10 p-4">
    <legend class="px-2 text-sm font-bold">E-mail (opcional agora)</legend>
    <?= field('SMTP_URL', 'smtp_url', ['value' => $v['smtp_url'], 'attrs' => 'placeholder="smtps://contato%40seudominio.com.br:SENHA@mail.seudominio.com.br:465" autocomplete="off"', 'hint' => 'Necessário para a recuperação de senha enviar e-mails. Vazio = envio simulado (storage/logs/mail.log).']) ?>
    <?= field('Remetente', 'mail_from', ['value' => $v['mail_from']]) ?>
  </fieldset>
  <?= submit_button('Instalar', 'btn-primary w-full') ?>
</form>
