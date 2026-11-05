<?php
/** @var string $kind @var array $pg @var array $menu @var string $hours */
$title = tr($pg['title'] ?? '') ?: ($kind === 'cafe' ? (tr(setting('cafe.name')) ?: __('nav.cafe')) : __('nav.breakfast'));
$body = tr($pg['body'] ?? '');
$cover = $pg['cover_id'] ?? null;
$gallery = array_filter((array)($pg['gallery'] ?? []));
media_preload(array_merge([$cover], $gallery));
$isCafe = $kind === 'cafe';
?>
<?= View::partial('front/partials/page-hero', ['title' => $title, 'subtitle' => __($kind . '.subtitle'), 'kicker' => __($kind . '.kicker'), 'image' => $cover]) ?>
<section class="section dining-page">
  <div class="container dining-intro">
    <div class="prose" data-reveal><?= Sanitizer::html($body) ?></div>
    <aside class="info-card" data-reveal style="--d:1">
      <?php if (!$isCafe): ?><p class="pill pill-accent2 big"><?= icon('sparkles') ?><?= e(__('breakfast.free_badge')) ?></p><?php endif; ?>
      <h2><?= icon('clock') ?><?= e(__('dining.hours')) ?></h2>
      <p class="info-big"><?= e($hours) ?></p>
      <p class="muted"><?= e(__($kind . '.where')) ?></p>
      <?php if ($isCafe && Content::navEnabled('breakfast')): ?><a class="link-arrow" href="<?= e(url('breakfast')) ?>"><?= e(__('cafe.see_breakfast')) ?><?= icon(I18n::isRtl() ? 'arrow-left' : 'arrow-right') ?></a><?php endif; ?>
      <?php if (!$isCafe && Content::navEnabled('cafe')): ?><a class="link-arrow" href="<?= e(url('cafe')) ?>"><?= e(__('breakfast.see_cafe')) ?><?= icon(I18n::isRtl() ? 'arrow-left' : 'arrow-right') ?></a><?php endif; ?>
    </aside>
  </div>

  <?php if ($menu): ?>
  <div class="container">
    <div class="menu-head" data-reveal>
      <p class="kicker"><?= e(__($kind . '.menu_kicker')) ?></p>
      <h2 class="section-title"><?= e(__($kind . '.menu_title')) ?></h2>
    </div>
    <?php if (count($menu) > 1): ?>
    <nav class="chips menu-tabs" aria-label="<?= e(__($kind . '.menu_title')) ?>">
      <?php foreach ($menu as $c): ?><a class="chip" href="#menu-<?= (int)$c['id'] ?>"><?= $c['icon'] ? icon($c['icon']) : '' ?><?= e(tr($c['name'])) ?></a><?php endforeach; ?>
    </nav>
    <?php endif; ?>
    <div class="menu-grid">
      <?php foreach ($menu as $ci => $c): ?>
        <div class="menu-cat" id="menu-<?= (int)$c['id'] ?>" data-reveal style="--d:<?= $ci % 2 ?>">
          <h3 class="menu-cat-title"><?= $c['icon'] ? icon($c['icon']) : '' ?><?= e(tr($c['name'])) ?></h3>
          <?php if (tr($c['description']) !== ''): ?><p class="muted"><?= e(tr($c['description'])) ?></p><?php endif; ?>
          <ul class="menu-items">
            <?php foreach ($c['items'] as $it): $m = media($it['media_id']); ?>
              <li class="menu-item<?= $m ? ' has-img' : '' ?>">
                <?php if ($m): ?><span class="menu-thumb"><?= picture($m, ['sizes' => '72px', 'max' => 480, 'alt' => '']) ?></span><?php endif; ?>
                <div class="menu-text">
                  <div class="menu-line">
                    <span class="menu-name"><?= e(tr($it['name'])) ?><?php if (tr($it['tag']) !== ''): ?> <em class="tag"><?= e(tr($it['tag'])) ?></em><?php endif; ?></span>
                    <?php if ((int)$it['price'] > 0): ?><span class="menu-price"><?= e(money((int)$it['price'])) ?></span><?php endif; ?>
                  </div>
                  <?php if (tr($it['description']) !== ''): ?><p><?= e(tr($it['description'])) ?></p><?php endif; ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($gallery): ?>
  <div class="container section-sm">
    <div class="strip" data-gallery>
      <?php foreach ($gallery as $i => $mid): $m = media($mid); if (!$m) continue; ?>
        <a class="strip-item" href="<?= e(Media::src($m, 2400)) ?>" data-lb data-caption="<?= e($title) ?>" data-reveal style="--d:<?= $i % 4 ?>"><?= picture($m, ['sizes' => '(max-width: 700px) 70vw, 25vw', 'alt' => $title]) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</section>
<?= View::partial('front/partials/contact-band') ?>
