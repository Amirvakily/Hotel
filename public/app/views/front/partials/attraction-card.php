<?php
/** @var array $a */
$i = $i ?? 0;
$km = (float)$a['distance_km'];
$maps = ($a['lat'] && $a['lng']) ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($a['lat'] . ',' . $a['lng']) : '';
?>
<article class="attraction-card" data-reveal style="--d:<?= $i % 4 ?>">
  <div class="attraction-media">
    <?= picture($a['media_id'], ['sizes' => '(max-width: 700px) 80vw, 25vw', 'alt' => tr($a['name'])]) ?>
    <?php if ($km > 0): ?><span class="pill pill-light attraction-km"><?= icon('navigation') ?><?= e(__('attractions.km', ['n' => $km < 10 ? num($km, $km == floor($km) ? 0 : 1) : num($km)])) ?></span><?php endif; ?>
  </div>
  <div class="attraction-body">
    <h3><?= e(tr($a['name'])) ?></h3>
    <p><?= e(tr($a['description'])) ?></p>
    <div class="attraction-meta">
      <?php if (tr($a['travel']) !== ''): ?><small><?= icon('clock') ?><?= e(tr($a['travel'])) ?></small><?php endif; ?>
      <?php if ($maps): ?><a href="<?= e($maps) ?>" target="_blank" rel="noopener"><?= e(__('attractions.on_map')) ?><?= icon('external-link') ?></a><?php endif; ?>
    </div>
  </div>
</article>
