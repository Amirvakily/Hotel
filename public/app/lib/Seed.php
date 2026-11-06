<?php
/**
 * Default and demo content. Default content (sections, amenities, categories) is always installed;
 * demo content (rooms, menus, sights, reviews, photos, 360 scenes) only when chosen in the installer.
 * All demo prices and contact details are examples – change them in the admin panel.
 */
final class Seed
{
    private static function L(string $fa, string $en, string $ar): string
    {
        return json_enc(['fa' => $fa, 'en' => $en, 'ar' => $ar]);
    }

    private static function A(string $fa, string $en, string $ar): array
    {
        return ['fa' => $fa, 'en' => $en, 'ar' => $ar];
    }

    /** Import a demo image from app/seed/media by file name. */
    private static array $mediaIds = [];

    private static function img(string $name): ?int
    {
        if (array_key_exists($name, self::$mediaIds)) {
            return self::$mediaIds[$name];
        }
        $dir = APP . '/seed/media/';
        $id = null;
        foreach (['.jpg', '.png', '.webp'] as $ext) {
            $f = $dir . $name . $ext;
            if (is_file($f)) {
                $tmp = STORAGE . '/cache/seed-' . bin2hex(random_bytes(4)) . $ext;
                copy($f, $tmp);
                try {
                    if (str_starts_with($name, 'pano-')) {
                        $mob = is_file($dir . $name . '-m.jpg') ? $dir . $name . '-m.jpg' : null;
                        $th = is_file($dir . $name . '-t.jpg') ? $dir . $name . '-t.jpg' : null;
                        $row = Media::storePano($tmp, $mob, $th, $name);
                    } else {
                        $row = Media::storeImage($tmp, $name);
                    }
                    $id = (int)$row['id'];
                } catch (Throwable $e) {
                    log_error('seed image ' . $name . ': ' . $e->getMessage());
                }
                @unlink($tmp);
                break;
            }
        }
        return self::$mediaIds[$name] = $id;
    }

    public static function run(bool $demo): void
    {
        self::defaults();
        if ($demo) {
            self::demo();
        }
    }

