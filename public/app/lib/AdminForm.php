<?php
/**
 * Renders admin form fields and turns submitted values back into storable data.
 * Every input is named f[<field>]... so dotted setting names survive PHP's input parsing.
 */
final class AdminForm
{
    public static function langs(): array
    {
        return I18n::$enabled ?: ['fa'];
    }

    private static function id(string $name): string
    {
        return 'f-' . preg_replace('~[^a-z0-9]+~i', '-', $name);
    }

    private static function n(string $name, string $suffix = ''): string
    {
        return 'f[' . e($name) . ']' . $suffix;
    }

    /** @param mixed $value current value (decoded arrays for JSON fields) */
    public static function field(array $f, $value, array $row = []): string
    {
        $type = $f['type'];
        $name = $f['name'];
        $id = self::id($name);
        $label = __($f['label'] ?? $name) . (!empty($f['required']) ? ' <b class="req">*</b>' : '');
        $help = !empty($f['help']) ? '<p class="fld-help">' . e(__($f['help'])) . '</p>' : '';
        $cls = 'fld fld--' . $type . (!empty($f['half']) ? ' fld--half' : '');
        $show = !empty($f['show_if']) ? ' data-show-if="' . e($f['show_if']) . '"' : '';
        if ($value === null && array_key_exists('default', $f)) {
            $value = $f['default'];
        }
        $ctl = self::control($f, $value, $id, $row);
        if ($type === 'bool') {
            return '<div class="' . $cls . '"' . $show . '>' . $ctl . $help . '</div>';
        }
        return '<div class="' . $cls . '"' . $show . '><label class="fld-label" for="' . $id . '">' . $label . '</label>' . $ctl . $help . '</div>';
    }

