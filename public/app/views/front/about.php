<?php
/** @var array $pg @var array $amenities @var array $faqs */
$cover = $pg['cover_id'] ?? null;
$facts = [
    ['door-open', __('about.rooms_total'), num((int)setting('hotel.rooms_total'))],
    ['layers', __('about.floors'), num((int)setting('hotel.floors'))],
    ['clock', __('about.checkin'), digits(setting('hotel.checkin'))],
    ['clock', __('about.checkout'), digits(setting('hotel.checkout'))],
    ['award', __('about.stars'), __('common.stars', ['n' => (int)setting('site.stars', 3)])],
    ['calendar', __('about.since'), digits((string)setting('site.established'))],
];
$policies = tr(setting('policies'));
?>
<?= View::partial('front/partials/page-hero', ['title' => __('about.title'), 'subtitle' => tr(setting('site.tagline')), 'kicker' => __('about.kicker'), 'image' => $cover]) ?>
<section class="section about-page">
  <div class="container about-grid">
    <div class="prose prose-lg" data-reveal><?= Sanitizer::html(tr($pg['body'] ?? '')) ?></div>
    <ul class="fact-cards">
      <?php foreach ($facts as $i => [$ic, $label, $val]): if ($val === '' || $val === '۰' || $val === '0') continue; ?>
        <li data-reveal style="--d:<?= $i % 3 ?>"><?= icon($ic) ?><small><?= e($label) ?></small><b><?= e($val) ?></b></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php if ($amenities): ?>
<section class="section section-alt">
  <div class="container">
    <?= View::partial('front/partials/section-head', ['kicker' => __('about.facilities_kicker'), 'title' => __('about.facilities')]) ?>
    <div class="tile-grid">
      <?php foreach (array_values($amenities) as $i => $a): ?>
        <div class="bento-tile" data-reveal style="--d:<?= $i % 4 ?>"><span class="tile-icon"><?= icon($a['icon'] ?: 'sparkles') ?></span><div><h3><?= e(tr($a['name'])) ?></h3><?php if (tr($a['description']) !== ''): ?><p><?= e(tr($a['description'])) ?></p><?php endif; ?></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<?php if ($policies !== '' || $faqs): ?>
<section class="section">
  <div class="container faq-grid">
    <div>
      <?= View::partial('front/partials/section-head', ['kicker' => __('about.policies_kicker'), 'title' => __('about.policies')]) ?>
      <div class="prose" data-reveal><?= Sanitizer::html($policies) ?></div>
    </div>
    <?php if ($faqs): ?><div><h2 class="block-title"><?= e(__('faq.title')) ?></h2><?= View::partial('front/partials/faq-list', ['faqs' => $faqs]) ?></div><?php endif; ?>
  </div>
</section>
<?php endif; ?>
<?= View::partial('front/partials/contact-band') ?>
