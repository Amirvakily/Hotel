<?php
/**
 * Site settings stored in the `settings` table as JSON values.
 * Every setting has a default here, so the site works even with an empty table.
 */
final class Settings
{
    private static ?array $cache = null;

    public static function defaults(): array
    {
        return [
            // identity
            'site.name' => ['fa' => 'هتل ونوس', 'en' => 'Venus Hotel', 'ar' => 'فندق ونوس'],
            'site.tagline' => ['fa' => 'آرامش و مهمان‌نوازی در قلب تبریز', 'en' => 'Calm and warm hospitality in the heart of Tabriz', 'ar' => 'راحة وضيافة في قلب تبريز'],
            'site.city' => ['fa' => 'تبریز', 'en' => 'Tabriz', 'ar' => 'تبريز'],
            'site.stars' => 3,
            'cafe.name' => ['fa' => 'کافه تروث', 'en' => 'Truth Café', 'ar' => 'كوفي شوب تروث'],
            'site.logo' => null,
            'site.logo_light' => null,
            'site.favicon' => null,
            'site.established' => 1395,
            // contact
            'contact.phones' => ['041-3555-0000', '0914-000-0000'],
            'contact.whatsapp' => '989140000000',
            'contact.telegram' => 'venushotel',
            'contact.email' => 'info@venushotel.ir',
            'contact.address' => ['fa' => 'تبریز، خیابان امام خمینی، نزدیک بازار بزرگ', 'en' => 'Imam Khomeini St., near the Grand Bazaar, Tabriz, Iran', 'ar' => 'تبريز، شارع الإمام الخميني، قرب البازار الكبير'],
            'contact.postal_code' => '',
            'social.instagram' => 'venushotel.tabriz',
            'social.telegram' => 'venushotel',
            'social.whatsapp' => '989140000000',
            'social.bale' => '',
            'social.eitaa' => '',
            'social.aparat' => '',
            'social.youtube' => '',
            // location
            'map.lat' => 38.0802,
            'map.lng' => 46.2930,
            'map.zoom' => 15,
            'map.show' => true,
            'map.tiles' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            // hotel facts
            'hotel.checkin' => '14:00',
            'hotel.checkout' => '12:00',
            'hotel.rooms_total' => 42,
            'hotel.floors' => 6,
            'hotel.breakfast_hours' => ['fa' => '۷:۰۰ تا ۱۰:۳۰', 'en' => '7:00 – 10:30', 'ar' => '٧:٠٠ – ١٠:٣٠'],
            'hotel.cafe_hours' => ['fa' => '۱۰:۰۰ تا ۲۴:۰۰', 'en' => '10:00 – 24:00', 'ar' => '١٠:٠٠ – ٢٤:٠٠'],
            // prices
            'price.currency' => ['fa' => 'تومان', 'en' => 'Toman', 'ar' => 'تومان'],
            'price.unit' => ['fa' => 'هر شب', 'en' => 'per night', 'ar' => 'لليلة'],
            'price.empty_mode' => 'call',  // call | hide
            'price.empty_label' => ['fa' => 'برای قیمت تماس بگیرید', 'en' => 'Call for price', 'ar' => 'اتصلوا بينا للسعر'],
            'price.secondary' => [
                'en' => ['on' => true, 'label' => 'USD', 'symbol' => '$', 'rate' => 0.00001, 'decimals' => 0],
                'ar' => ['on' => true, 'label' => 'دينار عراقي', 'symbol' => '', 'rate' => 0.0145, 'decimals' => 0],
                'fa' => ['on' => false, 'label' => '', 'symbol' => '', 'rate' => 0, 'decimals' => 0],
            ],
            'price.note' => ['fa' => 'صبحانه رایگان است. قیمت‌ها ممکن است در ایام تعطیل تغییر کند.', 'en' => 'Breakfast is free. Prices may change on holidays.', 'ar' => 'الفطور مجاني. الأسعار ممكن تتغير بالعطل.'],
            'price.child_age' => 5,
            'price.show_kids' => true,
            'price.group_from' => 0, // 0 = one more than the largest room
            // call to action
            'cta.whatsapp_text' => [
                'fa' => 'سلام، درباره «{room}» برای {guests} نفر سؤال داشتم.',
                'en' => 'Hello, I would like to ask about the "{room}" for {guests} guests.',
                'ar' => 'هلا، أريد أستفسر عن «{room}» لـ {guests} أشخاص.',
            ],
            'cta.show_whatsapp' => true,
            'cta.show_call' => true,
            'cta.show_telegram' => true,
            // languages
            'lang.enabled' => ['fa', 'en', 'ar'],
            'lang.default' => 'fa',
            // appearance
            'theme.primary' => '#111316',   // dark surfaces, main buttons
            'theme.accent' => '#0b8a7a',    // links, highlights, accent buttons
            'theme.accent2' => '#e8913a',   // stars, small highlights
            'theme.bg' => '#f5f4f0',        // page background
            'theme.ink' => '#111316',       // text
            'theme.hero_overlay' => 40,
            'theme.animations' => true,
            'theme.preloader' => true,
            'theme.radius' => 22,
            'theme.marquee' => true,
            // navigation (order + visibility)
            'nav.items' => [
                ['key' => 'home', 'on' => true],
                ['key' => 'rooms', 'on' => true],
                ['key' => 'gallery', 'on' => true],
                ['key' => 'tour', 'on' => true],
                ['key' => 'breakfast', 'on' => true],
                ['key' => 'cafe', 'on' => true],
                ['key' => 'attractions', 'on' => true],
                ['key' => 'about', 'on' => true],
                ['key' => 'contact', 'on' => true],
            ],
            'rooms.group_by_beds' => true,
            // SEO
            'seo.description' => [
                'fa' => 'هتل ونوس تبریز، هتل سه ستاره با صبحانه رایگان، کافه، اتاق‌های سه، چهار و پنج تخته و دسترسی آسان به بازار تبریز.',
                'en' => 'Venus Hotel Tabriz: a 3-star hotel with free breakfast, a café, family rooms for 3 to 5 guests and easy access to the Tabriz Bazaar.',
                'ar' => 'فندق ونوس في تبريز: فندق ثلاث نجوم مع فطور مجاني وكوفي شوب وغرف عائلية من ٣ إلى ٥ أشخاص قرب بازار تبريز.',
            ],
            'seo.og_image' => null,
            'seo.head_code' => '',
            'seo.footer_code' => '',
            // pages
            'page.breakfast' => [
                'title' => ['fa' => 'صبحانه رایگان', 'en' => 'Free breakfast', 'ar' => 'فطور مجاني'],
                'body' => ['fa' => '', 'en' => '', 'ar' => ''],
                'cover_id' => null,
                'gallery' => [],
            ],
            'page.cafe' => [
                'title' => ['fa' => 'کافه تروث', 'en' => 'Truth Café', 'ar' => 'كوفي شوب تروث'],
                'body' => ['fa' => '', 'en' => '', 'ar' => ''],
                'cover_id' => null,
                'gallery' => [],
            ],
            'page.about' => [
                'body' => ['fa' => '', 'en' => '', 'ar' => ''],
                'cover_id' => null,
            ],
            'policies' => ['fa' => '', 'en' => '', 'ar' => ''],
            // contact form
            'form.enabled' => true,
            'form.notify_email' => '',
            // system
            'system.cache' => true,
            'system.analytics' => true,
            'system.maintenance' => false,
            'system.maintenance_text' => ['fa' => 'سایت در حال به‌روزرسانی است. به‌زودی برمی‌گردیم.', 'en' => 'We are updating the website. Back soon.', 'ar' => 'الموقع قيد التحديث. نرجع قريباً.'],
        ];
    }

    public static function load(): array
    {
        if (self::$cache === null) {
            $values = self::defaults();
            try {
                foreach (DB::all('SELECT name, value FROM settings') as $row) {
                    $v = json_decode((string)$row['value'], true);
                    $values[$row['name']] = (json_last_error() === JSON_ERROR_NONE) ? $v : $row['value'];
                }
            } catch (Throwable $e) {
                // not installed yet
            }
            self::$cache = $values;
        }
        return self::$cache;
    }

    public static function get(string $name, $default = null)
    {
        $all = self::load();
        return array_key_exists($name, $all) ? $all[$name] : $default;
    }

    public static function set(string $name, $value): void
    {
        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (DB::val('SELECT COUNT(*) FROM settings WHERE name = ?', [$name])) {
            DB::update('settings', ['value' => $json], 'name = ?', [$name]);
        } else {
            DB::insert('settings', ['name' => $name, 'value' => $json]);
        }
        if (self::$cache !== null) {
            self::$cache[$name] = $value;
        }
    }

    public static function setMany(array $values): void
    {
        DB::tx(function () use ($values) {
            foreach ($values as $k => $v) {
                self::set($k, $v);
            }
        });
    }

    public static function reset(): void
    {
        self::$cache = null;
    }
}