    private static function control(array $f, $v, string $id, array $row): string
    {
        $name = $f['name'];
        $req = !empty($f['required']) ? ' required' : '';
        $ltr = !empty($f['ltr']) ? ' dir="ltr"' : '';
        switch ($f['type']) {
            case 'text':
            case 'url':
            case 'slug':
                $t = $f['type'] === 'url' ? 'url' : 'text';
                $extra = $f['type'] === 'slug' ? ' dir="ltr" pattern="[a-z0-9\-]*" data-slug-from="' . e($f['from'] ?? '') . '" placeholder="auto"' : ($f['type'] === 'url' ? ' dir="ltr" placeholder="https://"' : $ltr);
                $h = '<input class="inp" type="' . $t . '" id="' . $id . '" name="' . self::n($name) . '" value="' . e(is_scalar($v) ? $v : '') . '"' . $req . $extra . '>';
                if ($f['type'] === 'slug') {
                    $h = '<div class="inp-group">' . $h . '<button type="button" class="btn btn-sm" data-slugify>' . icon('refresh-cw') . '</button></div>';
                }
                return $h;
            case 'code':
                return '<textarea class="inp code" id="' . $id . '" name="' . self::n($name) . '" rows="6" dir="ltr" spellcheck="false">' . e(is_scalar($v) ? $v : '') . '</textarea>';
            case 'lines':
                $txt = is_array($v) ? implode("\n", $v) : (string)$v;
                return '<textarea class="inp" id="' . $id . '" name="' . self::n($name) . '" rows="3"' . $ltr . '>' . e($txt) . '</textarea>';
            case 'number':
                $attrs = '';
                foreach (['min', 'max', 'step'] as $a) {
                    if (isset($f[$a])) {
                        $attrs .= ' ' . $a . '="' . e($f[$a]) . '"';
                    }
                }
                return '<input class="inp" type="number" inputmode="decimal" id="' . $id . '" name="' . self::n($name) . '" value="' . e($v === null || $v === '' ? '' : (float)$v + 0) . '"' . $attrs . $req . ' dir="ltr">';
            case 'range':
                return '<div class="range"><input type="range" id="' . $id . '" name="' . self::n($name) . '" value="' . (int)$v . '" min="' . (int)($f['min'] ?? 0) . '" max="' . (int)($f['max'] ?? 100) . '" oninput="this.nextElementSibling.value=this.value"><output>' . (int)$v . '</output></div>';
            case 'price':
                return self::priceInput(self::n($name), $v, $id);
            case 'bool':
                $label = __($f['label'] ?? $name);
                return '<label class="switch"><input type="hidden" name="' . self::n($name) . '" value="0"><input type="checkbox" id="' . $id . '" name="' . self::n($name) . '" value="1"' . (!empty($v) ? ' checked' : '') . '><span class="switch-ui"></span><span>' . e($label) . '</span></label>';
            case 'color':
                $c = is_string($v) && preg_match('~^#[0-9a-f]{6}$~i', $v) ? $v : '#000000';
                return '<div class="color-inp"><input type="color" value="' . e($c) . '" data-color-for="' . $id . '"><input class="inp" type="text" id="' . $id . '" name="' . self::n($name) . '" value="' . e($c) . '" pattern="#[0-9a-fA-F]{6}" dir="ltr"></div>';
            case 'select':
                $opts = self::options($f);
                $h = '<select class="inp" id="' . $id . '" name="' . self::n($name) . '"' . $req . '>';
                if (!empty($f['empty'])) {
                    $h .= '<option value="">—</option>';
                }
                foreach ($opts as $k => $label) {
                    $h .= '<option value="' . e($k) . '"' . ((string)$k === (string)$v ? ' selected' : '') . '>' . e($label) . '</option>';
                }
                return $h . '</select>';
            case 'multi':
                $sel = array_map('intval', is_array($v) ? $v : json_dec($v, []));
                $h = '<div class="checks" id="' . $id . '"><input type="hidden" name="' . self::n($name) . '[]" value="">';
                foreach (self::options($f, true) as $k => $o) {
                    $h .= '<label class="check-chip"><input type="checkbox" name="' . self::n($name) . '[]" value="' . e($k) . '"' . (in_array((int)$k, $sel, true) ? ' checked' : '') . '><span>' . ($o['icon'] ? icon($o['icon']) : '') . e($o['label']) . '</span></label>';
                }
                return $h . '</div>';
            case 'langs':
                $sel = is_array($v) ? $v : ['fa'];
                $h = '<div class="checks" id="' . $id . '"><input type="hidden" name="' . self::n($name) . '[]" value="">';
                foreach (I18n::LANGS as $k => $m) {
                    $h .= '<label class="check-chip"><input type="checkbox" name="' . self::n($name) . '[]" value="' . $k . '"' . (in_array($k, $sel, true) ? ' checked' : '') . '><span>' . e($m['name']) . '</span></label>';
                }
                return $h . '</div>';
            case 'icon':
                return self::iconPicker(self::n($name), (string)$v, $id);
            case 'i18n':
            case 'i18n_textarea':
            case 'i18n_rich':
                return self::i18n($f, $v, $id);
            case 'image':
            case 'pano':
            case 'images':
                return self::media($f, $v, $id);
            case 'bed_layout':
                return self::bedLayout($name, is_array($v) ? $v : json_dec($v, []), $id);
            case 'prices':
                return self::prices($name, is_array($v) ? $v : json_dec($v, []), $id, $row);
            case 'features':
                return self::features($name, is_array($v) ? $v : json_dec($v, []), $id);
            case 'stats':
                return self::stats($name, is_array($v) ? $v : [], $id);
            case 'latlng':
                return self::latlng($f, $row, $id);
            case 'hotspots':
                return self::hotspots($f, $row, $id);
            case 'secondary':
                return self::secondary($name, is_array($v) ? $v : [], $id);
            case 'nav':
                return self::nav($name, is_array($v) ? $v : [], $id);
            case 'theme_presets':
                $h = '<div class="presets" id="' . $id . '">';
                foreach (AdminResources::themePresets() as $k => [$label, $vals]) {
                    $sw = '';
                    foreach (['theme.primary', 'theme.accent', 'theme.accent2', 'theme.bg'] as $c) {
                        $sw .= '<i style="background:' . e($vals[$c]) . '"></i>';
                    }
                    $h .= '<button type="button" class="preset" data-theme-preset="' . e(json_enc($vals)) . '"><span class="swatches">' . $sw . '</span>' . e(__($label)) . '</button>';
                }
                return $h . '</div>';
        }
        return '<input class="inp" type="text" name="' . self::n($name) . '" value="' . e(is_scalar($v) ? $v : '') . '">';
    }

    public static function priceInput(string $name, $v, string $id = ''): string
    {
        $val = ($v === null || $v === '' || (int)$v <= 0) ? '' : number_format((int)$v);
        return '<div class="price-inp"><input class="inp" type="text" inputmode="numeric" ' . ($id ? 'id="' . $id . '" ' : '') . 'name="' . $name . '" value="' . e($val) . '" placeholder="—" dir="ltr" data-price><span>' . e(tr(setting('price.currency'))) . '</span></div>';
    }

