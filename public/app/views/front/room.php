<?php
/** @var array $room @var array $amenities @var array $others @var ?string $scene @var ?array $tour */
$pd = room_price_data($room);
$p = $pd['p'];
$empty = Content::emptyPriceLabel();
$images = array_values(array_unique(array_filter(array_merge([$room['cover_id']], $room['gallery']))));
$phones = (array)setting('contact.phones', []);
$name = tr($room['name']);
$color = preg_match('~^#[0-9a-fA-F]{3,8}$~', (string)$room['color']) ? $room['color'] : '';
$roomAmen = array_values(array_filter(array_map(fn($id) => $amenities[$id] ?? null, $room['amenities'])));
$priceNote = tr($room['price_note']);
$rtl = I18n::isRtl();
$tg = telegram_link();
?>
<div class="room-page"<?= $color ? ' style="--room-color:' . e($color) . '"' : '' ?>>
  <section class="room-top container">
    <nav class="crumbs" aria-label="breadcrumb">
      <a href="<?= e(url('')) ?>"><?= e(__('nav.home')) ?></a><span aria-hidden="true">/</span>
      <a href="<?= e(url('rooms')) ?>"><?= e(__('nav.rooms')) ?></a><span aria-hidden="true">/</span>
      <span><?= e($name) ?></span>
    </nav>
    <div class="room-gallery g<?= min(5, count($images)) ?>" data-gallery>
      <?php foreach (array_slice($images, 0, 5) as $i => $mid): $m = media($mid); if (!$m) continue; ?>
        <a class="rg-item rg-<?= $i ?>" href="<?= e(Media::src($m, 2400)) ?>" data-lb data-caption="<?= e($name) ?>">
          <?= picture($m, ['sizes' => $i === 0 ? '(max-width: 800px) 100vw, 60vw' : '(max-width: 800px) 50vw, 20vw', 'eager' => $i === 0, 'alt' => $name . ' ' . ($i + 1)]) ?>
        </a>
      <?php endforeach; ?>
      <?php foreach (array_slice($images, 5) as $mid): $m = media($mid); if (!$m) continue; ?>
        <a class="rg-hidden" href="<?= e(Media::src($m, 2400)) ?>" data-lb data-caption="<?= e($name) ?>" hidden></a>
      <?php endforeach; ?>
      <div class="rg-actions">
        <?php if (count($images) > 1): ?><button type="button" class="btn btn-sm btn-light" data-lb-open><?= icon('images') ?><?= e(__('rooms.all_photos', ['n' => count($images)])) ?></button><?php endif; ?>
        <?php if ($scene): ?><a class="btn btn-sm btn-accent" href="#room-360"><?= icon('rotate-3d') ?><?= e(__('rooms.view_360')) ?></a><?php endif; ?>
      </div>
      <?php if (!$images): ?><div class="rg-item rg-0"><?= picture(null) ?></div><?php endif; ?>
    </div>
  </section>

  <section class="section room-detail container">
    <div class="room-main">
      <?php if (tr($room['badge']) !== ''): ?><span class="room-badge static"><?= e(tr($room['badge'])) ?></span><?php endif; ?>
      <h1 class="room-title" data-reveal><?= e($name) ?></h1>
      <?php if (tr($room['highlight']) !== ''): ?><p class="room-lead" data-reveal style="--d:1"><?= e(tr($room['highlight'])) ?></p><?php endif; ?>
      <ul class="spec-chips" data-reveal style="--d:2">
        <li><?= icon('bed-double') ?><span><small><?= e(__('rooms.beds_col')) ?></small><b><?= e(num((int)$room['beds'])) ?></b></span></li>
        <li><?= icon('users') ?><span><small><?= e(__('rooms.guests')) ?></small><b><?= e(($room['min_guests'] === $room['max_guests'] ? num($room['max_guests']) : num($room['min_guests']) . '–' . num($room['max_guests'])) . ($room['max_extra'] ? ' ' . __('rooms.cap_extra', ['n' => $room['max_extra']]) : '')) ?></b></span></li>
        <?php if ((int)$room['size_m2']): ?><li><?= icon('ruler') ?><span><small><?= e(__('rooms.size_col')) ?></small><b><?= e(__('rooms.size', ['n' => (int)$room['size_m2']])) ?></b></span></li><?php endif; ?>
        <?php if (tr($room['view']) !== ''): ?><li><?= icon('eye') ?><span><small><?= e(__('rooms.view_col')) ?></small><b><?= e(tr($room['view'])) ?></b></span></li><?php endif; ?>
        <?php if (tr($room['floor']) !== ''): ?><li><?= icon('layers') ?><span><small><?= e(__('rooms.floor')) ?></small><b><?= e(tr($room['floor'])) ?></b></span></li><?php endif; ?>
        <?php if ((int)$room['room_count']): ?><li><?= icon('door-open') ?><span><small><?= e(__('rooms.count_col')) ?></small><b><?= e(num((int)$room['room_count'])) ?></b></span></li><?php endif; ?>
      </ul>
      <div class="prose" data-reveal><?= Sanitizer::html(tr($room['description'])) ?></div>

      <div class="room-block" data-reveal>
        <h2 class="block-title"><?= e(__('rooms.bed_layout')) ?></h2>
        <div class="bed-plan">
          <?= bed_diagram($room['bed_layout'], 'bed-diagram bed-diagram--lg') ?>
          <p><?= e(bed_summary($room['bed_layout'])) ?></p>
        </div>
      </div>

      <?php if ($room['features']): ?>
      <div class="room-block" data-reveal>
        <h2 class="block-title"><?= e(__('rooms.features')) ?></h2>
        <ul class="check-list">
          <?php foreach ($room['features'] as $f): if (tr($f) === '') continue; ?><li><?= icon('check') ?><?= e(tr($f)) ?></li><?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <?php if ($roomAmen): ?>
      <div class="room-block" data-reveal>
        <h2 class="block-title"><?= e(__('rooms.amenities')) ?></h2>
        <ul class="amenity-list">
          <?php foreach ($roomAmen as $a): ?><li><?= icon($a['icon'] ?: 'check') ?><span><?= e(tr($a['name'])) ?></span></li><?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <?php if ($scene && $tour): ?>
      <div class="room-block" id="room-360" data-reveal>
        <h2 class="block-title"><?= icon('rotate-3d') ?><?= e(__('rooms.view_360')) ?></h2>
        <div class="pano-box" data-pano data-config="<?= e(json_enc($tour)) ?>">
          <?php $sc = $tour['scenes'][$scene] ?? null; if ($sc && $sc['thumb']): ?><img class="pano-poster" src="<?= e($sc['thumb']) ?>" alt="" loading="lazy"><?php endif; ?>
          <button type="button" class="btn btn-accent pano-start" data-pano-start><?= icon('move-3d') ?><?= e(__('tour.start_here')) ?></button>
          <div class="pano-view" data-pano-view></div>
        </div>
        <p class="muted small"><?= e(__('tour.hint')) ?> · <a href="<?= e(url('tour', ['scene' => $scene])) ?>"><?= e(__('tour.full')) ?></a></p>
      </div>
      <?php endif; ?>
    </div>

    <aside class="room-aside">
      <div class="price-card" data-price-card data-room data-min="<?= (int)$room['min_guests'] ?>" data-max="<?= (int)$room['capacity'] ?>" data-default="<?= (int)$room['max_guests'] ?>" data-name="<?= e($name) ?>"
        data-prices="<?= e(json_enc((object)$pd['fmt'])) ?>" data-sec="<?= e(json_enc((object)$pd['sec'])) ?>" data-extra="<?= e(json_enc((object)$pd['extra'])) ?>" data-from="<?= e($p['from'] ? money($p['from']) : '') ?>">
        <h2 class="price-card-title"><?= e(__('rooms.prices')) ?></h2>
        <?php if ($room['capacity'] > $room['min_guests']): ?>
        <div class="seg seg-sm" data-guest-picker data-local role="radiogroup" aria-label="<?= e(__('rooms.how_many')) ?>">
          <?php for ($g = $room['min_guests']; $g <= $room['capacity']; $g++): ?>
            <button type="button" role="radio" data-guests="<?= $g ?>" aria-checked="<?= $g === $room['max_guests'] ? 'true' : 'false' ?>"><?= e(num($g)) ?><?= $g > $room['max_guests'] ? '<sup>+</sup>' : '' ?></button>
          <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php if (setting('price.show_kids', true)): ?><?= View::partial('front/partials/kids') ?><?php endif; ?>
        <div class="room-price room-price--lg" data-price-box<?= (!$p['any'] && $empty === '') ? ' hidden' : '' ?>>
          <small data-price-label><?= e(__('rooms.price_for', ['n' => $room['max_guests']])) ?></small>
          <strong data-price><?= e($pd['fmt'][$room['max_guests']] ?: $empty) ?></strong>
          <small class="price-sec" data-price-sec><?= e($pd['sec'][$room['max_guests']] ?? '') ?></small>
          <small class="price-extra" data-price-extra hidden></small>
          <?php if ($p['any']): ?><small class="price-unit"><?= e(tr(setting('price.unit'))) ?></small><?php endif; ?>
        </div>
        <?php if ($p['any']): ?>
        <ul class="price-list">
          <?php foreach ($p['byGuests'] as $g => $v): ?>
            <li data-price-row="<?= $g ?>"<?= isset($p['extra'][$g]) ? ' class="is-extra"' : '' ?>><span><?= icon(isset($p['extra'][$g]) ? 'plus' : 'user') ?><?= e(__('rooms.n_people', ['n' => $g])) ?><?= isset($p['extra'][$g]) ? ' <em>' . e(__('rooms.with_extra', ['n' => $p['extra'][$g]])) . '</em>' : '' ?></span><b><?= e($v ? money($v) : ($empty !== '' ? $empty : '—')) ?></b></li>
          <?php endforeach; ?>
          <?php if ($room['max_extra'] && $p['extraPrice']): ?><li class="extra"><span><?= icon('plus') ?><?= e(__('rooms.extra_person')) ?></span><b><?= e(money($p['extraPrice'])) ?></b></li><?php endif; ?>
        </ul>
        <?php endif; ?>
        <p class="pill pill-accent"><?= icon('croissant') ?><?= e(__('rooms.breakfast_included')) ?></p>
        <?php if (setting('price.show_kids', true)): ?><p class="muted small icon-line"><?= icon("baby") ?><span><?= e(__("rooms.kids_note", ["age" => (int)setting("price.child_age", 5)])) ?></span></p><?php endif; ?>
        <?php if ($priceNote !== ''): ?><p class="muted small"><?= e($priceNote) ?></p><?php endif; ?>
        <div class="price-card-actions">
          <?php if (!empty($phones[0]) && setting('cta.show_call', true)): ?><a class="btn btn-accent btn-block" href="tel:<?= e(tel($phones[0])) ?>"><?= icon('phone') ?><?= e(__('rooms.call_to_book')) ?></a><?php endif; ?>
          <?php if (setting('contact.whatsapp') && setting('cta.show_whatsapp', true)): ?><a class="btn btn-outline btn-block" data-wa-link href="<?= e(whatsapp_link(strtr(tr(setting('cta.whatsapp_text')), ['{room}' => $name, '{guests}' => num($room['max_guests']), '{kids}' => '']))) ?>" target="_blank" rel="noopener"><?= icon('message-circle') ?><?= e(__('rooms.ask_whatsapp')) ?></a><?php endif; ?>
          <?php if ($tg !== '' && setting('cta.show_telegram', true)): ?><a class="btn btn-ghost btn-block" href="<?= e($tg) ?>" target="_blank" rel="noopener"><?= icon('send') ?><?= e(__('common.telegram')) ?></a><?php endif; ?>
        </div>
        <p class="muted small center"><?= e(__('rooms.no_booking')) ?></p>
      </div>
    </aside>
  </section>

  <?php if ($others): ?>
  <section class="section section-alt">
    <div class="container">
      <?= View::partial('front/partials/section-head', ['kicker' => '', 'title' => __('rooms.other'), 'link' => [url('rooms'), __('rooms.all')]]) ?>
      <div class="room-grid">
        <?php foreach ($others as $i => $o): ?><?= View::partial('front/partials/room-card', ['room' => $o, 'i' => $i]) ?><?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>
</div>