    // ------------------------------------------------------------------
    private static function defaults(): void
    {
        if (!DB::val('SELECT COUNT(*) FROM sections')) {
            $sections = [
                'hero' => [
                    'kicker' => self::A('تبریز · از ۱۳۹۵', 'Tabriz · since 2016', 'تبريز · منذ ٢٠١٦'),
                    'title' => self::A('هتل ونوس', 'Venus Hotel', 'فندق ونوس'),
                    'subtitle' => self::A('ستاره‌ای آرام در قلب تبریز؛ چند قدم تا بازار بزرگ، با صبحانه رایگان و کافه‌ای دنج.', 'A calm star in the heart of Tabriz – steps from the Grand Bazaar, with free breakfast and a cosy café.', 'هلا بيكم بتبريز! نجمة هادئة بقلب المدينة، خطوات عن البازار الكبير، مع فطور مجاني وكوفي شوب.'),
                    'cta_primary' => self::A('', '', ''), 'cta_secondary' => self::A('', '', ''),
                ],
                'intro' => [
                    'kicker' => self::A('به ونوس خوش آمدید', 'Welcome to Venus', 'أهلاً وسهلاً بيكم'),
                    'title' => self::A('مهمان‌نوازی تبریزی، با آرامشی که شایسته سفرتان است', 'Tabrizi hospitality, with the calm your trip deserves', 'ضيافة تبريزية، وراحة تستاهلونها'),
                    'body' => self::A(
                        '<p>هتل ونوس هتلی سه ستاره در مرکز تبریز است؛ نزدیک بازار تاریخی، خانه مشروطه و خیابان‌های قدیمی شهر. اتاق‌های ما برای خانواده‌ها و همسفرها طراحی شده‌اند و کوچک‌ترین اتاق هم سه تخت دارد.</p><p>صبح‌ها صبحانه بوفه با پنیر لیقوان، عسل سبلان و نان تازه رایگان است و عصرها کافه تروث با چای آذری و شیرینی‌های تبریزی منتظر شماست.</p>',
                        '<p>Venus is a three-star hotel in central Tabriz, close to the historic bazaar, the Constitution House and the old streets. Our rooms are made for families and friends travelling together – even the smallest has three beds.</p><p>Mornings start with a free buffet of Lighvan cheese, Sabalan honey and fresh bread, and in the evening Truth Café serves Azerbaijani tea and Tabriz sweets.</p>',
                        '<p>فندق ونوس فندق ثلاث نجوم بمركز تبريز، قريب من البازار التاريخي وبيت المشروطة والشوارع القديمة. غرفنا مصممة للعوائل والرفقة، وأصغر غرفة بيها ثلاث أسرّة.</p><p>الصبح اكو فطور بوفيه مجاني بجبن ليقوان وعسل سبلان وخبز طازج، وبالمسا الكوفي شوب ينتظركم بالچاي الآذري والحلويات التبريزية.</p>'
                    ),
                    'image_id' => null, 'image2_id' => null,
                    'stats' => [
                        ['value' => '42', 'label' => self::A('اتاق', 'rooms', 'غرفة')],
                        ['value' => '5', 'label' => self::A('دقیقه پیاده تا بازار', 'min walk to the bazaar', 'دقائق مشي للبازار')],
                        ['value' => '24', 'label' => self::A('ساعت پذیرش', 'hour front desk', 'ساعة استقبال')],
                        ['value' => '3', 'label' => self::A('زبان: فارسی، عربی، انگلیسی', 'languages spoken', 'لغات: فارسي، عربي، إنگليزي')],
                    ],
                ],
                'rooms' => [
                    'kicker' => self::A('اتاق‌ها', 'Rooms', 'الغرف'),
                    'title' => self::A('اتاقی برای هر تعداد همسفر', 'A room for every group', 'غرفة لكل عدد'),
                    'subtitle' => self::A('همه اتاق‌ها حداقل سه تخت دارند و قیمت بر اساس تعداد نفرات است.', 'Every room has at least three beds, and the price depends on how many of you there are.', 'كل الغرف بيها ثلاث أسرّة على الأقل، والسعر حسب عدد الأشخاص.'),
                    'limit' => 4,
                ],
                'highlights' => [
                    'kicker' => self::A('خدمات', 'Services', 'الخدمات'),
                    'title' => self::A('هر آنچه برای یک اقامت راحت لازم دارید', 'Everything for an easy stay', 'كل شي تحتاجونه لإقامة مريحة'),
                    'subtitle' => self::A('', '', ''),
                ],
                'dining' => [
                    'kicker' => self::A('خوراک و نوشیدنی', 'Food & drink', 'الأكل والمشروبات'),
                    'title' => self::A('صبحانه رایگان، و یک کافه جداگانه', 'Free breakfast, and a separate café', 'فطور مجاني، وكوفي شوب منفصل'),
                    'subtitle' => self::A('سالن صبحانه و کافه دو فضای جدا هستند.', 'The breakfast hall and the café are two different spaces.', 'صالة الفطور والكوفي شوب مكانين منفصلين.'),
                    'breakfast_title' => self::A('صبحانه بوفه رایگان', 'Free breakfast buffet', 'فطور بوفيه مجاني'),
                    'breakfast_text' => self::A('هر روز صبح با طعم‌های محلی آذربایجان؛ برای همه مهمانان رایگان.', 'Local Azerbaijani flavours every morning – free for every guest.', 'كل صباح بنكهات آذربيجانية محلية، مجاني لكل الضيوف.'),
                    'breakfast_image' => null,
                    'cafe_title' => self::A('کافه تروث', 'Truth Café', 'كوفي شوب تروث'),
                    'cafe_text' => self::A('قهوه تازه، چای آذری با قند و شیرینی‌های تبریزی تا نیمه‌شب.', 'Fresh coffee, Azerbaijani tea and Tabriz sweets until midnight.', 'قهوة طازجة وچاي آذري وحلويات تبريزية لحد نص الليل.'),
                    'cafe_image' => null,
                ],
                'tour' => [
                    'kicker' => self::A('تور مجازی', 'Virtual tour', 'جولة افتراضية'),
                    'title' => self::A('قبل از رسیدن، هتل را بگردید', 'Look around before you arrive', 'تجوّلوا بالفندق قبل لا توصلون'),
                    'body' => self::A('با تصاویر ۳۶۰ درجه در لابی، اتاق‌ها، سالن صبحانه و کافه بچرخید.', 'Turn around inside the lobby, rooms, breakfast hall and café with 360° photos.', 'دوّروا باللوبي والغرف وصالة الفطور والكوفي شوب بصور ٣٦٠ درجة.'),
                    'image_id' => null,
                ],
                'gallery' => [
                    'kicker' => self::A('گالری', 'Gallery', 'الصور'),
                    'title' => self::A('ونوس از نزدیک', 'Venus up close', 'ونوس من قريب'),
                    'subtitle' => self::A('', '', ''),
                    'limit' => 7,
                ],
                'reviews' => [
                    'kicker' => self::A('نظر مهمانان', 'Guest reviews', 'آراء الضيوف'),
                    'title' => self::A('مهمانان ما چه می‌گویند', 'What our guests say', 'شنو يگولون ضيوفنا'),
                    'subtitle' => self::A('', '', ''),
                ],
                'attractions' => [
                    'kicker' => self::A('تبریز', 'Tabriz', 'تبريز'),
                    'title' => self::A('شهر اولین‌ها، چند قدم آن‌طرف‌تر', 'The city of firsts, a few steps away', 'مدينة الأوائل، خطوات عنكم'),
                    'subtitle' => self::A('بازار تاریخی، مسجد کبود، ائل‌گلی و روستای کندوان.', 'The historic bazaar, the Blue Mosque, El Goli and Kandovan village.', 'البازار التاريخي والجامع الأزرق وإيل گلي وقرية كندوان.'),
                    'limit' => 6,
                ],
                'faq' => [
                    'kicker' => self::A('سؤالی دارید؟', 'Questions?', 'عندكم سؤال؟'),
                    'title' => self::A('پرسش‌های پرتکرار', 'Frequently asked', 'أسئلة متكررة'),
                    'subtitle' => self::A('اگر جوابتان اینجا نیست، تماس بگیرید.', 'If your answer is not here, just call us.', 'إذا ما لگيتوا الجواب، اتصلوا بينا.'),
                    'limit' => 6,
                ],
                'location' => [
                    'kicker' => self::A('موقعیت', 'Location', 'الموقع'),
                    'title' => self::A('در قلب تاریخی تبریز', 'In the historic heart of Tabriz', 'بالقلب التاريخي لتبريز'),
                    'subtitle' => self::A('', '', ''),
                ],
                'cta' => [
                    'title' => self::A('برای رزرو، فقط یک تماس فاصله دارید', 'Booking is one call away', 'الحجز بس اتصال واحد'),
                    'body' => self::A('رزرو آنلاین نداریم؛ با پذیرش تماس بگیرید یا در واتس‌اپ پیام بدهید.', 'We do not take online bookings – call the front desk or message us on WhatsApp.', 'ماكو حجز أونلاين؛ اتصلوا بالاستقبال أو دزّوا رسالة بالواتساب.'),
                    'image_id' => null,
                ],
            ];
            $i = 0;
            foreach ($sections as $k => $data) {
                DB::insert('sections', ['skey' => $k, 'active' => 1, 'sort' => $i++, 'data' => json_enc($data)]);
            }
        }

        if (!DB::val('SELECT COUNT(*) FROM amenities')) {
            $am = [
                ['croissant', 'hotel', 1, self::L('صبحانه رایگان', 'Free breakfast', 'فطور مجاني'), self::L('بوفه صبحانه با غذاهای محلی', 'Buffet with local dishes', 'بوفيه بأكلات محلية')],
                ['coffee', 'hotel', 1, self::L('کافه تروث', 'Truth Café', 'كوفي شوب تروث'), self::L('جدا از سالن صبحانه، تا نیمه‌شب', 'Separate from breakfast, until midnight', 'منفصل عن الفطور، لحد نص الليل')],
                ['wifi', 'both', 1, self::L('اینترنت رایگان', 'Free Wi-Fi', 'واي فاي مجاني'), self::L('در همه اتاق‌ها و لابی', 'In all rooms and the lobby', 'بكل الغرف واللوبي')],
                ['square-parking', 'hotel', 1, self::L('پارکینگ', 'Parking', 'موقف سيارات'), self::L('پارکینگ سرپوشیده برای مهمانان', 'Covered parking for guests', 'موقف مسقوف للضيوف')],
                ['concierge-bell', 'hotel', 1, self::L('پذیرش ۲۴ ساعته', '24-hour front desk', 'استقبال ٢٤ ساعة'), self::L('همیشه کسی هست که کمک کند', 'Someone is always here to help', 'دائماً اكو أحد يساعدكم')],
                ['plane', 'hotel', 1, self::L('ترانسفر فرودگاه', 'Airport transfer', 'توصيل من المطار'), self::L('با هماهنگی قبلی', 'On request', 'بالتنسيق المسبق')],
                ['luggage', 'hotel', 1, self::L('نگهداری چمدان', 'Luggage storage', 'حفظ الحقائب'), self::L('قبل از ورود و بعد از خروج', 'Before check-in and after check-out', 'قبل الدخول وبعد الخروج')],
                ['washing-machine', 'hotel', 1, self::L('خشک‌شویی', 'Laundry service', 'غسيل الملابس'), self::L('تحویل در همان روز', 'Same-day service', 'بنفس اليوم')],
                ['shield-check', 'hotel', 0, self::L('امنیت شبانه‌روزی', '24-hour security', 'أمن ٢٤ ساعة'), self::L('', '', '')],
                ['building-2', 'hotel', 0, self::L('آسانسور', 'Elevator', 'مصعد'), self::L('', '', '')],
                ['cigarette-off', 'hotel', 0, self::L('اتاق‌های غیر سیگاری', 'Non-smoking rooms', 'غرف لغير المدخنين'), self::L('', '', '')],
                ['snowflake', 'room', 0, self::L('سرمایش و گرمایش', 'Air conditioning & heating', 'تكييف وتدفئة'), self::L('', '', '')],
                ['tv', 'room', 0, self::L('تلویزیون', 'TV', 'تلفزيون'), self::L('', '', '')],
                ['refrigerator', 'room', 0, self::L('یخچال', 'Fridge', 'ثلاجة'), self::L('', '', '')],
                ['coffee', 'room', 0, self::L('چای‌ساز', 'Tea & coffee set', 'عدة چاي وقهوة'), self::L('', '', '')],
                ['vault', 'room', 0, self::L('گاوصندوق', 'Safe box', 'خزنة'), self::L('', '', '')],
                ['wind', 'room', 0, self::L('سشوار', 'Hair dryer', 'سشوار'), self::L('', '', '')],
                ['shower-head', 'room', 0, self::L('حمام اختصاصی', 'Private bathroom', 'حمام خاص'), self::L('', '', '')],
                ['bath', 'room', 0, self::L('وان حمام', 'Bathtub', 'بانيو'), self::L('', '', '')],
                ['sofa', 'room', 0, self::L('مبلمان نشیمن', 'Seating area', 'قنفات للجلوس'), self::L('', '', '')],
                ['lamp-desk', 'room', 0, self::L('میز کار', 'Work desk', 'مكتب'), self::L('', '', '')],
                ['bell', 'room', 0, self::L('سرویس اتاق', 'Room service', 'خدمة الغرف'), self::L('', '', '')],
            ];
            foreach ($am as $i => [$icon, $scope, $hl, $name, $desc]) {
                DB::insert('amenities', ['icon' => $icon, 'scope' => $scope, 'highlight' => $hl, 'name' => $name, 'description' => $desc, 'active' => 1, 'sort' => $i]);
            }
        }

        if (!DB::val('SELECT COUNT(*) FROM gallery_categories')) {
            $cats = [
                ['exterior', self::L('نمای هتل', 'Building', 'واجهة الفندق')],
                ['lobby', self::L('لابی و پذیرش', 'Lobby', 'اللوبي')],
                ['rooms', self::L('اتاق‌ها', 'Rooms', 'الغرف')],
                ['breakfast', self::L('سالن صبحانه', 'Breakfast hall', 'صالة الفطور')],
                ['cafe', self::L('کافه', 'Café', 'الكوفي شوب')],
            ];
            foreach ($cats as $i => [$slug, $name]) {
                DB::insert('gallery_categories', ['slug' => $slug, 'name' => $name, 'active' => 1, 'sort' => $i]);
            }
        }

        if (!DB::val('SELECT COUNT(*) FROM menu_categories')) {
            // empty starter categories so the admin has a structure to fill
            DB::insert('menu_categories', ['section' => 'breakfast', 'name' => self::L('محلی', 'Local', 'محلي'), 'description' => self::L('', '', ''), 'icon' => 'leaf', 'active' => 1, 'sort' => 0]);
            DB::insert('menu_categories', ['section' => 'cafe', 'name' => self::L('قهوه', 'Coffee', 'قهوة'), 'description' => self::L('', '', ''), 'icon' => 'coffee', 'active' => 1, 'sort' => 0]);
        }
    }

