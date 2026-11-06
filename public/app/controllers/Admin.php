<?php
/**
 * Admin panel: /admin/...
 */
final class Admin
{
    private static array $errors = [];

    public static function dispatch(string $path): void
    {
        Auth::start();
        header('X-Robots-Tag: noindex, nofollow');
        header('Cache-Control: no-store, private');
        $user = Auth::user();
        $lang = $user['lang'] ?? ($_COOKIE['venus_admin_lang'] ?? 'fa');
        I18n::set(in_array($lang, ['fa', 'en'], true) ? $lang : 'fa');

        $p = $path === '' ? [] : explode('/', trim($path, '/'));
        if (($p[0] ?? '') === 'login') {
            self::login();
            return;
        }
        if (!$user) {
            if (self::wantsJson()) {
                json_out(['error' => 'auth'], 401);
            }
            redirect(admin_url('login'));
        }
        if (is_post() && !csrf_check()) {
            if (self::wantsJson()) {
                json_out(['error' => __('admin.csrf')], 403);
            }
            flash(__('admin.csrf'), 'err');
            redirect($_SERVER['HTTP_REFERER'] ?? admin_url());
        }

        $a = $p[0] ?? '';
        try {
            match (true) {
                $a === '' => self::dashboard(),
                $a === 'logout' => self::logout(),
                $a === 'r' => self::resource($p[1] ?? '', $p[2] ?? '', $p[3] ?? ''),
                $a === 'settings' => self::requireAdmin() && self::settings($p[1] ?? 'general'),
                $a === 'sections' => self::sections($p[1] ?? ''),
                $a === 'prices' => self::prices(),
                $a === 'media' => self::media($p[1] ?? '', $p[2] ?? ''),
                $a === 'messages' => self::messages($p[1] ?? '', $p[2] ?? ''),
                $a === 'translations' => self::requireAdmin() && self::translations(),
                $a === 'users' => self::requireAdmin() && self::users($p[1] ?? '', $p[2] ?? ''),
                $a === 'account' => self::account(),
                $a === 'backup' => self::requireAdmin() && self::backup($p[1] ?? '', $p[2] ?? ''),
                $a === 'cache' => self::clearCache(),
                default => self::notFound(),
            };
        } catch (Throwable $e) {
            log_error('admin: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            if (self::wantsJson()) {
                json_out(['error' => cfg('debug') ? $e->getMessage() : __('admin.error')], 500);
            }
            http_response_code(500);
            echo self::render('admin/error', ['message' => cfg('debug') ? $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')' : __('admin.error')]);
        }
    }