    private static function options(array $f, bool $rich = false): array
    {
        if (isset($f['options'])) {
            $out = [];
            foreach ($f['options'] as $k => $label) {
                $out[$k] = !empty($f['raw_options']) ? $label : __($label);
            }
            return $out;
        }
        $src = $f['source'] ?? '';
        $out = [];
        if ($src === 'scenes') {
            foreach (DB::all('SELECT id, title FROM scenes ORDER BY sort, id') as $r) {
                $out[$r['id']] = tr($r['title']);
            }
        } elseif ($src === 'gallery_categories') {
            foreach (DB::all('SELECT id, name FROM gallery_categories ORDER BY sort, id') as $r) {
                $out[$r['id']] = tr($r['name']);
            }
        } elseif ($src === 'menu_categories') {
            foreach (DB::all('SELECT id, section, name FROM menu_categories ORDER BY section, sort, id') as $r) {
                $out[$r['id']] = __('admin.section.' . $r['section']) . ' › ' . tr($r['name']);
            }
        } elseif (str_starts_with($src, 'amenities')) {
            $scope = explode(':', $src)[1] ?? '';
            foreach (DB::all('SELECT id, icon, name, scope FROM amenities ORDER BY sort, id') as $r) {
                if ($scope && $r['scope'] !== $scope && $r['scope'] !== 'both') {
                    continue;
                }
                $out[$r['id']] = $rich ? ['label' => tr($r['name']), 'icon' => $r['icon']] : tr($r['name']);
            }
        }
        return $out;
    }

    private static function i18n(array $f, $v, string $id): string
    {
        $vals = is_array($v) ? $v : json_dec($v, []);
        if (!is_array($vals)) {
            $vals = ['fa' => (string)$v];
        }
        $h = '<div class="i18n" data-i18n>';
        foreach (self::langs() as $i => $l) {
            $m = I18n::LANGS[$l];
            $val = (string)($vals[$l] ?? '');
            $nm = self::n($f['name'], '[' . $l . ']');
            $req = !empty($f['required']) && $l === I18n::$default ? ' required' : '';
            $h .= '<div class="i18n-pane" data-lang="' . $l . '" lang="' . $l . '" dir="' . $m['dir'] . '"' . ($i ? ' hidden' : '') . '>';
            if ($f['type'] === 'i18n') {
                $h .= '<input class="inp" type="text" id="' . $id . ($i ? '-' . $l : '') . '" name="' . $nm . '" value="' . e($val) . '"' . $req . '>';
            } elseif ($f['type'] === 'i18n_textarea') {
                $h .= '<textarea class="inp" id="' . $id . ($i ? '-' . $l : '') . '" name="' . $nm . '" rows="3"' . $req . '>' . e($val) . '</textarea>';
            } else {
                $h .= '<div class="rte" data-rte><div class="rte-bar" role="toolbar">'
                    . '<button type="button" data-cmd="bold" title="Bold"><b>B</b></button><button type="button" data-cmd="italic" title="Italic"><i>I</i></button>'
                    . '<button type="button" data-cmd="h2">H2</button><button type="button" data-cmd="h3">H3</button>'
                    . '<button type="button" data-cmd="insertUnorderedList">• —</button><button type="button" data-cmd="insertOrderedList">1.</button>'
                    . '<button type="button" data-cmd="blockquote">❝</button><button type="button" data-cmd="link">' . icon('link') . '</button><button type="button" data-cmd="removeFormat">' . icon('x') . '</button>'
                    . '</div><div class="rte-area inp" contenteditable="true" id="' . $id . ($i ? '-' . $l : '') . '">' . Sanitizer::html($val) . '</div>'
                    . '<textarea name="' . $nm . '" hidden>' . e($val) . '</textarea></div>';
            }
            $h .= '</div>';
        }
        return $h . '</div>';
    }

