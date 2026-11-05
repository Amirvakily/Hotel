<!doctype html>
<html lang="<?= e(I18n::$lang) ?>" dir="<?= e(I18n::dir()) ?>">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">
<title><?= e(tr(setting('site.name'))) ?></title><link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>"></head>
<body class="page-maintenance">
<section class="error-page">
  <div class="container">
    <span class="error-mark" aria-hidden="true"><svg viewBox="0 0 40 40"><rect x="10" y="10" width="20" height="20" rx="3"/><rect x="10" y="10" width="20" height="20" rx="3" transform="rotate(45 20 20)"/><circle cx="20" cy="20" r="4.2" class="dot"/></svg></span>
    <h1 class="page-title"><?= e(tr(setting('site.name'))) ?></h1>
    <p class="page-sub"><?= e(tr(setting('system.maintenance_text'))) ?></p>
    <?php $ph = (array)setting('contact.phones', []); if (!empty($ph[0])): ?><a class="btn btn-accent" href="tel:<?= e(tel($ph[0])) ?>"><span dir="ltr"><?= e(digits($ph[0])) ?></span></a><?php endif; ?>
  </div>
</section>
</body></html>
