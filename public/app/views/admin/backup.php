<?php /** @var array $files @var bool $zip */ ?>
<div class="page-head"><div><h1><?= icon('database') ?><?= e(__('admin.backup')) ?></h1><p class="muted"><?= e(__('admin.backup_help')) ?></p></div>
  <div class="page-actions"><form method="post" action="<?= e(admin_url('backup/create')) ?>"><?= csrf_field() ?><button class="btn btn-accent"<?= $zip ? '' : ' disabled' ?>><?= icon('download') ?><?= e(__('admin.backup_create')) ?></button></form></div></div>
<?php if (!$zip): ?><div class="flash flash-err"><?= icon('circle-alert') ?><span><?= e(__('admin.backup_nozip')) ?></span></div><?php endif; ?>
<section class="card">
  <h2 class="card-title"><?= e(__('admin.backup_files')) ?></h2>
  <?php if (!$files): ?><p class="muted"><?= e(__('admin.backup_none')) ?></p><?php endif; ?>
  <ul class="file-list">
    <?php foreach ($files as $f): ?>
      <li><?= icon('archive') ?><div><b dir="ltr"><?= e($f['name']) ?></b><small class="muted"><?= e(local_date(date('Y-m-d H:i:s', $f['time']), true)) ?> · <?= e(num(round($f['size'] / 1048576, 1), 1)) ?> MB</small></div>
        <a class="btn btn-sm" href="<?= e(admin_url('backup/download/' . $f['name'])) ?>"><?= icon('download') ?><span><?= e(__('admin.download')) ?></span></a>
        <form method="post" action="<?= e(admin_url('backup/delete/' . $f['name'])) ?>"><?= csrf_field() ?><button class="btn btn-sm btn-ghost" data-confirm="<?= e(__('admin.confirm_delete')) ?>"><?= icon('trash-2') ?></button></form></li>
    <?php endforeach; ?>
  </ul>
</section>
<section class="card">
  <h2 class="card-title"><?= icon('upload') ?><?= e(__('admin.backup_restore')) ?></h2>
  <p class="muted"><?= e(__('admin.backup_restore_help')) ?></p>
  <form method="post" action="<?= e(admin_url('backup/restore')) ?>" enctype="multipart/form-data" class="row-inline">
    <?= csrf_field() ?><input class="inp" type="file" name="backup" accept=".zip,application/zip" required>
    <button class="btn btn-danger" data-confirm="<?= e(__('admin.confirm_restore')) ?>"<?= $zip ? '' : ' disabled' ?>><?= icon('refresh-cw') ?><?= e(__('admin.restore')) ?></button>
  </form>
</section>
