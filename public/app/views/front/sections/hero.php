<?php
/** @var array $d */
$slides = Content::slides();
$name = tr($d['title'] ?? '') ?: tr(setting('site.name'));
$sub = tr($d['subtitle'] ?? '') ?: tr(setting('site.tagline'));
$kicker = tr($d['kicker'] ?? '');
$maxG = max(1, Content::maxGuests());
$rtl = I18n::isRtl();
$stars = (int)setting('site.stars', 3);
?>
<section class="hero" data-hero aria-label="<?= e($name) ?>">
  <div class="hero-frame">
    <div class="hero-media" data-parallax>
      <?php foreach ($slides as $i => $s): ?>
        <div class="hero-slide<?= $i === 0 ? ' is-active' : '' ?>" data-slide data-caption="<?= e(tr($s['title'])) ?>">
          <?= picture($s['media_id'], ['sizes' => '100vw', 'eager' => $i === 0, 'alt' => tr($s['title']), 'class' => 'hero-img']) ?>
        </div>
      <?php endforeach; ?>
      <?php if (!$slides): ?><div class="hero-slide is-active hero-fallback"></div><?php endif; ?>
    </div>
    <div class="hero-shade" aria-hidden="true"></div>
    <div class="hero-content">
      <p class="hero-chips hero-anim" style="--d:0">
        <span class="chip-glass"><?= stars($stars) ?><?= e(__('common.stars', ['n' => $stars])) ?></span>
        <span class="chip-glass"><?= icon('map-pin') ?><?= e($kicker !== '' ? $kicker : tr(setting('site.city'))) ?></span>
      </p>
      <h1 class="hero-title"><span class="hero-line"><span class="hero-anim" style="--d:1"><?= e($name) ?></span></span></h1>
      <p class="hero-sub hero-anim" style="--d:2"><?= e($sub) ?></p>
      <?php if (Content::navEnabled('rooms')): ?>
      <form class="guest-bar hero-anim" style="--d:3" action="<?= e(url('rooms')) ?>" method="get" data-guest-bar>
        <span class="gb-label"><?= icon('users') ?><?= e(__('rooms.how_many')) ?></span>
        <span class="gb-seg" role="radiogroup" aria-label="<?= e(__('rooms.how_many')) ?>">
          <?php for ($g = 1; $g <= $maxG; $g++): ?>
            <button type="button" role="radio" aria-checked="<?= $g === min(3, $maxG) ? 'true' : 'false' ?>" data-guests="<?= $g ?>" aria-label="<?= e(__('rooms.n_people', ['n' => $g])) ?>"><?= e(num($g)) ?></button>
          <?php endfor; ?>
          <button type="button" role="radio" aria-checked="false" data-guests="<?= $maxG + 1 ?>" class="gb-more" aria-label="<?= e(__('rooms.group_option', ['n' => $maxG + 1])) ?>"><?= e(num($maxG + 1)) ?>+</button>
        </span>
        <input type="hidden" name="guests" value="<?= min(3, $maxG) ?>">
        <button class="btn btn-accent" type="submit"><?= e(__('hero.see_prices')) ?><?= icon($rtl ? 'arrow-left' : 'arrow-right') ?></button>
      </form>
      <?php if (setting('price.show_kids', true)): ?><p class="hero-note hero-anim" style="--d:4"><?= icon('baby') ?><?= e(__('rooms.kids_note', ['age' => (int)setting('price.child_age', 5)])) ?></p><?php endif; ?>
      <?php endif; ?>
    </div>
    <div class="hero-side">
      <?php if (count($slides) > 1): ?>
      <div class="hero-dots" role="tablist" aria-label="<?= e(__('hero.slides')) ?>">
        <?php foreach ($slides as $i => $s): ?>
          <button type="button" role="tab" class="hero-dot<?= $i === 0 ? ' is-active' : '' ?>" data-dot="<?= $i ?>" aria-label="<?= e(tr($s['title']) ?: (string)($i + 1)) ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"><i></i></button>
        <?php endforeach; ?>
      </div>
      <p class="hero-caption" data-hero-caption><?= e(tr($slides[0]['title'] ?? '')) ?></p>
      <?php endif; ?>
      <?php if (Content::navEnabled('tour') && Content::scenes()): ?>
        <a class="hero-360" href="<?= e(url('tour')) ?>"><span><?= icon('rotate-3d') ?></span><?= e(__('hero.tour')) ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php if (setting('theme.marquee', true)):
    $items = [];
    foreach (Content::amenities() as $a) {
        if ($a['highlight']) {
            $items[] = [$a['icon'] ?: 'sparkles', tr($a['name'])];
        }
    }
    if ($items): ?>
<div class="marquee" aria-hidden="true">
  <div class="marquee-track">
    <?php for ($k = 0; $k < 2; $k++): foreach ($items as [$ic, $label]): ?><span class="marquee-item"><?= icon($ic) ?><?= e($label) ?></span><?php endforeach; endfor; ?>
  </div>
</div>
<?php endif; endif; ?>
