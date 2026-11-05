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
      <?php foreach ($items as $i => $it): $m = media($it['media_id']); if (!$m) continue; ?>
        <a class="mosaic-item m<?= $i ?>" href="<?= e(Media::src($m, 2400)) ?>" data-lb data-caption="<?= e(tr($it['caption'])) ?>" data-reveal style="--d:<?= $i % 4 ?>">
          <?= picture($m, ['sizes' => '(max-width: 700px) 50vw, 33vw', 'alt' => tr($it['caption'])]) ?>
          <?php if (tr($it['caption']) !== ''): ?><span class="mosaic-cap"><?= e(tr($it['caption'])) ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
