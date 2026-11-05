<?php
/**
 * Languages, UI strings and multilingual content.
 * Content fields are JSON objects: {"fa": "...", "en": "...", "ar": "..."}.
 */
final class I18n
{
    public const LANGS = [
        'fa' => ['name' => 'فارسی', 'short' => 'فا', 'dir' => 'rtl', 'locale' => 'fa_IR', 'digits' => '۰۱۲۳۴۵۶۷۸۹', 'sep' => '٬'],
        'en' => ['name' => 'English', 'short' => 'EN', 'dir' => 'ltr', 'locale' => 'en_US', 'digits' => null, 'sep' => ','],
        'ar' => ['name' => 'العربية', 'short' => 'ع', 'dir' => 'rtl', 'locale' => 'ar_IQ', 'digits' => '٠١٢٣٤٥٦٧٨٩', 'sep' => '٬'],
    ];

    public static string $lang = 'fa';
    public static string $default = 'fa';
    public static array $enabled = ['fa', 'en', 'ar'];
    private static array $strings = [];

    public static function init(array $enabled, string $default): void
    {
        $enabled = array_values(array_filter($enabled, fn($l) => isset(self::LANGS[$l])));
        if (!$enabled) {
            $enabled = ['fa'];
        }
        if (!in_array($default, $enabled, true)) {
            $default = $enabled[0];
        }
        self::$enabled = $enabled;
        self::$default = $default;
        self::$lang = $default;
    }

    public static function set(string $lang): void
    {
        if (isset(self::LANGS[$lang])) {
            self::$lang = $lang;
        }
    }

    public static function meta(?string $lang = null): array
    {
        return self::LANGS[$lang ?? self::$lang] ?? self::LANGS['fa'];
    }

    public static function dir(?string $lang = null): string
    {
        return self::meta($lang)['dir'];
    }

    public static function isRtl(?string $lang = null): bool
    {
        return self::dir($lang) === 'rtl';
    }

    /** UI string from app/lang/{lang}.php, overridden by the translations table. */
    public static function t(string $key, array $params = [], ?string $lang = null): string
    {
        $lang = $lang ?? self::$lang;
        $s = self::strings($lang);
        $text = $s[$key] ?? (self::strings('en')[$key] ?? $key);
        // Plural forms separated by "|": en = one|other, ar = one|two|few(3-10)|many
        if (str_contains($text, '|') && isset($params['n']) && is_numeric($params['n'])) {
            $forms = explode('|', $text);
            $n = (float)$params['n'];
            $idx = match ($lang) {
                'en' => $n == 1 ? 0 : 1,
                'ar' => $n == 1 ? 0 : ($n == 2 ? 1 : (($n >= 3 && $n <= 10) ? 2 : 3)),
                default => 0,
            };
            $text = $forms[min($idx, count($forms) - 1)];
        }
        if ($params) {
            $rep = [];
            foreach ($params as $k => $v) {
                $rep['{' . $k . '}'] = is_int($v) || is_float($v) ? self::digits((string)$v, $lang) : (string)$v;
            }
            $text = strtr($text, $rep);
        }
        return $text;
    }

    public static function strings(string $lang): array
    {
        if (!isset(self::$strings[$lang])) {
            $file = APP . '/lang/' . $lang . '.php';
            $base = is_file($file) ? (require $file) : [];
            $adminFile = APP . '/lang/admin-' . $lang . '.php';
            if (is_file($adminFile)) {
                $base += (require $adminFile);
            }
            try {
                if (DB::tableExists('translations')) {
                    foreach (DB::all('SELECT tkey, value FROM translations WHERE lang = ?', [$lang]) as $row) {
                        if ($row['value'] !== null && $row['value'] !== '') {
                            $base[$row['tkey']] = $row['value'];
                        }
                    }
                }
            } catch (Throwable $e) {
                // database not ready (installer) – use file strings only
            }
            self::$strings[$lang] = $base;
        }
        return self::$strings[$lang];
    }

    public static function baseStrings(string $lang): array
    {
        $file = APP . '/lang/' . $lang . '.php';
        return is_file($file) ? (require $file) : [];
    }

    public static function resetCache(): void
    {
        self::$strings = [];
    }

    /** Pick the current language from a multilingual value, falling back to the default language. */
    public static function pick($value, ?string $lang = null): string
    {
        $lang = $lang ?? self::$lang;
        if ($value === null || $value === '') {
            return '';
        }
        if (is_string($value)) {
            $first = ltrim($value)[0] ?? '';
            if ($first !== '{') {
                return $value;
            }
            $decoded = json_decode($value, true);
            if (!is_array($decoded)) {
                return $value;
            }
            $value = $decoded;
        }
        if (!is_array($value)) {
            return (string)$value;
        }
        foreach ([$lang, self::$default, 'fa', 'en', 'ar'] as $l) {
            if (isset($value[$l]) && trim((string)$value[$l]) !== '') {
                return (string)$value[$l];
            }
        }
        return '';
    }

    /** True if the value has text for this exact language (no fallback). */
    public static function has($value, string $lang): bool
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }
        return is_array($value) && trim((string)($value[$lang] ?? '')) !== '';
    }

    public static function digits(string $s, ?string $lang = null): string
    {
        $d = self::meta($lang)['digits'];
        if (!$d) {
            return $s;
        }
        $map = [];
        $chars = mb_str_split($d);
        foreach ($chars as $i => $c) {
            $map[(string)$i] = $c;
        }
        return strtr($s, $map);
    }

    public static function number($n, int $decimals = 0, ?string $lang = null): string
    {
        $meta = self::meta($lang);
        $dec = $meta['dir'] === 'rtl' ? '٫' : '.';
        $s = number_format((float)$n, $decimals, $dec, $meta['sep']);
        return self::digits($s, $lang);
    }
}
