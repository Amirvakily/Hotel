<?php
/** @var array $d */
$cards = [];
if (Content::navEnabled('breakfast')) {
    $cards[] = ['key' => 'breakfast', 'title' => tr($d['breakfast_title'] ?? '') ?: __('nav.breakfast'), 'text' => tr($d['breakfast_text'] ?? ''), 'img' => $d['breakfast_image'] ?? null, 'badge' => __('common.free'), 'hours' => tr(setting('hotel.breakfast_hours')), 'icon' => 'croissant'];
}
if (Content::navEnabled('cafe')) {
    $cards[] = ['key' => 'cafe', 'title' => tr($d['cafe_title'] ?? '') ?: (tr(setting('cafe.name')) ?: __('nav.cafe')), 'text' => tr($d['cafe_text'] ?? ''), 'img' => $d['cafe_image'] ?? null, 'badge' => '', 'hours' => tr(setting('hotel.cafe_hours')), 'icon' => 'coffee'];
}
if (!$cards) return;
$rtl = I18n::isRtl();
?>
<section class="section dining-home">
  <div class="container">
    <?= View::partial('front/partials/section-head', ['kicker' => tr($d['kicker'] ?? ''), 'title' => tr($d['title'] ?? ''), 'subtitle' => tr($d['subtitle'] ?? '')]) ?>
    <div class="dining-cards">
      <?php foreach ($cards as $i => $c): ?>
        <a class="dining-card" href="<?= e(url($c['key'])) ?>" data-reveal style="--d:<?= $i ?>">
          <div class="dining-card-media"><?= picture($c['img'], ['sizes' => '(max-width: 800px) 100vw, 50vw', 'alt' => '']) ?></div>
          <div class="dining-card-body">
            <div class="dining-card-top">
              <span class="pill pill-light"><?= icon($c['icon']) ?><?= e($c['hours']) ?></span>
              <?php if ($c['badge'] !== ''): ?><span class="pill pill-accent2"><?= icon('sparkles') ?><?= e($c['badge']) ?></span><?php endif; ?>
            </div>
            <div>
              <h3><?= e($c['title']) ?></h3>
              <p><?= e($c['text']) ?></p>
            </div>
          </div>
          <span class="card-arrow" aria-hidden="true"><?= icon($rtl ? 'arrow-up-left' : 'arrow-up-right') ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