    private static function media(array $f, $v, string $id): string
    {
        $multi = $f['type'] === 'images';
        $ids = $multi ? array_values(array_filter(array_map('intval', is_array($v) ? $v : json_dec($v, [])))) : array_filter([(int)$v]);
        media_preload($ids);
        $thumbs = '';
        foreach ($ids as $mid) {
            $m = media($mid);
            if ($m) {
                $thumbs .= self::thumb($m);
            }
        }
        $kind = $f['type'] === 'pano' ? 'pano' : 'image';
        return '<div class="media-field" data-media-field data-multiple="' . ($multi ? 1 : 0) . '" data-kind="' . $kind . '" id="' . $id . '">'
            . '<input type="hidden" name="' . self::n($f['name']) . '" value="' . e(implode(',', $ids)) . '">'
            . '<div class="media-thumbs"' . ($multi ? ' data-sortable' : '') . '>' . $thumbs . '</div>'
            . '<div class="media-actions"><button type="button" class="btn btn-sm" data-media-pick>' . icon($kind === 'pano' ? 'rotate-3d' : 'image') . e(__($multi ? 'admin.add_photos' : ($kind === 'pano' ? 'admin.choose_pano' : 'admin.choose_photo'))) . '</button></div></div>';
    }

    public static function thumb(array $m): string
    {
        $isPano = $m['kind'] === 'pano';
        $v = Media::variants($m);
        $full = $isPano ? upload_url($v['main'] ?? '') : Media::src($m, 1600);
        return '<figure class="thumb" data-id="' . (int)$m['id'] . '" data-full="' . e($full) . '" data-w="' . (int)$m['width'] . '" data-h="' . (int)$m['height'] . '"'
            . ($isPano ? ' data-mobile="' . e(isset($v['mobile']) ? upload_url($v['mobile']) : '') . '"' : '') . '>'
            . '<img src="' . e(Media::src($m, 480)) . '" alt="" loading="lazy"><button type="button" class="thumb-x" data-remove aria-label="remove">' . icon('x') . '</button></figure>';
    }

    private static function iconPicker(string $name, string $v, string $id): string
    {
        static $icons = null;
        $icons = $icons ?? json_dec(@file_get_contents(APP . '/icons.json'), []);
        $h = '<div class="icon-picker" data-icon-picker><input type="hidden" id="' . $id . '" name="' . $name . '" value="' . e($v) . '">'
            . '<button type="button" class="btn btn-sm icon-current" data-icon-toggle>' . ($v ? icon($v) : icon('sparkles')) . '<span>' . e($v ?: '—') . '</span></button>'
            . '<div class="icon-grid" hidden>';
        foreach ($icons as $ic) {
            $h .= '<button type="button" data-icon="' . e($ic) . '" title="' . e($ic) . '"' . ($ic === $v ? ' class="is-on"' : '') . '>' . icon($ic) . '</button>';
        }
        return $h . '</div></div>';
    }

    private static function bedLayout(string $name, array $rows, string $id): string
    {
        if (!$rows) {
            $rows = [['t' => 'single', 'n' => 1]];
        }
        $opts = '';
        foreach (bed_types() as $t) {
            $opts .= '<option value="' . $t . '">' . e(__('bed.' . $t)) . '</option>';
        }
        $row = function ($r) use ($name, $opts) {
            $o = str_replace('value="' . e($r['t'] ?? 'single') . '"', 'value="' . e($r['t'] ?? 'single') . '" selected', $opts);
            return '<div class="rep-row"><input class="inp inp-num" type="number" min="1" max="12" name="' . self::n($name, '[n][]') . '" value="' . (int)($r['n'] ?? 1) . '" dir="ltr"><span>×</span><select class="inp" name="' . self::n($name, '[t][]') . '">' . $o . '</select><button type="button" class="icon-btn" data-rep-remove>' . icon('trash-2') . '</button></div>';
        };
        $h = '<div class="repeater" data-repeater data-bed-layout id="' . $id . '"><div class="rep-rows">';
        foreach ($rows as $r) {
            $h .= $row($r);
        }
        $h .= '</div><template>' . $row(['t' => 'single', 'n' => 1]) . '</template><div class="rep-foot"><button type="button" class="btn btn-sm" data-rep-add>' . icon('plus') . e(__('admin.add_bed')) . '</button><span class="bed-preview" data-bed-preview></span></div></div>';
        return $h;
    }

    private static function prices(string $name, array $prices, string $id, array $row): string
    {
        $max = max(10, (int)($row['max_guests'] ?? 0));
        $h = '<div class="prices-grid" data-prices id="' . $id . '">';
        for ($g = 1; $g <= $max; $g++) {
            $h .= '<div class="price-row" data-g="' . $g . '"><span class="price-row-label">' . icon('users') . e(__('rooms.n_people', ['n' => $g])) . '</span>'
                . self::priceInput(self::n($name, '[' . $g . ']'), $prices[(string)$g] ?? null) . '</div>';
        }
        return $h . '</div>';
    }

