<?php
/** @var array $d */
$scenes = Content::scenes();
if (!Content::tourOn()) return;
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
        <?php if ($cover && ($cover['kind'] ?? '') === 'pano'):
            $small = Media::src($cover, 1024);
            $big = Media::src($cover, 4096);
            $srcset = $small !== $big ? $small . ' 1024w, ' . $big . ' 4096w' : '';
            // 'sizes' is kept low on phones so they take the 1024px copy (sharp enough, ~160 KB lighter) ?>
          <div class="pano-strip" aria-hidden="true"><?php for ($k = 0; $k < 2; $k++): ?><img src="<?= e($small) ?>"<?= $srcset ? ' srcset="' . e($srcset) . '" sizes="(max-width: 860px) 70vw, 960px"' : '' ?> alt="" width="1024" height="512" loading="lazy" decoding="async"><?php endfor; ?></div>
        <?php elseif ($cover): ?>
          <?= picture($cover, ['sizes' => '(max-width: 860px) 100vw, 55vw', 'alt' => '']) ?>
        <?php endif; ?>
        <span class="tour-badge">360°</span>
        <span class="tour-play"><?= icon('move-3d') ?></span>
      </a>
    </div>
  </div>
</section>
