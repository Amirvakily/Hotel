<?php
/** @var array $d */
$stats = $d['stats'] ?? [];
?>
<section class="section intro">
  <div class="container">
    <p class="eyebrow" data-reveal><?= e(tr($d['kicker'] ?? '')) ?></p>
    <h2 class="statement" data-reveal style="--d:1"><?= e(tr($d['title'] ?? '')) ?></h2>
    <div class="intro-grid">
      <div class="intro-img intro-img--a" data-reveal><?= picture($d['image_id'] ?? null, ['sizes' => '(max-width: 900px) 100vw, 34vw']) ?></div>
      <div class="intro-body" data-reveal style="--d:1">
        <div class="prose"><?= Sanitizer::html(tr($d['body'] ?? '')) ?></div>
        <?php if (Content::navEnabled('about')): ?><a class="btn btn-ink" href="<?= e(url('about')) ?>"><?= e(__('common.more_about')) ?><?= icon(I18n::isRtl() ? 'arrow-left' : 'arrow-right') ?></a><?php endif; ?>
      </div>
      <div class="intro-side" data-reveal style="--d:2">
        <?php if ($stats): ?>
        <ul class="stats">
          <?php foreach ($stats as $st): $val = (string)($st['value'] ?? ''); ?>
            <li><b data-count="<?= e(preg_replace('~[^0-9]~', '', $val)) ?>"><?= e(digits($val)) ?></b><span><?= e(tr($st['label'] ?? '')) ?></span></li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <?php if (!empty($d['image2_id'])): ?><div class="intro-img intro-img--b"><?= picture($d['image2_id'], ['sizes' => '(max-width: 900px) 100vw, 26vw']) ?></div><?php endif; ?>
      </div>
    </div>
  </div>
</section>
