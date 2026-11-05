<?php
/** @var array $errors @var array $old @var bool $sent @var int $ts @var string $sig */
$phones = (array)setting('contact.phones', []);
$email = (string)setting('contact.email', '');
$wa = whatsapp_link();
$tg = telegram_link();
$v = fn($k) => e($old[$k] ?? '');
$err = fn($k) => isset($errors[$k]) ? '<p class="field-error" id="err-' . $k . '">' . e($errors[$k]) . '</p>' : '';
$inv = fn($k) => isset($errors[$k]) ? ' aria-invalid="true" aria-describedby="err-' . $k . '"' : '';
?>
<?= View::partial('front/partials/page-hero', ['title' => __('contact.title'), 'subtitle' => __('contact.subtitle'), 'kicker' => __('contact.kicker')]) ?>
<section class="section contact-page">
  <div class="container contact-grid">
    <div class="contact-cards">
      <?php foreach ($phones as $i => $ph): ?>
        <a class="contact-card" href="tel:<?= e(tel($ph)) ?>" data-reveal style="--d:<?= $i ?>"><?= icon('phone') ?><div><small><?= e(__('contact.phone_label')) ?></small><b dir="ltr"><?= e(digits($ph)) ?></b></div></a>
      <?php endforeach; ?>
      <?php if ($wa !== ''): ?><a class="contact-card" href="<?= e($wa) ?>" target="_blank" rel="noopener" data-reveal><?= icon('message-circle') ?><div><small><?= e(__('common.whatsapp')) ?></small><b dir="ltr"><?= e(digits('+' . preg_replace('/\D/', '', (string)setting('contact.whatsapp')))) ?></b></div></a><?php endif; ?>
      <?php if ($tg !== ''): ?><a class="contact-card" href="<?= e($tg) ?>" target="_blank" rel="noopener" data-reveal><?= icon('send') ?><div><small><?= e(__('common.telegram')) ?></small><b dir="ltr">@<?= e(ltrim((string)setting('contact.telegram'), '@')) ?></b></div></a><?php endif; ?>
      <?php if ($email !== ''): ?><a class="contact-card" href="mailto:<?= e($email) ?>" data-reveal><?= icon('mail') ?><div><small><?= e(__('contact.email')) ?></small><b><?= e($email) ?></b></div></a><?php endif; ?>
      <div class="contact-card static" data-reveal><?= icon('map-pin') ?><div><small><?= e(__('contact.address')) ?></small><b><?= e(tr(setting('contact.address'))) ?></b></div></div>
    </div>

    <?php if (setting('form.enabled', true)): ?>
    <div class="form-card" id="form" data-reveal style="--d:1">
      <h2><?= e(__('contact.form_title')) ?></h2>
      <p class="muted"><?= e(__('contact.form_sub')) ?></p>
      <?php if ($sent): ?>
        <div class="alert alert-ok" role="status"><?= icon('circle-check') ?><?= e(__('contact.sent')) ?></div>
      <?php endif; ?>
      <?php if (isset($errors['form'])): ?><div class="alert alert-err" role="alert"><?= icon('circle-alert') ?><?= e($errors['form']) ?></div><?php endif; ?>
      <form method="post" action="<?= e(url('contact')) ?>#form" class="form" novalidate data-contact-form>
        <input type="hidden" name="ts" value="<?= (int)$ts ?>"><input type="hidden" name="sig" value="<?= e($sig) ?>">
        <div class="hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
        <div class="field"><label for="f-name"><?= e(__('contact.name')) ?> *</label><input id="f-name" name="name" required minlength="2" maxlength="100" autocomplete="name" value="<?= $v('name') ?>"<?= $inv('name') ?>><?= $err('name') ?></div>
        <div class="field-row">
          <div class="field"><label for="f-phone"><?= e(__('contact.phone')) ?></label><input id="f-phone" name="phone" type="tel" inputmode="tel" maxlength="20" autocomplete="tel" dir="ltr" value="<?= $v('phone') ?>"<?= $inv('phone') ?>><?= $err('phone') ?></div>
          <div class="field"><label for="f-email"><?= e(__('contact.email')) ?></label><input id="f-email" name="email" type="email" maxlength="150" autocomplete="email" dir="ltr" value="<?= $v('email') ?>"<?= $inv('email') ?>><?= $err('email') ?></div>
        </div>
        <div class="field"><label for="f-subject"><?= e(__('contact.subject')) ?></label><input id="f-subject" name="subject" maxlength="150" value="<?= $v('subject') ?>"></div>
        <div class="field"><label for="f-body"><?= e(__('contact.message')) ?> *</label><textarea id="f-body" name="body" rows="5" required minlength="5" maxlength="3000"<?= $inv('body') ?>><?= $v('body') ?></textarea><?= $err('body') ?></div>
        <button class="btn btn-accent btn-lg" type="submit"><?= icon('send') ?><?= e(__('contact.send')) ?></button>
      </form>
    </div>
    <?php endif; ?>
  </div>
</section>
<section class="section section-alt">
  <div class="container">
    <?= View::partial('front/partials/section-head', ['kicker' => '', 'title' => __('contact.find_us')]) ?>
    <?= View::partial('front/partials/location') ?>
  </div>
</section>
