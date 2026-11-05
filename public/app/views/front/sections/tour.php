<?php
/** @var array $d */
$scenes = Content::scenes();
if (!$scenes || !Content::navEnabled('tour')) return;
$first = null;
foreach ($scenes as $s) { if ($s['type'] !== 'embed' && media($s['media_id'])) { $first = $s; if ($s['is_default']) break; } }
$cover = media($d['image_id'] ?? null) ?: ($first ? media($first['media_id']) : null);
?>
<section class="section section-tight">
  <div class="container">
    <div class="tour-panel" data-reveal>
      <div class="tour-panel-text">
        <p class="eyebrow eyebrow--light"><?= e(tr($d['kicker'] ?? '')) ?></p>
        <h2 class="section-title"><?= e(tr($d['title'] ?? '') ?: __('tour.title')) ?></h2>
        <p class="section-sub"><?= e(tr($d['body'] ?? '') ?: __('tour.subtitle')) ?></p>
        <ul class="scene-chips">
          <?php foreach (array_slice($scenes, 0, 6) as $s): ?>
            <li><a href="<?= e(url('tour', ['scene' => $s['slug']])) ?>"><?= e(tr($s['title'])) ?></a></li>
          <?php endforeach; ?>
        </ul>
        <a class="btn btn-accent" href="<?= e(url('tour')) ?>"><?= icon('rotate-3d') ?><?= e(__('tour.start')) ?></a>
      </div>
      <a class="tour-panel-media" href="<?= e(url('tour')) ?>" aria-label="<?= e(__('tour.start')) ?>">
        <?php if ($cover): ?><div class="pano-strip" style="background-image:url('<?= e(Media::src($cover, 2048)) ?>')"></div><?php endif; ?>
        <span class="tour-badge">360°</span>
        <span class="tour-play"><?= icon('move-3d') ?></span>
      </a>
    </div>
  </div>
</section>