    // ------------------------------------------------------------------ helpers
    private static function wantsJson(): bool
    {
        return str_contains((string)($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json') || isset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    private static function render(string $tpl, array $data = []): string
    {
        return View::render($tpl, $data + ['errors' => self::$errors], 'admin/layout');
    }

    private static function out(string $tpl, array $data = []): bool
    {
        header('Content-Type: text/html; charset=utf-8');
        echo self::render($tpl, $data);
        return true;
    }

    private static function requireAdmin(): bool
    {
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo self::render('admin/error', ['message' => __('admin.only_admin')]);
            exit;
        }
        return true;
    }

    private static function changed(): void
    {
        Cache::flush();
        Content::reset();
        Settings::reset();
        I18n::resetCache();
    }

    private static function notFound(): bool
    {
        http_response_code(404);
        return self::out('admin/error', ['message' => __('admin.not_found')]);
    }

    private static function input(): array
    {
        $f = $_POST['f'] ?? [];
        return is_array($f) ? $f : [];
    }

    // ------------------------------------------------------------------ auth
    private static function login(): void
    {
        if (Auth::check()) {
            redirect(admin_url());
        }
        $error = '';
        if (is_post()) {
            if (!csrf_check()) {
                $error = __('admin.csrf');
            } elseif (Auth::throttled()) {
                $error = __('admin.login_throttled');
            } elseif (Auth::attempt((string)($_POST['username'] ?? ''), (string)($_POST['password'] ?? ''))) {
                Auth::cleanup();
                $to = (string)($_GET['next'] ?? '');
                redirect(str_starts_with($to, App::$base . '/admin') ? $to : admin_url());
            } else {
                $error = __('admin.login_failed');
                usleep(400000);
            }
        }
        header('Content-Type: text/html; charset=utf-8');
        echo View::render('admin/login', ['error' => $error], null);
    }

    private static function logout(): bool
    {
        if (is_post()) {
            Auth::logout();
        }
        redirect(admin_url('login'));
        return true;
    }

    // ------------------------------------------------------------------ dashboard
    private static function dashboard(): bool
    {
        $days = [];
        $since = date('Y-m-d', strtotime('-29 days'));
        $rows = [];
        foreach (DB::all('SELECT day, views FROM visits WHERE day >= ?', [$since]) as $r) {
            $rows[$r['day']] = (int)$r['views'];
        }
        for ($i = 29; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $days[] = ['day' => $d, 'views' => $rows[$d] ?? 0];
        }
        $stats = [
            'rooms' => (int)DB::val('SELECT COUNT(*) FROM rooms WHERE active = 1'),
            'photos' => (int)DB::val("SELECT COUNT(*) FROM media WHERE kind = 'image'"),
            'scenes' => (int)DB::val('SELECT COUNT(*) FROM scenes WHERE active = 1'),
            'unread' => (int)DB::val('SELECT COUNT(*) FROM messages WHERE is_read = 0'),
            'today' => $rows[date('Y-m-d')] ?? 0,
            'month' => array_sum(array_column($days, 'views')),
        ];
        $top = DB::all('SELECT path, views FROM visit_paths ORDER BY views DESC LIMIT 8');
        $messages = DB::all('SELECT * FROM messages ORDER BY id DESC LIMIT 5');
        $phones = (array)setting('contact.phones', []);
        $checklist = [
            ['admin.check.phone', !in_array('041-3555-0000', $phones, true) && $phones, admin_url('settings/contact')],
            ['admin.check.map', abs((float)setting('map.lat') - 38.0802) > 0.00001 || abs((float)setting('map.lng') - 46.2930) > 0.00001, admin_url('settings/location')],
            ['admin.check.logo', (bool)setting('site.logo'), admin_url('settings/general')],
            ['admin.check.prices', (bool)DB::val('SELECT COUNT(*) FROM price_presets'), admin_url('prices')],
            ['admin.check.photos', (int)DB::val("SELECT COUNT(*) FROM media WHERE kind = 'image' AND title NOT LIKE 'room-%' AND title NOT LIKE 'lobby%' AND title NOT LIKE 'cafe%' AND title NOT LIKE 'breakfast%' AND title NOT LIKE 'exterior%' AND title NOT LIKE 'att-%' AND title NOT IN ('reception','bathroom','corridor')") > 0, admin_url('r/gallery_items')],
        ];
        return self::out('admin/dashboard', compact('days', 'stats', 'top', 'messages', 'checklist'));
    }

    // ------------------------------------------------------------------ generic resources
    private static function resource(string $key, string $id, string $action): bool
    {
        $res = AdminResources::get($key);
        if (!$res) {
            return self::notFound();
        }
        $t = $res['table'];
        if ($id === 'sort' && is_post()) {
            $ids = json_decode(file_get_contents('php://input') ?: '[]', true)['ids'] ?? ($_POST['ids'] ?? []);
            DB::tx(function () use ($ids, $t) {
                foreach (array_values((array)$ids) as $i => $rid) {
                    DB::update($t, ['sort' => $i], 'id = ?', [(int)$rid]);
                }
            });
            self::changed();
            json_out(['ok' => true]);
        }
        if ($id === 'bulk' && is_post() && $key === 'gallery_items') {
            $cat = (int)($_POST['category_id'] ?? 0) ?: null;
            $ids = array_filter(array_map('intval', explode(',', (string)($_POST['media_ids'] ?? ''))));
            $sort = (int)DB::val("SELECT COALESCE(MAX(sort), 0) FROM gallery_items") + 1;
            foreach ($ids as $mid) {
                DB::insert('gallery_items', ['category_id' => $cat, 'media_id' => $mid, 'caption' => '{}', 'active' => 1, 'sort' => $sort++, 'created_at' => DB::now()]);
            }
            self::changed();
            flash(__('admin.added_n', ['n' => count($ids)]));
            redirect(admin_url('r/gallery_items', $cat ? ['category_id' => $cat] : []));
        }
        if ($id === 'switch' && is_post() && $key === 'scenes') {
            self::requireAdmin();
            $on = !empty($_POST['on']);
            $items = array_values((array)setting('nav.items', []));
            $found = false;
            foreach ($items as &$item) {
                if (($item['key'] ?? '') === 'tour') {
                    $item['on'] = $on;
                    $found = true;
                }
            }
            unset($item);
            if (!$found) {
                $items[] = ['key' => 'tour', 'on' => $on];
            }
            Settings::set('nav.items', $items);
            self::changed();
            flash(__($on ? 'admin.tour_switched_on' : 'admin.tour_switched_off'));
            redirect(admin_url('r/scenes'));
        }
        if ($id === '') {
            return self::resourceList($res);
        }
        $row = null;
        if ($id !== 'new') {
            $row = DB::one('SELECT * FROM ' . DB::ident($t) . ' WHERE id = ?', [(int)$id]);
            if (!$row) {
                return self::notFound();
            }
        }
        if ($action === 'delete' && is_post() && $row) {
            self::deleteRow($res, $row);
            self::changed();
            flash(__('admin.deleted'));
            redirect(admin_url('r/' . $key));
        }
        if ($action === 'toggle' && is_post() && $row) {
            $col = preg_match('~^[a-z_]+$~', (string)($_POST['col'] ?? 'active')) ? $_POST['col'] : 'active';
            if (!array_key_exists($col, $row)) {
                json_out(['error' => 'bad column'], 400);
            }
            $v = $row[$col] ? 0 : 1;
            DB::update($t, [$col => $v], 'id = ?', [$row['id']]);
            self::changed();
            json_out(['ok' => true, 'value' => $v]);
        }
        if ($action === 'duplicate' && is_post() && $row) {
            $copy = $row;
            unset($copy['id']);
            if (isset($copy['slug'])) {
                $copy['slug'] = self::uniqueSlug($t, $copy['slug'] . '-copy', 0);
            }
            if (isset($copy['sort'])) {
                $copy['sort'] = (int)DB::val('SELECT COALESCE(MAX(sort),0) FROM ' . DB::ident($t)) + 1;
            }
            if (isset($copy['is_default'])) {
                $copy['is_default'] = 0;
            }
            $nid = DB::insert($t, $copy);
            self::changed();
            flash(__('admin.duplicated'));
            redirect(admin_url('r/' . $key . '/' . $nid));
        }
        if (is_post()) {
            $saved = self::saveRow($res, $row);
            if ($saved) {
                self::changed();
                flash(__('admin.saved'));
                redirect(isset($_POST['back']) ? admin_url('r/' . $key) : admin_url('r/' . $key . '/' . $saved));
            }
            $row = array_merge($row ?? [], self::$postedRow);
        }
        return self::out('admin/form', ['res' => $res, 'row' => $row, 'isNew' => !$row || !isset($row['id'])]);
    }

    private static array $postedRow = [];

    private static function resourceList(array $res): bool
    {
        $t = $res['table'];
        $where = '1=1';
        $params = [];
        $filter = $res['filter'] ?? null;
        $fv = $filter !== null ? ($_GET[$filter] ?? '') : '';
        if ($filter && $fv !== '') {
            if ($filter === 'category_id' && $res['key'] === 'menu_items' && !ctype_digit((string)$fv)) {
                $where = 'category_id IN (SELECT id FROM menu_categories WHERE section = ?)';
            } else {
                $where = DB::ident($filter) . ' = ?';
            }
            $params[] = $fv;
        }
        $order = !empty($res['sortable']) ? 'sort, id' : 'id DESC';
        $rows = DB::all('SELECT * FROM ' . DB::ident($t) . " WHERE $where ORDER BY $order", $params);
        $mediaIds = [];
        foreach ($rows as $r) {
            foreach (['cover_id', 'media_id'] as $c) {
                if (!empty($r[$c])) {
                    $mediaIds[] = $r[$c];
                }
            }
        }
        media_preload($mediaIds);
        return self::out($res['view'] ?? null ? 'admin/' . $res['view'] : 'admin/list', ['res' => $res, 'rows' => $rows, 'filterValue' => $fv]);
    }

    private static function uniqueSlug(string $table, string $slug, int $exceptId): string
    {
        $base = $slug !== '' ? $slug : 'item';
        $s = $base;
        $i = 2;
        while (DB::val('SELECT COUNT(*) FROM ' . DB::ident($table) . ' WHERE slug = ? AND id <> ?', [$s, $exceptId])) {
            $s = $base . '-' . $i++;
        }
        return $s;
    }

    /** @return int|false the row id, or false when validation failed */
    private static function saveRow(array $res, ?array $row)
    {
        $in = self::input();
        $t = $res['table'];
        $data = [];
        $errors = [];
        foreach ($res['fields'] as $f) {
            $val = AdminForm::read($f, $in);
            if (is_array($val) && isset($val['__skip'])) {
                continue;
            }
            if (is_array($val) && isset($val['__cols'])) {
                $data = array_merge($data, $val['__cols']);
                continue;
            }
            if (!empty($f['required'])) {
                $empty = is_array($val) ? trim((string)($val[I18n::$default] ?? reset($val) ?? '')) === '' : ($val === null || $val === '');
                if ($empty) {
                    $errors[$f['name']] = __('admin.required');
                }
            }
            $data[$f['name']] = AdminForm::toColumn($f, $val);
        }
        $id = (int)($row['id'] ?? 0);
        if (array_key_exists('slug', $data)) {
            $slug = (string)$data['slug'];
            if ($slug === '') {
                $src = json_dec($data['name'] ?? $data['title'] ?? '', []);
                $slug = slugify((string)($src['en'] ?? '') ?: (string)($src['fa'] ?? ''));
            }
            $data['slug'] = self::uniqueSlug($t, $slug, $id);
        }
        if ($res['key'] === 'rooms') {
            $data['beds'] = max(1, (int)$data['beds']);
            $data['min_guests'] = max(1, (int)$data['min_guests']);
            $data['max_guests'] = max($data['min_guests'], (int)$data['max_guests']);
            $prices = json_dec($data['prices'], []);
            $keep = [];
            for ($g = $data['min_guests']; $g <= $data['max_guests']; $g++) {
                $keep[(string)$g] = $prices[(string)$g] ?? null;
            }
            $data['prices'] = json_enc((object)$keep);
        }
        if ($res['key'] === 'scenes' && ($data['type'] ?? '') !== 'embed' && empty($data['media_id'])) {
            $errors['media_id'] = __('admin.required');
        }
        if ($res['key'] === 'scenes' && ($data['type'] ?? '') === 'embed' && empty($data['embed_url'])) {
            $errors['embed_url'] = __('admin.required');
        }
        if ($errors) {
            self::$errors = $errors;
            $posted = [];
            foreach ($data as $k => $v) {
                $posted[$k] = $v;
            }
            self::$postedRow = $posted;
            flash(__('admin.fix_errors'), 'err');
            return false;
        }
        if (!empty($res['timestamps'])) {
            $data['updated_at'] = DB::now();
            if (!$id) {
                $data['created_at'] = DB::now();
            }
        }
        if (!empty($res['timestamps_updated'])) {
            $data['updated_at'] = DB::now();
        }
        if (!empty($res['timestamps_created']) && !$id) {
            $data['created_at'] = DB::now();
        }
        return DB::tx(function () use ($data, $id, $t, $res) {
            if (($data['is_default'] ?? 0) && $res['key'] === 'scenes') {
                DB::update($t, ['is_default' => 0], 'id <> ?', [$id]);
            }
            if ($id) {
                DB::update($t, $data, 'id = ?', [$id]);
                return $id;
            }
            if (!empty($res['sortable'])) {
                $data['sort'] = (int)DB::val('SELECT COALESCE(MAX(sort), 0) FROM ' . DB::ident($t)) + 1;
            }
            return DB::insert($t, $data);
        });
    }

    private static function deleteRow(array $res, array $row): void
    {
        $t = $res['table'];
        DB::tx(function () use ($res, $row, $t) {
            if ($res['key'] === 'menu_categories') {
                DB::delete('menu_items', 'category_id = ?', [$row['id']]);
            }
            if ($res['key'] === 'gallery_categories') {
                DB::update('gallery_items', ['category_id' => null], 'category_id = ?', [$row['id']]);
            }
            if ($res['key'] === 'scenes') {
                DB::update('rooms', ['scene_id' => null], 'scene_id = ?', [$row['id']]);
            }
            DB::delete($t, 'id = ?', [$row['id']]);
        });
    }

    // ------------------------------------------------------------------ settings
    private static function settings(string $group): bool
    {
        $groups = AdminResources::settings();
        if (!isset($groups[$group])) {
            return self::notFound();
        }
        $def = $groups[$group];
        if (is_post()) {
            $in = self::input();
            $values = [];
            $nested = [];
            foreach ($def['fields'] as $f) {
                if (!empty($f['admin_only']) && !Auth::isAdmin()) {
                    continue;
                }
                $val = AdminForm::read($f, $in);
                if (is_array($val) && isset($val['__skip'])) {
                    continue;
                }
                if (is_array($val) && isset($val['__cols'])) {
                    foreach ($val['__cols'] as $k => $v) {
                        $values[$k] = $v;
                    }
                    continue;
                }
                if (str_contains($f['name'], '>')) {
                    [$k, $sub] = explode('>', $f['name'], 2);
                    $nested[$k][$sub] = $val;
                    continue;
                }
                $values[$f['name']] = $val;
            }
            foreach ($nested as $k => $subs) {
                $cur = (array)setting($k, []);
                $values[$k] = array_merge($cur, $subs);
            }
            if (isset($values['lang.enabled'], $values['lang.default']) && !in_array($values['lang.default'], $values['lang.enabled'], true)) {
                $values['lang.default'] = $values['lang.enabled'][0];
            }
            Settings::setMany($values);
            self::changed();
            flash(__('admin.saved'));
            redirect(admin_url('settings/' . $group));
        }
        return self::out('admin/settings', ['groups' => $groups, 'group' => $group, 'def' => $def]);
    }

    // ------------------------------------------------------------------ home sections
    private static function sections(string $key): bool
    {
        if ($key === '') {
            if (is_post()) {
                $body = json_decode(file_get_contents('php://input') ?: '{}', true) ?: $_POST;
                if (isset($body['order'])) {
                    DB::tx(function () use ($body) {
                        foreach (array_values((array)$body['order']) as $i => $id) {
                            DB::update('sections', ['sort' => $i], 'id = ?', [(int)$id]);
                        }
                    });
                }
                if (isset($body['toggle'])) {
                    $r = DB::one('SELECT * FROM sections WHERE id = ?', [(int)$body['toggle']]);
                    if ($r) {
                        DB::update('sections', ['active' => $r['active'] ? 0 : 1], 'id = ?', [$r['id']]);
                    }
                }
                self::changed();
                json_out(['ok' => true]);
            }
            return self::out('admin/sections', ['sections' => DB::all('SELECT * FROM sections ORDER BY sort, id')]);
        }
        $row = DB::one('SELECT * FROM sections WHERE skey = ?', [$key]);
        if (!$row) {
            return self::notFound();
        }
        $fields = AdminResources::sectionFields($key);
        if (is_post()) {
            $in = self::input();
            $data = json_dec($row['data'], []);
            foreach ($fields as $f) {
                $data[$f['name']] = AdminForm::read($f, $in);
            }
            DB::update('sections', ['data' => json_enc($data), 'active' => isset($_POST['active']) ? 1 : 0], 'id = ?', [$row['id']]);
            self::changed();
            flash(__('admin.saved'));
            redirect(admin_url('sections/' . $key));
        }
        return self::out('admin/section', ['row' => $row, 'fields' => $fields, 'data' => json_dec($row['data'], [])]);
    }

    // ------------------------------------------------------------------ prices & presets
    private static function readGrid(): array
    {
        $grid = [];
        foreach ((array)($_POST['p'] ?? []) as $rid => $byG) {
            foreach ((array)$byG as $g => $v) {
                $d = preg_replace('~\D~', '', strtr((string)$v, ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9']));
                $grid[(int)$rid][(string)(int)$g] = $d === '' || (int)$d <= 0 ? null : (int)$d;
            }
        }
        $extra = [];
        foreach ((array)($_POST['x'] ?? []) as $rid => $v) {
            $d = preg_replace('~\D~', '', (string)$v);
            $extra[(int)$rid] = $d === '' || (int)$d <= 0 ? null : (int)$d;
        }
        return ['prices' => $grid, 'extra' => $extra];
    }

    private static function applyGrid(array $grid): int
    {
        $n = 0;
        DB::tx(function () use ($grid, &$n) {
            foreach (DB::all('SELECT id, min_guests, max_guests FROM rooms') as $r) {
                if (!isset($grid['prices'][(int)$r['id']])) {
                    continue;
                }
                $keep = [];
                for ($g = max(1, (int)$r['min_guests']); $g <= max(1, (int)$r['max_guests']); $g++) {
                    $keep[(string)$g] = $grid['prices'][(int)$r['id']][(string)$g] ?? null;
                }
                $upd = ['prices' => json_enc((object)$keep), 'updated_at' => DB::now()];
                if (array_key_exists((int)$r['id'], $grid['extra'] ?? [])) {
                    $upd['extra_bed_price'] = $grid['extra'][(int)$r['id']];
                }
                DB::update('rooms', $upd, 'id = ?', [$r['id']]);
                $n++;
            }
        });
        return $n;
    }

    private static function currentGrid(): array
    {
        $g = ['prices' => [], 'extra' => []];
        foreach (DB::all('SELECT id, prices, extra_bed_price FROM rooms') as $r) {
            $g['prices'][(int)$r['id']] = json_dec($r['prices'], []);
            $g['extra'][(int)$r['id']] = $r['extra_bed_price'] ? (int)$r['extra_bed_price'] : null;
        }
        return $g;
    }

    private static function prices(): bool
    {
        if (is_post()) {
            $do = (string)($_POST['do'] ?? '');
            $pid = (int)($_POST['preset'] ?? 0);
            $name = Sanitizer::text((string)($_POST['name'] ?? ''), 80);
            switch ($do) {
                case 'save':
                    self::applyGrid(self::readGrid());
                    Settings::set('price.active_preset', null);
                    flash(__('admin.prices_saved'));
                    break;
                case 'save_as':
                    $grid = self::readGrid();
                    self::applyGrid($grid);
                    $id = DB::insert('price_presets', ['name' => $name !== '' ? $name : __('admin.preset') . ' ' . local_date(DB::now()), 'note' => Sanitizer::text((string)($_POST['note'] ?? ''), 150), 'data' => json_enc($grid), 'created_at' => DB::now(), 'updated_at' => DB::now()]);
                    Settings::set('price.active_preset', $id);
                    flash(__('admin.preset_saved'));
                    break;
                case 'update':
                    if ($pid) {
                        $grid = self::readGrid();
                        self::applyGrid($grid);
                        DB::update('price_presets', ['data' => json_enc($grid), 'updated_at' => DB::now()], 'id = ?', [$pid]);
                        Settings::set('price.active_preset', $pid);
                        flash(__('admin.preset_updated'));
                    }
                    break;
                case 'apply':
                    $pr = DB::one('SELECT * FROM price_presets WHERE id = ?', [$pid]);
                    if ($pr) {
                        $grid = json_dec($pr['data'], []);
                        $grid['prices'] = array_map(fn($x) => (array)$x, (array)($grid['prices'] ?? []));
                        $grid['extra'] = (array)($grid['extra'] ?? []);
                        $n = self::applyGrid($grid);
                        Settings::set('price.active_preset', $pid);
                        flash(__('admin.preset_applied', ['name' => $pr['name'], 'n' => $n]));
                    }
                    break;
                case 'rename':
                    if ($pid && $name !== '') {
                        DB::update('price_presets', ['name' => $name, 'updated_at' => DB::now()], 'id = ?', [$pid]);
                        flash(__('admin.saved'));
                    }
                    break;
                case 'delete':
                    if ($pid) {
                        DB::delete('price_presets', 'id = ?', [$pid]);
                        if ((int)setting('price.active_preset') === $pid) {
                            Settings::set('price.active_preset', null);
                        }
                        flash(__('admin.deleted'));
                    }
                    break;
            }
            self::changed();
            redirect(admin_url('prices'));
        }
        $rooms = DB::all('SELECT id, name, badge, color, min_guests, max_guests, max_extra, prices, extra_bed_price, active FROM rooms ORDER BY sort, id');
        $presets = DB::all('SELECT * FROM price_presets ORDER BY id DESC');
        $current = self::currentGrid();
        return self::out('admin/prices', ['rooms' => $rooms, 'presets' => $presets, 'current' => $current, 'active' => (int)setting('price.active_preset')]);
    }

    // ------------------------------------------------------------------ media
    private static function media(string $a, string $b): bool
    {
        if ($a === 'list') {
            $kind = ($_GET['kind'] ?? 'image') === 'pano' ? 'pano' : 'image';
            $q = trim((string)($_GET['q'] ?? ''));
            $page = max(1, (int)($_GET['page'] ?? 1));
            $per = 60;
            $sql = 'SELECT * FROM media WHERE kind = ?';
            $p = [$kind];
            if ($q !== '') {
                $sql .= ' AND title LIKE ?';
                $p[] = '%' . $q . '%';
            }
            $rows = DB::all($sql . ' ORDER BY id DESC LIMIT ' . ($per + 1) . ' OFFSET ' . (($page - 1) * $per), $p);
            $more = count($rows) > $per;
            $items = array_map(fn($m) => ['id' => (int)$m['id'], 'html' => AdminForm::thumb($m), 'thumb' => Media::src($m, 480), 'title' => $m['title'], 'w' => (int)$m['width'], 'h' => (int)$m['height']], array_slice($rows, 0, $per));
            json_out(['items' => $items, 'more' => $more]);
        }
        if ($a === 'upload' && is_post()) {
            $out = [];
            $files = $_FILES['files'] ?? null;
            if (!$files) {
                json_out(['error' => __('admin.upload_err_empty')], 400);
            }
            $count = is_array($files['name']) ? count($files['name']) : 1;
            for ($i = 0; $i < $count; $i++) {
                $err = is_array($files['error']) ? $files['error'][$i] : $files['error'];
                $tmp = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
                $nm = is_array($files['name']) ? $files['name'][$i] : $files['name'];
                if ($err !== UPLOAD_ERR_OK || !is_uploaded_file($tmp)) {
                    $out[] = ['error' => $nm . ': ' . self::uploadErr((int)$err)];
                    continue;
                }
                try {
                    $m = Media::storeImage($tmp, $nm);
                    $out[] = ['id' => (int)$m['id'], 'html' => AdminForm::thumb($m), 'thumb' => Media::src($m, 480)];
                } catch (Throwable $e) {
                    $out[] = ['error' => $nm . ': ' . Media::uploadError($e->getMessage())];
                }
            }
            self::changed();
            json_out(['items' => $out]);
        }
        if ($a === 'pano' && is_post()) {
            $main = $_FILES['main'] ?? null;
            if (!$main || $main['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($main['tmp_name'])) {
                json_out(['error' => self::uploadErr((int)($main['error'] ?? 4))], 400);
            }
            $mob = (($_FILES['mobile']['error'] ?? 4) === UPLOAD_ERR_OK && is_uploaded_file($_FILES['mobile']['tmp_name'])) ? $_FILES['mobile']['tmp_name'] : null;
            $th = (($_FILES['thumb']['error'] ?? 4) === UPLOAD_ERR_OK && is_uploaded_file($_FILES['thumb']['tmp_name'])) ? $_FILES['thumb']['tmp_name'] : null;
            try {
                $m = Media::storePano($main['tmp_name'], $mob, $th, (string)($_POST['name'] ?? $main['name']));
                json_out(['items' => [['id' => (int)$m['id'], 'html' => AdminForm::thumb($m), 'thumb' => Media::src($m, 480)]]]);
            } catch (Throwable $e) {
                json_out(['error' => Media::uploadError($e->getMessage())], 400);
            }
        }
        if (ctype_digit($a) && is_post()) {
            $m = DB::one('SELECT * FROM media WHERE id = ?', [(int)$a]);
            if (!$m) {
                return self::notFound();
            }
            if ($b === 'delete') {
                Media::delete((int)$a);
                self::changed();
                if (self::wantsJson()) {
                    json_out(['ok' => true]);
                }
                flash(__('admin.deleted'));
                redirect(admin_url('media', ['kind' => $m['kind']]));
            }
            $alt = AdminForm::read(['name' => 'alt', 'type' => 'i18n'], self::input());
            DB::update('media', ['alt' => json_enc($alt), 'title' => Sanitizer::text((string)($_POST['title'] ?? $m['title']), 150)], 'id = ?', [$m['id']]);
            self::changed();
            flash(__('admin.saved'));
            redirect(admin_url('media', ['kind' => $m['kind']]));
        }
        $kind = ($_GET['kind'] ?? 'image') === 'pano' ? 'pano' : 'image';
        $rows = DB::all('SELECT * FROM media WHERE kind = ? ORDER BY id DESC', [$kind]);
        $bytes = (int)DB::val('SELECT COALESCE(SUM(size),0) FROM media');
        return self::out('admin/media', ['rows' => $rows, 'kind' => $kind, 'bytes' => $bytes]);
    }

    private static function uploadErr(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => __('admin.upload_err_big', ['max' => ini_get('upload_max_filesize')]),
            UPLOAD_ERR_NO_FILE => __('admin.upload_err_empty'),
            default => __('admin.upload_err_write'),
        };
    }

    // ------------------------------------------------------------------ messages
    private static function messages(string $a, string $b): bool
    {
        if ($a === 'export') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="messages-' . date('Y-m-d') . '.csv"');
            $o = fopen('php://output', 'w');
            fwrite($o, "\xEF\xBB\xBF");
            fputcsv($o, ['id', 'date', 'name', 'phone', 'email', 'subject', 'message', 'language']);
            foreach (DB::all('SELECT * FROM messages ORDER BY id DESC') as $m) {
                fputcsv($o, [$m['id'], $m['created_at'], $m['name'], $m['phone'], $m['email'], $m['subject'], $m['body'], $m['lang']]);
            }
            fclose($o);
            exit;
        }
        if ($a === 'read-all' && is_post()) {
            DB::update('messages', ['is_read' => 1], 'is_read = 0');
            redirect(admin_url('messages'));
        }
        if (ctype_digit($a)) {
            $m = DB::one('SELECT * FROM messages WHERE id = ?', [(int)$a]);
            if (!$m) {
                return self::notFound();
            }
            if ($b === 'delete' && is_post()) {
                DB::delete('messages', 'id = ?', [$m['id']]);
                flash(__('admin.deleted'));
                redirect(admin_url('messages'));
            }
            if ($b === 'unread' && is_post()) {
                DB::update('messages', ['is_read' => 0], 'id = ?', [$m['id']]);
                redirect(admin_url('messages'));
            }
            if (!$m['is_read']) {
                DB::update('messages', ['is_read' => 1], 'id = ?', [$m['id']]);
            }
            return self::out('admin/message', ['m' => $m]);
        }
        return self::out('admin/messages', ['rows' => DB::all('SELECT * FROM messages ORDER BY id DESC LIMIT 500')]);
    }

    // ------------------------------------------------------------------ translations
    private static function translations(): bool
    {
        $langs = array_keys(I18n::LANGS);
        $base = [];
        foreach ($langs as $l) {
            $base[$l] = I18n::baseStrings($l);
        }
        $keys = array_keys($base['fa'] + $base['en']);
        $keys = array_values(array_filter($keys, fn($k) => !str_starts_with($k, 'admin.')));
        if (is_post()) {
            $in = (array)($_POST['t'] ?? []);
            DB::tx(function () use ($in, $langs, $base, $keys) {
                foreach ($langs as $l) {
                    foreach ((array)($in[$l] ?? []) as $k => $v) {
                        if (!in_array($k, $keys, true)) {
                            continue;
                        }
                        $v = Sanitizer::text((string)$v, 1000);
                        DB::delete('translations', 'lang = ? AND tkey = ?', [$l, $k]);
                        if ($v !== '' && $v !== ($base[$l][$k] ?? '')) {
                            DB::insert('translations', ['lang' => $l, 'tkey' => $k, 'value' => $v]);
                        }
                    }
                }
            });
            self::changed();
            flash(__('admin.saved'));
            redirect(admin_url('translations'));
        }
        $over = [];
        foreach (DB::all('SELECT lang, tkey, value FROM translations') as $r) {
            $over[$r['lang']][$r['tkey']] = $r['value'];
        }
        return self::out('admin/translations', ['keys' => $keys, 'base' => $base, 'over' => $over, 'langs' => $langs]);
    }

    // ------------------------------------------------------------------ users & account
    private static function users(string $a, string $b): bool
    {
        $me = Auth::user();
        if ($a === '') {
            return self::out('admin/users', ['rows' => DB::all('SELECT * FROM users ORDER BY id')]);
        }
        $row = $a === 'new' ? null : DB::one('SELECT * FROM users WHERE id = ?', [(int)$a]);
        if ($a !== 'new' && !$row) {
            return self::notFound();
        }
        if ($b === 'delete' && is_post() && $row) {
            $admins = (int)DB::val("SELECT COUNT(*) FROM users WHERE role = 'admin'");
            if ((int)$row['id'] === (int)$me['id'] || ($row['role'] === 'admin' && $admins <= 1)) {
                flash(__('admin.cannot_delete_user'), 'err');
            } else {
                DB::delete('users', 'id = ?', [$row['id']]);
                flash(__('admin.deleted'));
            }
            redirect(admin_url('users'));
        }
        $error = '';
        if (is_post()) {
            $username = trim((string)($_POST['username'] ?? ''));
            $pass = (string)($_POST['password'] ?? '');
            $data = [
                'username' => $username, 'name' => Sanitizer::text((string)($_POST['name'] ?? ''), 100),
                'email' => Sanitizer::text((string)($_POST['email'] ?? ''), 150),
                'role' => ($_POST['role'] ?? '') === 'admin' ? 'admin' : 'editor',
                'lang' => ($_POST['lang'] ?? 'fa') === 'en' ? 'en' : 'fa',
            ];
            if (!preg_match('~^[A-Za-z0-9_.\-@]{3,60}$~', $username)) {
                $error = __('admin.bad_username');
            } elseif (DB::val('SELECT COUNT(*) FROM users WHERE username = ? AND id <> ?', [$username, (int)($row['id'] ?? 0)])) {
                $error = __('admin.username_taken');
            } elseif ((!$row || $pass !== '') && mb_strlen($pass) < 8) {
                $error = __('admin.password_short');
            } elseif ($row && (int)$row['id'] === (int)$me['id'] && $data['role'] !== 'admin') {
                $error = __('admin.cannot_demote_self');
            }
            if (!$error) {
                if ($pass !== '') {
                    $data['password'] = password_hash($pass, PASSWORD_DEFAULT);
                }
                if ($row) {
                    DB::update('users', $data, 'id = ?', [$row['id']]);
                } else {
                    $data['created_at'] = DB::now();
                    DB::insert('users', $data);
                }
                flash(__('admin.saved'));
                redirect(admin_url('users'));
            }
            $row = array_merge($row ?? [], $data);
        }
        return self::out('admin/user', ['row' => $row, 'error' => $error]);
    }

    private static function account(): bool
    {
        $me = Auth::user();
        $error = '';
        if (is_post()) {
            $data = ['name' => Sanitizer::text((string)($_POST['name'] ?? ''), 100), 'lang' => ($_POST['lang'] ?? 'fa') === 'en' ? 'en' : 'fa', 'email' => Sanitizer::text((string)($_POST['email'] ?? ''), 150)];
            $new = (string)($_POST['new_password'] ?? '');
            if ($new !== '') {
                if (!password_verify((string)($_POST['current_password'] ?? ''), $me['password'])) {
                    $error = __('admin.wrong_password');
                } elseif (mb_strlen($new) < 8) {
                    $error = __('admin.password_short');
                } elseif ($new !== (string)($_POST['new_password2'] ?? '')) {
                    $error = __('admin.password_mismatch');
                } else {
                    $data['password'] = password_hash($new, PASSWORD_DEFAULT);
                }
            }
            if (!$error) {
                DB::update('users', $data, 'id = ?', [$me['id']]);
                if (isset($data['password'])) {
                    session_regenerate_id(true);
                }
                flash(__('admin.saved'));
                redirect(admin_url('account'));
            }
        }
        return self::out('admin/account', ['me' => $me, 'error' => $error]);
    }

    // ------------------------------------------------------------------ backup
    private static function backup(string $a, string $b): bool
    {
        $dir = STORAGE . '/backups';
        if ($a === 'create' && is_post()) {
            try {
                $file = Backup::create();
                flash(__('admin.backup_created', ['file' => basename($file)]));
            } catch (Throwable $e) {
                flash($e->getMessage(), 'err');
            }
            redirect(admin_url('backup'));
        }
        if ($a === 'download' && preg_match('~^[a-z0-9\-_.]+\.zip$~', $b) && is_file($dir . '/' . $b)) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $b . '"');
            header('Content-Length: ' . filesize($dir . '/' . $b));
            readfile($dir . '/' . $b);
            exit;
        }
        if ($a === 'delete' && is_post() && preg_match('~^[a-z0-9\-_.]+\.zip$~', $b)) {
            @unlink($dir . '/' . $b);
            flash(__('admin.deleted'));
            redirect(admin_url('backup'));
        }
        if ($a === 'restore' && is_post()) {
            $f = $_FILES['backup'] ?? null;
            if (!$f || $f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
                flash(self::uploadErr((int)($f['error'] ?? 4)), 'err');
            } else {
                try {
                    $n = Backup::restore($f['tmp_name']);
                    self::changed();
                    flash(__('admin.backup_restored', ['n' => $n]));
                } catch (Throwable $e) {
                    flash($e->getMessage(), 'err');
                }
            }
            redirect(admin_url('backup'));
        }
        $files = [];
        foreach (glob($dir . '/*.zip') ?: [] as $f) {
            $files[] = ['name' => basename($f), 'size' => filesize($f), 'time' => filemtime($f)];
        }
        usort($files, fn($x, $y) => $y['time'] <=> $x['time']);
        return self::out('admin/backup', ['files' => $files, 'zip' => class_exists('ZipArchive')]);
    }

    private static function clearCache(): bool
    {
        if (is_post()) {
            $n = Cache::flush();
            flash(__('admin.cache_cleared', ['n' => $n]));
        }
        redirect($_SERVER['HTTP_REFERER'] ?? admin_url());
        return true;
    }
}
