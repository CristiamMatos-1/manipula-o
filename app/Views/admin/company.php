<?php
use App\Core\Rbac;

$canEdit = Rbac::can($user['role'], 'company:edit');
$pageTitle = 'Dados da empresa';
$pageDesc = 'Informações globais exibidas no site: nome e logo, dados legais, farmacêutico responsável, contato, vídeo e redes sociais. Textos e banner da página inicial ficam em "Página inicial".';
require __DIR__ . '/_header.php';

$f = static fn (string $name, string $label, array $o = []) => field($label, $name, ['value' => $v[$name] ?? '', 'error' => $errors[$name] ?? null] + $o);
?>
<form method="post" action="<?= e(url('/admin/empresa')) ?>" class="space-y-6" enctype="multipart/form-data" novalidate>
  <?= csrf_field() ?>
  <fieldset <?= $canEdit ? '' : 'disabled' ?> class="space-y-6">
    <section class="card space-y-4">
      <h2 class="text-lg font-bold">Identidade</h2>
      <div class="grid gap-4 sm:grid-cols-2">
        <?= $f('name', 'Nome da empresa', ['attrs' => 'required']) ?>
        <?= $f('tagline', 'Slogan') ?>
        <?= $f('founded_year', 'Ano de fundação', ['type' => 'number', 'attrs' => 'inputmode="numeric" required', 'hint' => 'Usado para calcular o tempo de mercado automaticamente.']) ?>
      </div>
      <?= image_field('Logo da empresa (imagem)', 'logo_url', $v['logo_url'] ?? null, $errors['logo_url'] ?? null, 'JPG, PNG, WebP ou GIF. Fundo transparente (PNG) fica melhor. Aparece no topo, rodapé e painel.') ?>
      <fieldset>
        <legend class="label">O que exibir na marca (topo, rodapé e painel)</legend>
        <?php $mode = $v['logo_display'] ?? 'both'; ?>
        <div class="mt-2 grid gap-2 sm:grid-cols-3">
          <?php foreach (['both' => 'Imagem e nome', 'image' => 'Somente a imagem', 'name' => 'Somente o nome'] as $val => $label): ?>
            <label class="flex min-h-[44px] cursor-pointer items-center gap-3 rounded-xl border border-brand-navy/15 bg-white px-4 py-2 text-sm font-semibold has-[:checked]:border-brand-blue has-[:checked]:bg-brand-sky/10">
              <input type="radio" name="logo_display" value="<?= e($val) ?>" class="h-4 w-4" <?= $mode === $val ? 'checked' : '' ?>> <?= e($label) ?>
            </label>
          <?php endforeach ?>
        </div>
        <?php if (!empty($errors['logo_display'])): ?><p role="alert" class="mt-1 text-xs font-medium text-red-600"><?= e($errors['logo_display']) ?></p><?php endif ?>
      </fieldset>
    </section>
    <section class="card space-y-4">
      <h2 class="text-lg font-bold">Dados legais (aparecem no rodapé)</h2>
      <div class="grid gap-4 sm:grid-cols-2">
        <?= $f('legal_name', 'Razão social', ['class' => 'sm:col-span-2', 'attrs' => 'maxlength="150" placeholder="Ex.: Drogacare Farmácia de Manipulação Ltda."']) ?>
        <?= $f('cnpj', 'CNPJ', ['attrs' => 'inputmode="numeric" maxlength="18" placeholder="00.000.000/0000-00"', 'hint' => 'Pode digitar só os números; o sistema formata.']) ?>
        <?= $f('legal_extra', 'Outras informações legais', ['type' => 'textarea', 'rows' => 3, 'class' => 'sm:col-span-2', 'attrs' => 'maxlength="300"', 'hint' => 'Ex.: Inscrição estadual, AFE/ANVISA, licença sanitária, CRF da empresa. Uma informação por linha.']) ?>
      </div>
    </section>
    <section class="card space-y-4">
      <h2 class="text-lg font-bold">Farmacêutico(a) Responsável</h2>
      <div class="grid gap-4 sm:grid-cols-2">
        <?= $f('pharmacist_name', 'Nome') ?>
        <?= $f('pharmacist_crf', 'CRF', ['attrs' => 'placeholder="CRF-SP 12345"']) ?>
      </div>
      <?= image_field('Foto', 'pharmacist_photo_url', $v['pharmacist_photo_url'] ?? null, $errors['pharmacist_photo_url'] ?? null, 'Se não houver foto, aparece uma imagem padrão.') ?>
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
