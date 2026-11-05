<?php
/**
 * Venus Hotel – web installer. Open https://your-site/install/ once after uploading the files.
 * It locks itself after a successful install (storage/config.php exists).
 */
require dirname(__DIR__) . '/app/bootstrap.php';
App::detectBase();
$base = App::$base;
$done = false;
$error = '';
$installed = Installer::installed();
$reqs = Installer::requirements();
$reqOk = Installer::requirementsOk();
$v = fn($k, $d = '') => htmlspecialchars((string)($_POST[$k] ?? $d), ENT_QUOTES, 'UTF-8');

session_name('venus_install');
session_start();
if (empty($_SESSION['tok'])) {
    $_SESSION['tok'] = bin2hex(random_bytes(16));
}

if (!$installed && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!hash_equals($_SESSION['tok'], (string)($_POST['tok'] ?? ''))) {
        $error = 'Session expired, please try again. / نشست منقضی شد، دوباره تلاش کنید.';
    } elseif (($_POST['pass'] ?? '') !== ($_POST['pass2'] ?? '')) {
        $error = 'Passwords do not match. / رمزها یکسان نیستند.';
    } else {
        $opts = [
            'driver' => $_POST['driver'] ?? 'sqlite',
            'mysql_host' => $_POST['mysql_host'] ?? '', 'mysql_port' => $_POST['mysql_port'] ?? '3306',
            'mysql_name' => $_POST['mysql_name'] ?? '', 'mysql_user' => $_POST['mysql_user'] ?? '', 'mysql_pass' => $_POST['mysql_pass'] ?? '',
            'admin_user' => $_POST['user'] ?? '', 'admin_pass' => $_POST['pass'] ?? '', 'admin_name' => $_POST['name'] ?? '',
            'demo' => !empty($_POST['demo']), 'pretty_urls' => ($_POST['pretty'] ?? '1') === '1', 'site_url' => $_POST['site_url'] ?? '',
        ];
        if ($opts['driver'] === 'mysql' && ($e = Installer::testMysql($opts))) {
            $error = 'MySQL connection failed / اتصال به MySQL ناموفق بود: ' . $e;
        } else {
            try {
                @set_time_limit(300);
                Installer::install($opts);
                $done = true;
                $installed = true;
            } catch (Throwable $e) {
                $error = $e->getMessage();
                log_error('install: ' . $e->getMessage() . ' ' . $e->getFile() . ':' . $e->getLine());
            }
        }
    }
}
?><!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
<title>نصب هتل ونوس · Venus Hotel installer</title>
<style>
@font-face{font-family:Vazirmatn;src:url(<?= $base ?>/assets/fonts/vazirmatn-var.woff2) format('woff2');font-weight:100 900}
:root{--p:#12304f;--g:#c6a15b;--bg:#f7f2ea;--ink:#161a21;--line:rgba(0,0,0,.12)}
*{box-sizing:border-box}body{margin:0;font-family:Vazirmatn,Tahoma,sans-serif;background:var(--bg);color:var(--ink);line-height:1.8}
.wrap{max-width:760px;margin:0 auto;padding:32px 16px 60px}
.head{display:flex;align-items:center;gap:14px;margin-bottom:26px}.head svg{width:52px;color:var(--g)}
h1{margin:0;font-weight:400;font-size:1.7rem}h1 small{display:block;font-size:.85rem;color:#777}
.card{background:#fff;border:1px solid var(--line);border-radius:16px;padding:24px;margin-bottom:18px}
h2{margin:0 0 14px;font-size:1.1rem;font-weight:600}
.req{display:grid;grid-template-columns:1fr auto;gap:6px 12px;font-size:.92rem}.req span.ok{color:#1d7a4f}.req span.no{color:#b3261e}.req span.opt{color:#a16207}
label{display:block;font-size:.9rem;font-weight:500;margin:12px 0 4px}
input[type=text],input[type=password],input[type=url],input[type=number]{width:100%;padding:11px 14px;border:1px solid var(--line);border-radius:10px;font:inherit;background:#fcfaf6}
input:focus{outline:2px solid var(--g);border-color:transparent}
.row{display:grid;grid-template-columns:1fr 1fr;gap:12px}.hint{font-size:.8rem;color:#777;margin:2px 0 0}
.radio{display:flex;gap:10px;flex-wrap:wrap}.radio label{display:flex;gap:8px;align-items:center;border:1px solid var(--line);border-radius:10px;padding:10px 14px;margin:0;cursor:pointer;flex:1;min-width:200px}
.btn{display:inline-flex;align-items:center;gap:8px;background:var(--p);color:#fff;border:0;border-radius:999px;padding:12px 28px;font:inherit;font-weight:600;cursor:pointer;text-decoration:none}
.btn.gold{background:var(--g);color:#111}.err{background:#fdecea;color:#8c1d18;border-radius:10px;padding:12px 14px;margin-bottom:16px}
.ok-box{background:#e8f5ee;color:#14532d;border-radius:12px;padding:16px}
[dir=ltr]{direction:ltr;text-align:left}.mysql{display:none}.mysql.show{display:block}
@media(max-width:600px){.row{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="wrap">
  <div class="head">
    <svg viewBox="0 0 48 48"><g fill="none" stroke="currentColor" stroke-width="1.6"><rect x="11.5" y="11.5" width="25" height="25"/><rect x="11.5" y="11.5" width="25" height="25" transform="rotate(45 24 24)"/><circle cx="24" cy="24" r="7"/></g></svg>
    <h1>نصب سایت هتل ونوس<small>Venus Hotel website installer · v<?= VENUS_VERSION ?></small></h1>
  </div>

<?php if ($done): ?>
  <div class="card">
    <div class="ok-box"><b>نصب با موفقیت انجام شد. · Installed successfully.</b></div>
    <p>برای امنیت بیشتر، پوشه <code>install</code> را از هاست حذف کنید (نصب‌کننده از الان قفل است).<br><span dir="ltr">For extra safety, delete the <code>install</code> folder (the installer is now locked).</span></p>
    <p><a class="btn gold" href="<?= $base ?>/admin/">ورود به پنل مدیریت · Admin panel</a> &nbsp; <a class="btn" href="<?= $base ?>/">دیدن سایت · View site</a></p>
  </div>
<?php elseif ($installed): ?>
  <div class="card">
    <h2>سایت قبلاً نصب شده است · Already installed</h2>
    <p>برای نصب دوباره، فایل <code>storage/config.php</code> را حذف کنید. · To reinstall, delete <code>storage/config.php</code>.</p>
    <p><a class="btn" href="<?= $base ?>/admin/">پنل مدیریت · Admin</a></p>
  </div>
<?php else: ?>
  <div class="card">
    <h2>۱. بررسی سرور · Server check</h2>
    <div class="req" dir="ltr">
      <?php foreach ($reqs as $r): ?>
        <div><?= htmlspecialchars($r['label']) ?> <small style="color:#888">— <?= htmlspecialchars($r['note']) ?></small></div>
        <span class="<?= $r['ok'] ? 'ok' : ($r['required'] ? 'no' : 'opt') ?>"><?= $r['ok'] ? '✓' : ($r['required'] ? '✗ required' : '– optional') ?></span>
      <?php endforeach; ?>
    </div>
    <?php if (!$reqOk): ?><p class="err" style="margin-top:14px">سرور شرایط لازم را ندارد. از پشتیبانی هاست بخواهید موارد قرمز را فعال کنند. · Ask your host to enable the red items.</p><?php endif; ?>
  </div>

  <?php if ($error): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <?php if ($reqOk): ?>
  <form method="post" class="card" autocomplete="off">
    <input type="hidden" name="tok" value="<?= $_SESSION['tok'] ?>">
    <input type="hidden" name="pretty" id="pretty" value="<?= $v('pretty', '1') ?>">
    <h2>۲. پایگاه داده · Database</h2>
    <div class="radio">
      <label><input type="radio" name="driver" value="sqlite" <?= ($_POST['driver'] ?? 'sqlite') !== 'mysql' ? 'checked' : '' ?> <?= extension_loaded('pdo_sqlite') ? '' : 'disabled' ?>> SQLite <small>(پیشنهادی، بدون تنظیم · recommended)</small></label>
      <label><input type="radio" name="driver" value="mysql" <?= ($_POST['driver'] ?? '') === 'mysql' ? 'checked' : '' ?> <?= extension_loaded('pdo_mysql') ? '' : 'disabled' ?>> MySQL / MariaDB</label>
    </div>
    <div class="mysql<?= ($_POST['driver'] ?? '') === 'mysql' ? ' show' : '' ?>" id="mysql">
      <p class="hint">در cPanel از بخش «MySQL Databases» یک دیتابیس و کاربر بسازید و کاربر را با همه دسترسی‌ها به دیتابیس اضافه کنید. · Create a database and user in cPanel → MySQL Databases.</p>
      <div class="row">
        <div><label>Host</label><input type="text" name="mysql_host" value="<?= $v('mysql_host', 'localhost') ?>" dir="ltr"></div>
        <div><label>Port</label><input type="number" name="mysql_port" value="<?= $v('mysql_port', '3306') ?>" dir="ltr"></div>
      </div>
      <label>نام دیتابیس · Database name</label><input type="text" name="mysql_name" value="<?= $v('mysql_name') ?>" dir="ltr">
      <div class="row">
        <div><label>نام کاربری · User</label><input type="text" name="mysql_user" value="<?= $v('mysql_user') ?>" dir="ltr"></div>
        <div><label>رمز · Password</label><input type="password" name="mysql_pass" value="" dir="ltr"></div>
      </div>
    </div>

    <h2 style="margin-top:24px">۳. حساب مدیر · Admin account</h2>
    <label>نام شما · Your name</label><input type="text" name="name" value="<?= $v('name') ?>">
    <label>نام کاربری · Username</label><input type="text" name="user" value="<?= $v('user', 'admin') ?>" dir="ltr" required pattern="[A-Za-z0-9_.\-@]{3,60}">
    <div class="row">
      <div><label>رمز عبور · Password</label><input type="password" name="pass" dir="ltr" required minlength="8"></div>
      <div><label>تکرار رمز · Repeat</label><input type="password" name="pass2" dir="ltr" required minlength="8"></div>
    </div>
    <p class="hint">حداقل ۸ کاراکتر · At least 8 characters</p>

    <h2 style="margin-top:24px">۴. محتوا · Content</h2>
    <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" name="demo" value="1" <?= !isset($_POST['tok']) || !empty($_POST['demo']) ? 'checked' : '' ?>> نصب محتوای نمونه (اتاق‌ها، منو، عکس‌ها، تور ۳۶۰) · Install demo content</label>
    <p class="hint">همه چیز بعداً از پنل مدیریت قابل تغییر یا حذف است. · Everything can be edited or deleted later.</p>
    <label>آدرس سایت (اختیاری) · Site URL (optional)</label><input type="url" name="site_url" value="<?= $v('site_url') ?>" dir="ltr" placeholder="https://venushotel.ir">
    <p class="hint" id="rw">در حال بررسی آدرس‌های تمیز… · Checking clean URLs…</p>
    <p style="margin-top:22px"><button class="btn gold" type="submit">نصب · Install</button></p>
  </form>
  <?php endif; ?>
<?php endif; ?>
</div>
<script>
(function(){
  var r=document.querySelectorAll('input[name=driver]'),m=document.getElementById('mysql');
  r.forEach(function(x){x.addEventListener('change',function(){m.classList.toggle('show',x.value==='mysql'&&x.checked)})});
  var p=document.getElementById('pretty'),h=document.getElementById('rw');if(!p)return;
  fetch('<?= $base ?>/venus-rewrite-check',{redirect:'follow',cache:'no-store'}).then(function(res){
    var ok=res.ok&&/\/install\/?/.test(res.url);p.value=ok?'1':'0';
    h.textContent=ok?'✓ آدرس‌های تمیز فعال است · Clean URLs work':'آدرس‌های تمیز در دسترس نیست؛ سایت با index.php?r= کار می‌کند · Clean URLs unavailable, using index.php?r=';
  }).catch(function(){p.value='0';h.textContent='';});
})();
</script>
</body>
</html>
