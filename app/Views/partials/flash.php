<?php foreach (\App\Core\Session::pullFlash() as $f): ?>
  <p role="<?= $f['type'] === 'error' ? 'alert' : 'status' ?>" class="mb-4 rounded-lg border px-3 py-2 text-sm font-medium <?= $f['type'] === 'error' ? 'border-red-200 bg-red-50 text-red-700' : 'border-brand-green bg-brand-mint/50 text-brand-forest' ?>"><?= e($f['message']) ?></p>
<?php endforeach ?>
