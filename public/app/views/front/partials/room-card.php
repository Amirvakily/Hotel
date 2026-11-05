<?php
/** @var array $room */
$pd = room_price_data($room);
$p = $pd['p'];
$empty = Content::emptyPriceLabel();
$href = url('rooms/' . $room['slug']);
$color = preg_match('~^#[0-9a-fA-F]{3,8}$~', (string)$room['color']) ? $room['color'] : '';
$badge = tr($room['badge']);
$i = $i ?? 0;
?>
<article class="room-card" data-room data-beds="<?= (int)$room['beds'] ?>" data-min="<?= (int)$room['min_guests'] ?>" data-max="<?= (int)$room['capacity'] ?>"
  data-name="<?= e(tr($room['name'])) ?>" data-prices="<?= e(json_enc((object)$pd['fmt'])) ?>" data-sec="<?= e(json_enc((object)$pd['sec'])) ?>" data-extra="<?= e(json_enc((object)$pd['extra'])) ?>"
  data-from="<?= e($p['from'] ? money($p['from']) : '') ?>" data-reveal style="--d:<?= $i % 3 ?><?= $color ? ';--room-color:' . e($color) : '' ?>">
  <a class="room-media" href="<?= e($href) ?>" tabindex="-1" aria-hidden="true">
    <div class="room-img"><?= picture($room['cover_id'], ['sizes' => '(max-width: 700px) 92vw, (max-width: 1100px) 46vw, 30vw', 'alt' => tr($room['name'])]) ?></div>
    <?php if ($badge !== ''): ?><span class="room-badge"><?= e($badge) ?></span><?php endif; ?>
    <?php if ($room['scene_id']): ?><span class="room-360" title="360°"><?= icon('rotate-3d') ?></span><?php endif; ?>
  </a>
  <div class="room-body">
    <h3 class="room-name"><a href="<?= e($href) ?>"><?= e(tr($room['name'])) ?></a></h3>
    <?php if (tr($room['highlight']) !== ''): ?><p class="room-highlight"><?= e(tr($room['highlight'])) ?></p><?php endif; ?>
    <div class="room-beds">
      <?= bed_diagram($room['bed_layout']) ?>
      <span><?= e(bed_summary($room['bed_layout'])) ?></span>
    </div>
    <ul class="room-specs">
      <li><?= icon('users') ?><?= e(capacity_text($room)) ?></li>
      <?php if ((int)$room['size_m2'] > 0): ?><li><?= icon('ruler') ?><?= e(__('rooms.size', ['n' => (int)$room['size_m2']])) ?></li><?php endif; ?>
      <?php if (tr($room['view']) !== ''): ?><li><?= icon('eye') ?><?= e(tr($room['view'])) ?></li><?php endif; ?>
    </ul>
    <div class="room-foot">
      <div class="room-price" data-price-box<?= (!$p['any'] && $empty === '') ? ' hidden' : '' ?>>
        <small data-price-label><?= e($p['from'] ? __('rooms.from') : '') ?></small>
        <strong data-price><?= e($p['from'] ? money($p['from']) : $empty) ?></strong>
        <small class="price-sec" data-price-sec><?= e($p['from'] ? money_secondary($p['from']) : '') ?></small>
        <small class="price-extra" data-price-extra hidden></small>
        <?php if ($p['any']): ?><small class="price-unit" data-price-unit><?= e(tr(setting('price.unit'))) ?></small><?php endif; ?>
      </div>
      <p class="room-unfit" data-unfit hidden><?= icon('circle-alert') ?><span></span></p>
      <a class="room-go" href="<?= e($href) ?>" aria-label="<?= e(__('rooms.details') . ': ' . tr($room['name'])) ?>"><?= icon(I18n::isRtl() ? 'arrow-up-left' : 'arrow-up-right') ?></a>
    </div>
  </div>
</article>
