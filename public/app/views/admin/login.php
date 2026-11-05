<!doctype html>
<html lang="<?= e(I18n::$lang) ?>" dir="<?= e(I18n::dir()) ?>">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
<title><?= e(__('admin.login')) ?> · <?= e(tr(setting('site.name'))) ?></title>
<link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset_min('admin/admin.css')) ?>"></head>
<body class="login-page">
<main class="login-box">
  <div class="login-brand"><span class="sb-mark"><svg viewBox="0 0 40 40"><rect x="10" y="10" width="20" height="20" rx="3"/><rect x="10" y="10" width="20" height="20" rx="3" transform="rotate(45 20 20)"/><circle cx="20" cy="20" r="4.2"/></svg></span>
    <div><b><?= e(tr(setting('site.name'))) ?></b><small><?= e(__('admin.panel')) ?></small></div></div>
  <h1><?= e(__('admin.login_title')) ?></h1>
  <?php if ($error): ?><div class="flash flash-err" role="alert"><?= icon('circle-alert') ?><span><?= e($error) ?></span></div><?php endif; ?>
  <form method="post" class="stack">
    <?= csrf_field() ?>
    <label class="fld"><span class="fld-label"><?= e(__('admin.username')) ?></span><input class="inp" name="username" autocomplete="username" required dir="ltr" autofocus value="<?= e($_POST['username'] ?? '') ?>"></label>
    <label class="fld"><span class="fld-label"><?= e(__('admin.password')) ?></span><input class="inp" type="password" name="password" autocomplete="current-password" required dir="ltr"></label>
    <button class="btn btn-accent btn-block" type="submit"><?= e(__('admin.login')) ?></button>
  </form>
  <p class="muted small center"><a href="<?= e(url('', [], I18n::$default)) ?>">← <?= e(__('admin.view_site')) ?></a></p>
</main>
</body></html>
