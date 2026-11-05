<?php
/**
 * Request lifecycle: base path, language prefix, routing to the public site or the admin panel.
 */
final class App
{
    public static string $base = '';
    public static bool $pretty = true;
    public static string $path = '';
    /** Page meta used by the layout: title, description, image, canonical, noindex, body class. */
    public static array $page = [];

    public static function detectBase(): void
    {
        $script = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
        $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');
        foreach (['/install', '/scripts'] as $sub) {
            if (str_ends_with($dir, $sub)) {
                $dir = substr($dir, 0, -strlen($sub));
            }
        }
        $forced = cfg('base_path');
        if (is_string($forced)) {
            $dir = rtrim($forced, '/');
        }
        self::$base = ($dir === '/' || $dir === '.') ? '' : $dir;
    }

    public static function boot(): void
    {
        self::detectBase();
        self::$pretty = (bool)cfg('pretty_urls', true);
        DB::pdo();
        self::maybeMigrate();
        I18n::init((array)setting('lang.enabled', ['fa']), (string)setting('lang.default', 'fa'));
    }

    public static function run(): void
    {
        self::detectBase();
        if (!$GLOBALS['VENUS_CONFIG']) {
            redirect(self::$base . '/install/');
        }
        try {
            self::boot();
            [$lang, $path] = self::parse();
            I18n::set($lang);
            self::$path = $path;
            self::headers();
            if ($path === 'admin' || str_starts_with($path, 'admin/')) {
                Admin::dispatch(trim(substr($path, 5), '/'));
                return;
            }
            Front::dispatch($path);
        } catch (Throwable $e) {
            self::fail($e);
        }
    }

    /** @return array{0:string,1:string} [language, path] */
    public static function parse(): array
    {
        $default = I18n::$default;
        if (isset($_GET['r']) && is_string($_GET['r'])) {
            $lang = (string)($_GET['lang'] ?? $default);
            if (!in_array($lang, I18n::$enabled, true)) {
                $lang = $default;
            }
            return [$lang, trim($_GET['r'], '/')];
        }
        $uri = (string)parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
        $uri = rawurldecode($uri);
        if (self::$base !== '' && str_starts_with($uri, self::$base)) {
            $uri = substr($uri, strlen(self::$base));
        }
        $uri = trim($uri, '/');
        if ($uri === 'index.php') {
            $uri = '';
        } elseif (str_starts_with($uri, 'index.php/')) {
            $uri = substr($uri, 10);
        }
        $lang = isset($_GET['lang']) && in_array($_GET['lang'], I18n::$enabled, true) ? (string)$_GET['lang'] : $default;
        $parts = explode('/', $uri, 2);
        if (isset(I18n::LANGS[$parts[0]])) {
            if ($parts[0] === $default || !in_array($parts[0], I18n::$enabled, true)) {
                // /fa/rooms -> /rooms (default language has no prefix)
                $rest = $parts[1] ?? '';
                I18n::set($default);
                redirect(url($rest, $_GET, $default), 301);
            }
            $lang = $parts[0];
            $uri = $parts[1] ?? '';
        }
        return [$lang, $uri];
    }

    private static function headers(): void
    {
        if (headers_sent()) {
            return;
        }
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), payment=(), geolocation=(self), accelerometer=(self), gyroscope=(self), magnetometer=(self)');
        header_remove('X-Powered-By');
    }

    private static function maybeMigrate(): void
    {
        if (Settings::get('system.schema') !== VENUS_VERSION) {
            Schema::migrate();
            Settings::set('system.schema', VENUS_VERSION);
        }
    }

    public static function fail(Throwable $e): void
    {
        log_error(get_class($e) . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString());
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }
        $debug = (bool)cfg('debug');
        $msg = $debug ? '<pre style="white-space:pre-wrap;direction:ltr;text-align:left">' . e($e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString()) . '</pre>' : '';
        echo '<!doctype html><html lang="fa" dir="rtl"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>خطا | Error</title>'
            . '<body style="font-family:system-ui,Tahoma,sans-serif;background:#f7f2ea;color:#161a21;display:grid;place-items:center;min-height:100vh;margin:0;padding:24px">'
            . '<div style="max-width:640px;text-align:center"><h1 style="font-weight:600">مشکلی پیش آمد</h1><p>Something went wrong. Please try again in a moment.</p>' . $msg . '</div></body></html>';
    }

    public static function isBot(): bool
    {
        $ua = strtolower((string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
        return $ua === '' || (bool)preg_match('~bot|crawl|spider|slurp|preview|monitor|curl|wget|python|headless|lighthouse|httpclient|java/|go-http~', $ua);
    }
}
