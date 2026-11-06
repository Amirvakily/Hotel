<?php
/**
 * Public website pages.
 */
final class Front
{
    private const ROUTES = [
        '~^$~' => ['home', true],
        '~^rooms$~' => ['rooms', true],
        '~^rooms/([a-z0-9\-]+)$~' => ['room', true],
        '~^gallery$~' => ['gallery', true],
        '~^tour$~' => ['tour', true],
        '~^breakfast$~' => ['breakfast', true],
        '~^cafe$~' => ['cafe', true],
        '~^about$~' => ['about', true],
        '~^attractions$~' => ['attractions', true],
        '~^contact$~' => ['contact', false],
        '~^page/([a-z0-9\-]+)$~' => ['page', true],
        '~^sitemap\.xml$~' => ['sitemap', false],
        '~^robots\.txt$~' => ['robots', false],
    ];
    /** Query parameters that may vary a cached page. */
    private const CACHE_QUERY = ['r', 'lang', 'cat', 'scene'];

    public static function dispatch(string $path): void
    {
        $handler = null;
        $args = [];
        $cacheable = false;
        foreach (self::ROUTES as $re => [$method, $cache]) {
            if (preg_match($re, $path, $m)) {
                $handler = $method;
                $args = array_slice($m, 1);
                $cacheable = $cache;
                break;
            }
        }

        if (setting('system.maintenance') && !self::adminLoggedIn() && $handler !== 'robots') {
            self::maintenance();
            return;
        }
        if (!$handler) {
            self::notFound();
            return;
        }
        // Pages switched off in the navigation settings are not reachable either.
        $navKey = ['rooms' => 'rooms', 'room' => 'rooms', 'gallery' => 'gallery', 'tour' => 'tour', 'breakfast' => 'breakfast', 'cafe' => 'cafe', 'attractions' => 'attractions', 'about' => 'about', 'contact' => 'contact'][$handler] ?? null;
        if ($navKey && !Content::navEnabled($navKey)) {
            self::notFound();
            return;
        }

        $isGet = in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true);
        $extraQuery = array_diff(array_keys($_GET), self::CACHE_QUERY);
        $useCache = $cacheable && $isGet && !$extraQuery && Cache::enabled() && !isset($_COOKIE[Auth::COOKIE]);
        $cacheKey = (App::$pretty ? 'p|' : 'q|') . App::$base . '|' . I18n::$lang . '|' . $path . '|' . http_build_query(array_intersect_key($_GET, array_flip(self::CACHE_QUERY)));

        if ($isGet) {
            self::track($path);
        }
        if ($useCache && Cache::serve($cacheKey)) {
            return;
        }

