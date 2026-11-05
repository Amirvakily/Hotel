<?php
/** @var array $d */
$rooms = Content::rooms();
$featured = array_values(array_filter($rooms, fn($r) => $r['featured']));
$list = $featured ?: $rooms;
$limit = (int)($d['limit'] ?? 4);
$list = array_slice($list, 0, max(1, $limit));
if (!$list || !Content::navEnabled('rooms')) return;
$rtl = I18n::isRtl();
?>
<section class="section rooms-home">
  <div class="container">
    <?= View::partial('front/partials/section-head', ['kicker' => tr($d['kicker'] ?? ''), 'title' => tr($d['title'] ?? '') ?: __('rooms.title'), 'subtitle' => tr($d['subtitle'] ?? ''), 'link' => [url('rooms'), __('rooms.all')]]) ?>
    <div class="carousel" data-carousel>
      <div class="carousel-track">
        <?php foreach ($list as $i => $room): ?>
          <?= View::partial('front/partials/room-card', ['room' => $room, 'i' => $i]) ?>
        <?php endforeach; ?>
      </div>
      <div class="carousel-nav">
        <button type="button" class="icon-btn" data-carousel-prev aria-label="<?= e(__('common.prev')) ?>"><?= icon($rtl ? 'arrow-right' : 'arrow-left') ?></button>
        <button type="button" class="icon-btn" data-carousel-next aria-label="<?= e(__('common.next')) ?>"><?= icon($rtl ? 'arrow-left' : 'arrow-right') ?></button>
      </div>
    </div>
  </div>
</section>
