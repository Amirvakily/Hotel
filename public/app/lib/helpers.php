<?php
/**
 * Global helper functions used by templates and controllers.
 */

function cfg(string $key, $default = null)
{
    $c = $GLOBALS['VENUS_CONFIG'] ?? [];
    foreach (explode('.', $key) as $part) {
        if (!is_array($c) || !array_key_exists($part, $c)) {
            return $default;
        }
        $c = $c[$part];
    }
    return $c;
}

/** Escape for HTML. */
function e($s): string
{
    return htmlspecialchars((string)($s ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** UI string. */
function __(string $key, array $params = []): string
{
    return I18n::t($key, $params);
}

/** Pick the current language from a multilingual JSON value. */
function tr($value, ?string $lang = null): string
{
    return I18n::pick($value, $lang);
}

function setting(string $name, $default = null)
{
    return Settings::get($name, $default);
}

/** Localised number (Persian / Arabic digits). */
function num($n, int $decimals = 0): string
{
    return I18n::number($n, $decimals);
}

function digits($s): string
{
    return I18n::digits((string)$s);
}

function json_dec($s, $default = [])
{
    if (is_array($s)) {
        return $s;
    }
    if ($s === null || $s === '') {
        return $default;
    }
    $v = json_decode((string)$s, true);
    return $v === null ? $default : $v;
}

function json_enc($v): string
{
    return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (($_SERVER['SERVER_PORT'] ?? '') == 443);
}

function base_path(): string
{
    return App::$base;
}

/** Public URL for a front page, with the language prefix when not the default language. */
function url(string $path = '', array $query = [], ?string $lang = null): string
{
    $lang = $lang ?? I18n::$lang;
    $path = trim($path, '/');
    if (App::$pretty) {
        $prefix = $lang !== I18n::$default ? '/' . $lang : '';
        $u = App::$base . $prefix . '/' . $path;
        if ($path === '' && $prefix !== '') {
            $u = App::$base . $prefix . '/';
        }
    } else {
        $u = App::$base . '/index.php';
        $q = [];
        if ($path !== '') {
            $q['r'] = $path;
        }
        if ($lang !== I18n::$default) {
            $q['lang'] = $lang;
        }
        $query = $q + $query;
    }
    if ($query) {
        $u .= (str_contains($u, '?') ? '&' : '?') . http_build_query($query);
    }
    return $u;
}

function admin_url(string $path = '', array $query = []): string
{
    $path = trim($path, '/');
    if (App::$pretty) {
        $u = App::$base . '/admin' . ($path !== '' ? '/' . $path : '/');
    } else {
        $u = App::$base . '/index.php';
        $query = ['r' => 'admin' . ($path !== '' ? '/' . $path : '')] + $query;
    }
    if ($query) {
        $u .= (str_contains($u, '?') ? '&' : '?') . http_build_query($query);
    }
    return $u;
}

/** Full absolute URL (for SEO tags, sitemap, sharing). */
function abs_url(string $relative): string
{
    if (preg_match('~^https?://~', $relative)) {
        return $relative;
    }
    $configured = rtrim((string)cfg('site_url', ''), '/');
    if ($configured !== '') {
        $origin = preg_replace('~^(https?://[^/]+).*$~', '$1', $configured);
    } else {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $origin = (is_https() ? 'https' : 'http') . '://' . $host;
    }
    return $origin . $relative;
}

/** WCAG contrast ratio between two [r, g, b] colours. */
function contrast_ratio(array $a, array $b): float
{
    $lum = function (array $c): float {
        $c = array_map(function ($v) {
            $v /= 255;
            return $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
        }, $c);
        return 0.2126 * $c[0] + 0.7152 * $c[1] + 0.0722 * $c[2];
    };
    $x = $lum($a);
    $y = $lum($b);
    return (max($x, $y) + 0.05) / (min($x, $y) + 0.05);
}

/**
 * Readable colours for a button in the accent colour: [background, text].
 * White text when it reaches 4.5:1, otherwise the accent is darkened a little,
 * and very light accents (gold, yellow) get dark text instead.
 */
function accent_pair(string $hex, string $dark = '#111316'): array
{
    if (preg_match('~^#([0-9a-f])([0-9a-f])([0-9a-f])$~i', $hex, $s)) {
        $hex = '#' . $s[1] . $s[1] . $s[2] . $s[2] . $s[3] . $s[3];
    }
    if (!preg_match('~^#([0-9a-f]{6})$~i', $hex, $m)) {
        return [$hex, '#fff'];
    }
    $rgb = array_map('hexdec', str_split($m[1], 2));
    $white = [255, 255, 255];
    for ($k = 0; $k <= 6; $k++) {
        $c = array_map(fn($v) => (int)round($v * (1 - 0.04 * $k)), $rgb);
        if (contrast_ratio($c, $white) >= 4.5) {
            return [sprintf('#%02x%02x%02x', ...$c), '#fff'];
        }
    }
    return [$hex, preg_match('~^#[0-9a-f]{6}$~i', $dark) ? $dark : '#111316'];
}

function asset(string $path): string
{
    $file = ROOT . '/assets/' . $path;
    $v = is_file($file) ? substr(base_convert((string)filemtime($file), 10, 36), -6) : VENUS_VERSION;
    return App::$base . '/assets/' . $path . '?v=' . $v;
}

/**
 * Like asset(), but serves the minified copy (site.min.css next to site.css) when it was
 * built from the current source: scripts/minify.sh writes the source's md5 on its first line.
 */
function asset_min(string $path): string
{
    static $memo = [];
    if (isset($memo[$path])) {
        return $memo[$path];
    }
    $min = (string)preg_replace('~\.(css|js)$~', '.min.$1', $path);
    $src = ROOT . '/assets/' . $path;
    $minFile = ROOT . '/assets/' . $min;
    if ($min !== $path && is_file($minFile) && is_file($src)) {
        $head = (string)@file_get_contents($minFile, false, null, 0, 48);
        if (preg_match('~^/\*src:([0-9a-f]{32})\*/~', $head, $m) && hash_equals($m[1], (string)md5_file($src))) {
            return $memo[$path] = asset($min);
        }
    }
    return $memo[$path] = asset($path);
}

function upload_url(string $rel): string
{
    return App::$base . '/uploads/' . ltrim($rel, '/');
}

function icon(string $name, string $class = ''): string
{
    static $sprite = null;
    if ($sprite === null) {
        $sprite = asset('img/icons.svg');
    }
    $name = preg_replace('/[^a-z0-9\-]/', '', strtolower($name));
    return '<svg class="icon' . ($class ? ' ' . e($class) : '') . '" aria-hidden="true" focusable="false"><use href="' . e($sprite) . '#i-' . $name . '"/></svg>';
}

/** Format a Toman price in the current language. Returns '' when the price is empty. */
function money($amount, ?string $lang = null): string
{
    if ($amount === null || $amount === '' || (int)$amount <= 0) {
        return '';
    }
    $lang = $lang ?? I18n::$lang;
    $cur = I18n::pick(setting('price.currency'), $lang);
    return I18n::number((int)$amount, 0, $lang) . ' ' . $cur;
}

/** Approximate price in a second currency (USD for English, IQD for Arabic...). */
function money_secondary($amount, ?string $lang = null): string
{
    $lang = $lang ?? I18n::$lang;
    $sec = setting('price.secondary', [])[$lang] ?? null;
    if (!$sec || empty($sec['on']) || empty($sec['rate']) || !$amount) {
        return '';
    }
    $v = (float)$amount * (float)$sec['rate'];
    $dec = (int)($sec['decimals'] ?? 0);
    if ($v >= 1000) {
        $v = round($v, -2); // keep it readable
        $dec = 0;
    } elseif ($v >= 100) {
        $v = round($v);
        $dec = 0;
    }
    $n = I18n::number($v, $dec, $lang);
    $sym = (string)($sec['symbol'] ?? '');
    $label = (string)($sec['label'] ?? '');
    return '≈ ' . ($sym !== '' ? $sym . $n : $n . ' ' . $label);
}

function redirect(string $url, int $code = 302): void
{
    header('Location: ' . $url, true, $code);
    exit;
}

function json_out($data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function input(string $key, $default = null)
{
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

function app_key(): string
{
    return (string)cfg('app_key', 'venus-insecure-key');
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return '';
    }
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): bool
{
    $sent = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return is_string($sent) && $sent !== '' && hash_equals(csrf_token(), $sent);
}

function slugify(string $s): string
{
    $s = trim(mb_strtolower($s));
    $s = preg_replace('~[^\p{L}\p{N}]+~u', '-', $s);
    $s = trim((string)$s, '-');
    // Latin-only slugs keep URLs clean; non-Latin text falls back to a short random slug.
    if (!preg_match('~^[a-z0-9\-]+$~', $s)) {
        $s = 'item-' . substr(bin2hex(random_bytes(3)), 0, 6);
    }
    return substr($s, 0, 80);
}

function client_ip(): string
{
    return substr((string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

function str_limit(string $s, int $n): string
{
    $s = trim(preg_replace('/\s+/u', ' ', strip_tags($s)));
    return mb_strlen($s) > $n ? rtrim(mb_substr($s, 0, $n - 1)) . '…' : $s;
}

/** Date for display: Jalali (Persian) for fa, Gregorian otherwise. */
function local_date(?string $datetime, bool $withTime = false): string
{
    if (!$datetime) {
        return '';
    }
    $ts = strtotime($datetime);
    if (!$ts) {
        return '';
    }
    if (I18n::$lang === 'fa') {
        [$y, $m, $d] = Jalali::fromGregorian((int)date('Y', $ts), (int)date('n', $ts), (int)date('j', $ts));
        $s = sprintf('%04d/%02d/%02d', $y, $m, $d);
    } else {
        $s = date('Y-m-d', $ts);
    }
    if ($withTime) {
        $s .= ' ' . date('H:i', $ts);
    }
    return I18n::digits($s);
}

/** Cached media row by id. */
function media($id): ?array
{
    static $cache = [];
    $id = (int)$id;
    if ($id <= 0) {
        return null;
    }
    if (!array_key_exists($id, $cache)) {
        $cache[$id] = DB::one('SELECT * FROM media WHERE id = ?', [$id]);
    }
    return $cache[$id];
}

/** Preload many media rows at once (avoids N queries in lists). */
function media_preload(array $ids): void
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
    if (!$ids) {
        return;
    }
    foreach (DB::all('SELECT * FROM media WHERE id IN (' . implode(',', $ids) . ')') as $row) {
        Media::remember($row);
    }
}

function media_src($m, int $width = 960, string $fmt = 'jpg'): string
{
    $m = is_array($m) ? $m : media($m);
    return $m ? Media::src($m, $width, $fmt) : '';
}

/**
 * Responsive <picture> tag with WebP + fallback, lazy loading and a blurred preview.
 * Options: sizes, class, alt, eager (bool), ratio (CSS aspect-ratio), max (largest width).
 */
function picture($m, array $o = []): string
{
    $m = is_array($m) ? $m : media($m);
    $class = $o['class'] ?? '';
    if (!$m) {
        return '<span class="img-ph ' . e($class) . '" aria-hidden="true">' . icon('image') . '</span>';
    }
    return Media::picture($m, $o);
}

function stars(int $n): string
{
    $h = '<span class="stars" role="img" aria-label="' . e(__('common.stars', ['n' => $n])) . '">';
    for ($i = 0; $i < $n; $i++) {
        $h .= icon('star', 'star');
    }
    return $h . '</span>';
}

/** Tel link value (digits and + only). */
function tel(string $phone): string
{
    $p = strtr($phone, ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9']);
    return preg_replace('/[^0-9+]/', '', $p);
}

function whatsapp_link(string $text = ''): string
{
    $n = preg_replace('/\D/', '', (string)setting('contact.whatsapp', ''));
    if ($n === '') {
        return '';
    }
    return 'https://wa.me/' . $n . ($text !== '' ? '?text=' . rawurlencode($text) : '');
}

function telegram_link(): string
{
    $t = trim((string)setting('contact.telegram', ''), '@ ');
    return $t === '' ? '' : 'https://t.me/' . rawurlencode($t);
}

function social_url(string $network, string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }
    if (preg_match('~^https?://~', $value)) {
        return $value;
    }
    $v = rawurlencode(ltrim($value, '@'));
    return match ($network) {
        'instagram' => 'https://instagram.com/' . $v,
        'telegram' => 'https://t.me/' . $v,
        'whatsapp' => 'https://wa.me/' . preg_replace('/\D/', '', $value),
        'bale' => 'https://ble.ir/' . $v,
        'eitaa' => 'https://eitaa.com/' . $v,
        'aparat' => 'https://www.aparat.com/' . $v,
        'youtube' => 'https://youtube.com/@' . $v,
        default => '',
    };
}

function flash(?string $msg = null, string $type = 'ok')
{
    if ($msg !== null) {
        $_SESSION['_flash'][] = ['msg' => $msg, 'type' => $type];
        return null;
    }
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}

function log_error(string $msg): void
{
    @file_put_contents(STORAGE . '/logs/app.log', '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n", FILE_APPEND);
}

/** Bed types the admin can choose from. */
function bed_types(): array
{
    return ['single', 'double', 'queen', 'king', 'twin', 'bunk', 'sofa', 'extra'];
}

/** "1 double bed + 1 single bed" in the current language. */
function bed_summary(array $layout): string
{
    $parts = [];
    foreach ($layout as $b) {
        $n = (int)($b['n'] ?? 1);
        $t = (string)($b['t'] ?? 'single');
        if ($n <= 0) {
            continue;
        }
        $parts[] = __('bed.count', ['n' => $n, 'type' => __('bed.' . $t)]);
    }
    return implode(' + ', $parts);
}

/** Small top-down drawing of the beds, so similar rooms are easy to tell apart. */
function bed_diagram(array $layout, string $class = 'bed-diagram'): string
{
    $sizes = ['single' => [15, 28, 1], 'twin' => [15, 28, 1], 'double' => [26, 28, 2], 'queen' => [28, 30, 2], 'king' => [32, 30, 2], 'bunk' => [15, 28, 1], 'sofa' => [26, 14, 0], 'extra' => [13, 24, 1]];
    $x = 2;
    $shapes = '';
    $h = 34;
    $count = 0;
    foreach ($layout as $b) {
        $t = (string)($b['t'] ?? 'single');
        [$w, $bh, $pillows] = $sizes[$t] ?? $sizes['single'];
        for ($i = 0; $i < (int)($b['n'] ?? 1) && $count < 12; $i++, $count++) {
            $y = $h - $bh - 2;
            $dash = $t === 'extra' ? ' stroke-dasharray="2 2"' : '';
            $shapes .= '<rect x="' . $x . '" y="' . $y . '" width="' . $w . '" height="' . $bh . '" rx="3"' . $dash . '/>';
            if ($pillows === 1) {
                $shapes .= '<rect class="p" x="' . ($x + 3) . '" y="' . ($y + 2.5) . '" width="' . ($w - 6) . '" height="5" rx="1.5"/>';
            } elseif ($pillows === 2) {
                $pw = ($w - 9) / 2;
                $shapes .= '<rect class="p" x="' . ($x + 3) . '" y="' . ($y + 2.5) . '" width="' . $pw . '" height="5" rx="1.5"/><rect class="p" x="' . ($x + 6 + $pw) . '" y="' . ($y + 2.5) . '" width="' . $pw . '" height="5" rx="1.5"/>';
            }
            if ($t !== 'sofa') {
                $shapes .= '<line x1="' . $x . '" y1="' . ($y + $bh * 0.42) . '" x2="' . ($x + $w) . '" y2="' . ($y + $bh * 0.42) . '"/>';
            }
            if ($t === 'bunk') {
                $shapes .= '<text x="' . ($x + $w / 2) . '" y="' . ($y + $bh * 0.78) . '" text-anchor="middle">×2</text>';
            }
            $x += $w + 6;
        }
    }
    if ($shapes === '') {
        return '';
    }
    $W = $x - 4;
    return '<svg class="' . e($class) . '" viewBox="0 0 ' . $W . ' ' . $h . '" width="' . ($W * 1.4) . '" height="' . ($h * 1.4) . '" role="img" aria-label="' . e(bed_summary($layout)) . '"><g fill="none" stroke="currentColor" stroke-width="1.3">' . $shapes . '</g></svg>';
}

/** Data the room card / page needs for the guest-count price switcher. */
function room_price_data(array $room): array
{
    $p = Content::roomPrices($room);
    $fmt = [];
    $sec = [];
    foreach ($p['byGuests'] as $g => $v) {
        $fmt[$g] = $v ? money($v) : '';
        $sec[$g] = $v ? money_secondary($v) : '';
    }
    return ['p' => $p, 'fmt' => $fmt, 'sec' => $sec, 'extra' => $p['extra']];
}

/** "Up to 3 guests (+1 extra)" */
function capacity_text(array $room): string
{
    $t = __('rooms.up_to', ['n' => (int)$room['max_guests']]);
    if (!empty($room['max_extra'])) {
        $t .= ' ' . __('rooms.cap_extra', ['n' => (int)$room['max_extra']]);
    }
    return $t;
}