        $html = self::$handler(...$args);
        if ($html === null) {
            return; // handler already responded
        }
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
            header('Cache-Control: public, max-age=0, must-revalidate');
        }
        if ($useCache && http_response_code() === 200) {
            Cache::store($cacheKey, $html);
        }
        echo $html;
    }

    private static function adminLoggedIn(): bool
    {
        if (!isset($_COOKIE[Auth::COOKIE])) {
            return false;
        }
        Auth::start();
        return Auth::check();
    }

    private static function track(string $path): void
    {
        if (!setting('system.analytics', true) || App::isBot()) {
            return;
        }
        try {
            DB::bump('visits', 'day', date('Y-m-d'), 'views');
            DB::bump('visit_paths', 'path', mb_substr('/' . $path, 0, 150), 'views');
        } catch (Throwable $e) {
            // counting must never break a page
        }
    }

    private static function page(string $slug): ?string
    {
        $p = Content::page($slug);
        if (!$p) {
            self::notFound();
            return null;
        }
        App::$page = ['title' => tr($p['title']), 'description' => str_limit(tr($p['excerpt']) ?: tr($p['body']), 160), 'image' => $p['cover_id'], 'key' => 'page-' . $slug];
        return View::render('front/page', ['p' => $p]);
    }

    // ---------------- pages ----------------

    private static function home(): string
    {
        App::$page = ['title' => '', 'description' => tr(setting('seo.description')), 'key' => 'home', 'home' => true];
        return View::render('front/home', ['sections' => Content::sections()]);
    }

    private static function rooms(): string
    {
        App::$page = ['title' => __('rooms.title'), 'description' => __('rooms.meta'), 'key' => 'rooms'];
        $rooms = Content::rooms();
        return View::render('front/rooms', ['rooms' => $rooms, 'amenities' => Content::amenities()]);
    }

    private static function room(string $slug): ?string
    {
        $room = Content::room($slug);
        if (!$room) {
            self::notFound();
            return null;
        }
        media_preload(array_merge([$room['cover_id']], $room['gallery']));
        $scene = null;
        if ($room['scene_id'] && Content::tourOn()) {
            $s = DB::one('SELECT slug FROM scenes WHERE id = ? AND active = 1', [$room['scene_id']]);
            if ($s) {
                $scene = $s['slug'];
            }
        }
        App::$page = [
            'title' => tr($room['name']),
            'description' => str_limit(tr($room['summary']) ?: tr($room['description']), 160),
            'image' => $room['cover_id'],
            'key' => 'rooms',
        ];
        $others = array_values(array_filter(Content::rooms(), fn($r) => $r['id'] !== $room['id']));
        return View::render('front/room', [
            'room' => $room,
            'amenities' => Content::amenities(),
            'others' => array_slice($others, 0, 3),
            'scene' => $scene,
            'tour' => $scene ? Content::tourConfig($scene) : null,
        ]);
    }

    private static function gallery(): string
    {
        App::$page = ['title' => __('gallery.title'), 'description' => __('gallery.subtitle'), 'key' => 'gallery'];
        return View::render('front/gallery', [
            'categories' => Content::galleryCategories(),
            'items' => Content::galleryItems(),
            'active' => (string)($_GET['cat'] ?? ''),
        ]);
    }

    private static function tour(): string
    {
        App::$page = ['title' => __('tour.title'), 'description' => __('tour.subtitle'), 'key' => 'tour'];
        $scene = isset($_GET['scene']) ? (string)$_GET['scene'] : null;
        return View::render('front/tour', ['tour' => Content::tourConfig($scene), 'scenes' => Content::scenes()]);
    }

    private static function breakfast(): string
    {
        $pg = (array)setting('page.breakfast', []);
        App::$page = ['title' => tr($pg['title'] ?? '') ?: __('nav.breakfast'), 'description' => str_limit(tr($pg['body'] ?? ''), 160), 'image' => $pg['cover_id'] ?? null, 'key' => 'breakfast'];
        return View::render('front/dining', ['kind' => 'breakfast', 'pg' => $pg, 'menu' => Content::menu('breakfast'), 'hours' => tr(setting('hotel.breakfast_hours'))]);
    }

    private static function cafe(): string
    {
        $pg = (array)setting('page.cafe', []);
        App::$page = ['title' => tr($pg['title'] ?? '') ?: __('nav.cafe'), 'description' => str_limit(tr($pg['body'] ?? ''), 160), 'image' => $pg['cover_id'] ?? null, 'key' => 'cafe'];
        return View::render('front/dining', ['kind' => 'cafe', 'pg' => $pg, 'menu' => Content::menu('cafe'), 'hours' => tr(setting('hotel.cafe_hours'))]);
    }

    private static function about(): string
    {
        $pg = (array)setting('page.about', []);
        App::$page = ['title' => __('about.title'), 'description' => str_limit(tr($pg['body'] ?? '') ?: tr(setting('seo.description')), 160), 'image' => $pg['cover_id'] ?? null, 'key' => 'about'];
        return View::render('front/about', ['pg' => $pg, 'amenities' => Content::amenities('hotel'), 'faqs' => Content::faqs()]);
    }

    private static function attractions(): string
    {
        App::$page = ['title' => __('attractions.title'), 'description' => __('attractions.subtitle'), 'key' => 'attractions'];
        return View::render('front/attractions', ['items' => Content::attractions()]);
    }

    private static function contact(): string
    {
        App::$page = ['title' => __('contact.title'), 'description' => __('contact.subtitle'), 'key' => 'contact'];
        $errors = [];
        $old = [];
        $sent = isset($_GET['sent']);
        if (is_post() && setting('form.enabled', true)) {
            [$errors, $old] = self::handleContact();
            if (!$errors) {
                redirect(url('contact', ['sent' => 1]) . '#form', 303);
            }
            http_response_code(422);
        }
        $ts = time();
        return View::render('front/contact', [
            'errors' => $errors, 'old' => $old, 'sent' => $sent,
            'ts' => $ts, 'sig' => hash_hmac('sha256', 'contact|' . $ts, app_key()),
        ]);
    }

    /** @return array{0: array, 1: array} [errors, old input] */
    private static function handleContact(): array
    {
        $f = [
            'name' => Sanitizer::text($_POST['name'] ?? '', 100),
            'phone' => Sanitizer::text($_POST['phone'] ?? '', 40),
            'email' => Sanitizer::text($_POST['email'] ?? '', 150),
            'subject' => Sanitizer::text($_POST['subject'] ?? '', 150),
            'body' => Sanitizer::text($_POST['body'] ?? '', 3000),
        ];
        $errors = [];
        // Spam checks: hidden field, signed timestamp (form filled in < 3 s or > 1 day is rejected), rate limit.
        if (!empty($_POST['website'])) {
            redirect(url('contact', ['sent' => 1]), 303);
        }
        $ts = (int)($_POST['ts'] ?? 0);
        $sig = (string)($_POST['sig'] ?? '');
        $age = time() - $ts;
        if (!hash_equals(hash_hmac('sha256', 'contact|' . $ts, app_key()), $sig) || $age < 3 || $age > 86400) {
            $errors['form'] = __('contact.err_spam');
        }
        $recent = (int)DB::val('SELECT COUNT(*) FROM messages WHERE ip = ? AND created_at > ?', [client_ip(), date('Y-m-d H:i:s', time() - 3600)]);
        if ($recent >= 5) {
            $errors['form'] = __('contact.err_rate');
        }
        if (mb_strlen($f['name']) < 2) {
            $errors['name'] = __('contact.err_name');
        }
        if ($f['phone'] === '' && $f['email'] === '') {
            $errors['phone'] = __('contact.err_reach');
        }
        if ($f['email'] !== '' && !filter_var($f['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = __('contact.err_email');
        }
        if ($f['phone'] !== '' && !preg_match('~^[+0-9۰-۹٠-٩\s\-()]{6,20}$~u', $f['phone'])) {
            $errors['phone'] = __('contact.err_phone');
        }
        if (mb_strlen($f['body']) < 5) {
            $errors['body'] = __('contact.err_body');
        }
        if ($errors) {
            return [$errors, $f];
        }
        DB::insert('messages', $f + [
            'lang' => I18n::$lang, 'ip' => client_ip(),
            'user_agent' => mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 180),
            'is_read' => 0, 'created_at' => DB::now(),
        ]);
        $to = trim((string)setting('form.notify_email', ''));
        if ($to !== '' && filter_var($to, FILTER_VALIDATE_EMAIL) && function_exists('mail')) {
            $subject = '=?UTF-8?B?' . base64_encode('Venus Hotel – ' . ($f['subject'] ?: $f['name'])) . '?=';
            $body = "Name: {$f['name']}\nPhone: {$f['phone']}\nEmail: {$f['email']}\nSubject: {$f['subject']}\n\n{$f['body']}\n";
            $headers = "Content-Type: text/plain; charset=UTF-8\r\n";
            if ($f['email'] !== '') {
                $headers .= 'Reply-To: ' . str_replace(["\r", "\n"], '', $f['email']) . "\r\n";
            }
            @mail($to, $subject, $body, $headers);
        }
        return [[], []];
    }

    private static function sitemap(): ?string
    {
        header('Content-Type: application/xml; charset=utf-8');
        $paths = [''];
        foreach (['rooms', 'gallery', 'tour', 'breakfast', 'cafe', 'attractions', 'about', 'contact'] as $k) {
            if (Content::navEnabled($k)) {
                $paths[] = $k;
            }
        }
        foreach (Content::rooms() as $r) {
            $paths[] = 'rooms/' . $r['slug'];
        }
        foreach (Content::pages() as $p) {
            $paths[] = 'page/' . $p['slug'];
        }
        $x = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
        foreach ($paths as $p) {
            foreach (I18n::$enabled as $l) {
                $x .= '<url><loc>' . e(abs_url(url($p, [], $l))) . '</loc>';
                foreach (I18n::$enabled as $alt) {
                    $x .= '<xhtml:link rel="alternate" hreflang="' . $alt . '" href="' . e(abs_url(url($p, [], $alt))) . '"/>';
                }
                $x .= '<changefreq>weekly</changefreq><priority>' . ($p === '' ? '1.0' : '0.7') . '</priority></url>' . "\n";
            }
        }
        echo $x . '</urlset>';
        return null;
    }

    private static function robots(): ?string
    {
        header('Content-Type: text/plain; charset=utf-8');
        echo "User-agent: *\nDisallow: " . App::$base . "/admin/\nDisallow: " . App::$base . "/install/\nAllow: /\n\nSitemap: " . abs_url(url('sitemap.xml', [], I18n::$default)) . "\n";
        return null;
    }

    public static function notFound(): void
    {
        http_response_code(404);
        App::$page = ['title' => __('404.title'), 'noindex' => true, 'key' => '404'];
        echo View::render('front/404', []);
    }

    private static function maintenance(): void
    {
        http_response_code(503);
        header('Retry-After: 3600');
        App::$page = ['title' => __('maintenance.title'), 'noindex' => true, 'key' => 'maintenance', 'bare' => true];
        echo View::render('front/maintenance', [], null);
    }
}
