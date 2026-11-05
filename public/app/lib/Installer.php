<?php
/**
 * Shared install logic for the web installer (install/index.php) and the command line (scripts/install.php).
 */
final class Installer
{
    public static function installed(): bool
    {
        return is_file(STORAGE . '/config.php');
    }

    /** @return array<int, array{label:string, ok:bool, required:bool, note:string}> */
    public static function requirements(): array
    {
        $gd = function_exists('gd_info') ? gd_info() : [];
        $writable = fn(string $p) => is_dir($p) && is_writable($p);
        return [
            ['label' => 'PHP ≥ 8.0', 'ok' => PHP_VERSION_ID >= 80000, 'required' => true, 'note' => PHP_VERSION],
            ['label' => 'PDO SQLite', 'ok' => extension_loaded('pdo_sqlite'), 'required' => false, 'note' => 'SQLite database (recommended, no setup)'],
            ['label' => 'PDO MySQL', 'ok' => extension_loaded('pdo_mysql'), 'required' => false, 'note' => 'MySQL / MariaDB database'],
            ['label' => 'GD (images)', 'ok' => extension_loaded('gd'), 'required' => true, 'note' => 'resize uploaded photos'],
            ['label' => 'GD WebP', 'ok' => !empty($gd['WebP Support']), 'required' => false, 'note' => 'smaller, faster images'],
            ['label' => 'mbstring', 'ok' => extension_loaded('mbstring'), 'required' => true, 'note' => 'Persian / Arabic text'],
            ['label' => 'fileinfo', 'ok' => extension_loaded('fileinfo'), 'required' => false, 'note' => 'extra upload checks'],
            ['label' => 'DOM / XML', 'ok' => class_exists('DOMDocument'), 'required' => false, 'note' => 'clean rich text'],
            ['label' => 'zip', 'ok' => class_exists('ZipArchive'), 'required' => false, 'note' => 'backups'],
            ['label' => 'storage/ writable', 'ok' => $writable(STORAGE), 'required' => true, 'note' => 'chmod 755 or 775'],
            ['label' => 'uploads/ writable', 'ok' => $writable(UPLOADS), 'required' => true, 'note' => 'chmod 755 or 775'],
        ];
    }

    public static function requirementsOk(): bool
    {
        foreach (self::requirements() as $r) {
            if ($r['required'] && !$r['ok']) {
                return false;
            }
        }
        return extension_loaded('pdo_sqlite') || extension_loaded('pdo_mysql');
    }

    /**
     * @param array $o driver, mysql_host, mysql_port, mysql_name, mysql_user, mysql_pass, admin_user, admin_pass, admin_name, demo, pretty_urls, site_url
     * @return array the config that was written
     */
    public static function install(array $o): array
    {
        if (self::installed()) {
            throw new RuntimeException('Already installed. Delete storage/config.php to install again.');
        }
        $user = trim((string)($o['admin_user'] ?? ''));
        $pass = (string)($o['admin_pass'] ?? '');
        if (!preg_match('~^[A-Za-z0-9_.\-@]{3,60}$~', $user)) {
            throw new InvalidArgumentException('Username: 3–60 letters, digits, . _ - @');
        }
        if (mb_strlen($pass) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters.');
        }
        $driver = ($o['driver'] ?? 'sqlite') === 'mysql' ? 'mysql' : 'sqlite';
        $db = $driver === 'mysql'
            ? ['driver' => 'mysql', 'host' => trim((string)($o['mysql_host'] ?? 'localhost')) ?: 'localhost', 'port' => (int)($o['mysql_port'] ?? 3306) ?: 3306,
                'name' => trim((string)($o['mysql_name'] ?? '')), 'user' => trim((string)($o['mysql_user'] ?? '')), 'pass' => (string)($o['mysql_pass'] ?? '')]
            : ['driver' => 'sqlite', 'path' => 'storage/database.sqlite'];
        $config = [
            'app_key' => bin2hex(random_bytes(32)),
            'db' => $db,
            'pretty_urls' => (bool)($o['pretty_urls'] ?? true),
            'site_url' => rtrim(trim((string)($o['site_url'] ?? '')), '/'),
            'debug' => false,
            'timezone' => 'Asia/Tehran',
        ];
        foreach (['cache', 'cache/pages', 'logs', 'backups', 'sessions'] as $d) {
            if (!is_dir(STORAGE . '/' . $d)) {
                @mkdir(STORAGE . '/' . $d, 0755, true);
            }
        }
        $GLOBALS['VENUS_CONFIG'] = $config;
        DB::connect($config);
        Schema::migrate();
        Settings::reset();
        Settings::set('system.schema', VENUS_VERSION);
        if (!DB::val('SELECT COUNT(*) FROM users WHERE username = ?', [$user])) {
            DB::insert('users', [
                'username' => $user, 'name' => trim((string)($o['admin_name'] ?? '')) ?: $user, 'email' => '',
                'password' => password_hash($pass, PASSWORD_DEFAULT), 'role' => 'admin', 'lang' => 'fa', 'created_at' => DB::now(),
            ]);
        }
        if (!DB::val('SELECT COUNT(*) FROM rooms')) {
            Seed::run(!empty($o['demo']));
        }
        self::writeConfig($config);
        return $config;
    }

    public static function writeConfig(array $config): void
    {
        $php = "<?php\n// Venus Hotel configuration – created by the installer. Keep this file private.\nreturn " . var_export($config, true) . ";\n";
        $tmp = STORAGE . '/config.php.tmp';
        if (@file_put_contents($tmp, $php) === false || !@rename($tmp, STORAGE . '/config.php')) {
            throw new RuntimeException('Cannot write storage/config.php – check folder permissions.');
        }
        @chmod(STORAGE . '/config.php', 0640);
    }

    public static function testMysql(array $o): ?string
    {
        try {
            $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $o['mysql_host'] ?: 'localhost', (int)($o['mysql_port'] ?: 3306), $o['mysql_name']);
            new PDO($dsn, $o['mysql_user'], $o['mysql_pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]);
            return null;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }
}
