<?php
/** @var array $rooms @var array $amenities */
$maxG = max(1, Content::maxGuests());
$group = (bool)setting('rooms.group_by_beds', true);
$groups = [];
foreach ($rooms as $r) {
    $groups[$group ? (int)$r['beds'] : 0][] = $r;
}
ksort($groups);
$empty = Content::emptyPriceLabel();
$rtl = I18n::isRtl();
// Union of extra features and amenities, for the compare table
$featureRows = [];
foreach ($rooms as $r) {
    foreach ($r['features'] as $f) {
        $label = tr($f);
        if ($label !== '') {
            $featureRows[$label] = true;
        }
    }
}
$amenityIds = [];
foreach ($rooms as $r) {
    foreach ($r['amenities'] as $aid) {
        if (isset($amenities[$aid])) {
            $amenityIds[$aid] = true;
        }
    }
}
$hasFeature = function (array $room, string $label): bool {
    foreach ($room['features'] as $f) {
        if (tr($f) === $label) {
            return true;
        }
    }
    return false;
};
?>
<?= View::partial('front/partials/page-hero', ['title' => __('rooms.title'), 'subtitle' => __('rooms.subtitle'), 'kicker' => __('rooms.kicker')]) ?>

<section class="section rooms-page" data-rooms-page>
  <div class="rooms-toolbar" data-toolbar>
    <div class="container toolbar-inner">
      <div class="guest-picker" role="radiogroup" aria-label="<?= e(__('rooms.how_many')) ?>">
        <span class="gp-label"><?= icon('users') ?><?= e(__('rooms.how_many')) ?></span>
        <div class="seg" data-guest-picker>
          <button type="button" role="radio" aria-checked="true" data-guests="0"><?= e(__('rooms.any')) ?></button>
          <?php for ($g = 1; $g <= $maxG; $g++): ?>
            <button type="button" role="radio" aria-checked="false" data-guests="<?= $g ?>"><?= e(__('rooms.n_people', ['n' => $g])) ?></button>
          <?php endfor; ?>
          <button type="button" role="radio" aria-checked="false" data-guests="<?= $maxG + 1 ?>" data-group><?= e(__('rooms.group_option', ['n' => $maxG + 1])) ?></button>
        </div>
        <?php if (setting('price.show_kids', true)): ?><?= View::partial('front/partials/kids') ?><?php endif; ?>
      </div>
      <div class="view-tabs" role="tablist" aria-label="<?= e(__('rooms.view_as')) ?>">
        <button type="button" role="tab" aria-selected="true" data-view-btn="cards"><?= icon('layout-grid') ?><span><?= e(__('rooms.view_cards')) ?></span></button>
        <button type="button" role="tab" aria-selected="false" data-view-btn="table"><?= icon('table') ?><span><?= e(__('rooms.view_table')) ?></span></button>
        <button type="button" role="tab" aria-selected="false" data-view-btn="compare"><?= icon('sliders-horizontal') ?><span><?= e(__('rooms.view_compare')) ?></span></button>
      </div>
    </div>
  </div>

  <div class="container">
    <p class="guest-status" data-guest-status aria-live="polite"></p>
    <div class="group-note" data-group-note hidden>
      <?= icon('users') ?>
      <div><b><?= e(__('rooms.group_title', ['n' => $maxG + 1])) ?></b><p><?= e(__('rooms.group_text')) ?></p></div>
      <?php $gp = (array)setting('contact.phones', []); if (!empty($gp[0])): ?><a class="btn btn-accent btn-sm" href="tel:<?= e(tel($gp[0])) ?>"><?= icon('phone') ?><?= e(__('common.call')) ?></a><?php endif; ?>
      <?php if (whatsapp_link() !== ''): ?><a class="btn btn-outline btn-sm" href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener"><?= icon('message-circle') ?><?= e(__('common.whatsapp')) ?></a><?php endif; ?>
    </div>

    <!-- Cards -->
    <div class="rooms-view" data-view="cards" role="tabpanel">
      <?php foreach ($groups as $beds => $list): ?>
        <div class="room-group" data-group="<?= (int)$beds ?>">
          <?php if ($group): ?>
          <div class="group-head" data-reveal>
            <div>
              <h2><?= e(__('rooms.group', ['n' => (int)$beds])) ?></h2>
              <p><?= e(count($list) > 1 ? __('rooms.group_types', ['n' => count($list)]) : __('rooms.group_one')) ?></p>
            </div>
            <?php if (count($list) > 1): ?>
              <button type="button" class="btn btn-sm btn-outline" data-compare-beds="<?= (int)$beds ?>"><?= icon('sliders-horizontal') ?><?= e(__('rooms.compare_these', ['n' => count($list)])) ?></button>
            <?php endif; ?>
          </div>
          <?php endif; ?>
          <div class="room-grid">
            <?php foreach ($list as $i => $room): ?>
              <?= View::partial('front/partials/room-card', ['room' => $room, 'i' => $i]) ?>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (!$rooms): ?><p class="empty"><?= e(__('rooms.none')) ?></p><?php endif; ?>
    </div>

    <!-- Price table -->
    <div class="rooms-view" data-view="table" role="tabpanel" hidden>
      <div class="table-wrap">
        <table class="price-table">
          <caption class="sr-only"><?= e(__('rooms.view_table')) ?></caption>
          <thead>
            <tr>
              <th scope="col"><?= e(__('rooms.room')) ?></th>
              <th scope="col"><?= e(__('rooms.beds_col')) ?></th>
              <?php for ($g = 1; $g <= $maxG; $g++): ?><th scope="col" data-col="<?= $g ?>"><?= e(__('rooms.n_people', ['n' => $g])) ?></th><?php endfor; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rooms as $room): $pd = room_price_data($room); ?>
              <tr style="--room-color:<?= e($room['color'] ?: 'var(--c-accent)') ?>">
                <th scope="row"><a href="<?= e(url('rooms/' . $room['slug'])) ?>"><?= e(tr($room['name'])) ?></a><?php if (tr($room['badge']) !== ''): ?><small class="tag"><?= e(tr($room['badge'])) ?></small><?php endif; ?></th>
                <td><?= e(num((int)$room['beds'])) ?></td>
                <?php for ($g = 1; $g <= $maxG; $g++): ?>
                  <td data-col="<?= $g ?>"><?php
                    if ($g < $room['min_guests'] || $g > $room['capacity']) {
                        echo '<span class="na" title="' . e(__('rooms.unfit', ['n' => $g])) . '">—</span>';
                    } elseif ($pd['fmt'][$g] ?? '') {
                        echo '<b>' . e($pd['fmt'][$g]) . '</b>' . (($pd['sec'][$g] ?? '') !== '' ? '<small>' . e($pd['sec'][$g]) . '</small>' : '')
                            . (isset($pd['extra'][$g]) ? '<small class="xtra">' . e(__('rooms.with_extra', ['n' => $pd['extra'][$g]])) . '</small>' : '');
                    } else {
                        echo '<span class="call">' . e($empty !== '' ? $empty : '—') . '</span>';
                    }
                  ?></td>
                <?php endfor; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Compare -->
    <div class="rooms-view" data-view="compare" role="tabpanel" hidden>
      <?php if (count($groups) > 1 && $group): ?>
      <div class="chips" data-compare-filter>
        <button type="button" class="chip is-active" data-filter-beds="0"><?= e(__('rooms.any')) ?></button>
        <?php foreach (array_keys($groups) as $b): ?><button type="button" class="chip" data-filter-beds="<?= (int)$b ?>"><?= e(__('rooms.group', ['n' => (int)$b])) ?></button><?php endforeach; ?>
      </div>
      <?php endif; ?>
      <div class="table-wrap">
        <table class="compare-table">
          <caption class="sr-only"><?= e(__('rooms.view_compare')) ?></caption>
          <thead>
            <tr>
              <td></td>
              <?php foreach ($rooms as $room): ?>
                <th scope="col" data-beds="<?= (int)$room['beds'] ?>" style="--room-color:<?= e($room['color'] ?: 'var(--c-accent)') ?>">
                  <a class="compare-head" href="<?= e(url('rooms/' . $room['slug'])) ?>">
                    <span class="compare-thumb"><?= picture($room['cover_id'], ['sizes' => '200px', 'max' => 480, 'alt' => '']) ?></span>
                    <?php if (tr($room['badge']) !== ''): ?><span class="tag"><?= e(tr($room['badge'])) ?></span><?php endif; ?>
                    <b><?= e(tr($room['name'])) ?></b>
                  </a>
                </th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <tr><th scope="row"><?= e(__('rooms.difference')) ?></th><?php foreach ($rooms as $room): ?><td data-beds="<?= (int)$room['beds'] ?>"><?= e(tr($room['highlight'])) ?></td><?php endforeach; ?></tr>
            <tr><th scope="row"><?= e(__('rooms.bed_layout')) ?></th><?php foreach ($rooms as $room): ?><td data-beds="<?= (int)$room['beds'] ?>"><?= bed_diagram($room['bed_layout']) ?><span class="small"><?= e(bed_summary($room['bed_layout'])) ?></span></td><?php endforeach; ?></tr>
            <tr><th scope="row"><?= e(__('rooms.guests')) ?></th><?php foreach ($rooms as $room): ?><td data-beds="<?= (int)$room['beds'] ?>"><?= e(($room['min_guests'] === $room['max_guests'] ? num($room['max_guests']) : num($room['min_guests']) . ' – ' . num($room['max_guests'])) . ($room['max_extra'] ? ' ' . __('rooms.cap_extra', ['n' => $room['max_extra']]) : '')) ?></td><?php endforeach; ?></tr>
            <tr><th scope="row"><?= e(__('rooms.extra_person')) ?></th><?php foreach ($rooms as $room): $xp = Content::roomPrices($room)['extraPrice']; ?><td data-beds="<?= (int)$room['beds'] ?>"><?= $room['max_extra'] ? e(($xp ? money($xp) : ($empty ?: '—')) . ' · ' . __('rooms.max_extra_n', ['n' => $room['max_extra']])) : '<span class="no">—</span>' ?></td><?php endforeach; ?></tr>
            <tr><th scope="row"><?= e(__('rooms.size_col')) ?></th><?php foreach ($rooms as $room): ?><td data-beds="<?= (int)$room['beds'] ?>"><?= (int)$room['size_m2'] ? e(__('rooms.size', ['n' => (int)$room['size_m2']])) : '—' ?></td><?php endforeach; ?></tr>
            <tr><th scope="row"><?= e(__('rooms.view_col')) ?></th><?php foreach ($rooms as $room): ?><td data-beds="<?= (int)$room['beds'] ?>"><?= e(tr($room['view']) ?: '—') ?></td><?php endforeach; ?></tr>
            <tr><th scope="row"><?= e(__('rooms.floor')) ?></th><?php foreach ($rooms as $room): ?><td data-beds="<?= (int)$room['beds'] ?>"><?= e(tr($room['floor']) ?: '—') ?></td><?php endforeach; ?></tr>
            <tr><th scope="row"><?= e(__('rooms.count_col')) ?></th><?php foreach ($rooms as $room): ?><td data-beds="<?= (int)$room['beds'] ?>"><?= (int)$room['room_count'] ? e(num((int)$room['room_count'])) : '—' ?></td><?php endforeach; ?></tr>
            <?php foreach (array_keys($featureRows) as $label): ?>
              <tr><th scope="row"><?= e($label) ?></th><?php foreach ($rooms as $room): ?><td data-beds="<?= (int)$room['beds'] ?>"><?= $hasFeature($room, $label) ? '<span class="yes">' . icon('check') . '</span>' : '<span class="no">—</span>' ?></td><?php endforeach; ?></tr>
            <?php endforeach; ?>
            <?php foreach (array_keys($amenityIds) as $aid): ?>
              <tr><th scope="row"><?= icon($amenities[$aid]['icon'] ?: 'check') ?><?= e(tr($amenities[$aid]['name'])) ?></th><?php foreach ($rooms as $room): ?><td data-beds="<?= (int)$room['beds'] ?>"><?= in_array($aid, $room['amenities'], true) ? '<span class="yes">' . icon('check') . '</span>' : '<span class="no">—</span>' ?></td><?php endforeach; ?></tr>
            <?php endforeach; ?>
            <tr class="compare-price-row"><th scope="row"><?= e(__('rooms.price')) ?></th><?php foreach ($rooms as $room): $pd = room_price_data($room); ?>
              <td data-beds="<?= (int)$room['beds'] ?>" data-room data-min="<?= (int)$room['min_guests'] ?>" data-max="<?= (int)$room['capacity'] ?>" data-prices="<?= e(json_enc((object)$pd['fmt'])) ?>" data-sec="<?= e(json_enc((object)$pd['sec'])) ?>" data-extra="<?= e(json_enc((object)$pd['extra'])) ?>" data-name="<?= e(tr($room['name'])) ?>">
                <div class="room-price" data-price-box<?= (!$pd['p']['any'] && $empty === '') ? ' hidden' : '' ?>>
                  <small data-price-label><?= e($pd['p']['from'] ? __('rooms.from') : '') ?></small>
                  <strong data-price><?= e($pd['p']['from'] ? money($pd['p']['from']) : $empty) ?></strong>
                  <small class="price-sec" data-price-sec><?= e($pd['p']['from'] ? money_secondary($pd['p']['from']) : '') ?></small>
                  <small class="price-extra" data-price-extra hidden></small>
                </div>
                <p class="room-unfit" data-unfit hidden><?= icon('circle-alert') ?><span></span></p>
              </td>
            <?php endforeach; ?></tr>
            <tr><td></td><?php foreach ($rooms as $room): ?><td data-beds="<?= (int)$room['beds'] ?>"><a class="btn btn-sm btn-ink" href="<?= e(url('rooms/' . $room['slug'])) ?>"><?= e(__('rooms.details')) ?></a></td><?php endforeach; ?></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="rooms-note" data-reveal>
      <?= icon('info') ?>
      <div>
        <p><?= e(tr(setting('price.note'))) ?></p>
        <?php if (setting('price.show_kids', true)): ?><p><?= e(__('rooms.kids_note', ['age' => (int)setting('price.child_age', 5)])) ?></p><?php endif; ?>
        <p><?= e(__('rooms.no_booking')) ?></p>
      </div>
    </div>
  </div>
</section>
<?= View::partial('front/partials/contact-band') ?>
