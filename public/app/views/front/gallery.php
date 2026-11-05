<?php
/** @var array $categories @var array $items @var string $active */
$counts = [];
foreach ($items as $it) {
    $counts[$it['cat_slug'] ?? ''] = ($counts[$it['cat_slug'] ?? ''] ?? 0) + 1;
}
$valid = array_column($categories, 'slug');
if (!in_array($active, $valid, true)) {
    $active = '';
}
?>
<?= View::partial('front/partials/page-hero', ['title' => __('gallery.title'), 'subtitle' => __('gallery.subtitle'), 'kicker' => __('gallery.kicker')]) ?>
<section class="section gallery-page">
  <div class="container">
    <div class="chips" data-gallery-filter role="tablist" aria-label="<?= e(__('gallery.filter')) ?>">
      <button type="button" class="chip<?= $active === '' ? ' is-active' : '' ?>" data-filter=""><?= e(__('gallery.all')) ?> <small><?= e(num(count($items))) ?></small></button>
      <?php foreach ($categories as $c): if (empty($counts[$c['slug']])) continue; ?>
        <button type="button" class="chip<?= $active === $c['slug'] ? ' is-active' : '' ?>" data-filter="<?= e($c['slug']) ?>"><?= e(tr($c['name'])) ?> <small><?= e(num($counts[$c['slug']])) ?></small></button>
      <?php endforeach; ?>
    </div>
    <div class="masonry" data-gallery>
      <?php foreach ($items as $i => $it): $m = media($it['media_id']); if (!$m) continue; $show = $active === '' || $it['cat_slug'] === $active; ?>
        <a class="masonry-item" href="<?= e(Media::src($m, 2400)) ?>" data-lb data-cat="<?= e($it['cat_slug']) ?>" data-caption="<?= e(tr($it['caption'])) ?>"<?= $show ? '' : ' hidden' ?>>
          <?= picture($m, ['sizes' => '(max-width: 600px) 50vw, (max-width: 1000px) 33vw, 25vw', 'alt' => tr($it['caption']), 'eager' => $i < 4]) ?>
          <?php if (tr($it['caption']) !== ''): ?><span class="masonry-cap"><?= e(tr($it['caption'])) ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
    <?php if (!$items): ?><p class="empty"><?= e(__('gallery.empty')) ?></p><?php endif; ?>
  </div>
</section>
