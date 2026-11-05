<?php
/** @var array $tour @var array $scenes */
$embeds = array_values(array_filter($scenes, fn($s) => $s['type'] === 'embed' && $s['embed_url']));
$hasPano = !empty($tour['scenes']);
?>
<section class="tour-page">
  <div class="container tour-head">
    <nav class="crumbs" aria-label="breadcrumb"><a href="<?= e(url('')) ?>"><?= e(__('nav.home')) ?></a><span aria-hidden="true">/</span><span><?= e(__('tour.title')) ?></span></nav>
    <h1 class="page-title"><?= e(__('tour.title')) ?></h1>
    <p class="page-sub"><?= e(__('tour.subtitle')) ?></p>
  </div>
  <?php if ($hasPano): ?>
  <div class="tour-stage" data-tour data-config="<?= e(json_enc($tour)) ?>">
    <div class="tour-viewer" data-pano-view id="tour-viewer"></div>
    <div class="tour-loading" data-tour-loading><span class="spinner"></span><?= e(__('tour.loading')) ?></div>
    <div class="tour-ui">
      <p class="tour-title" data-tour-title></p>
      <div class="tour-controls">
        <button type="button" class="tour-btn" data-tour-zoom="in" aria-label="<?= e(__('tour.zoom_in')) ?>"><?= icon('zoom-in') ?></button>
        <button type="button" class="tour-btn" data-tour-zoom="out" aria-label="<?= e(__('tour.zoom_out')) ?>"><?= icon('zoom-out') ?></button>
        <button type="button" class="tour-btn" data-tour-rotate aria-pressed="false" aria-label="<?= e(__('tour.auto_rotate')) ?>"><?= icon('rotate-ccw') ?></button>
        <button type="button" class="tour-btn" data-tour-gyro hidden aria-pressed="false" aria-label="<?= e(__('tour.gyro')) ?>"><?= icon('smartphone') ?></button>
        <button type="button" class="tour-btn" data-tour-fs aria-label="<?= e(__('tour.fullscreen')) ?>"><?= icon('maximize') ?></button>
      </div>
      <p class="tour-hint" data-tour-hint><?= icon('move-3d') ?><?= e(__('tour.hint')) ?></p>
    </div>
  </div>
  <div class="container">
    <div class="scene-rail" data-scene-rail role="tablist" aria-label="<?= e(__('tour.scenes')) ?>">
      <?php foreach ($tour['scenes'] as $slug => $sc): ?>
        <button type="button" class="scene-thumb" role="tab" data-scene="<?= e($slug) ?>" aria-selected="false">
          <?php if ($sc['thumb']): ?><img src="<?= e($sc['thumb']) ?>" alt="" loading="lazy" width="320" height="160"><?php endif; ?>
          <span><?= e($sc['title']) ?></span>
        </button>
      <?php endforeach; ?>
    </div>
  </div>
  <?php else: ?>
    <div class="container"><p class="empty"><?= e(__('tour.empty')) ?></p></div>
  <?php endif; ?>

  <?php if ($embeds): ?>
  <div class="container section-sm">
    <h2 class="block-title"><?= e(__('tour.external')) ?></h2>
    <div class="embed-grid">
      <?php foreach ($embeds as $s): ?>
        <div class="embed-card">
          <h3><?= e(tr($s['title'])) ?></h3>
          <div class="embed-frame" data-embed="<?= e($s['embed_url']) ?>"><button type="button" class="btn btn-accent" data-embed-load><?= icon('move-3d') ?><?= e(__('tour.load_3d')) ?></button></div>
          <?php if (tr($s['description']) !== ''): ?><p class="muted"><?= e(tr($s['description'])) ?></p><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
  <div class="container section-sm">
    <div class="tour-help">
      <div><?= icon('move-3d') ?><p><?= e(__('tour.help_drag')) ?></p></div>
      <div><?= icon('circle-dot') ?><p><?= e(__('tour.help_hotspot')) ?></p></div>
      <div><?= icon('smartphone') ?><p><?= e(__('tour.help_phone')) ?></p></div>
    </div>
  </div>
</section>
<?= View::partial('front/partials/contact-band') ?>
