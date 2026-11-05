<?php /** @var ?array $row @var string $error */ $isNew = !$row || empty($row['id']); ?>
<form method="post" class="edit-form" autocomplete="off">
  <?= csrf_field() ?>
  <div class="page-head"><div><a class="back" href="<?= e(admin_url('users')) ?>"><?= icon(I18n::isRtl() ? 'arrow-right' : 'arrow-left') ?><?= e(__('admin.users')) ?></a><h1><?= e($isNew ? __('admin.add_user') : $row['username']) ?></h1></div></div>
  <?php if ($error): ?><div class="flash flash-err"><?= icon('circle-alert') ?><span><?= e($error) ?></span></div><?php endif; ?>
  <section class="card form-card"><div class="fields">
    <label class="fld fld--half"><span class="fld-label"><?= e(__('admin.username')) ?> *</span><input class="inp" name="username" value="<?= e($row['username'] ?? '') ?>" required dir="ltr"></label>
    <label class="fld fld--half"><span class="fld-label"><?= e(__('admin.f.name')) ?></span><input class="inp" name="name" value="<?= e($row['name'] ?? '') ?>"></label>
    <label class="fld fld--half"><span class="fld-label"><?= e(__('admin.f.email')) ?></span><input class="inp" type="email" name="email" value="<?= e($row['email'] ?? '') ?>" dir="ltr"></label>
    <label class="fld fld--half"><span class="fld-label"><?= e(__('admin.role')) ?></span><select class="inp" name="role"><option value="editor"<?= ($row['role'] ?? '') === 'editor' ? ' selected' : '' ?>><?= e(__('admin.role.editor')) ?></option><option value="admin"<?= ($row['role'] ?? '') === 'admin' ? ' selected' : '' ?>><?= e(__('admin.role.admin')) ?></option></select><span class="fld-help"><?= e(__('admin.roles_help')) ?></span></label>
    <label class="fld fld--half"><span class="fld-label"><?= e(__('admin.language')) ?></span><select class="inp" name="lang"><option value="fa">فارسی</option><option value="en"<?= ($row['lang'] ?? '') === 'en' ? ' selected' : '' ?>>English</option></select></label>
    <label class="fld fld--half"><span class="fld-label"><?= e(__('admin.password')) ?><?= $isNew ? ' *' : '' ?></span><input class="inp" type="password" name="password" autocomplete="new-password" dir="ltr" minlength="8"><span class="fld-help"><?= e(__($isNew ? 'admin.password_rule' : 'admin.password_keep')) ?></span></label>
  </div></section>
  <div class="savebar"><button class="btn btn-accent"><?= icon('save') ?><?= e(__('admin.save')) ?></button>
    <?php if (!$isNew): ?><span class="spacer"></span><button class="btn btn-danger" type="submit" form="del-user" data-confirm="<?= e(__('admin.confirm_delete')) ?>"><?= icon('trash-2') ?><?= e(__('admin.delete')) ?></button><?php endif; ?></div>
</form>
<?php if (!$isNew): ?><form id="del-user" method="post" action="<?= e(admin_url('users/' . $row['id'] . '/delete')) ?>"><?= csrf_field() ?></form><?php endif; ?>