    // ------------------------------------------------------------------
    private static function demo(): void
    {
        $am = [];
        foreach (DB::all('SELECT id, icon, scope FROM amenities') as $a) {
            $am[$a['scope'] . ':' . $a['icon']] = (int)$a['id'];
        }
        $a = fn(string ...$keys) => array_values(array_filter(array_map(fn($k) => $am['room:' . $k] ?? $am['both:' . $k] ?? null, $keys)));

        // ---------- photos ----------
        $img = [
            'exterior' => self::img('exterior'), 'exterior2' => self::img('exterior-day'),
            'lobby' => self::img('lobby'), 'lobby2' => self::img('lobby-lounge'), 'reception' => self::img('reception'),
            'classic1' => self::img('room-classic'), 'classic2' => self::img('room-classic-2'),
            'comfort1' => self::img('room-comfort'), 'comfort2' => self::img('room-comfort-2'),
            'deluxe1' => self::img('room-deluxe'), 'deluxe2' => self::img('room-deluxe-2'), 'deluxe3' => self::img('room-deluxe-bath'),
            'quad1' => self::img('room-quad'), 'quad2' => self::img('room-quad-2'),
            'suite1' => self::img('room-suite'), 'suite2' => self::img('room-suite-2'), 'suite3' => self::img('room-suite-3'),
            'bath' => self::img('bathroom'), 'corridor' => self::img('corridor'),
            'breakfast1' => self::img('breakfast-hall'), 'breakfast2' => self::img('breakfast-buffet'),
            'cafe1' => self::img('cafe'), 'cafe2' => self::img('cafe-counter'), 'cafe3' => self::img('cafe-window'),
        ];
        $att = [
            'bazaar' => self::img('att-bazaar'), 'blue' => self::img('att-blue-mosque'), 'arg' => self::img('att-arg'),
            'elgoli' => self::img('att-elgoli'), 'kandovan' => self::img('att-kandovan'), 'saat' => self::img('att-saat'),
            'mashrouteh' => self::img('att-constitution'), 'museum' => self::img('att-museum'),
        ];
        $pano = [
            'lobby' => self::img('pano-lobby'), 'deluxe' => self::img('pano-room-deluxe'), 'quad' => self::img('pano-room-quad'),
            'breakfast' => self::img('pano-breakfast'), 'cafe' => self::img('pano-cafe'),
        ];
        $ids = fn(...$k) => array_values(array_filter(array_map(fn($x) => $img[$x] ?? null, $k)));

        // ---------- 360 scenes ----------
        $sceneIds = [];
        $scenes = [
            ['lobby', self::L('لابی و پذیرش', 'Lobby & reception', 'اللوبي والاستقبال'), $pano['lobby'], 1, -35, [
                ['type' => 'scene', 'target' => 'cafe', 'yaw' => 90, 'pitch' => -6, 'text' => self::A('کافه تروث', 'Truth Café', 'كوفي شوب تروث')],
                ['type' => 'scene', 'target' => 'breakfast', 'yaw' => -90, 'pitch' => -6, 'text' => self::A('سالن صبحانه', 'Breakfast hall', 'صالة الفطور')],
                ['type' => 'scene', 'target' => 'deluxe', 'yaw' => 22, 'pitch' => -6, 'text' => self::A('اتاق‌ها', 'To the rooms', 'للغرف')],
                ['type' => 'info', 'yaw' => 180, 'pitch' => -10, 'text' => self::A('پذیرش ۲۴ ساعته', '24-hour reception', 'استقبال ٢٤ ساعة')],
            ]],
            ['deluxe', self::L('اتاق سه‌تخته دلوکس', 'Deluxe Triple room', 'الغرفة الثلاثية الديلوكس'), $pano['deluxe'], 0, -60, [
                ['type' => 'scene', 'target' => 'lobby', 'yaw' => 152, 'pitch' => -8, 'text' => self::A('بازگشت به لابی', 'Back to the lobby', 'رجوع للوبي')],
                ['type' => 'scene', 'target' => 'quad', 'yaw' => 105, 'pitch' => -8, 'text' => self::A('اتاق چهارتخته', 'Family Quad room', 'الغرفة الرباعية')],
                ['type' => 'info', 'yaw' => -4, 'pitch' => 6, 'text' => self::A('پنجره رو به کوه عینالی', 'Window facing Mount Eynali', 'شباك على جبل عينالي')],
            ]],
            ['quad', self::L('اتاق چهارتخته خانوادگی', 'Family Quad room', 'الغرفة العائلية الرباعية'), $pano['quad'], 0, -60, [
                ['type' => 'scene', 'target' => 'lobby', 'yaw' => 152, 'pitch' => -8, 'text' => self::A('بازگشت به لابی', 'Back to the lobby', 'رجوع للوبي')],
                ['type' => 'scene', 'target' => 'deluxe', 'yaw' => 105, 'pitch' => -8, 'text' => self::A('اتاق دلوکس', 'Deluxe room', 'الغرفة الديلوكس')],
            ]],
            ['breakfast', self::L('سالن صبحانه', 'Breakfast hall', 'صالة الفطور'), $pano['breakfast'], 0, 160, [
                ['type' => 'scene', 'target' => 'lobby', 'yaw' => 180, 'pitch' => -2, 'text' => self::A('بازگشت به لابی', 'Back to the lobby', 'رجوع للوبي')],
                ['type' => 'info', 'yaw' => 165, 'pitch' => -24, 'text' => self::A('بوفه صبحانه رایگان', 'Free breakfast buffet', 'بوفيه فطور مجاني')],
            ]],
            ['cafe', self::L('کافه تروث', 'Truth Café', 'كوفي شوب تروث'), $pano['cafe'], 0, 70, [
                ['type' => 'scene', 'target' => 'lobby', 'yaw' => 180, 'pitch' => -4, 'text' => self::A('بازگشت به لابی', 'Back to the lobby', 'رجوع للوبي')],
                ['type' => 'info', 'yaw' => 90, 'pitch' => -12, 'text' => self::A('پیشخوان قهوه', 'Coffee bar', 'بار القهوة')],
            ]],
        ];
        $hotspotsFor = [];
        foreach ($scenes as $i => [$slug, $title, $mid, $def, $yaw, $hs]) {
            if (!$mid) {
                continue;
            }
            $sceneIds[$slug] = DB::insert('scenes', [
                'slug' => $slug, 'title' => $title, 'description' => self::L('', '', ''), 'type' => 'equirect', 'media_id' => $mid,
                'embed_url' => '', 'haov' => 360, 'vaov' => 180, 'voffset' => 0, 'yaw' => $yaw, 'pitch' => 0, 'hfov' => 105,
                'hotspots' => '[]', 'is_default' => $def, 'autorotate' => 1, 'active' => 1, 'sort' => $i,
            ]);
            $hotspotsFor[$slug] = $hs;
        }
        foreach ($hotspotsFor as $slug => $hs) {
            $hs = array_values(array_filter($hs, fn($h) => $h['type'] !== 'scene' || isset($sceneIds[$h['target']])));
            DB::update('scenes', ['hotspots' => json_enc($hs)], 'id = ?', [$sceneIds[$slug]]);
        }

        // ---------- rooms ----------
        $rooms = [
            [
                'slug' => 'classic-triple', 'color' => '#2f8f8b',
                'name' => self::L('سه‌تخته کلاسیک', 'Classic Triple', 'الثلاثية الكلاسيك'),
                'badge' => self::L('کلاسیک', 'Classic', 'كلاسيك'),
                'highlight' => self::L('سه تخت یک‌نفره جدا؛ مناسب دوستان و همسفرها', 'Three separate single beds – ideal for friends travelling together', 'ثلاث أسرّة مفردة منفصلة، مناسبة للرفقة والأصدقاء'),
                'summary' => self::L('اتاق سه‌تخته اقتصادی و دنج با سه تخت یک‌نفره، رو به حیاط آرام هتل.', 'A cosy, good-value triple with three single beds, facing the quiet courtyard.', 'غرفة ثلاثية مريحة وبسعر مناسب بثلاث أسرّة مفردة، على الحوش الهادئ.'),
                'description' => self::L(
                    '<p>اتاق کلاسیک برای کسانی است که تخت جدا می‌خواهند: سه تخت یک‌نفره با فاصله مناسب، کمد، میز کار و حمام اختصاصی با دوش. پنجره رو به حیاط داخلی است و شب‌ها کاملاً آرام است.</p>',
                    '<p>The Classic room is for guests who want separate beds: three well-spaced single beds, a wardrobe, a work desk and a private bathroom with shower. The window faces the inner courtyard, so nights are quiet.</p>',
                    '<p>الغرفة الكلاسيك للي يريدون أسرّة منفصلة: ثلاث أسرّة مفردة بمسافة مريحة، كنتور، مكتب، وحمام خاص بدوش. الشباك على الحوش الداخلي، فالليل هادئ تماماً.</p>'
                ),
                'beds' => 3, 'min' => 1, 'max' => 3, 'count' => 14, 'size' => 22,
                'floor' => self::L('طبقه ۱ تا ۳', 'Floors 1–3', 'الطوابق ١–٣'), 'view' => self::L('رو به حیاط', 'Courtyard', 'على الحوش'),
                'layout' => [['t' => 'single', 'n' => 3]],
                'prices' => ['1' => 2400000, '2' => 2900000, '3' => 3300000], 'extra' => 700000, 'xmax' => 1,
                'features' => [self::A('سه تخت یک‌نفره جدا', 'Three separate single beds', 'ثلاث أسرّة مفردة منفصلة'), self::A('دوش', 'Walk-in shower', 'دوش'), self::A('اتاق ساکت رو به حیاط', 'Quiet courtyard side', 'جهة الحوش الهادئة')],
                'amen' => $a('wifi', 'snowflake', 'tv', 'refrigerator', 'coffee', 'shower-head', 'lamp-desk'),
                'cover' => $img['classic1'], 'gallery' => $ids('classic1', 'classic2', 'bath', 'corridor'), 'scene' => null, 'featured' => 1,
            ],
            [
                'slug' => 'comfort-triple', 'color' => '#b8863b',
                'name' => self::L('سه‌تخته کامفورت', 'Comfort Triple', 'الثلاثية كومفورت'),
                'badge' => self::L('کامفورت', 'Comfort', 'كومفورت'),
                'highlight' => self::L('یک تخت دونفره و یک تخت یک‌نفره؛ مناسب زوج با یک فرزند', 'One double and one single bed – great for a couple with a child', 'سرير مزدوج وسرير مفرد، مناسبة لزوجين ويّا طفل'),
                'summary' => self::L('اتاقی روشن و جادارتر رو به شهر، با تخت دونفره و یک تخت یک‌نفره.', 'A brighter, roomier triple with city views, a double bed and a single bed.', 'غرفة أوسع ومنوّرة على المدينة، بسرير مزدوج وسرير مفرد.'),
                'description' => self::L(
                    '<p>کامفورت محبوب‌ترین اتاق خانواده‌هاست: یک تخت دونفره بزرگ، یک تخت یک‌نفره، صندلی راحتی کنار پنجره و چشم‌انداز خیابان‌های قدیمی تبریز.</p>',
                    '<p>Comfort is the family favourite: a large double bed, a single bed, an armchair by the window and a view over the old streets of Tabriz.</p>',
                    '<p>كومفورت أكثر غرفة تحبها العوائل: سرير مزدوج كبير، سرير مفرد، كرسي مريح يم الشباك، وإطلالة على شوارع تبريز القديمة.</p>'
                ),
                'beds' => 3, 'min' => 1, 'max' => 3, 'count' => 12, 'size' => 25,
                'floor' => self::L('طبقه ۲ تا ۵', 'Floors 2–5', 'الطوابق ٢–٥'), 'view' => self::L('رو به شهر', 'City view', 'على المدينة'),
                'layout' => [['t' => 'double', 'n' => 1], ['t' => 'single', 'n' => 1]],
                'prices' => ['1' => 2600000, '2' => 3200000, '3' => 3700000], 'extra' => 750000, 'xmax' => 1,
                'features' => [self::A('تخت دونفره + یک‌نفره', 'Double + single bed', 'سرير مزدوج + مفرد'), self::A('چشم‌انداز شهر', 'City view', 'إطلالة على المدينة'), self::A('صندلی راحتی', 'Armchair', 'كرسي مريح'), self::A('دوش', 'Walk-in shower', 'دوش')],
                'amen' => $a('wifi', 'snowflake', 'tv', 'refrigerator', 'coffee', 'vault', 'wind', 'shower-head', 'lamp-desk'),
                'cover' => $img['comfort1'], 'gallery' => $ids('comfort1', 'comfort2', 'bath'), 'scene' => null, 'featured' => 1,
            ],
            [
                'slug' => 'deluxe-triple', 'color' => '#2b4f86',
                'name' => self::L('سه‌تخته دلوکس', 'Deluxe Triple', 'الثلاثية الديلوكس'),
                'badge' => self::L('دلوکس', 'Deluxe', 'ديلوكس'),
                'highlight' => self::L('بزرگ‌تر، رو به کوه عینالی، با وان حمام و نشیمن کوچک', 'Largest triple: Mount Eynali view, bathtub and a small lounge', 'أكبر ثلاثية: إطلالة على جبل عينالي، بانيو، وجلسة صغيرة'),
                'summary' => self::L('بزرگ‌ترین اتاق سه‌تخته با تخت دونفره بزرگ، تخت یک‌نفره، نشیمن و وان.', 'Our largest triple, with a queen bed, a single bed, a lounge corner and a bathtub.', 'أكبر غرفة ثلاثية، بسرير كوين وسرير مفرد وجلسة وبانيو.'),
                'description' => self::L(
                    '<p>دلوکس در طبقات بالا قرار دارد و از پنجره‌های بزرگش کوه عینالی پیداست. علاوه بر تخت دونفره بزرگ و تخت یک‌نفره، یک مبل نشیمن، میز کار و حمام با وان دارد.</p>',
                    '<p>Deluxe rooms are on the upper floors, with large windows facing Mount Eynali. Besides a queen bed and a single bed there is a sofa corner, a work desk and a bathroom with a tub.</p>',
                    '<p>الديلوكس بالطوابق العالية، وشبابيكها الكبيرة على جبل عينالي. غير السرير الكوين والسرير المفرد، اكو قنفة للجلوس ومكتب وحمام ويا بانيو.</p>'
                ),
                'beds' => 3, 'min' => 1, 'max' => 3, 'count' => 6, 'size' => 30,
                'floor' => self::L('طبقه ۵ و ۶', 'Floors 5–6', 'الطوابق ٥–٦'), 'view' => self::L('رو به کوه عینالی', 'Mount Eynali', 'على جبل عينالي'),
                'layout' => [['t' => 'queen', 'n' => 1], ['t' => 'single', 'n' => 1]],
                'prices' => ['1' => 3100000, '2' => 3800000, '3' => 4400000], 'extra' => 850000, 'xmax' => 1,
                'features' => [self::A('تخت دونفره بزرگ + یک‌نفره', 'Queen + single bed', 'سرير كوين + مفرد'), self::A('وان حمام', 'Bathtub', 'بانيو'), self::A('نشیمن کوچک', 'Lounge corner', 'جلسة صغيرة'), self::A('چشم‌انداز کوه', 'Mountain view', 'إطلالة على الجبل'), self::A('حوله و دمپایی', 'Bathrobe & slippers', 'روب ونعال')],
                'amen' => $a('wifi', 'snowflake', 'tv', 'refrigerator', 'coffee', 'vault', 'wind', 'bath', 'sofa', 'lamp-desk', 'bell'),
                'cover' => $img['deluxe1'], 'gallery' => $ids('deluxe1', 'deluxe2', 'deluxe3', 'corridor'), 'scene' => $sceneIds['deluxe'] ?? null, 'featured' => 1,
            ],
            [
                'slug' => 'family-quad', 'color' => '#a4573b',
                'name' => self::L('چهارتخته خانوادگی', 'Family Quad', 'العائلية الرباعية'),
                'badge' => self::L('خانوادگی', 'Family', 'عائلية'),
                'highlight' => self::L('یک تخت دونفره و دو تخت یک‌نفره؛ جادار برای خانواده چهارنفره', 'One double and two single beds – space for a family of four', 'سرير مزدوج وسريرين مفردين، وسيعة لعائلة من أربعة'),
                'summary' => self::L('اتاق چهارتخته جادار با کمد بزرگ و مبل، مناسب خانواده‌ها.', 'A spacious four-bed room with a large wardrobe and a sofa, made for families.', 'غرفة رباعية وسيعة بكنتور كبير وقنفة، مناسبة للعوائل.'),
                'description' => self::L(
                    '<p>اتاق خانوادگی جای کافی برای چمدان‌ها و بازی بچه‌ها دارد: یک تخت دونفره، دو تخت یک‌نفره، کمد دیواری بزرگ و یک مبل.</p>',
                    '<p>The Family Quad has room for suitcases and for kids to play: a double bed, two single beds, a large built-in wardrobe and a sofa.</p>',
                    '<p>الغرفة العائلية بيها مكان كافي للجنط ولعب الأطفال: سرير مزدوج، سريرين مفردين، كنتور كبير، وقنفة.</p>'
                ),
                'beds' => 4, 'min' => 2, 'max' => 4, 'count' => 7, 'size' => 32,
                'floor' => self::L('طبقه ۲ تا ۴', 'Floors 2–4', 'الطوابق ٢–٤'), 'view' => self::L('رو به شهر', 'City view', 'على المدينة'),
                'layout' => [['t' => 'double', 'n' => 1], ['t' => 'single', 'n' => 2]],
                'prices' => ['2' => 3900000, '3' => 4400000, '4' => 4900000], 'extra' => 800000, 'xmax' => 1,
                'features' => [self::A('کمد دیواری بزرگ', 'Large wardrobe', 'كنتور كبير'), self::A('مبل', 'Sofa', 'قنفة'), self::A('امکان تخت کودک', 'Baby cot on request', 'سرير أطفال عند الطلب')],
                'amen' => $a('wifi', 'snowflake', 'tv', 'refrigerator', 'coffee', 'vault', 'wind', 'shower-head', 'sofa'),
                'cover' => $img['quad1'], 'gallery' => $ids('quad1', 'quad2', 'bath'), 'scene' => $sceneIds['quad'] ?? null, 'featured' => 1,
            ],
            [
                'slug' => 'family-suite', 'color' => '#7a3b5e',
                'name' => self::L('سوئیت پنج‌تخته', 'Five-Bed Family Suite', 'الجناح العائلي الخماسي'),
                'badge' => self::L('سوئیت', 'Suite', 'جناح'),
                'highlight' => self::L('دو اتاق جدا با پذیرایی؛ برای خانواده‌های بزرگ', 'Two separate rooms and a living room – for large families', 'غرفتين منفصلتين وصالة، للعوائل الكبيرة'),
                'summary' => self::L('سوئیت دو خوابه با پذیرایی، یک تخت دونفره و سه تخت یک‌نفره.', 'A two-bedroom suite with a living room, one double bed and three single beds.', 'جناح بغرفتين نوم وصالة، سرير مزدوج وثلاث أسرّة مفردة.'),
                'description' => self::L(
                    '<p>سوئیت خانوادگی دو اتاق خواب جدا دارد؛ یکی با تخت دونفره و دیگری با سه تخت یک‌نفره، به‌علاوه یک پذیرایی با مبلمان و میز ناهارخوری کوچک.</p><p>برای گروه‌های بیشتر از پنج نفر، تخت اضافه هم داریم.</p>',
                    '<p>The Family Suite has two separate bedrooms – one with a double bed and one with three single beds – plus a living room with sofas and a small dining table.</p><p>For groups larger than five, an extra bed is available.</p>',
                    '<p>الجناح العائلي بيه غرفتين نوم منفصلة، وحدة بسرير مزدوج والثانية بثلاث أسرّة مفردة، وصالة بقنفات وميز أكل صغير.</p><p>إذا عددكم أكثر من خمسة، اكو سرير إضافي.</p>'
                ),
                'beds' => 5, 'min' => 3, 'max' => 5, 'count' => 3, 'size' => 48,
                'floor' => self::L('طبقه ۶', 'Floor 6', 'الطابق ٦'), 'view' => self::L('کوه و شهر', 'Mountain & city', 'الجبل والمدينة'),
                'layout' => [['t' => 'double', 'n' => 1], ['t' => 'single', 'n' => 3]],
                'prices' => ['3' => 5600000, '4' => 6100000, '5' => null], 'extra' => 900000, 'xmax' => 1,
                'features' => [self::A('دو اتاق خواب جدا', 'Two separate bedrooms', 'غرفتين نوم منفصلة'), self::A('پذیرایی', 'Living room', 'صالة'), self::A('وان حمام', 'Bathtub', 'بانيو'), self::A('میز ناهارخوری', 'Dining table', 'ميز أكل'), self::A('حوله و دمپایی', 'Bathrobe & slippers', 'روب ونعال')],
                'amen' => $a('wifi', 'snowflake', 'tv', 'refrigerator', 'coffee', 'vault', 'wind', 'bath', 'sofa', 'lamp-desk', 'bell'),
                'cover' => $img['suite1'], 'gallery' => $ids('suite1', 'suite2', 'suite3', 'bath'), 'scene' => null, 'featured' => 0,
            ],
        ];
        $notes = self::L('نفر اضافه با تخت سفری؛ هزینه هر نفر اضافه جداگانه حساب می‌شود.', 'Extra guests sleep on a rollaway bed and are charged per person.', 'الشخص الإضافي ينام على سرير متنقل ويُحسب سعره لوحده.');
        foreach ($rooms as $i => $r) {
            DB::insert('rooms', [
                'slug' => $r['slug'], 'name' => $r['name'], 'highlight' => $r['highlight'], 'summary' => $r['summary'], 'description' => $r['description'],
                'badge' => $r['badge'], 'color' => $r['color'], 'beds' => $r['beds'], 'min_guests' => $r['min'], 'max_guests' => $r['max'], 'max_extra' => $r['xmax'] ?? 0,
                'room_count' => $r['count'], 'size_m2' => $r['size'], 'floor' => $r['floor'], 'view' => $r['view'],
                'bed_layout' => json_enc($r['layout']), 'prices' => json_enc($r['prices']), 'extra_bed_price' => $r['extra'] ?: null,
                'show_prices' => 1, 'price_note' => $notes, 'amenities' => json_enc($r['amen']), 'features' => json_enc($r['features']),
                'cover_id' => $r['cover'], 'gallery' => json_enc($r['gallery']), 'scene_id' => $r['scene'],
                'featured' => $r['featured'], 'active' => 1, 'sort' => $i, 'created_at' => DB::now(), 'updated_at' => DB::now(),
            ]);
        }

        // ---------- slides ----------
        $slides = [
            [$img['lobby'], self::L('لابی', 'The lobby', 'اللوبي')],
            [$img['deluxe1'], self::L('اتاق سه‌تخته دلوکس', 'Deluxe Triple', 'الثلاثية الديلوكس')],
            [$img['cafe1'], self::L('کافه تروث', 'Truth Café', 'كوفي شوب تروث')],
            [$img['exterior'], self::L('نمای هتل در شب', 'The hotel at dusk', 'الفندق بالمسا')],
        ];
        foreach ($slides as $i => [$mid, $title]) {
            if ($mid) {
                DB::insert('slides', ['media_id' => $mid, 'title' => $title, 'subtitle' => self::L('', '', ''), 'active' => 1, 'sort' => $i]);
            }
        }

        // ---------- gallery ----------
        $cats = [];
        foreach (DB::all('SELECT id, slug FROM gallery_categories') as $c) {
            $cats[$c['slug']] = (int)$c['id'];
        }
        $gal = [
            ['exterior', 'exterior', self::L('نمای هتل در شب', 'The hotel at dusk', 'الفندق بالمسا')],
            ['exterior', 'exterior2', self::L('ورودی هتل', 'Hotel entrance', 'مدخل الفندق')],
            ['lobby', 'lobby', self::L('لابی', 'Lobby', 'اللوبي')],
            ['lobby', 'reception', self::L('پذیرش', 'Reception', 'الاستقبال')],
            ['lobby', 'lobby2', self::L('نشیمن لابی', 'Lobby lounge', 'جلسة اللوبي')],
            ['rooms', 'deluxe1', self::L('سه‌تخته دلوکس', 'Deluxe Triple', 'الثلاثية الديلوكس')],
            ['rooms', 'comfort1', self::L('سه‌تخته کامفورت', 'Comfort Triple', 'الثلاثية كومفورت')],
            ['rooms', 'classic1', self::L('سه‌تخته کلاسیک', 'Classic Triple', 'الثلاثية الكلاسيك')],
            ['rooms', 'quad1', self::L('چهارتخته خانوادگی', 'Family Quad', 'العائلية الرباعية')],
            ['rooms', 'suite1', self::L('سوئیت پنج‌تخته', 'Family Suite', 'الجناح العائلي')],
            ['rooms', 'bath', self::L('حمام', 'Bathroom', 'الحمام')],
            ['rooms', 'corridor', self::L('راهرو طبقات', 'Corridor', 'الممر')],
            ['breakfast', 'breakfast1', self::L('سالن صبحانه', 'Breakfast hall', 'صالة الفطور')],
            ['breakfast', 'breakfast2', self::L('بوفه صبحانه', 'Breakfast buffet', 'بوفيه الفطور')],
            ['cafe', 'cafe1', self::L('کافه تروث', 'Truth Café', 'كوفي شوب تروث')],
            ['cafe', 'cafe2', self::L('پیشخوان قهوه', 'Coffee bar', 'بار القهوة')],
            ['cafe', 'cafe3', self::L('کنار پنجره', 'By the window', 'يم الشباك')],
        ];
        $order = [0, 5, 14, 2, 12, 6, 3, 15, 8, 1, 13, 9, 4, 7, 16, 10, 11];
        foreach ($order as $sort => $k) {
            [$cat, $key, $cap] = $gal[$k];
            if (!empty($img[$key]) && isset($cats[$cat])) {
                DB::insert('gallery_items', ['category_id' => $cats[$cat], 'media_id' => $img[$key], 'caption' => $cap, 'active' => 1, 'sort' => $sort, 'created_at' => DB::now()]);
            }
        }

        // ---------- home sections images ----------
        $sec = DB::all('SELECT id, skey, data FROM sections');
        foreach ($sec as $s) {
            $d = json_dec($s['data'], []);
            if ($s['skey'] === 'intro') {
                $d['image_id'] = $img['lobby2'] ?: $img['lobby'];
                $d['image2_id'] = $img['cafe3'] ?: $img['breakfast2'];
            } elseif ($s['skey'] === 'dining') {
                $d['breakfast_image'] = $img['breakfast2'];
                $d['cafe_image'] = $img['cafe1'];
            } elseif ($s['skey'] === 'cta') {
                $d['image_id'] = $img['exterior2'] ?: $img['exterior'];
            } elseif ($s['skey'] === 'tour') {
                $d['image_id'] = $pano['lobby'];
            }
            DB::update('sections', ['data' => json_enc($d)], 'id = ?', [$s['id']]);
        }

        // ---------- menus ----------
        DB::q('DELETE FROM menu_items');
        DB::q('DELETE FROM menu_categories');
        $menus = [
            'breakfast' => [
                ['leaf', self::L('محلی آذربایجان', 'Local Azerbaijani', 'محلي آذربيجاني'), self::L('طعم‌هایی که تبریز را با آن می‌شناسند', 'The flavours Tabriz is known for', 'النكهات اللي تنعرف بيها تبريز'), [
                    [self::L('پنیر لیقوان', 'Lighvan cheese', 'جبن ليقوان'), self::L('پنیر گوسفندی معروف روستای لیقوان', 'The famous sheep’s cheese from Lighvan village', 'جبن غنم مشهور من قرية ليقوان'), self::L('محلی', 'Local', 'محلي')],
                    [self::L('عسل سبلان', 'Sabalan honey', 'عسل سبلان'), self::L('عسل کوهستانی با سرشیر تازه', 'Mountain honey with fresh clotted cream', 'عسل جبلي ويّا قيمر طازج'), self::L('', '', '')],
                    [self::L('کره و مربای خانگی', 'Butter & home-made jam', 'زبدة ومربى بيتي'), self::L('مربای آلبالو، هویج و به', 'Sour cherry, carrot and quince jam', 'مربى الكرز والجزر والسفرجل'), self::L('', '', '')],
                    [self::L('نان تازه', 'Fresh bread', 'خبز طازج'), self::L('لواش و سنگک از نانوایی محل', 'Lavash and sangak from the local bakery', 'لواش وسنگك من فرن المنطقة'), self::L('', '', '')],
                    [self::L('گردو و سبزی خوردن', 'Walnuts & fresh herbs', 'جوز وخضرة'), self::L('', '', ''), self::L('', '', '')],
                ]],
                ['flame', self::L('گرم', 'Hot dishes', 'أكلات حارة'), self::L('', '', ''), [
                    [self::L('املت گوجه', 'Tomato omelette', 'أومليت طماطة'), self::L('', '', ''), self::L('', '', '')],
                    [self::L('تخم‌مرغ آب‌پز', 'Boiled eggs', 'بيض مسلوق'), self::L('', '', ''), self::L('', '', '')],
                    [self::L('عدسی', 'Lentil soup', 'شوربة عدس'), self::L('در روزهای سرد', 'On cold days', 'بالأيام الباردة'), self::L('', '', '')],
                    [self::L('حلیم', 'Haleem', 'هريسة'), self::L('جمعه‌ها', 'On Fridays', 'يوم الجمعة'), self::L('جمعه', 'Fri', 'جمعة')],
                    [self::L('لوبیا گرم', 'Warm beans', 'فاصوليا'), self::L('', '', ''), self::L('', '', '')],
                ]],
                ['coffee', self::L('نوشیدنی', 'Drinks', 'مشروبات'), self::L('', '', ''), [
                    [self::L('چای تازه‌دم', 'Freshly brewed tea', 'چاي مخدّر'), self::L('', '', ''), self::L('', '', '')],
                    [self::L('قهوه', 'Coffee', 'قهوة'), self::L('', '', ''), self::L('', '', '')],
                    [self::L('شیر', 'Milk', 'حليب'), self::L('', '', ''), self::L('', '', '')],
                    [self::L('آب‌پرتقال طبیعی', 'Fresh orange juice', 'عصير برتقال طبيعي'), self::L('', '', ''), self::L('', '', '')],
                ]],
                ['salad', self::L('تازه', 'Fresh', 'طازج'), self::L('', '', ''), [
                    [self::L('میوه فصل', 'Seasonal fruit', 'فواكه الموسم'), self::L('', '', ''), self::L('', '', '')],
                    [self::L('گوجه، خیار و زیتون', 'Tomato, cucumber & olives', 'طماطة وخيار وزيتون'), self::L('', '', ''), self::L('', '', '')],
                    [self::L('ماست و کره بادام‌زمینی', 'Yoghurt & peanut butter', 'لبن وزبدة فول سوداني'), self::L('', '', ''), self::L('', '', '')],
                ]],
            ],
            'cafe' => [
                ['coffee', self::L('قهوه', 'Coffee', 'قهوة'), self::L('', '', ''), [
                    [self::L('اسپرسو', 'Espresso', 'إسبريسو'), self::L('', '', ''), self::L('', '', ''), 120000],
                    [self::L('کاپوچینو', 'Cappuccino', 'كابتشينو'), self::L('', '', ''), self::L('', '', ''), 160000],
                    [self::L('لاته', 'Latte', 'لاتيه'), self::L('', '', ''), self::L('', '', ''), 170000],
                    [self::L('قهوه ترک', 'Turkish coffee', 'قهوة تركية'), self::L('با لوکوم', 'Served with lokum', 'ويّا راحة الحلقوم'), self::L('', '', ''), 140000],
                ]],
                ['cup-soda', self::L('چای و دمنوش', 'Tea & infusions', 'چاي ومنقوعات'), self::L('', '', ''), [
                    [self::L('چای آذری با قند', 'Azerbaijani tea with sugar cubes', 'چاي آذري ويّا قند'), self::L('در استکان کمرباریک', 'In a traditional glass', 'باستكان'), self::L('محبوب', 'Favourite', 'مفضّل'), 90000],
                    [self::L('چای زعفرانی', 'Saffron tea', 'چاي بالزعفران'), self::L('', '', ''), self::L('', '', ''), 120000],
                    [self::L('دمنوش گل‌گاوزبان', 'Borage infusion', 'منقوع لسان الثور'), self::L('', '', ''), self::L('', '', ''), 110000],
                ]],
                ['glass-water', self::L('نوشیدنی سرد', 'Cold drinks', 'مشروبات باردة'), self::L('', '', ''), [
                    [self::L('آیس لاته', 'Iced latte', 'آيس لاتيه'), self::L('', '', ''), self::L('', '', ''), 190000],
                    [self::L('لیموناد نعناع', 'Mint lemonade', 'ليموناضة بالنعناع'), self::L('', '', ''), self::L('', '', ''), 150000],
                    [self::L('شربت سکنجبین', 'Sekanjabin (mint-vinegar syrup)', 'شربت سكنجبين'), self::L('شربت سنتی ایرانی', 'A traditional Persian cooler', 'شربت إيراني تقليدي'), self::L('', '', ''), 130000],
                ]],
                ['cake-slice', self::L('شیرینی تبریزی', 'Tabriz sweets', 'حلويات تبريزية'), self::L('از قنادی‌های قدیمی شهر', 'From the city’s old confectioners', 'من أقدم حلوانيات المدينة'), [
                    [self::L('قرابیه', 'Qurabiyeh', 'قرابية'), self::L('شیرینی بادامی معروف تبریز', 'Tabriz’s famous almond cookie', 'حلوى اللوز المشهورة بتبريز'), self::L('تبریزی', 'Local', 'تبريزي'), 80000],
                    [self::L('ریس', 'Riss', 'ريس'), self::L('', '', ''), self::L('', '', ''), 90000],
                    [self::L('باقلوا', 'Baklava', 'بقلاوة'), self::L('', '', ''), self::L('', '', ''), 110000],
                    [self::L('چیزکیک', 'Cheesecake', 'تشيز كيك'), self::L('', '', ''), self::L('', '', ''), 170000],
                ]],
                ['sandwich', self::L('خوراک سبک', 'Light bites', 'أكلات خفيفة'), self::L('', '', ''), [
                    [self::L('پنینی مرغ', 'Chicken panini', 'بانيني دجاج'), self::L('', '', ''), self::L('', '', ''), 280000],
                    [self::L('کلاب ساندویچ', 'Club sandwich', 'كلوب ساندويش'), self::L('', '', ''), self::L('', '', ''), 260000],
                    [self::L('سالاد سزار', 'Caesar salad', 'سلطة سيزر'), self::L('', '', ''), self::L('', '', ''), 250000],
                ]],
            ],
        ];
        foreach ($menus as $section => $cats2) {
            foreach ($cats2 as $ci => [$icon, $name, $desc, $items]) {
                $cid = DB::insert('menu_categories', ['section' => $section, 'name' => $name, 'description' => $desc, 'icon' => $icon, 'active' => 1, 'sort' => $ci]);
                foreach ($items as $ii => $it) {
                    DB::insert('menu_items', ['category_id' => $cid, 'name' => $it[0], 'description' => $it[1], 'tag' => $it[2], 'price' => $it[3] ?? null, 'media_id' => null, 'active' => 1, 'sort' => $ii]);
                }
            }
        }

        // ---------- attractions (distances from the default map position) ----------
        $atts = [
            ['bazaar', 'shopping-bag', 0.3, 38.0807, 46.2924, self::L('بازار بزرگ تبریز', 'Tabriz Grand Bazaar', 'بازار تبريز الكبير'), self::L('بزرگ‌ترین بازار سرپوشیده جهان و میراث جهانی یونسکو؛ پر از فرش، ادویه و صنایع دستی.', 'One of the largest covered bazaars in the world and a UNESCO World Heritage Site – carpets, spices and crafts.', 'من أكبر الأسواق المسقوفة بالعالم، ومن التراث العالمي لليونسكو؛ سجاد وبهارات وصناعات يدوية.'), self::L('۵ دقیقه پیاده', '5 min walk', '٥ دقائق مشي')],
            ['mashrouteh', 'landmark', 0.3, 38.0812, 46.2946, self::L('خانه مشروطه', 'Constitution House', 'بيت المشروطة'), self::L('خانه‌ای قاجاری با شیشه‌های رنگی که محل جلسات انقلاب مشروطه بود.', 'A Qajar-era house with stained glass, where the Constitutional Revolution was planned.', 'بيت قاجاري بزجاج ملوّن، كانت بيه اجتماعات ثورة المشروطة.'), self::L('۵ دقیقه پیاده', '5 min walk', '٥ دقائق مشي')],
            ['saat', 'clock', 0.9, 38.0739, 46.2958, self::L('عمارت شهرداری (ساعت)', 'Municipality Palace (Saat Tower)', 'عمارة البلدية (الساعة)'), self::L('نماد تبریز با برج ساعت و میدان زیبایش.', 'The symbol of Tabriz, with its clock tower and square.', 'رمز تبريز ببرج الساعة والساحة الحلوة.'), self::L('۱۲ دقیقه پیاده', '12 min walk', '١٢ دقيقة مشي')],
            ['blue', 'landmark', 1.2, 38.0755, 46.3006, self::L('مسجد کبود', 'Blue Mosque (Kabud)', 'الجامع الأزرق (كبود)'), self::L('فیروزه اسلام؛ کاشی‌کاری لاجوردی قرن نهم هجری.', 'The “Turquoise of Islam”, with 15th-century lapis tilework.', '«فيروزة الإسلام»، بكاشي لازوردي من القرن التاسع الهجري.'), self::L('۴ دقیقه با ماشین', '4 min by car', '٤ دقائق بالسيارة')],
            ['museum', 'building', 1.3, 38.0757, 46.3024, self::L('موزه آذربایجان', 'Azerbaijan Museum', 'متحف آذربيجان'), self::L('دومین موزه باستان‌شناسی بزرگ ایران، کنار مسجد کبود.', 'Iran’s second-largest archaeology museum, next to the Blue Mosque.', 'ثاني أكبر متحف آثار بإيران، يم الجامع الأزرق.'), self::L('۴ دقیقه با ماشین', '4 min by car', '٤ دقائق بالسيارة')],
            ['arg', 'landmark', 1.5, 38.0725, 46.2868, self::L('ارگ علیشاه', 'Arg of Tabriz', 'قلعة أرگ علي شاه'), self::L('دیوار آجری عظیم قرن هشتم، از بلندترین سازه‌های آجری قدیم.', 'A huge 14th-century brick wall, one of the tallest old brick structures.', 'سور طابوق ضخم من القرن الثامن الهجري، من أعلى المباني الطابوقية القديمة.'), self::L('۵ دقیقه با ماشین', '5 min by car', '٥ دقائق بالسيارة')],
            ['elgoli', 'trees', 8.5, 38.0233, 46.3580, self::L('پارک ائل‌گلی', 'El Goli Park', 'منتزه إيل گلي'), self::L('استخر بزرگ با عمارت میانی، پاتوق عصرهای تبریزی‌ها.', 'A large lake with a pavilion in the middle – where Tabriz spends its evenings.', 'بحيرة كبيرة بنصها عمارة، مكان مسيات أهل تبريز.'), self::L('۱۵ دقیقه با ماشین', '15 min by car', '١٥ دقيقة بالسيارة')],
            ['kandovan', 'mountain', 58, 37.7942, 46.2474, self::L('روستای کندوان', 'Kandovan Village', 'قرية كندوان'), self::L('روستای صخره‌ای شگفت‌انگیز با خانه‌هایی درون سنگ، در دامنه سهند.', 'An extraordinary village of homes carved into rock cones on Mount Sahand.', 'قرية عجيبة بيوتها منحوتة بالصخر على جبل سهند.'), self::L('۱ ساعت با ماشین', '1 hour by car', 'ساعة بالسيارة')],
        ];
        foreach ($atts as $i => [$key, $icon, $km, $lat, $lng, $name, $desc, $travel]) {
            DB::insert('attractions', ['name' => $name, 'description' => $desc, 'distance_km' => $km, 'travel' => $travel, 'media_id' => $att[$key] ?? null, 'icon' => $icon, 'lat' => $lat, 'lng' => $lng, 'active' => 1, 'sort' => $i]);
        }

        // ---------- reviews ----------
        $reviews = [
            [self::L('مریم از تهران', 'Maryam, Tehran', 'مريم من طهران'), self::L('تهران', 'Tehran, Iran', 'طهران'), 5, self::L('اتاق دلوکس تمیز و ساکت بود و صبحانه با پنیر لیقوان عالی. تا بازار پیاده رفتیم.', 'The Deluxe room was spotless and quiet, and breakfast with Lighvan cheese was wonderful. We walked to the bazaar.', 'الغرفة الديلوكس نظيفة وهادئة، والفطور ويّا جبن ليقوان رائع. مشينا للبازار.'), 'Google'],
            [self::L('ابوعلی', 'Abu Ali', 'أبو علي'), self::L('بغداد، عراق', 'Baghdad, Iraq', 'بغداد، العراق'), 5, self::L('ما خانواده پنج‌نفره بودیم و سوئیت خیلی جادار بود. پرسنل عربی صحبت می‌کردند.', 'We were a family of five and the suite was very spacious. The staff spoke Arabic.', 'كنّا عائلة من خمسة والجناح وسيع هواية. الموظفين يحچون عربي. والله زين!'), 'Snapp Trip'],
            [self::L('حسین', 'Hussein', 'حسين'), self::L('نجف، عراق', 'Najaf, Iraq', 'النجف، العراق'), 4, self::L('موقعیت هتل نزدیک بازار است و پارکینگ دارد. چای آذری کافه را حتماً امتحان کنید.', 'The location is close to the bazaar and there is parking. Try the Azerbaijani tea at the café.', 'الموقع قريب من البازار واكو موقف. جرّبوا الچاي الآذري بالكوفي شوب.'), 'Google'],
            [self::L('Anna', 'Anna', 'آنا'), self::L('مونیخ، آلمان', 'Munich, Germany', 'ميونخ، ألمانيا'), 5, self::L('مهمان‌نوازی گرم و قیمت منصفانه. اتاق دقیقاً مثل عکس‌های سایت بود.', 'Warm hospitality and fair prices. The room looked exactly like the photos on the website.', 'ضيافة دافية وأسعار معقولة. الغرفة نفس الصور اللي بالموقع بالضبط.'), 'TripAdvisor'],
            [self::L('رضا و نگار', 'Reza & Negar', 'رضا ونگار'), self::L('اصفهان', 'Isfahan, Iran', 'أصفهان'), 5, self::L('با دو بچه آمدیم؛ اتاق کامفورت برای ما کاملاً کافی بود.', 'We came with two kids; the Comfort room was just right for us.', 'جينا ويّا طفلين، وغرفة كومفورت كانت كافية إلنا.'), 'Google'],
        ];
        foreach ($reviews as $i => [$author, $origin, $rating, $body, $src]) {
            DB::insert('reviews', ['author' => $author, 'origin' => $origin, 'rating' => $rating, 'body' => $body, 'source' => $src, 'stay_date' => '', 'active' => 1, 'sort' => $i]);
        }

        // ---------- FAQ ----------
        $faqs = [
            [self::L('آیا صبحانه رایگان است؟', 'Is breakfast free?', 'الفطور مجاني؟'), self::L('<p>بله، صبحانه بوفه برای همه مهمانان رایگان است و هر روز از ۷ تا ۱۰:۳۰ در سالن صبحانه سرو می‌شود.</p>', '<p>Yes. The breakfast buffet is free for every guest and is served daily from 7:00 to 10:30 in the breakfast hall.</p>', '<p>إي نعم، فطور البوفيه مجاني لكل الضيوف، كل يوم من ٧ لـ ١٠:٣٠ بصالة الفطور.</p>')],
            [self::L('ساعت ورود و خروج چند است؟', 'What are check-in and check-out times?', 'شوكت الدخول والخروج؟'), self::L('<p>ورود از ساعت ۱۴ و خروج تا ساعت ۱۲. در صورت خالی بودن اتاق، ورود زودتر هم ممکن است.</p>', '<p>Check-in is from 14:00 and check-out is by 12:00. Early check-in is possible when the room is free.</p>', '<p>الدخول من الساعة ٢ الظهر والخروج لحد ١٢ الظهر. إذا الغرفة فارغة، تگدرون تدخلون أبچر.</p>')],
            [self::L('چطور اتاق رزرو کنم؟', 'How do I book a room?', 'شلون أحجز غرفة؟'), self::L('<p>رزرو آنلاین نداریم. با پذیرش تماس بگیرید یا در واتس‌اپ پیام بدهید؛ نوع اتاق و تعداد نفرات را بگویید تا قیمت و موجودی را اعلام کنیم.</p>', '<p>We do not take online bookings. Call the front desk or send a WhatsApp message with the room type and number of guests, and we will confirm price and availability.</p>', '<p>ماكو حجز أونلاين. اتصلوا بالاستقبال أو دزّوا رسالة واتساب بنوع الغرفة وعدد الأشخاص، ونأكد لكم السعر والتوفر.</p>')],
            [self::L('قیمت اتاق برای کودکان چطور است؟', 'How are children charged?', 'شلون سعر الأطفال؟'), self::L('<p>کودکان زیر ۵ سال بدون تخت اضافه رایگان هستند. برای بزرگ‌ترها، قیمت بر اساس تعداد نفرات حساب می‌شود.</p>', '<p>Children under 5 stay free without an extra bed. Older children count as guests in the price.</p>', '<p>الأطفال تحت ٥ سنوات مجاناً بدون سرير إضافي. الأكبر يحسبون ويّا عدد الأشخاص.</p>')],
            [self::L('پارکینگ دارید؟', 'Is there parking?', 'اكو موقف سيارات؟'), self::L('<p>بله، پارکینگ سرپوشیده رایگان برای مهمانان داریم.</p>', '<p>Yes, there is free covered parking for guests.</p>', '<p>إي، اكو موقف مسقوف مجاني للضيوف.</p>')],
            [self::L('فاصله هتل تا فرودگاه چقدر است؟', 'How far is the airport?', 'شكد يبعد المطار؟'), self::L('<p>فرودگاه شهید مدنی تبریز حدود ۲۰ دقیقه با ماشین فاصله دارد. با هماهنگی قبلی ترانسفر فرودگاه داریم.</p>', '<p>Tabriz Shahid Madani Airport is about 20 minutes by car. Airport transfer is available on request.</p>', '<p>مطار تبريز (الشهيد مدني) حوالي ٢٠ دقيقة بالسيارة. اكو توصيل من المطار بالتنسيق المسبق.</p>')],
            [self::L('چگونه پرداخت کنم؟', 'How can I pay?', 'شلون أدفع؟'), self::L('<p>پرداخت با کارت بانکی ایرانی یا نقدی در پذیرش انجام می‌شود. مهمانان خارجی می‌توانند نقدی پرداخت کنند.</p>', '<p>Pay at the front desk by Iranian bank card or in cash. Foreign guests can pay in cash.</p>', '<p>الدفع بالاستقبال بكارت بنكي إيراني أو نقداً. الضيوف الأجانب يگدرون يدفعون نقداً.</p>')],
        ];
        foreach ($faqs as $i => [$q, $ans]) {
            DB::insert('faqs', ['question' => $q, 'answer' => $ans, 'active' => 1, 'sort' => $i]);
        }

        // ---------- custom page ----------
        DB::insert('pages', [
            'slug' => 'privacy', 'title' => self::L('حریم خصوصی', 'Privacy', 'الخصوصية'),
            'excerpt' => self::L('اطلاعات شما چطور نگهداری می‌شود.', 'How we look after your information.', 'شلون نحافظ على معلوماتكم.'),
            'body' => self::L('<p>اطلاعاتی که در فرم تماس وارد می‌کنید فقط برای پاسخ به شما استفاده می‌شود و در اختیار دیگران قرار نمی‌گیرد.</p><p>این سایت برای شمارش بازدیدها از کوکی یا ابزار ردیابی شخص ثالث استفاده نمی‌کند.</p>', '<p>Information you send through the contact form is only used to reply to you and is never shared.</p><p>This website does not use third-party tracking to count visits.</p>', '<p>المعلومات اللي تكتبونها بنموذج التواصل نستخدمها بس حتى نرد عليكم، وما ننطيها لأحد.</p><p>هذا الموقع ما يستخدم أدوات تتبع خارجية لعدّ الزيارات.</p>'),
            'cover_id' => null, 'show_in_menu' => 0, 'show_in_footer' => 1, 'active' => 1, 'sort' => 0, 'updated_at' => DB::now(),
        ]);

        // ---------- page texts ----------
        Settings::setMany([
            'page.breakfast' => [
                'title' => self::A('صبحانه رایگان', 'Free breakfast', 'فطور مجاني'),
                'body' => self::A(
                    '<p>هر روز صبح، سالن صبحانه ونوس با بوفه‌ای از طعم‌های آذربایجان منتظر شماست: پنیر لیقوان، عسل سبلان با سرشیر، نان تازه از نانوایی محل، املت و عدسی گرم.</p><p>صبحانه برای همه مهمانان <strong>رایگان</strong> است؛ چه یک شب بمانید چه یک هفته.</p>',
                    '<p>Every morning the Venus breakfast hall welcomes you with a buffet of Azerbaijani flavours: Lighvan cheese, Sabalan honey with clotted cream, fresh bread from the local bakery, omelettes and warm lentil soup.</p><p>Breakfast is <strong>free</strong> for every guest – whether you stay one night or a week.</p>',
                    '<p>كل صباح، صالة فطور ونوس تنتظركم ببوفيه بنكهات آذربيجان: جبن ليقوان، عسل سبلان ويّا قيمر، خبز طازج من فرن المنطقة، أومليت وشوربة عدس حارة.</p><p>الفطور <strong>مجاني</strong> لكل الضيوف، سواء بقيتوا ليلة لو أسبوع.</p>'
                ),
                'cover_id' => $img['breakfast1'], 'gallery' => $ids('breakfast1', 'breakfast2'),
            ],
            'page.cafe' => [
                'title' => self::A('کافه تروث', 'Truth Café', 'كوفي شوب تروث'),
                'body' => self::A(
                    '<p>کافه تروث فضایی جدا از سالن صبحانه است؛ گوشه‌ای گرم با نور ملایم، برای قهوه عصرگاهی، چای آذری با قند و شیرینی‌های تبریزی مثل قرابیه.</p><p>کافه برای مهمانان هتل و مراجعه‌کنندگان بیرونی باز است.</p>',
                    '<p>Truth Café is separate from the breakfast hall – a warm, softly lit corner for afternoon coffee, Azerbaijani tea with sugar cubes and Tabriz sweets such as qurabiyeh.</p><p>The café is open to hotel guests and visitors.</p>',
                    '<p>كوفي شوب تروث مكان منفصل عن صالة الفطور؛ زاوية دافية بإضاءة هادئة، لقهوة العصر والچاي الآذري ويّا القند والحلويات التبريزية مثل القرابية.</p><p>الكوفي شوب مفتوح لضيوف الفندق والزوار.</p>'
                ),
                'cover_id' => $img['cafe1'], 'gallery' => $ids('cafe1', 'cafe2', 'cafe3'),
            ],
            'page.about' => [
                'body' => self::A(
                    '<p>هتل ونوس در سال ۱۳۹۵ در مرکز تاریخی تبریز افتتاح شد. نام ما از ونوس، ستاره صبحگاهی، آمده است؛ ستاره‌ای که مسافران قدیم با آن راه را پیدا می‌کردند.</p><p>ما هتلی سه ستاره با ۴۲ اتاق هستیم که برای خانواده‌ها و گروه‌ها طراحی شده؛ کوچک‌ترین اتاق‌ها سه تخت دارند. پذیرش شبانه‌روزی، پارکینگ، صبحانه رایگان و کافه‌ای مستقل از خدمات ماست.</p><h3>چرا ونوس؟</h3><ul><li>چند دقیقه پیاده تا بازار بزرگ تبریز</li><li>اتاق‌های سه، چهار و پنج تخته</li><li>کارکنان فارسی، ترکی، عربی و انگلیسی‌زبان</li></ul>',
                    '<p>Venus Hotel opened in 2016 in the historic centre of Tabriz. Our name comes from Venus, the morning star that old travellers used to find their way.</p><p>We are a three-star hotel with 42 rooms designed for families and groups – even the smallest rooms have three beds. A 24-hour front desk, parking, free breakfast and an independent café are part of every stay.</p><h3>Why Venus?</h3><ul><li>A few minutes’ walk from the Tabriz Grand Bazaar</li><li>Rooms for three, four and five guests</li><li>Staff who speak Persian, Azerbaijani Turkish, Arabic and English</li></ul>',
                    '<p>فندق ونوس انفتح سنة ٢٠١٦ بالمركز التاريخي لتبريز. اسمنا من ونوس، نجمة الصبح اللي جانوا المسافرين يهتدون بيها.</p><p>إحنا فندق ثلاث نجوم بـ ٤٢ غرفة مصممة للعوائل والمجموعات، وأصغر غرفة بيها ثلاث أسرّة. استقبال ٢٤ ساعة، موقف، فطور مجاني، وكوفي شوب مستقل.</p><h3>ليش ونوس؟</h3><ul><li>دقائق مشي عن بازار تبريز الكبير</li><li>غرف لثلاثة وأربعة وخمسة أشخاص</li><li>موظفين يحچون فارسي وتركي وعربي وإنگليزي</li></ul>'
                ),
                'cover_id' => $img['lobby'],
            ],
            'policies' => self::A(
                '<ul><li>ورود از ساعت ۱۴، خروج تا ساعت ۱۲</li><li>همراه داشتن کارت ملی یا گذرنامه برای همه مهمانان بزرگسال لازم است</li><li>کودکان زیر ۵ سال بدون تخت اضافه رایگان</li><li>سیگار کشیدن در اتاق‌ها ممنوع است</li><li>ورود حیوانات خانگی امکان‌پذیر نیست</li></ul>',
                '<ul><li>Check-in from 14:00, check-out by 12:00</li><li>All adult guests need a national ID card or passport</li><li>Children under 5 stay free without an extra bed</li><li>No smoking in the rooms</li><li>Pets are not allowed</li></ul>',
                '<ul><li>الدخول من الساعة ٢ الظهر، والخروج لحد ١٢ الظهر</li><li>لازم هوية أو جواز سفر لكل الضيوف الكبار</li><li>الأطفال تحت ٥ سنوات مجاناً بدون سرير إضافي</li><li>ممنوع التدخين بالغرف</li><li>ما نستقبل الحيوانات الأليفة</li></ul>'
            ),
            'seo.og_image' => $img['exterior'],
        ]);
    }
}
