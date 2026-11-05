<?php
/** @var array $d */
if (!Content::navEnabled('attractions')) return;
$items = array_slice(Content::attractions(), 0, (int)($d['limit'] ?? 6));
if (!$items) return;
$rtl = I18n::isRtl();
?>
<section class="section attractions-home">
  <div class="container">
    <?= View::partial('front/partials/section-head', ['kicker' => tr($d['kicker'] ?? ''), 'title' => tr($d['title'] ?? '') ?: __('attractions.title'), 'subtitle' => tr($d['subtitle'] ?? ''), 'link' => [url('attractions'), __('attractions.all')]]) ?>
    <div class="carousel" data-carousel>
      <div class="carousel-track carousel-track--wide">
        <?php foreach ($items as $i => $a): ?><?= View::partial('front/partials/attraction-card', ['a' => $a, 'i' => $i]) ?><?php endforeach; ?>
      </div>
      <div class="carousel-nav">
        <button type="button" class="icon-btn" data-carousel-prev aria-label="<?= e(__('common.prev')) ?>"><?= icon($rtl ? 'arrow-right' : 'arrow-left') ?></button>
        <button type="button" class="icon-btn" data-carousel-next aria-label="<?= e(__('common.next')) ?>"><?= icon($rtl ? 'arrow-left' : 'arrow-right') ?></button>
      </div>
    </div>
  </div>
</section>
