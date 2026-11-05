<?php
$logo = media(setting(!empty($light) ? 'site.logo_light' : 'site.logo')) ?: media(setting('site.logo'));
$name = tr(setting('site.name'));
?>
<?php if ($logo): ?>
  <img class="brand-img" src="<?= e(Media::src($logo, 480, $logo['ext'])) ?>" alt="<?= e($name) ?>" width="<?= (int)$logo['width'] ?>" height="<?= (int)$logo['height'] ?>">
<?php else: ?>
  <span class="brand-mark" aria-hidden="true">
    <svg viewBox="0 0 40 40" focusable="false"><rect x="10" y="10" width="20" height="20" rx="3"/><rect x="10" y="10" width="20" height="20" rx="3" transform="rotate(45 20 20)"/><circle cx="20" cy="20" r="4.2" class="dot"/></svg>
  </span>
  <span class="brand-text">
    <span class="brand-name"><?= e($name) ?></span>
    <span class="brand-sub"><?= e(I18n::$lang === 'en' ? 'Hotel · ' . tr(setting('site.city')) : tr(setting('site.city'))) ?></span>
  </span>
<?php endif; ?>