    private static function features(string $name, array $rows, string $id): string
    {
        $langs = self::langs();
        $row = function (array $r, $i) use ($name, $langs) {
            $h = '<div class="rep-row rep-row--i18n">';
            foreach ($langs as $k => $l) {
                $h .= '<input class="inp" type="text" lang="' . $l . '" dir="' . I18n::LANGS[$l]['dir'] . '" name="' . self::n($name, '[' . $i . '][' . $l . ']') . '" value="' . e($r[$l] ?? '') . '" placeholder="' . e(I18n::LANGS[$l]['name']) . '" data-lang-input="' . $l . '"' . ($k ? ' hidden' : '') . '>';
            }
            return $h . '<button type="button" class="icon-btn" data-rep-remove>' . icon('trash-2') . '</button></div>';
        };
        $h = '<div class="repeater" data-repeater data-indexed id="' . $id . '"><div class="rep-rows">';
        foreach (array_values($rows) as $i => $r) {
            $h .= $row(is_array($r) ? $r : ['fa' => (string)$r], $i);
        }
        $h .= '</div><template>' . $row([], '__i__') . '</template><div class="rep-foot"><button type="button" class="btn btn-sm" data-rep-add>' . icon('plus') . e(__('admin.add_row')) . '</button></div></div>';
        return $h;
    }

    private static function stats(string $name, array $rows, string $id): string
    {
        $langs = self::langs();
        $row = function (array $r, $i) use ($name, $langs) {
            $h = '<div class="rep-row rep-row--i18n"><input class="inp inp-num" type="text" name="' . self::n($name, '[' . $i . '][value]') . '" value="' . e($r['value'] ?? '') . '" placeholder="42" dir="ltr">';
            foreach ($langs as $k => $l) {
                $h .= '<input class="inp" type="text" lang="' . $l . '" dir="' . I18n::LANGS[$l]['dir'] . '" name="' . self::n($name, '[' . $i . '][label][' . $l . ']') . '" value="' . e($r['label'][$l] ?? '') . '" placeholder="' . e(I18n::LANGS[$l]['name']) . '" data-lang-input="' . $l . '"' . ($k ? ' hidden' : '') . '>';
            }
            return $h . '<button type="button" class="icon-btn" data-rep-remove>' . icon('trash-2') . '</button></div>';
        };
        $h = '<div class="repeater" data-repeater data-indexed id="' . $id . '"><div class="rep-rows">';
        foreach (array_values($rows) as $i => $r) {
            $h .= $row($r, $i);
        }
        $h .= '</div><template>' . $row([], '__i__') . '</template><div class="rep-foot"><button type="button" class="btn btn-sm" data-rep-add>' . icon('plus') . e(__('admin.add_row')) . '</button></div></div>';
        return $h;
    }

    private static function latlng(array $f, array $row, string $id): string
    {
        if (isset($f['settings'])) {
            [$ka, $kb] = $f['settings'];
            $lat = setting($ka);
            $lng = setting($kb);
        } else {
            [$ka, $kb] = $f['cols'];
            $lat = $row[$ka] ?? '';
            $lng = $row[$kb] ?? '';
        }
        $center = [(float)($lat ?: setting('map.lat')), (float)($lng ?: setting('map.lng'))];
        return '<div class="latlng" data-latlng data-center="' . e(json_enc($center)) . '" id="' . $id . '">'
            . '<div class="latlng-inputs"><label>Lat <input class="inp" type="text" inputmode="decimal" name="' . self::n($f['name'], '[lat]') . '" value="' . e($lat) . '" dir="ltr" data-lat></label>'
            . '<label>Lng <input class="inp" type="text" inputmode="decimal" name="' . self::n($f['name'], '[lng]') . '" value="' . e($lng) . '" dir="ltr" data-lng></label>'
            . '<button type="button" class="btn btn-sm" data-geo>' . icon('locate') . e(__('admin.my_location')) . '</button></div>'
            . '<div class="inp-group"><input class="inp" type="text" placeholder="' . e(__('admin.paste_map_link')) . '" dir="ltr" data-maplink><button type="button" class="btn btn-sm" data-maplink-go>' . e(__('admin.read_link')) . '</button></div>'
            . '<div class="latlng-map" data-map-pick></div><p class="fld-help">' . e(__('admin.h.map_click')) . '</p></div>';
    }

