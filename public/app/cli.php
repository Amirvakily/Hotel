<?php
/**
 * Venus Hotel – command line tools (run over SSH or in cPanel Terminal).
 *
 *   php app/cli.php install --user=admin --pass=secret123 [--name="..."] [--no-demo]
 *                           [--mysql --db-host=localhost --db-name=... --db-user=... --db-pass=...]
 *                           [--site-url=https://example.com] [--no-pretty]
 *   php app/cli.php migrate              Create missing tables/columns after an update
 *   php app/cli.php password <user> <new-password>
 *   php app/cli.php cache:clear
 *   php app/cli.php backup               Write a zip to storage/backups/
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}
require __DIR__ . '/bootstrap.php';

$args = $argv;
array_shift($args);
$cmd = array_shift($args) ?? 'help';
$opt = [];
$pos = [];
foreach ($args as $a) {
    if (preg_match('~^--([a-z\-]+)(?:=(.*))?$~', $a, $m)) {
        $opt[$m[1]] = $m[2] ?? true;
    } else {
        $pos[] = $a;
    }
}
$out = fn(string $s) => fwrite(STDOUT, $s . "\n");
$fail = function (string $s) {
    fwrite(STDERR, "Error: $s\n");
    exit(1);
};
$boot = function () use ($fail) {
    if (!Installer::installed()) {
        $fail('not installed – run: php app/cli.php install --user=admin --pass=...');
    }
    App::$base = '';
    App::boot();
};

switch ($cmd) {
    case 'install':
        if (Installer::installed()) {
            $fail('already installed (delete storage/config.php to reinstall)');
        }
        $o = [
            'admin_user' => $opt['user'] ?? 'admin',
            'admin_pass' => $opt['pass'] ?? '',
            'admin_name' => $opt['name'] ?? '',
            'demo' => empty($opt['no-demo']),
            'pretty_urls' => empty($opt['no-pretty']),
            'site_url' => $opt['site-url'] ?? '',
            'driver' => !empty($opt['mysql']) ? 'mysql' : 'sqlite',
            'mysql_host' => $opt['db-host'] ?? 'localhost', 'mysql_port' => $opt['db-port'] ?? 3306,
            'mysql_name' => $opt['db-name'] ?? '', 'mysql_user' => $opt['db-user'] ?? '', 'mysql_pass' => $opt['db-pass'] ?? '',
        ];
        if ($o['admin_pass'] === '') {
            $fail('--pass is required (at least 8 characters)');
        }
        if ($o['driver'] === 'mysql' && ($e = Installer::testMysql($o))) {
            $fail('MySQL: ' . $e);
        }
        try {
            $t = microtime(true);
            Installer::install($o);
            $out(sprintf('Installed in %.1fs. Admin user: %s', microtime(true) - $t, $o['admin_user']));
        } catch (Throwable $e) {
            $fail($e->getMessage());
        }
        break;

    case 'migrate':
        $boot();
        $log = Schema::migrate();
        Settings::set('system.schema', VENUS_VERSION);
        Cache::flush();
        $out($log ? implode("\n", $log) : 'Database is up to date.');
        break;

    case 'password':
        $boot();
        [$u, $p] = [$pos[0] ?? '', $pos[1] ?? ''];
        if ($u === '' || mb_strlen($p) < 8) {
            $fail('usage: php app/cli.php password <user> <new-password (8+ chars)>');
        }
        $n = DB::update('users', ['password' => password_hash($p, PASSWORD_DEFAULT)], 'username = ?', [$u]);
        DB::delete('login_attempts', '1 = 1');
        $n ? $out("Password changed for $u.") : $fail("no user named $u");
        break;

    case 'cache:clear':
        $boot();
        $out('Removed ' . Cache::flush() . ' cached pages.');
        break;

    case 'backup':
        $boot();
        try {
            $file = Backup::create();
            $out('Backup written: ' . $file);
        } catch (Throwable $e) {
            $fail($e->getMessage());
        }
        break;

    default:
        $out(trim(substr(file_get_contents(__FILE__), 5, 900), "/* \n"));
}
