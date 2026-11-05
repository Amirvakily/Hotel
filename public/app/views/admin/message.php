<?php /** @var array $m */
$wa = $m['phone'] ? 'https://wa.me/' . preg_replace('~^0~', '98', preg_replace('~\D~', '', strtr($m['phone'], ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9']))) : ''; ?>
<div class="page-head">
  <div><a class="back" href="<?= e(admin_url('messages')) ?>"><?= icon(I18n::isRtl() ? 'arrow-right' : 'arrow-left') ?><?= e(__('admin.messages')) ?></a><h1><?= e($m['subject'] ?: __('admin.message_from', ['name' => $m['name']])) ?></h1></div>
  <div class="page-actions">
    <form method="post" action="<?= e(admin_url('messages/' . $m['id'] . '/unread')) ?>"><?= csrf_field() ?><button class="btn btn-ghost"><?= icon('eye-off') ?><?= e(__('admin.mark_unread')) ?></button></form>
    <form method="post" action="<?= e(admin_url('messages/' . $m['id'] . '/delete')) ?>"><?= csrf_field() ?><button class="btn btn-danger" data-confirm="<?= e(__('admin.confirm_delete')) ?>"><?= icon('trash-2') ?><?= e(__('admin.delete')) ?></button></form>
  </div>
</div>
<div class="grid-2 grid-2--wide">
  <section class="card"><div class="msg-body" dir="auto"><?= nl2br(e($m['body'])) ?></div></section>
  <section class="card">
    <dl class="dl">
      <dt><?= e(__('contact.name')) ?></dt><dd><?= e($m['name']) ?></dd>
      <?php if ($m['phone']): ?><dt><?= e(__('contact.phone')) ?></dt><dd><a href="tel:<?= e(tel($m['phone'])) ?>" dir="ltr"><?= e($m['phone']) ?></a></dd><?php endif; ?>
      <?php if ($m['email']): ?><dt><?= e(__('contact.email')) ?></dt><dd><a href="mailto:<?= e($m['email']) ?>" dir="ltr"><?= e($m['email']) ?></a></dd><?php endif; ?>
      <dt><?= e(__('admin.date')) ?></dt><dd><?= e(local_date($m['created_at'], true)) ?></dd>
      <dt><?= e(__('admin.language')) ?></dt><dd><?= e(I18n::LANGS[$m['lang']]['name'] ?? $m['lang']) ?></dd>
    </dl>
    <div class="row-inline">
      <?php if ($m['phone']): ?><a class="btn btn-accent" href="tel:<?= e(tel($m['phone'])) ?>"><?= icon('phone') ?><?= e(__('common.call')) ?></a><a class="btn" href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= icon('message-circle') ?><?= e(__('common.whatsapp')) ?></a><?php endif; ?>
      <?php if ($m['email']): ?><a class="btn" href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode('Re: ' . ($m['subject'] ?: tr(setting('site.name')))) ?>"><?= icon('mail') ?><?= e(__('admin.reply_email')) ?></a><?php endif; ?>
    </div>
  </section>
</div>
