<?php
$nav = Content::nav();
$current = App::$path;
$over = !empty(App::$page['home']);
$phones = (array)setting('contact.phones', []);
$phone = $phones[0] ?? '';
$isActive = function (string $path) use ($current): bool {
    if ($path === '') {
        return $current === '';
    }
    return $current === $path || str_starts_with($current, $path . '/');
};
?>
<header class="site-header<?= $over ? ' is-over' : '' ?>" data-header>
  <div class="header-bar">
    <a class="brand" href="<?= e(url('')) ?>">
      <?= View::partial('front/partials/logo') ?>
    </a>
    <nav class="main-nav" aria-label="<?= e(__('nav.label')) ?>">
      <ul>
        <?php foreach ($nav as $item): if ($item['key'] === 'home') continue; ?>
          <li><a href="<?= e($item['url']) ?>"<?= $isActive($item['path']) ? ' aria-current="page"' : '' ?>><?= e($item['label']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <div class="header-actions">
      <?php if (count(I18n::$enabled) > 1): ?>
      <div class="lang-switch" data-dropdown>
        <button class="icon-btn lang-btn" type="button" aria-haspopup="true" aria-expanded="false" data-dropdown-btn>
          <?= icon('globe') ?><span class="sr-only"><?= e(__('common.language')) ?> </span><span><?= e(I18n::meta()['short']) ?></span>
        </button>
        <ul class="lang-menu" data-dropdown-menu hidden>
          <?php foreach (I18n::$enabled as $l): ?>
            <li><a href="<?= e(url($current, array_intersect_key($_GET, array_flip(['cat', 'scene'])), $l)) ?>" hreflang="<?= e($l) ?>" lang="<?= e($l) ?>"<?= $l === I18n::$lang ? ' aria-current="true"' : '' ?>><?= e(I18n::LANGS[$l]['name']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
      <?php if ($phone !== '' && setting('cta.show_call', true)): ?>
        <a class="btn btn-sm btn-accent header-call" href="tel:<?= e(tel($phone)) ?>"><?= icon('phone') ?><span><?= e(__('common.call_us')) ?></span></a>
      <?php endif; ?>
      <button class="icon-btn burger" type="button" aria-expanded="false" aria-controls="mobile-menu" data-burger>
        <span class="sr-only"><?= e(__('nav.menu')) ?></span><span class="burger-lines" aria-hidden="true"><i></i><i></i></span>
      </button>
    </div>
  </div>
</header>
<div class="mobile-menu" id="mobile-menu" data-mobile-menu hidden>
  <div class="mobile-menu-inner">
    <nav aria-label="<?= e(__('nav.label')) ?>">
      <ul>
        <?php foreach ($nav as $i => $item): ?>
          <li style="--i:<?= $i ?>"><a href="<?= e($item['url']) ?>"<?= $isActive($item['path']) ? ' aria-current="page"' : '' ?>><span><?= e($item['label']) ?></span><?= icon(I18n::isRtl() ? 'arrow-left' : 'arrow-right') ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <div class="mobile-menu-foot">
      <?php if ($phone !== ''): ?><a class="btn btn-accent btn-lg" href="tel:<?= e(tel($phone)) ?>"><?= icon('phone') ?><span dir="ltr"><?= e(digits($phone)) ?></span></a><?php endif; ?>
      <?php if (count(I18n::$enabled) > 1): ?>
      <div class="mobile-langs">
        <?php foreach (I18n::$enabled as $l): ?>
          <a href="<?= e(url($current, [], $l)) ?>" lang="<?= e($l) ?>"<?= $l === I18n::$lang ? ' aria-current="true"' : '' ?>><?= e(I18n::LANGS[$l]['name']) ?></a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
