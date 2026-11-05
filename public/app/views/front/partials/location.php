<?php
$lat = (float)setting('map.lat');
$lng = (float)setting('map.lng');
$zoom = (int)setting('map.zoom', 15);
$withAttractions = !empty($withAttractions);
$markers = [];
if ($withAttractions) {
    foreach (Content::attractions() as $a) {
        if ($a['lat'] && $a['lng']) {
            $markers[] = ['lat' => (float)$a['lat'], 'lng' => (float)$a['lng'], 'title' => tr($a['name'])];
        }
    }
}
$ll = $lat . ',' . $lng;
$apps = [
    ['neshan', __('maps.neshan'), setting('map.link_neshan') ?: 'https://neshan.org/maps/@' . $ll . ',16z,0p'],
    ['balad', __('maps.balad'), setting('map.link_balad') ?: 'https://balad.ir/location?latitude=' . $lat . '&longitude=' . $lng . '&zoom=16'],
    ['google', __('maps.google'), setting('map.link_google') ?: 'https://www.google.com/maps/dir/?api=1&destination=' . $ll],
    ['waze', __('maps.waze'), 'https://waze.com/ul?ll=' . $ll . '&navigate=yes'],
];
$phones = (array)setting('contact.phones', []);
?>
<div class="location">
  <?php if (setting('map.show', true)): ?>
  <div class="map-frame" data-reveal>
    <div class="map" data-map data-lat="<?= e($lat) ?>" data-lng="<?= e($lng) ?>" data-zoom="<?= $zoom ?>" data-tiles="<?= e(setting('map.tiles')) ?>"
      data-title="<?= e(tr(setting('site.name'))) ?>" data-markers="<?= e(json_enc($markers)) ?>" role="region" aria-label="<?= e(__('contact.map')) ?>">
      <div class="map-placeholder"><?= icon('map') ?><span><?= e(__('common.loading')) ?></span></div>
    </div>
  </div>
  <?php endif; ?>
  <div class="location-card" data-reveal style="--d:1">
    <h3><?= icon('map-pin') ?><?= e(tr(setting('site.name'))) ?></h3>
    <p><?= e(tr(setting('contact.address'))) ?></p>
    <?php foreach ($phones as $ph): ?><p><a class="phone-link" href="tel:<?= e(tel($ph)) ?>" dir="ltr"><?= icon('phone') ?><?= e(digits($ph)) ?></a></p><?php endforeach; ?>
    <p class="nav-apps-title"><?= e(__('contact.navigate')) ?></p>
    <div class="nav-apps">
      <?php foreach ($apps as [$k, $label, $href]): ?>
        <a class="nav-app nav-app--<?= e($k) ?>" href="<?= e($href) ?>" target="_blank" rel="noopener"><?= icon('navigation') ?><?= e($label) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
