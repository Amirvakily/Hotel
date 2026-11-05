<?php /** @var array $rows */ ?>
<div class="page-head">
  <div><h1><?= icon('message-square') ?><?= e(__('admin.messages')) ?></h1><p class="muted"><?= e(__('admin.count_items', ['n' => count($rows)])) ?></p></div>
  <div class="page-actions">
    <form method="post" action="<?= e(admin_url('messages/read-all')) ?>"><?= csrf_field() ?><button class="btn"><?= icon('check') ?><?= e(__('admin.mark_all_read')) ?></button></form>
    <a class="btn" href="<?= e(admin_url('messages/export')) ?>"><?= icon('download') ?><?= e(__('admin.export_csv')) ?></a>
  </div>
</div>
<div class="card card-flush">
  <?php if (!$rows): ?><div class="empty-state"><?= icon('message-square') ?><p><?= e(__('admin.no_messages')) ?></p></div><?php else: ?>
  <ul class="msg-list msg-list--full">
    <?php foreach ($rows as $m): ?>
      <li class="<?= $m['is_read'] ? '' : 'is-unread' ?>"><a href="<?= e(admin_url('messages/' . $m['id'])) ?>">
        <b><?= e($m['name']) ?><?php if ($m['subject']): ?> · <?= e($m['subject']) ?><?php endif; ?></b>
        <span><?= e(str_limit($m['body'], 140)) ?></span>
        <small><?= e(local_date($m['created_at'], true)) ?> · <?= e(I18n::LANGS[$m['lang']]['name'] ?? $m['lang']) ?><?= $m['phone'] ? ' · <bdi dir="ltr">' . e($m['phone']) . '</bdi>' : '' ?></small>
      </a></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</div>
