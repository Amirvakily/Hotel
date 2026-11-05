<?php
/** @var array $d */
?>
<section class="section location-home">
  <div class="container">
    <?= View::partial('front/partials/section-head', ['kicker' => tr($d['kicker'] ?? ''), 'title' => tr($d['title'] ?? '') ?: __('contact.find_us'), 'subtitle' => tr($d['subtitle'] ?? '')]) ?>
    <?= View::partial('front/partials/location', ['withAttractions' => true]) ?>
  </div>
</section>
