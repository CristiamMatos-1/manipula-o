<h1 class="text-xl font-extrabold text-brand-navy sm:text-2xl">Instalação concluída</h1>
<ul class="mt-4 space-y-1 text-sm text-brand-forest">
  <?php foreach ($log as $line): ?><li class="flex gap-2"><?= icon('check', 'h-4 w-4 shrink-0 mt-0.5') ?> <?= e($line) ?></li><?php endforeach ?>
</ul>
<div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
  <p class="font-bold">Próximos passos</p>
  <ol class="mt-2 list-decimal space-y-1 pl-5">
    <li>Acesse o painel com <strong><?= e($email) ?></strong> e senha <strong>admin</strong>. A troca da senha é obrigatória.</li>
    <li>O instalador agora está bloqueado (arquivo <code>storage/installed.lock</code>). Para reinstalar, apague esse arquivo e <code>config/config.php</code>.</li>
    <li>Preencha os <em>Dados da empresa</em> no painel.</li>
  </ol>
</div>
<a href="<?= e(url('/admin/login')) ?>" class="btn-primary mt-6 w-full">Ir para o login</a>
