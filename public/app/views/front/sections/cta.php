<?php
/** @var array $d */
$phones = (array)setting('contact.phones', []);
$wa = whatsapp_link();
?>
<section class="section section-tight">
  <div class="container">
    <div class="cta-banner" data-reveal>
      <div class="cta-media" data-parallax><?= picture($d['image_id'] ?? null, ['sizes' => '(max-width: 1300px) 100vw, 1240px', 'alt' => '']) ?></div>
      <div class="cta-shade" aria-hidden="true"></div>
      <div class="cta-inner">
        <h2><?= e(tr($d['title'] ?? '')) ?></h2>
        <p><?= e(tr($d['body'] ?? '')) ?></p>
        <div class="cta-actions">
          <?php if (!empty($phones[0])): ?><a class="btn btn-accent btn-lg" href="tel:<?= e(tel($phones[0])) ?>"><?= icon('phone') ?><span dir="ltr"><?= e(digits($phones[0])) ?></span></a><?php endif; ?>
          <?php if ($wa !== '' && setting('cta.show_whatsapp', true)): ?><a class="btn btn-glass btn-lg" href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= icon('message-circle') ?><?= e(__('common.whatsapp')) ?></a><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
