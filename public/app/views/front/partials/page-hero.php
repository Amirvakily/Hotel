<?php
/** Page header: title block, then an optional wide rounded cover image. @var string $title */
$subtitle = $subtitle ?? '';
$kicker = $kicker ?? '';
$image = $image ?? null;
$m = $image ? media($image) : null;
?>
<section class="page-hero">
  <div class="container">
    <nav class="crumbs" aria-label="breadcrumb"><a href="<?= e(url('')) ?>"><?= e(__('nav.home')) ?></a><?= icon(I18n::isRtl() ? 'chevron-left' : 'chevron-right') ?><span><?= e($title) ?></span></nav>
    <div class="page-hero-grid">
      <div>
        <?php if ($kicker !== ''): ?><p class="eyebrow" data-reveal><?= e($kicker) ?></p><?php endif; ?>
        <h1 class="page-title" data-reveal style="--d:1"><?= e($title) ?></h1>
      </div>
      <?php if ($subtitle !== ''): ?><p class="page-sub" data-reveal style="--d:2"><?= e($subtitle) ?></p><?php endif; ?>
    </div>
    <?php if ($m): ?><div class="page-cover" data-reveal style="--d:2"><div class="page-cover-inner" data-parallax><?= picture($m, ['sizes' => '(max-width: 1300px) 100vw, 1240px', 'eager' => true, 'alt' => $title]) ?></div></div><?php endif; ?>
  </div>
</section>
