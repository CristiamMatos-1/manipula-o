<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
  <div>
    <h1 class="text-2xl font-extrabold text-brand-navy sm:text-3xl"><?= e($pageTitle) ?></h1>
    <?php if (!empty($pageDesc)): ?><p class="mt-1 text-sm text-brand-navy/70"><?= e($pageDesc) ?></p><?php endif ?>
  </div>
  <?php if (!empty($pageActions)): ?><div class="flex flex-wrap gap-2"><?= $pageActions ?></div><?php endif ?>
</div>