    private static function hotspots(array $f, array $row, string $id): string
    {
        $scenes = [];
        foreach (DB::all('SELECT id, slug, title FROM scenes ORDER BY sort, id') as $s) {
            if ((int)$s['id'] !== (int)($row['id'] ?? 0)) {
                $scenes[] = ['slug' => $s['slug'], 'title' => tr($s['title'])];
            }
        }
        $hs = json_dec($row['hotspots'] ?? '[]', []);
        $data = ['hotspots' => $hs, 'scenes' => $scenes, 'langs' => self::langs(), 'view' => ['yaw' => (float)($row['yaw'] ?? 0), 'pitch' => (float)($row['pitch'] ?? 0), 'hfov' => (float)($row['hfov'] ?? 100)]];
        return '<div class="hs-editor" data-hotspots id="' . $id . '" data-config="' . e(json_enc($data)) . '">'
            . '<input type="hidden" name="' . self::n($f['name'], '[json]') . '" value="' . e(json_enc($hs)) . '" data-hs-json>'
            . '<input type="hidden" name="' . self::n($f['name'], '[yaw]') . '" value="' . e($data['view']['yaw']) . '" data-hs-yaw>'
            . '<input type="hidden" name="' . self::n($f['name'], '[pitch]') . '" value="' . e($data['view']['pitch']) . '" data-hs-pitch>'
            . '<input type="hidden" name="' . self::n($f['name'], '[hfov]') . '" value="' . e($data['view']['hfov']) . '" data-hs-hfov>'
            . '<div class="hs-stage"><div class="hs-viewer" data-hs-viewer><p class="hs-empty">' . e(__('admin.hs.no_pano')) . '</p></div><span class="hs-cross" aria-hidden="true"></span></div>'
            . '<div class="hs-tools"><button type="button" class="btn btn-sm" data-hs-setview>' . icon('focus') . e(__('admin.hs.set_view')) . '</button>'
            . '<button type="button" class="btn btn-sm btn-accent" data-hs-add="scene">' . icon('plus') . e(__('admin.hs.add_scene')) . '</button>'
            . '<button type="button" class="btn btn-sm" data-hs-add="info">' . icon('info') . e(__('admin.hs.add_info')) . '</button>'
            . '<span class="hs-view-readout" data-hs-readout></span></div>'
            . '<p class="fld-help">' . e(__('admin.hs.help')) . '</p><div class="hs-list" data-hs-list></div></div>';
    }

    private static function secondary(string $name, array $v, string $id): string
    {
        $h = '<div class="sec-grid" id="' . $id . '">';
        foreach (I18n::LANGS as $l => $m) {
            $s = $v[$l] ?? [];
            $h .= '<div class="sec-row"><b>' . e($m['name']) . '</b>'
                . '<label class="switch"><input type="hidden" name="' . self::n($name, "[$l][on]") . '" value="0"><input type="checkbox" name="' . self::n($name, "[$l][on]") . '" value="1"' . (!empty($s['on']) ? ' checked' : '') . '><span class="switch-ui"></span><span>' . e(__('admin.show')) . '</span></label>'
                . '<input class="inp" type="text" name="' . self::n($name, "[$l][label]") . '" value="' . e($s['label'] ?? '') . '" placeholder="' . e(__('admin.f.currency_name')) . '">'
                . '<input class="inp" type="text" name="' . self::n($name, "[$l][symbol]") . '" value="' . e($s['symbol'] ?? '') . '" placeholder="$" dir="ltr">'
                . '<label class="sec-rate">' . e(__('admin.f.rate')) . '<input class="inp" type="text" inputmode="decimal" name="' . self::n($name, "[$l][rate]") . '" value="' . e($s['rate'] ?? '') . '" dir="ltr"></label></div>';
        }
        return $h . '</div>';
    }

    private static function nav(string $name, array $items, string $id): string
    {
        $known = ['home', 'rooms', 'gallery', 'tour', 'breakfast', 'cafe', 'attractions', 'about', 'contact'];
        $have = array_column($items, 'key');
        foreach ($known as $k) {
            if (!in_array($k, $have, true)) {
                $items[] = ['key' => $k, 'on' => false];
            }
        }
        $h = '<ul class="nav-sort" data-sortable-list id="' . $id . '">';
        foreach ($items as $it) {
            $k = $it['key'];
            if (!in_array($k, $known, true)) {
                continue;
            }
            $h .= '<li><span class="handle">' . icon('grip-vertical') . '</span><input type="hidden" name="' . self::n($name, '[order][]') . '" value="' . e($k) . '">'
                . '<label class="switch"><input type="checkbox" name="' . self::n($name, '[on][]') . '" value="' . e($k) . '"' . (!empty($it['on']) ? ' checked' : '') . '><span class="switch-ui"></span><span>' . e(__('nav.' . $k)) . '</span></label></li>';
        }
        return $h . '</ul>';
    }

