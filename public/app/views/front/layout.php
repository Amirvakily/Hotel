<?php
/** @var string $content */
$lang = I18n::$lang;
$dir = I18n::dir();
$siteName = tr(setting('site.name'));
$pg = App::$page;
$title = $pg['title'] ?? '';
$fullTitle = $title !== '' ? $title . ' | ' . $siteName : $siteName . ' | ' . tr(setting('site.tagline'));
$desc = $pg['description'] ?? '';
if ($desc === '') {
    $desc = tr(setting('seo.description'));
}
$ogImg = media($pg['image'] ?? null) ?: media(setting('seo.og_image')) ?: (Content::slides() ? media(Content::slides()[0]['media_id']) : null);
$canonical = abs_url(url(App::$path));
$theme = [
    '--c-primary' => setting('theme.primary'), '--c-accent' => setting('theme.accent'), '--c-accent2' => setting('theme.accent2'),
    '--c-bg' => setting('theme.bg'), '--c-ink' => setting('theme.ink'),
];
$css = '';
foreach ($theme as $k => $v) {
    if (is_string($v) && preg_match('~^#[0-9a-fA-F]{3,8}$~', $v)) {
        $css .= $k . ':' . $v . ';';
    }
}
$css .= '--hero-shade:' . max(0, min(90, (int)setting('theme.hero_overlay', 40))) / 100 . ';';
$css .= '--radius:' . max(0, min(40, (int)setting('theme.radius', 22))) . 'px;';
$favicon = media(setting('site.favicon'));
$bodyClass = 'page-' . preg_replace('~[^a-z0-9\-]~', '', (string)($pg['key'] ?? 'page'));
if (!setting('theme.animations', true)) {
    $bodyClass .= ' no-anim';
}
$phones = (array)setting('contact.phones', []);
$schema = [
    '@context' => 'https://schema.org', '@type' => 'Hotel', 'name' => $siteName, 'url' => abs_url(url('')),
    'description' => tr(setting('seo.description')),
    'starRating' => ['@type' => 'Rating', 'ratingValue' => (int)setting('site.stars', 3)],
    'address' => ['@type' => 'PostalAddress', 'streetAddress' => tr(setting('contact.address')), 'addressLocality' => tr(setting('site.city')), 'addressCountry' => 'IR'],
    'geo' => ['@type' => 'GeoCoordinates', 'latitude' => (float)setting('map.lat'), 'longitude' => (float)setting('map.lng')],
    'telephone' => $phones[0] ?? '', 'email' => setting('contact.email'),
    'checkinTime' => setting('hotel.checkin'), 'checkoutTime' => setting('hotel.checkout'),
    'amenityFeature' => array_values(array_map(fn($a) => ['@type' => 'LocationFeatureSpecification', 'name' => tr($a['name']), 'value' => true], Content::amenities('hotel'))),
];
if ($ogImg) {
    $schema['image'] = abs_url(Media::src($ogImg, 1600));
}
?><!doctype html>
<html lang="<?= e($lang) ?>" dir="<?= e($dir) ?>" class="no-js">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($fullTitle) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<?php if (!empty($pg['noindex'])): ?><meta name="robots" content="noindex"><?php else: ?><link rel="canonical" href="<?= e($canonical) ?>">
<?php foreach (I18n::$enabled as $l): ?><link rel="alternate" hreflang="<?= e($l) ?>" href="<?= e(abs_url(url(App::$path, [], $l))) ?>">
<?php endforeach; ?><link rel="alternate" hreflang="x-default" href="<?= e(abs_url(url(App::$path, [], I18n::$default))) ?>">
<?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e($siteName) ?>">
<meta property="og:title" content="<?= e($fullTitle) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:locale" content="<?= e(I18n::meta()['locale']) ?>">
<?php if ($ogImg): ?><meta property="og:image" content="<?= e(abs_url(Media::src($ogImg, 1600))) ?>"><meta name="twitter:card" content="summary_large_image"><?php endif; ?>
<meta name="theme-color" content="<?= e(setting('theme.primary')) ?>">
<?php if ($lang === 'en'): ?><link rel="preload" href="<?= e(asset('fonts/jakarta-var.woff2')) ?>" as="font" type="font/woff2" crossorigin><?php else: ?><link rel="preload" href="<?= e(asset('fonts/vazirmatn-var.woff2')) ?>" as="font" type="font/woff2" crossorigin><?php endif; ?>
<link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
<style>:root{<?= $css ?>}</style>
<script>document.documentElement.classList.replace('no-js','js');</script>
<?php if ($favicon): ?><link rel="icon" href="<?= e(Media::src($favicon, 480, 'png')) ?>"><?php else: ?><link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml"><?php endif; ?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
<?= (string)setting('seo.head_code', '') ?>
</head>
<body class="<?= e($bodyClass) ?>" data-lang="<?= e($lang) ?>">
<a class="skip-link" href="#main"><?= e(__('common.skip')) ?></a>
<?= View::partial('front/partials/header') ?>
<main id="main" tabindex="-1">
<?= $content ?>
</main>
<?= View::partial('front/partials/footer') ?>
<div class="lightbox" data-lightbox hidden role="dialog" aria-modal="true" aria-label="<?= e(__('gallery.viewer')) ?>">
  <button class="lb-btn lb-close" data-lb-close aria-label="<?= e(__('common.close')) ?>"><?= icon('x') ?></button>
  <button class="lb-btn lb-prev" data-lb-prev aria-label="<?= e(__('common.prev')) ?>"><?= icon(I18n::isRtl() ? 'chevron-right' : 'chevron-left') ?></button>
  <figure class="lb-figure"><img data-lb-img alt=""><figcaption data-lb-cap></figcaption></figure>
  <button class="lb-btn lb-next" data-lb-next aria-label="<?= e(__('common.next')) ?>"><?= icon(I18n::isRtl() ? 'chevron-left' : 'chevron-right') ?></button>
  <div class="lb-count" data-lb-count></div>
</div>
<script>window.VENUS=<?= json_encode([
    'base' => App::$base,
    'lang' => $lang,
    'rtl' => I18n::isRtl(),
    'assets' => App::$base . '/assets/',
    'v' => VENUS_VERSION,
    'wa' => preg_replace('/\D/', '', (string)setting('contact.whatsapp', '')),
    'waText' => tr(setting('cta.whatsapp_text')),
    'digits' => I18n::meta()['digits'],
    'i18n' => [
        'from' => __('rooms.from'), 'forGuests' => __('rooms.price_for'), 'unfit' => __('rooms.unfit'),
        'guestsN' => __('rooms.guests_n'), 'empty' => Content::emptyPriceLabel(), 'loading' => __('common.loading'),
        'tourError' => __('tour.error'), 'withExtra' => __('rooms.with_extra'),
        'waKids' => __('rooms.wa_kids', ['age' => (int)setting('price.child_age', 5)]),
    ],
    'preloader' => (bool)setting('theme.preloader', true),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?>;</script>
<script src="<?= e(asset('js/site.js')) ?>" defer></script>
<?= (string)setting('seo.footer_code', '') ?>
</body>
</html>
