<?php
use App\Core\Rbac;

$canEdit = Rbac::can($user['role'], 'company:edit');
$pageTitle = 'Dados da empresa';
$pageDesc = 'Informações globais exibidas no site: fundação, farmacêutico responsável, contato, vídeo e redes sociais.';
require __DIR__ . '/_header.php';

$f = static fn (string $name, string $label, array $o = []) => field($label, $name, ['value' => $v[$name] ?? '', 'error' => $errors[$name] ?? null] + $o);
?>
<form method="post" action="<?= e(url('/admin/empresa')) ?>" class="space-y-6" novalidate>
  <?= csrf_field() ?>
  <fieldset <?= $canEdit ? '' : 'disabled' ?> class="space-y-6">
    <section class="card space-y-4">
      <h2 class="text-lg font-bold">Identidade</h2>
      <div class="grid gap-4 sm:grid-cols-2">
        <?= $f('name', 'Nome da empresa', ['attrs' => 'required']) ?>
        <?= $f('tagline', 'Slogan') ?>
        <?= $f('founded_year', 'Ano de fundação', ['type' => 'number', 'attrs' => 'inputmode="numeric" required', 'hint' => 'Usado para calcular o tempo de mercado automaticamente.']) ?>
      </div>
    </section>
    <section class="card space-y-4">
      <h2 class="text-lg font-bold">Farmacêutico(a) Responsável</h2>
      <div class="grid gap-4 sm:grid-cols-2">
        <?= $f('pharmacist_name', 'Nome') ?>
        <?= $f('pharmacist_crf', 'CRF', ['attrs' => 'placeholder="CRF-SP 12345"']) ?>
        <?= $f('pharmacist_photo_url', 'URL da foto', ['class' => 'sm:col-span-2', 'attrs' => 'placeholder="https://... ou /foto.jpg"', 'hint' => 'Dica: coloque o arquivo na pasta public/ e use /nome-do-arquivo.jpg']) ?>
      </div>
    </section>
    <section class="card space-y-4">
      <h2 class="text-lg font-bold">Contato e localização</h2>
      <div class="grid gap-4 sm:grid-cols-2">
        <?= $f('phone', 'Telefone') ?>
        <?= $f('whatsapp', 'WhatsApp (somente números)', ['attrs' => 'placeholder="5511999999999" inputmode="numeric"', 'hint' => 'DDI + DDD + número. Se vazio, o botão flutuante não aparece.']) ?>
        <?= $f('email', 'E-mail de contato', ['type' => 'email']) ?>
        <?= $f('address', 'Endereço') ?>
        <?= $f('maps_embed_url', 'Link de incorporação do Google Maps', ['class' => 'sm:col-span-2', 'attrs' => 'placeholder="https://www.google.com/maps/embed?pb=..."', 'hint' => 'Google Maps > Compartilhar > Incorporar um mapa > copie apenas o endereço do src. Se vazio, o mapa é gerado a partir do endereço.']) ?>
      </div>
    </section>
    <section class="card space-y-4">
      <h2 class="text-lg font-bold">Vídeo e redes sociais</h2>
      <div class="grid gap-4 sm:grid-cols-2">
        <?= $f('hero_video_url', 'Vídeo institucional (Hero)', ['class' => 'sm:col-span-2', 'attrs' => 'placeholder="https://www.youtube.com/watch?v=... ou https://.../video.mp4"', 'hint' => 'Link do YouTube ou arquivo .mp4/.webm.']) ?>
        <?= $f('youtube_url', 'Canal do YouTube', ['type' => 'url']) ?>
        <?= $f('instagram_url', 'Instagram', ['type' => 'url']) ?>
        <?= $f('facebook_url', 'Facebook', ['type' => 'url']) ?>
      </div>
    </section>
    <?php if ($canEdit): ?><?= submit_button('Salvar dados da empresa', 'btn-success') ?><?php endif ?>
  </fieldset>
  <?php if (!$canEdit): ?><p class="text-sm text-brand-navy/60">Seu perfil é somente leitura.</p><?php endif ?>
</form>
