<?php /** @var array $me @var string $error */ ?>
<form method="post" class="edit-form" autocomplete="off">
  <?= csrf_field() ?>
  <div class="page-head"><div><h1><?= icon('user') ?><?= e(__('admin.account')) ?></h1><p class="muted" dir="ltr"><?= e($me['username']) ?> · <?= e(__('admin.role.' . $me['role'])) ?></p></div></div>
  <?php if ($error): ?><div class="flash flash-err"><?= icon('circle-alert') ?><span><?= e($error) ?></span></div><?php endif; ?>
  <section class="card form-card"><div class="fields">
    <label class="fld fld--half"><span class="fld-label"><?= e(__('admin.f.name')) ?></span><input class="inp" name="name" value="<?= e($me['name']) ?>"></label>
    <label class="fld fld--half"><span class="fld-label"><?= e(__('admin.f.email')) ?></span><input class="inp" type="email" name="email" value="<?= e($me['email']) ?>" dir="ltr"></label>
    <label class="fld fld--half"><span class="fld-label"><?= e(__('admin.panel_language')) ?></span><select class="inp" name="lang"><option value="fa">فارسی</option><option value="en"<?= $me['lang'] === 'en' ? ' selected' : '' ?>>English</option></select></label>
  </div></section>
  <section class="card form-card"><h2 class="card-title"><?= icon('lock') ?><?= e(__('admin.change_password')) ?></h2><div class="fields">
    <label class="fld"><span class="fld-label"><?= e(__('admin.current_password')) ?></span><input class="inp" type="password" name="current_password" autocomplete="current-password" dir="ltr"></label>
    <label class="fld fld--half"><span class="fld-label"><?= e(__('admin.new_password')) ?></span><input class="inp" type="password" name="new_password" autocomplete="new-password" dir="ltr" minlength="8"></label>
    <label class="fld fld--half"><span class="fld-label"><?= e(__('admin.repeat_password')) ?></span><input class="inp" type="password" name="new_password2" autocomplete="new-password" dir="ltr" minlength="8"></label>
  </div></section>
  <div class="savebar"><button class="btn btn-accent"><?= icon('save') ?><?= e(__('admin.save')) ?></button></div>
</form>
