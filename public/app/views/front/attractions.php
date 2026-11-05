<?php /** @var array $items */ ?>
<?= View::partial('front/partials/page-hero', ['title' => __('attractions.title'), 'subtitle' => __('attractions.subtitle'), 'kicker' => __('attractions.kicker')]) ?>
<section class="section">
  <div class="container">
    <div class="attraction-grid">
      <?php foreach ($items as $i => $a): ?><?= View::partial('front/partials/attraction-card', ['a' => $a, 'i' => $i]) ?><?php endforeach; ?>
    </div>
    <?php if (!$items): ?><p class="empty"><?= e(__('attractions.empty')) ?></p><?php endif; ?>
  </div>
</section>
<section class="section section-alt">
  <div class="container">
    <?= View::partial('front/partials/section-head', ['kicker' => '', 'title' => __('attractions.map_title')]) ?>
    <?= View::partial('front/partials/location', ['withAttractions' => true]) ?>
  </div>
</section>
