<?php
/** @var array $d */
if (!Content::navEnabled('gallery')) return;
$items = Content::galleryItems(null, (int)($d['limit'] ?? 7));
if (!$items) return;
?>
<section class="section gallery-home">
  <div class="container">
    <?= View::partial('front/partials/section-head', ['kicker' => tr($d['kicker'] ?? ''), 'title' => tr($d['title'] ?? '') ?: __('gallery.title'), 'subtitle' => tr($d['subtitle'] ?? ''), 'link' => [url('gallery'), __('gallery.all_photos')]]) ?>
    <div class="mosaic" data-gallery>
      <?php foreach ($items as $i => $it): $m = media($it['media_id']); if (!$m) continue; $cap = tr($it['caption']); $alt = $cap === '' ? tr($m['alt'] ?? '') : ''; ?>
        <a class="mosaic-item m<?= $i ?>" href="<?= e(Media::src($m, 2400)) ?>" data-lb data-caption="<?= e($cap) ?>"<?= $cap === '' && $alt === '' ? ' aria-label="' . e(__('gallery.photo', ['n' => num($i + 1)])) . '"' : '' ?> data-reveal style="--d:<?= $i % 4 ?>">
          <?= picture($m, ['sizes' => '(max-width: 700px) 50vw, 33vw', 'alt' => $alt]) ?>
          <?php if ($cap !== ''): ?><span class="mosaic-cap"><?= e($cap) ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
