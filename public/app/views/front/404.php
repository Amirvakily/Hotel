<section class="error-page">
  <div class="container">
    <span class="error-mark" aria-hidden="true"><svg viewBox="0 0 40 40"><rect x="10" y="10" width="20" height="20" rx="3"/><rect x="10" y="10" width="20" height="20" rx="3" transform="rotate(45 20 20)"/><circle cx="20" cy="20" r="4.2" class="dot"/></svg></span>
    <p class="kicker"><?= e(digits('404')) ?></p>
    <h1 class="page-title"><?= e(__('404.title')) ?></h1>
    <p class="page-sub"><?= e(__('404.text')) ?></p>
    <div class="hero-actions center">
      <a class="btn btn-accent" href="<?= e(url('')) ?>"><?= e(__('404.home')) ?></a>
      <?php if (Content::navEnabled('rooms')): ?><a class="btn btn-outline" href="<?= e(url('rooms')) ?>"><?= e(__('nav.rooms')) ?></a><?php endif; ?>
    </div>
  </div>
</section>