    // ------------------------------------------------------------------ reading
    private static function toInt($s): ?int
    {
        $s = strtr((string)$s, ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9', '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']);
        $d = preg_replace('~\D~', '', $s);
        return $d === '' ? null : (int)$d;
    }

    private static function toFloat($s): ?float
    {
        $s = strtr(trim((string)$s), ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9', '٫' => '.', '/' => '.', ',' => '']);
        return is_numeric($s) ? (float)$s : null;
    }

    /**
     * Read one field from the submitted f[] array.
     * Returns the value, or ['__cols' => [col => value]] for fields stored in several columns/settings.
     */
    public static function read(array $f, array $in)
    {
        $v = $in[$f['name']] ?? null;
        switch ($f['type']) {
            case 'text':
            case 'url':
                $s = Sanitizer::text(is_string($v) ? $v : '', 1000);
                if ($f['type'] === 'url' && $s !== '' && !preg_match('~^https?://~i', $s)) {
                    $s = '';
                }
                return $s;
            case 'code':
                return is_string($v) ? mb_substr($v, 0, 20000) : '';
            case 'slug':
                return is_string($v) ? substr(trim(preg_replace('~[^a-z0-9\-]+~', '-', strtolower($v)), '-'), 0, 80) : '';
            case 'lines':
                return array_values(array_filter(array_map(fn($l) => Sanitizer::text($l, 100), preg_split('~\R~', (string)$v))));
            case 'number':
                $n = self::toFloat($v);
                if ($n === null) {
                    return null;
                }
                if (isset($f['min'])) {
                    $n = max((float)$f['min'], $n);
                }
                if (isset($f['max'])) {
                    $n = min((float)$f['max'], $n);
                }
                return (isset($f['step']) && str_contains((string)$f['step'], '.')) ? $n : (int)round($n);
            case 'range':
                return (int)$v;
            case 'price':
                $p = self::toInt($v);
                return ($p !== null && $p > 0) ? $p : null;
            case 'bool':
                return (is_array($v) ? end($v) : $v) ? 1 : 0;
            case 'color':
                return is_string($v) && preg_match('~^#[0-9a-fA-F]{6}$~', $v) ? strtolower($v) : ($f['default'] ?? '#000000');
            case 'select':
                $opts = self::options($f);
                if ($v === '' || $v === null) {
                    return !empty($f['empty']) ? null : array_key_first($opts);
                }
                $v = (string)$v;
                if (!array_key_exists($v, $opts) && !array_key_exists((int)$v, $opts)) {
                    return array_key_first($opts);
                }
                return (!empty($f['int']) || isset($f['source'])) ? (int)$v : $v;
            case 'multi':
                return array_values(array_unique(array_filter(array_map('intval', (array)$v))));
            case 'langs':
                $l = array_values(array_filter((array)$v, fn($x) => isset(I18n::LANGS[$x])));
                return $l ?: ['fa'];
            case 'icon':
                return preg_replace('~[^a-z0-9\-]~', '', (string)$v);
            case 'i18n':
            case 'i18n_textarea':
            case 'i18n_rich':
                $out = [];
                foreach (I18n::LANGS as $l => $_) {
                    $s = is_array($v) ? (string)($v[$l] ?? '') : '';
                    $out[$l] = $f['type'] === 'i18n_rich' ? Sanitizer::html($s) : Sanitizer::text($s, $f['type'] === 'i18n' ? 500 : 5000);
                }
                return $out;
            case 'image':
            case 'pano':
                $id = (int)(is_string($v) ? explode(',', $v)[0] : 0);
                return $id > 0 ? $id : null;
            case 'images':
                return array_values(array_unique(array_filter(array_map('intval', explode(',', (string)$v)))));
            case 'bed_layout':
                $out = [];
                foreach ((array)($v['t'] ?? []) as $i => $t) {
                    $n = max(0, min(12, (int)($v['n'][$i] ?? 0)));
                    if ($n > 0 && in_array($t, bed_types(), true)) {
                        $out[] = ['t' => $t, 'n' => $n];
                    }
                }
                return $out;
            case 'prices':
                $out = [];
                foreach ((array)$v as $g => $p) {
                    $g = (int)$g;
                    if ($g >= 1 && $g <= 30) {
                        $pi = self::toInt($p);
                        $out[(string)$g] = ($pi !== null && $pi > 0) ? $pi : null;
                    }
                }
                return $out;
            case 'features':
                $out = [];
                foreach ((array)$v as $row) {
                    $r = [];
                    foreach (I18n::LANGS as $l => $_) {
                        $r[$l] = Sanitizer::text((string)($row[$l] ?? ''), 200);
                    }
                    if (implode('', $r) !== '') {
                        $out[] = $r;
                    }
                }
                return $out;
            case 'stats':
                $out = [];
                foreach ((array)$v as $row) {
                    $val = Sanitizer::text((string)($row['value'] ?? ''), 20);
                    $lab = [];
                    foreach (I18n::LANGS as $l => $_) {
                        $lab[$l] = Sanitizer::text((string)($row['label'][$l] ?? ''), 100);
                    }
                    if ($val !== '' || implode('', $lab) !== '') {
                        $out[] = ['value' => $val, 'label' => $lab];
                    }
                }
                return $out;
            case 'latlng':
                $lat = self::toFloat($v['lat'] ?? '');
                $lng = self::toFloat($v['lng'] ?? '');
                if ($lat !== null && ($lat < -90 || $lat > 90)) {
                    $lat = null;
                }
                if ($lng !== null && ($lng < -180 || $lng > 180)) {
                    $lng = null;
                }
                $keys = $f['settings'] ?? $f['cols'];
                return ['__cols' => [$keys[0] => $lat, $keys[1] => $lng]];
            case 'hotspots':
                $hs = json_decode((string)($v['json'] ?? '[]'), true);
                $clean = [];
                foreach (is_array($hs) ? $hs : [] as $h) {
                    $item = ['type' => ($h['type'] ?? '') === 'scene' ? 'scene' : 'info', 'yaw' => round((float)($h['yaw'] ?? 0), 2), 'pitch' => round((float)($h['pitch'] ?? 0), 2), 'text' => []];
                    foreach (I18n::LANGS as $l => $_) {
                        $item['text'][$l] = Sanitizer::text((string)($h['text'][$l] ?? ''), 120);
                    }
                    if ($item['type'] === 'scene') {
                        $item['target'] = preg_replace('~[^a-z0-9\-]~', '', (string)($h['target'] ?? ''));
                    }
                    $clean[] = $item;
                }
                return ['__cols' => [
                    'hotspots' => json_enc($clean),
                    'yaw' => round((float)($v['yaw'] ?? 0), 2), 'pitch' => round((float)($v['pitch'] ?? 0), 2),
                    'hfov' => max(30, min(120, round((float)($v['hfov'] ?? 100), 2))),
                ]];
            case 'secondary':
                $out = [];
                foreach (I18n::LANGS as $l => $_) {
                    $s = (array)($v[$l] ?? []);
                    $out[$l] = [
                        'on' => (string)($s['on'] ?? '0') === '1',
                        'label' => Sanitizer::text((string)($s['label'] ?? ''), 30),
                        'symbol' => Sanitizer::text((string)($s['symbol'] ?? ''), 5),
                        'rate' => (float)(self::toFloat($s['rate'] ?? '') ?? 0),
                        'decimals' => 0,
                    ];
                }
                return $out;
            case 'nav':
                $on = (array)($v['on'] ?? []);
                $out = [];
                foreach ((array)($v['order'] ?? []) as $k) {
                    $out[] = ['key' => preg_replace('~[^a-z]~', '', (string)$k), 'on' => in_array($k, $on, true)];
                }
                return $out;
            case 'theme_presets':
                return ['__skip' => true];
        }
        return is_string($v) ? Sanitizer::text($v) : null;
    }

    /** Convert a read value to a database column value. */
    public static function toColumn(array $f, $value)
    {
        if (is_array($value)) {
            return json_enc($value);
        }
        return $value;
    }

    /** Decode a database column into the value a control expects. */
    public static function fromColumn(array $f, $raw)
    {
        return match ($f['type']) {
            'i18n', 'i18n_textarea', 'i18n_rich' => json_dec($raw, []),
            'images', 'multi', 'bed_layout', 'prices', 'features', 'stats' => json_dec($raw, []),
            default => $raw,
        };
    }
}
