<?php
$phones = (array)setting('contact.phones', []);
$wa = whatsapp_link();
$tg = telegram_link();
?>
<section class="section section-tight">
  <div class="container">
    <div class="contact-band" data-reveal>
      <div>
        <h2><?= e(__('band.title')) ?></h2>
        <p><?= e(__('band.text')) ?></p>
      </div>
      <div class="contact-band-actions">
        <?php if (!empty($phones[0])): ?><a class="btn btn-accent" href="tel:<?= e(tel($phones[0])) ?>"><?= icon('phone') ?><span dir="ltr"><?= e(digits($phones[0])) ?></span></a><?php endif; ?>
        <?php if ($wa !== '' && setting('cta.show_whatsapp', true)): ?><a class="btn btn-light" href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= icon('message-circle') ?><?= e(__('common.whatsapp')) ?></a><?php endif; ?>
        <?php if ($tg !== '' && setting('cta.show_telegram', true)): ?><a class="btn btn-glass" href="<?= e($tg) ?>" target="_blank" rel="noopener"><?= icon('send') ?><?= e(__('common.telegram')) ?></a><?php endif; ?>
      </div>
    </div>
  </div>
</section>
