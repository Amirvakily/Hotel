<?php
/** @var array $d */
$faqs = array_slice(Content::faqs(), 0, (int)($d['limit'] ?? 6));
if (!$faqs) return;
$phones = (array)setting('contact.phones', []);
?>
<section class="section faq-home">
  <div class="container faq-grid">
    <div class="faq-side">
      <?= View::partial('front/partials/section-head', ['kicker' => tr($d['kicker'] ?? ''), 'title' => tr($d['title'] ?? '') ?: __('faq.title'), 'subtitle' => tr($d['subtitle'] ?? '')]) ?>
      <?php if (!empty($phones[0])): ?>
      <div class="help-card" data-reveal>
        <span class="tile-icon"><?= icon('headset') ?></span>
        <div><b><?= e(__('faq.still')) ?></b><a href="tel:<?= e(tel($phones[0])) ?>" dir="ltr"><?= e(digits($phones[0])) ?></a></div>
      </div>
      <?php endif; ?>
    </div>
    <?= View::partial('front/partials/faq-list', ['faqs' => $faqs]) ?>
  </div>
</section>
