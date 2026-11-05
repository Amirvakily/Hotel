<?php
/** @var array $d */
$items = array_values(array_filter(Content::amenities(), fn($a) => $a['highlight']));
if (!$items) return;
$img = Content::section('dining')['breakfast_image'] ?? null;
?>
<section class="section highlights">
  <div class="container">
    <div class="bento">
      <div class="bento-feature" data-reveal>
        <p class="eyebrow eyebrow--light"><?= e(tr($d['kicker'] ?? '')) ?></p>
        <h2 class="section-title"><?= e(tr($d['title'] ?? '')) ?></h2>
        <?php if (tr($d['subtitle'] ?? '') !== ''): ?><p><?= e(tr($d['subtitle'])) ?></p><?php endif; ?>
        <?php if (Content::navEnabled('about')): ?><a class="btn btn-light btn-sm" href="<?= e(url('about')) ?>"><?= e(__('about.facilities')) ?><?= icon(I18n::isRtl() ? 'arrow-left' : 'arrow-right') ?></a><?php endif; ?>
      </div>
      <?php if (media($img)): ?>
        <a class="bento-photo" href="<?= e(url('breakfast')) ?>" data-reveal style="--d:1">
          <?= picture($img, ['sizes' => '(max-width: 900px) 100vw, 40vw', 'alt' => '']) ?>
          <span class="bento-photo-label"><span class="pill pill-light"><?= icon('sparkles') ?><?= e(__('common.free')) ?></span><b><?= e(__('breakfast.free_badge')) ?></b></span>
        </a>
      <?php endif; ?>
      <?php foreach ($items as $i => $a): ?>
        <div class="bento-tile" data-reveal style="--d:<?= ($i % 4) + 1 ?>">
          <span class="tile-icon"><?= icon($a['icon'] ?: 'sparkles') ?></span>
          <div>
            <h3><?= e(tr($a['name'])) ?></h3>
            <?php if (tr($a['description']) !== ''): ?><p><?= e(tr($a['description'])) ?></p><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
