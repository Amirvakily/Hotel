<?php
/** @var array $d */
$reviews = Content::reviews();
if (!$reviews) return;
$avg = array_sum(array_map(fn($r) => (int)$r['rating'], $reviews)) / max(1, count($reviews));
$rtl = I18n::isRtl();
?>
<section class="section reviews">
  <div class="container">
    <div class="reviews-head" data-reveal>
      <div class="section-head-text">
        <p class="eyebrow"><?= e(tr($d['kicker'] ?? '')) ?></p>
        <h2 class="section-title"><?= e(tr($d['title'] ?? '') ?: __('reviews.title')) ?></h2>
      </div>
      <div class="rating-summary">
        <b><?= e(num($avg, 1)) ?></b>
        <div><span class="review-stars"><?= str_repeat('★', (int)round($avg)) ?></span><small><?= e(__('reviews.count', ['n' => count($reviews)])) ?></small></div>
      </div>
    </div>
    <div class="carousel" data-carousel>
      <div class="carousel-track">
        <?php foreach ($reviews as $i => $r): $author = tr($r['author']); ?>
          <figure class="review" data-reveal style="--d:<?= $i % 3 ?>">
            <span class="review-stars" aria-label="<?= e(__('reviews.rating', ['n' => (int)$r['rating']])) ?>"><?= str_repeat('★', max(0, min(5, (int)$r['rating']))) ?><span class="dim"><?= str_repeat('★', 5 - max(0, min(5, (int)$r['rating']))) ?></span></span>
            <blockquote><?= e(tr($r['body'])) ?></blockquote>
            <figcaption>
              <span class="avatar" aria-hidden="true"><?= e(mb_substr($author, 0, 1)) ?></span>
              <span><b><?= e($author) ?></b><small><?= e(tr($r['origin'])) ?><?= $r['source'] ? ' · ' . e($r['source']) : '' ?></small></span>
            </figcaption>
          </figure>
        <?php endforeach; ?>
      </div>
      <div class="carousel-nav">
        <button type="button" class="icon-btn" data-carousel-prev aria-label="<?= e(__('common.prev')) ?>"><?= icon($rtl ? 'arrow-right' : 'arrow-left') ?></button>
        <button type="button" class="icon-btn" data-carousel-next aria-label="<?= e(__('common.next')) ?>"><?= icon($rtl ? 'arrow-left' : 'arrow-right') ?></button>
      </div>
    </div>
  </div>
</section>
