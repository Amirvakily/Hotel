<?php
/** @var string $content */
$me = Auth::user();
$unread = (int)DB::val('SELECT COUNT(*) FROM messages WHERE is_read = 0');
$path = App::$path;
$is = fn(string $p) => $path === 'admin/' . $p || str_starts_with($path, 'admin/' . $p . '/') || ($p === '' && $path === 'admin');
$groups = [
    ['', [['', 'layout-dashboard', 'admin.dashboard']]],
    ['admin.g.stay', [['r/rooms', 'bed-double', 'admin.rooms'], ['prices', 'wallet', 'admin.prices'], ['r/amenities', 'sparkles', 'admin.amenities']]],
    ['admin.g.home', [['sections', 'layout-grid', 'admin.sections'], ['r/slides', 'images', 'admin.slides']]],
    ['admin.g.media', [['r/gallery_items', 'image', 'admin.gallery'], ['r/scenes', 'rotate-3d', 'admin.tour'], ['media', 'archive', 'admin.media']]],
    ['admin.g.food', [['r/menu_items', 'coffee', 'admin.menu_items'], ['r/menu_categories', 'list', 'admin.menu_categories']]],
    ['admin.g.site', [['r/attractions', 'landmark', 'admin.attractions'], ['r/reviews', 'quote', 'admin.reviews'], ['r/faqs', 'circle-help', 'admin.faqs'], ['r/pages', 'file-text', 'admin.pages'], ['messages', 'message-square', 'admin.messages']]],
];
if (Auth::isAdmin()) {
    $groups[] = ['admin.g.settings', [['settings', 'settings', 'admin.settings'], ['translations', 'languages', 'admin.translations'], ['users', 'users', 'admin.users'], ['backup', 'database', 'admin.backup']]];
}
$flash = flash();
?><!doctype html>
<html lang="<?= e(I18n::$lang) ?>" dir="<?= e(I18n::dir()) ?>">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
<title><?= e(__('admin.panel')) ?> · <?= e(tr(setting('site.name'))) ?></title>
<link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('admin/admin.css')) ?>">
</head>
<body class="admin">
<aside class="sb" id="sb" data-sb>
  <a class="sb-brand" href="<?= e(admin_url()) ?>">
    <span class="sb-mark"><svg viewBox="0 0 40 40"><rect x="10" y="10" width="20" height="20" rx="3"/><rect x="10" y="10" width="20" height="20" rx="3" transform="rotate(45 20 20)"/><circle cx="20" cy="20" r="4.2"/></svg></span>
    <span><b><?= e(tr(setting('site.name'))) ?></b><small><?= e(__('admin.panel')) ?></small></span>
  </a>
  <nav class="sb-nav">
    <?php foreach ($groups as [$g, $items]): ?>
      <?php if ($g): ?><p class="sb-group"><?= e(__($g)) ?></p><?php endif; ?>
      <?php foreach ($items as [$p, $ic, $label]): ?>
        <a href="<?= e(admin_url($p)) ?>"<?= $is($p) ? ' aria-current="page"' : '' ?>><?= icon($ic) ?><span><?= e(__($label)) ?></span><?php if ($p === 'messages' && $unread): ?><b class="badge"><?= num($unread) ?></b><?php endif; ?></a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </nav>
  <div class="sb-foot">
    <a href="<?= e(url('', [], I18n::$default)) ?>" target="_blank" rel="noopener"><?= icon('external-link') ?><span><?= e(__('admin.view_site')) ?></span></a>
  </div>
</aside>
<div class="sb-backdrop" data-sb-close></div>
<div class="main">
  <header class="topbar">
    <button class="icon-btn sb-toggle" type="button" data-sb-open aria-label="menu"><?= icon('menu') ?></button>
    <div class="topbar-title" data-page-title></div>
    <div class="topbar-actions">
      <form method="post" action="<?= e(admin_url('cache/clear')) ?>"><?= csrf_field() ?><button class="btn btn-sm btn-ghost" title="<?= e(__('admin.clear_cache')) ?>"><?= icon('refresh-cw') ?><span class="hide-sm"><?= e(__('admin.clear_cache')) ?></span></button></form>
      <a class="btn btn-sm btn-ghost" href="<?= e(admin_url('account')) ?>"><?= icon('user') ?><span class="hide-sm"><?= e($me['name'] ?: $me['username']) ?></span></a>
      <form method="post" action="<?= e(admin_url('logout')) ?>"><?= csrf_field() ?><button class="btn btn-sm btn-ghost" title="<?= e(__('admin.logout')) ?>"><?= icon('log-out') ?></button></form>
    </div>
  </header>
  <?php if ($flash): ?><div class="flashes"><?php foreach ($flash as $f): ?><div class="flash flash-<?= e($f['type']) ?>" role="status"><?= icon($f['type'] === 'err' ? 'circle-alert' : 'circle-check') ?><span><?= e($f['msg']) ?></span><button type="button" data-dismiss aria-label="close"><?= icon('x') ?></button></div><?php endforeach; ?></div><?php endif; ?>
  <main class="content"><?= $content ?></main>
</div>
<?= View::partial('admin/partials/media-modal') ?>
<script>window.ADMIN=<?= json_encode([
    'csrf' => csrf_token(), 'base' => App::$base, 'assets' => App::$base . '/assets/', 'rtl' => I18n::isRtl(), 'lang' => I18n::$lang,
    'url' => ['mediaList' => admin_url('media/list'), 'upload' => admin_url('media/upload'), 'pano' => admin_url('media/pano'), 'uploads' => App::$base . '/uploads/'],
    'langs' => array_map(fn($l) => ['code' => $l, 'name' => I18n::LANGS[$l]['name'], 'dir' => I18n::LANGS[$l]['dir']], AdminForm::langs()),
    'maxUpload' => ini_get('upload_max_filesize'),
    't' => [
        'confirmDelete' => __('admin.confirm_delete'), 'uploading' => __('admin.uploading'), 'uploadFailed' => __('admin.upload_failed'),
        'select' => __('admin.select'), 'noMedia' => __('admin.no_media'), 'loadMore' => __('admin.load_more'), 'saved' => __('admin.saved'),
        'hsTarget' => __('admin.hs.target'), 'hsText' => __('admin.hs.text'), 'hsScene' => __('admin.hs.scene'), 'hsInfo' => __('admin.hs.info'),
        'hsLook' => __('admin.hs.look'), 'hsRemove' => __('admin.hs.remove'), 'hsViewSet' => __('admin.hs.view_set'), 'panoCheck' => __('admin.pano_check'),
        'panoFull' => __('admin.pano_full'), 'panoPartial' => __('admin.pano_partial'), 'linkBad' => __('admin.link_bad'), 'unsaved' => __('admin.unsaved'),
        'beds' => array_combine(bed_types(), array_map(fn($t) => __('bed.' . $t), bed_types())), 'preparing' => __('admin.preparing'),
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?>;</script>
<script src="<?= e(asset('vendor/sortable/Sortable.min.js')) ?>" defer></script>
<script src="<?= e(asset('admin/admin.js')) ?>" defer></script>
</body>
</html>
