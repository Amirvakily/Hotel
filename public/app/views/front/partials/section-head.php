<?php
/** @var string $kicker @var string $title */
$subtitle = $subtitle ?? '';
$link = $link ?? null;
$center = !empty($center);
$light = !empty($light);
?>
<div class="section-head<?= $center ? ' is-center' : '' ?><?= $light ? ' is-light' : '' ?>" data-reveal>
  <div class="section-head-text">
    <?php if ($kicker !== ''): ?><p class="eyebrow"><?= e($kicker) ?></p><?php endif; ?>
    <h2 class="section-title"><?= e($title) ?></h2>
    <?php if ($subtitle !== ''): ?><p class="section-sub"><?= e($subtitle) ?></p><?php endif; ?>
  </div>
  <?php if ($link): ?><a class="btn btn-outline btn-sm" href="<?= e($link[0]) ?>"><?= e($link[1]) ?><?= icon(I18n::isRtl() ? 'arrow-left' : 'arrow-right') ?></a><?php endif; ?>
</div>
