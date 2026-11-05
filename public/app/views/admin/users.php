<?php /** @var array $rows */ ?>
<div class="page-head">
  <div><h1><?= icon('users') ?><?= e(__('admin.users')) ?></h1><p class="muted"><?= e(__('admin.users_help')) ?></p></div>
  <div class="page-actions"><a class="btn btn-accent" href="<?= e(admin_url('users/new')) ?>"><?= icon('plus') ?><?= e(__('admin.add_user')) ?></a></div>
</div>
<div class="card card-flush"><div class="table-wrap"><table class="tbl">
  <thead><tr><th><?= e(__('admin.username')) ?></th><th><?= e(__('admin.f.name')) ?></th><th><?= e(__('admin.role')) ?></th><th><?= e(__('admin.last_login')) ?></th><th></th></tr></thead>
  <tbody><?php foreach ($rows as $u): ?><tr>
    <td data-label="<?= e(__('admin.username')) ?>"><code dir="ltr"><?= e($u['username']) ?></code></td><td data-label="<?= e(__('admin.f.name')) ?>"><?= e($u['name']) ?></td>
    <td data-label="<?= e(__('admin.role')) ?>"><span class="pill"><?= e(__('admin.role.' . $u['role'])) ?></span></td><td data-label="<?= e(__('admin.last_login')) ?>"><?= e(local_date($u['last_login'], true) ?: '—') ?></td>
    <td class="w-actions"><a class="btn btn-sm" href="<?= e(admin_url('users/' . $u['id'])) ?>"><?= icon('pencil') ?><span><?= e(__('admin.edit')) ?></span></a></td>
  </tr><?php endforeach; ?></tbody>
</table></div></div>
