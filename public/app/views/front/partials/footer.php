<?php
$nav = Content::nav();
$phones = (array)setting('contact.phones', []);
$email = (string)setting('contact.email', '');
$socials = [];
foreach (['instagram' => 'instagram', 'telegram' => 'send', 'whatsapp' => 'message-circle', 'bale' => 'message-square', 'eitaa' => 'message-square', 'aparat' => 'play', 'youtube' => 'play'] as $net => $ic) {
    $u = social_url($net, (string)setting('social.' . $net, ''));
    if ($u !== '') {
        $socials[] = ['net' => $net, 'url' => $u, 'icon' => $ic, 'label' => __('social.' . $net)];
    }
}
$year = (int)date('Y');
$yearShown = I18n::$lang === 'fa' ? Jalali::fromGregorian($year, (int)date('n'), (int)date('j'))[0] : $year;
$wa = whatsapp_link();
$name = tr(setting('site.name'));
?>
<footer class="site-footer">
  <div class="container">
    <div class="footer-cta" data-reveal>
      <h2><?= e(__('footer.cta')) ?></h2>
      <div class="footer-cta-actions">
        <?php if (!empty($phones[0])): ?><a class="btn btn-accent btn-lg" href="tel:<?= e(tel($phones[0])) ?>"><?= icon('phone') ?><span dir="ltr"><?= e(digits($phones[0])) ?></span></a><?php endif; ?>
        <?php if ($wa !== '' && setting('cta.show_whatsapp', true)): ?><a class="btn btn-light btn-lg" href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= icon('message-circle') ?><?= e(__('common.whatsapp')) ?></a><?php endif; ?>
      </div>
    </div>
    <div class="footer-grid">
      <div class="footer-brand">
        <a class="brand brand--light" href="<?= e(url('')) ?>"><?= View::partial('front/partials/logo', ['light' => true]) ?></a>
        <p><?= e(tr(setting('site.tagline'))) ?></p>
        <p class="footer-stars"><?= stars((int)setting('site.stars', 3)) ?><span><?= e(__('common.stars', ['n' => (int)setting('site.stars', 3)])) ?></span></p>
        <?php if ($socials): ?>
        <ul class="socials">
          <?php foreach ($socials as $s): ?>
            <li><a href="<?= e($s['url']) ?>" target="_blank" rel="noopener" aria-label="<?= e($s['label']) ?>" title="<?= e($s['label']) ?>"><?= $s['net'] === 'instagram' ? '<svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="17.3" cy="6.7" r="1.1" fill="currentColor"/></svg>' : icon($s['icon']) ?></a></li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>
      <div>
        <h2 class="footer-title"><?= e(__('footer.links')) ?></h2>
        <ul class="footer-links">
          <?php foreach ($nav as $item): ?><li><a href="<?= e($item['url']) ?>"><?= e($item['label']) ?></a></li><?php endforeach; ?>
          <?php foreach (Content::pages('footer') as $p): ?><li><a href="<?= e(url('page/' . $p['slug'])) ?>"><?= e(tr($p['title'])) ?></a></li><?php endforeach; ?>
        </ul>
      </div>
      <div>
        <h2 class="footer-title"><?= e(__('footer.contact')) ?></h2>
        <ul class="footer-contact">
          <li><?= icon('map-pin') ?><span><?= e(tr(setting('contact.address'))) ?></span></li>
          <?php foreach ($phones as $ph): ?><li><?= icon('phone') ?><a href="tel:<?= e(tel($ph)) ?>" dir="ltr"><?= e(digits($ph)) ?></a></li><?php endforeach; ?>
          <?php if ($email !== ''): ?><li><?= icon('mail') ?><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></li><?php endif; ?>
        </ul>
      </div>
      <div>
        <h2 class="footer-title"><?= e(__('footer.hours')) ?></h2>
        <ul class="footer-hours">
          <li><span><?= e(__('about.checkin')) ?></span><b><?= e(digits(setting('hotel.checkin'))) ?></b></li>
          <li><span><?= e(__('about.checkout')) ?></span><b><?= e(digits(setting('hotel.checkout'))) ?></b></li>
          <li><span><?= e(__('nav.breakfast')) ?></span><b><?= e(tr(setting('hotel.breakfast_hours'))) ?></b></li>
          <li><span><?= e(tr(setting('cafe.name')) ?: __('nav.cafe')) ?></span><b><?= e(tr(setting('hotel.cafe_hours'))) ?></b></li>
        </ul>
      </div>
    </div>
    <p class="footer-word" aria-hidden="true"><?= e(I18n::$lang === 'en' ? 'VENUS' : $name) ?></p>
    <div class="footer-bottom">
      <p>© <?= e(digits((string)$yearShown)) ?> <?= e($name) ?> · <?= e(__('footer.rights')) ?></p>
      <?php if (count(I18n::$enabled) > 1): ?><p class="footer-langs"><?php foreach (I18n::$enabled as $l): ?><a href="<?= e(url(App::$path, [], $l)) ?>" lang="<?= e($l) ?>"<?= $l === I18n::$lang ? ' aria-current="true"' : '' ?>><?= e(I18n::LANGS[$l]['name']) ?></a><?php endforeach; ?></p><?php endif; ?>
    </div>
  </div>
</footer>
<nav class="dock" aria-label="<?= e(__('common.quick_contact')) ?>">
  <?php if (!empty($phones[0])): ?><a href="tel:<?= e(tel($phones[0])) ?>"><?= icon('phone') ?><span><?= e(__('common.call')) ?></span></a><?php endif; ?>
  <?php if ($wa !== '' && setting('cta.show_whatsapp', true)): ?><a href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= icon('message-circle') ?><span><?= e(__('common.whatsapp')) ?></span></a><?php endif; ?>
  <?php if (Content::navEnabled('rooms')): ?><a href="<?= e(url('rooms')) ?>"><?= icon('bed-double') ?><span><?= e(__('nav.rooms')) ?></span></a><?php endif; ?>
  <?php if (Content::navEnabled('contact')): ?><a href="<?= e(url('contact')) ?>"><?= icon('map-pin') ?><span><?= e(__('common.location')) ?></span></a><?php endif; ?>
</nav>
