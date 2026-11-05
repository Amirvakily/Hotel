<?php
/**
 * Admin sessions, login throttling and roles.
 * Roles: admin (everything) and editor (content only; no users, settings, backup).
 */
final class Auth
{
    public const COOKIE = 'venus_admin';
    private const MAX_ATTEMPTS = 6;
    private const WINDOW_MIN = 15;
    private const IDLE_LIMIT = 7200; // seconds

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        session_name(self::COOKIE);
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => (App::$base ?: '') . '/',
            'secure' => is_https(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        $dir = STORAGE . '/sessions';
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }
        if (is_dir($dir) && is_writable($dir)) {
            session_save_path($dir);
        }
        session_start();
        if (!empty($_SESSION['uid']) && (time() - (int)($_SESSION['seen'] ?? 0)) > self::IDLE_LIMIT) {
            $_SESSION = [];
            session_regenerate_id(true);
        }
        $_SESSION['seen'] = time();
    }

    public static function user(): ?array
    {
        static $user = false;
        if ($user !== false) {
            return $user;
        }
        $uid = (int)($_SESSION['uid'] ?? 0);
        $user = $uid ? DB::one('SELECT * FROM users WHERE id = ?', [$uid]) : null;
        return $user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role'] ?? '') === 'admin';
    }

    public static function throttled(): bool
    {
        $since = date('Y-m-d H:i:s', time() - self::WINDOW_MIN * 60);
        $n = (int)DB::val('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND created_at > ?', [client_ip(), $since]);
        return $n >= self::MAX_ATTEMPTS;
    }

    public static function attempt(string $username, string $password): bool
    {
        $u = DB::one('SELECT * FROM users WHERE username = ?', [trim($username)]);
        // Always run a hash check to keep timing similar for unknown users.
        $hash = $u['password'] ?? password_hash('timing-dummy', PASSWORD_DEFAULT);
        $ok = password_verify($password, $hash) && $u;
        if (!$ok) {
            DB::insert('login_attempts', ['ip' => client_ip(), 'username' => mb_substr($username, 0, 100), 'created_at' => DB::now()]);
            return false;
        }
        if (password_needs_rehash($u['password'], PASSWORD_DEFAULT)) {
            DB::update('users', ['password' => password_hash($password, PASSWORD_DEFAULT)], 'id = ?', [$u['id']]);
        }
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$u['id'];
        $_SESSION['seen'] = time();
        DB::update('users', ['last_login' => DB::now()], 'id = ?', [$u['id']]);
        DB::delete('login_attempts', 'ip = ?', [client_ip()]);
        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', ['expires' => time() - 42000, 'path' => $p['path'], 'secure' => $p['secure'], 'httponly' => true, 'samesite' => 'Lax']);
        }
        session_destroy();
    }

    public static function cleanup(): void
    {
        DB::delete('login_attempts', 'created_at < ?', [date('Y-m-d H:i:s', time() - 86400)]);
    }
}
