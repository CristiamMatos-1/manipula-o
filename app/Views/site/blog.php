<main class="container py-10 sm:py-14">
  <h1 class="section-title">Artigos</h1>
  <p class="mt-2 text-brand-navy/70">Informação de qualidade para cuidar da sua saúde.</p>
  <?php if (!$posts): ?>
    <p class="mt-10 text-brand-navy/60">Em breve, novos artigos.</p>
  <?php else: ?>
    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <?php foreach ($posts as $post) { require APP_PATH . '/Views/partials/post_card.php'; } ?>
    </div>
  <?php endif ?>
</main>
