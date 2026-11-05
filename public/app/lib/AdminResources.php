<?php
/**
 * What the admin panel can edit: each resource maps to a table, a list view and a form.
 * Field types are handled by AdminForm.
 */
final class AdminResources
{
    public static function all(): array
    {
        return [
            'rooms' => [
                'table' => 'rooms', 'icon' => 'bed-double', 'title' => 'admin.rooms', 'sortable' => true, 'group' => 'stay',
                'list' => [
                    ['cover_id', 'image'], ['name', 'i18n', 'admin.f.name'], ['badge', 'i18n', 'admin.f.badge'], ['beds', 'number', 'admin.f.beds'],
                    ['max_guests', 'guests', 'admin.f.guests'], ['prices', 'from_price', 'admin.f.from_price'], ['room_count', 'number', 'admin.f.room_count'], ['active', 'bool', 'admin.f.active'],
                ],
                'tabs' => ['basic' => 'admin.tab.basic', 'beds' => 'admin.tab.beds', 'prices' => 'admin.tab.prices', 'photos' => 'admin.tab.photos', 'amenities' => 'admin.tab.amenities'],
                'fields' => [
                    ['name' => 'name', 'type' => 'i18n', 'label' => 'admin.f.name', 'required' => true, 'tab' => 'basic'],
                    ['name' => 'slug', 'type' => 'slug', 'label' => 'admin.f.slug', 'from' => 'name', 'tab' => 'basic', 'help' => 'admin.h.slug'],
                    ['name' => 'badge', 'type' => 'i18n', 'label' => 'admin.f.badge', 'tab' => 'basic', 'help' => 'admin.h.badge', 'half' => true],
                    ['name' => 'color', 'type' => 'color', 'label' => 'admin.f.color', 'tab' => 'basic', 'help' => 'admin.h.color', 'half' => true, 'default' => '#0b8a7a'],
                    ['name' => 'highlight', 'type' => 'i18n', 'label' => 'admin.f.highlight', 'tab' => 'basic', 'help' => 'admin.h.highlight'],
                    ['name' => 'summary', 'type' => 'i18n_textarea', 'label' => 'admin.f.summary', 'tab' => 'basic'],
                    ['name' => 'description', 'type' => 'i18n_rich', 'label' => 'admin.f.description', 'tab' => 'basic'],
                    ['name' => 'beds', 'type' => 'number', 'label' => 'admin.f.beds', 'tab' => 'beds', 'min' => 1, 'max' => 20, 'default' => 3, 'half' => true],
                    ['name' => 'room_count', 'type' => 'number', 'label' => 'admin.f.room_count', 'tab' => 'beds', 'min' => 0, 'max' => 999, 'half' => true, 'help' => 'admin.h.room_count'],
                    ['name' => 'min_guests', 'type' => 'number', 'label' => 'admin.f.min_guests', 'tab' => 'beds', 'min' => 1, 'max' => 20, 'default' => 1, 'half' => true],
                    ['name' => 'max_guests', 'type' => 'number', 'label' => 'admin.f.max_guests', 'tab' => 'beds', 'min' => 1, 'max' => 20, 'default' => 3, 'half' => true, 'help' => 'admin.h.max_guests'],
                    ['name' => 'max_extra', 'type' => 'number', 'label' => 'admin.f.max_extra', 'tab' => 'beds', 'min' => 0, 'max' => 10, 'default' => 0, 'half' => true, 'help' => 'admin.h.max_extra'],
                    ['name' => 'bed_layout', 'type' => 'bed_layout', 'label' => 'admin.f.bed_layout', 'tab' => 'beds', 'help' => 'admin.h.bed_layout'],
                    ['name' => 'size_m2', 'type' => 'number', 'label' => 'admin.f.size', 'tab' => 'beds', 'min' => 0, 'max' => 500, 'half' => true],
                    ['name' => 'floor', 'type' => 'i18n', 'label' => 'admin.f.floor', 'tab' => 'beds', 'half' => true],
                    ['name' => 'view', 'type' => 'i18n', 'label' => 'admin.f.view', 'tab' => 'beds'],
                    ['name' => 'show_prices', 'type' => 'bool', 'label' => 'admin.f.show_prices', 'tab' => 'prices', 'default' => 1, 'help' => 'admin.h.show_prices'],
                    ['name' => 'prices', 'type' => 'prices', 'label' => 'admin.f.prices', 'tab' => 'prices', 'help' => 'admin.h.prices'],
                    ['name' => 'extra_bed_price', 'type' => 'price', 'label' => 'admin.f.extra_person', 'tab' => 'prices', 'help' => 'admin.h.extra_person'],
                    ['name' => 'price_note', 'type' => 'i18n', 'label' => 'admin.f.price_note', 'tab' => 'prices'],
                    ['name' => 'cover_id', 'type' => 'image', 'label' => 'admin.f.cover', 'tab' => 'photos'],
                    ['name' => 'gallery', 'type' => 'images', 'label' => 'admin.f.gallery', 'tab' => 'photos', 'help' => 'admin.h.gallery'],
                    ['name' => 'scene_id', 'type' => 'select', 'label' => 'admin.f.scene', 'tab' => 'photos', 'source' => 'scenes', 'empty' => true, 'help' => 'admin.h.scene'],
                    ['name' => 'amenities', 'type' => 'multi', 'label' => 'admin.f.amenities', 'tab' => 'amenities', 'source' => 'amenities:room'],
                    ['name' => 'features', 'type' => 'features', 'label' => 'admin.f.features', 'tab' => 'amenities', 'help' => 'admin.h.features'],
                    ['name' => 'featured', 'type' => 'bool', 'label' => 'admin.f.featured', 'tab' => 'basic', 'help' => 'admin.h.featured', 'half' => true],
                    ['name' => 'active', 'type' => 'bool', 'label' => 'admin.f.active', 'tab' => 'basic', 'default' => 1, 'half' => true],
                ],
                'timestamps' => true,
            ],
            'amenities' => [
                'table' => 'amenities', 'icon' => 'sparkles', 'title' => 'admin.amenities', 'sortable' => true, 'group' => 'stay',
                'list' => [['icon', 'icon'], ['name', 'i18n', 'admin.f.name'], ['scope', 'scope', 'admin.f.scope'], ['highlight', 'bool', 'admin.f.highlight_home'], ['active', 'bool', 'admin.f.active']],
                'fields' => [
                    ['name' => 'icon', 'type' => 'icon', 'label' => 'admin.f.icon'],
                    ['name' => 'name', 'type' => 'i18n', 'label' => 'admin.f.name', 'required' => true],
                    ['name' => 'description', 'type' => 'i18n', 'label' => 'admin.f.short_text'],
                    ['name' => 'scope', 'type' => 'select', 'label' => 'admin.f.scope', 'options' => ['hotel' => 'admin.scope.hotel', 'room' => 'admin.scope.room', 'both' => 'admin.scope.both'], 'default' => 'hotel'],
                    ['name' => 'highlight', 'type' => 'bool', 'label' => 'admin.f.highlight_home'],
                    ['name' => 'active', 'type' => 'bool', 'label' => 'admin.f.active', 'default' => 1],
                ],
            ],
            'slides' => [
                'table' => 'slides', 'icon' => 'images', 'title' => 'admin.slides', 'sortable' => true, 'group' => 'site',
                'list' => [['media_id', 'image'], ['title', 'i18n', 'admin.f.caption'], ['active', 'bool', 'admin.f.active']],
                'fields' => [
                    ['name' => 'media_id', 'type' => 'image', 'label' => 'admin.f.photo', 'required' => true],
                    ['name' => 'title', 'type' => 'i18n', 'label' => 'admin.f.caption'],
                    ['name' => 'active', 'type' => 'bool', 'label' => 'admin.f.active', 'default' => 1],
                ],
            ],
            'gallery_categories' => [
                'table' => 'gallery_categories', 'icon' => 'layout-grid', 'title' => 'admin.gallery_categories', 'sortable' => true, 'group' => 'media', 'parent' => 'gallery_items',
                'list' => [['name', 'i18n', 'admin.f.name'], ['slug', 'text', 'admin.f.slug'], ['active', 'bool', 'admin.f.active']],
                'fields' => [
                    ['name' => 'name', 'type' => 'i18n', 'label' => 'admin.f.name', 'required' => true],
                    ['name' => 'slug', 'type' => 'slug', 'label' => 'admin.f.slug', 'from' => 'name'],
                    ['name' => 'active', 'type' => 'bool', 'label' => 'admin.f.active', 'default' => 1],
                ],
            ],
            'gallery_items' => [
                'table' => 'gallery_items', 'icon' => 'image', 'title' => 'admin.gallery', 'sortable' => true, 'group' => 'media', 'view' => 'gallery', 'filter' => 'category_id',
                'list' => [['media_id', 'image'], ['caption', 'i18n', 'admin.f.caption'], ['category_id', 'category', 'admin.f.category'], ['active', 'bool', 'admin.f.active']],
                'fields' => [
                    ['name' => 'media_id', 'type' => 'image', 'label' => 'admin.f.photo', 'required' => true],
                    ['name' => 'category_id', 'type' => 'select', 'label' => 'admin.f.category', 'source' => 'gallery_categories'],
                    ['name' => 'caption', 'type' => 'i18n', 'label' => 'admin.f.caption'],
                    ['name' => 'active', 'type' => 'bool', 'label' => 'admin.f.active', 'default' => 1],
                ],
                'timestamps_created' => true,
            ],
            'scenes' => [
                'table' => 'scenes', 'icon' => 'rotate-3d', 'title' => 'admin.tour', 'sortable' => true, 'group' => 'media',
                'list' => [['media_id', 'image'], ['title', 'i18n', 'admin.f.title'], ['type', 'scene_type', 'admin.f.scene_type'], ['is_default', 'bool', 'admin.f.first_scene'], ['active', 'bool', 'admin.f.active']],
                'tabs' => ['basic' => 'admin.tab.basic', 'view' => 'admin.tab.tour_editor'],
                'fields' => [
                    ['name' => 'title', 'type' => 'i18n', 'label' => 'admin.f.title', 'required' => true, 'tab' => 'basic'],
                    ['name' => 'slug', 'type' => 'slug', 'label' => 'admin.f.slug', 'from' => 'title', 'tab' => 'basic'],
                    ['name' => 'type', 'type' => 'select', 'label' => 'admin.f.scene_type', 'tab' => 'basic', 'options' => ['equirect' => 'admin.scene.equirect', 'partial' => 'admin.scene.partial', 'embed' => 'admin.scene.embed'], 'default' => 'equirect', 'help' => 'admin.h.scene_type'],
                    ['name' => 'media_id', 'type' => 'pano', 'label' => 'admin.f.pano', 'tab' => 'basic', 'help' => 'admin.h.pano', 'show_if' => 'type!=embed'],
                    ['name' => 'embed_url', 'type' => 'url', 'label' => 'admin.f.embed_url', 'tab' => 'basic', 'help' => 'admin.h.embed', 'show_if' => 'type=embed'],
                    ['name' => 'haov', 'type' => 'number', 'label' => 'admin.f.haov', 'tab' => 'basic', 'step' => '0.1', 'min' => 10, 'max' => 360, 'default' => 360, 'half' => true, 'show_if' => 'type=partial', 'help' => 'admin.h.haov'],
                    ['name' => 'vaov', 'type' => 'number', 'label' => 'admin.f.vaov', 'tab' => 'basic', 'step' => '0.1', 'min' => 10, 'max' => 180, 'default' => 180, 'half' => true, 'show_if' => 'type=partial'],
                    ['name' => 'voffset', 'type' => 'number', 'label' => 'admin.f.voffset', 'tab' => 'basic', 'step' => '0.1', 'min' => -90, 'max' => 90, 'default' => 0, 'show_if' => 'type=partial'],
                    ['name' => 'description', 'type' => 'i18n_textarea', 'label' => 'admin.f.description', 'tab' => 'basic', 'show_if' => 'type=embed'],
                    ['name' => 'is_default', 'type' => 'bool', 'label' => 'admin.f.first_scene', 'tab' => 'basic', 'half' => true],
                    ['name' => 'autorotate', 'type' => 'bool', 'label' => 'admin.f.autorotate', 'tab' => 'basic', 'half' => true],
                    ['name' => 'active', 'type' => 'bool', 'label' => 'admin.f.active', 'tab' => 'basic', 'default' => 1],
                    ['name' => 'hotspots', 'type' => 'hotspots', 'label' => 'admin.f.hotspots', 'tab' => 'view'],
                ],
            ],
            'menu_categories' => [
                'table' => 'menu_categories', 'icon' => 'list', 'title' => 'admin.menu_categories', 'sortable' => true, 'group' => 'food', 'filter' => 'section',
                'list' => [['icon', 'icon'], ['name', 'i18n', 'admin.f.name'], ['section', 'section', 'admin.f.section'], ['active', 'bool', 'admin.f.active']],
                'fields' => [
                    ['name' => 'section', 'type' => 'select', 'label' => 'admin.f.section', 'options' => ['cafe' => 'admin.section.cafe', 'breakfast' => 'admin.section.breakfast'], 'default' => 'cafe'],
                    ['name' => 'name', 'type' => 'i18n', 'label' => 'admin.f.name', 'required' => true],
                    ['name' => 'description', 'type' => 'i18n', 'label' => 'admin.f.short_text'],
                    ['name' => 'icon', 'type' => 'icon', 'label' => 'admin.f.icon'],
                    ['name' => 'active', 'type' => 'bool', 'label' => 'admin.f.active', 'default' => 1],
                ],
            ],
            'menu_items' => [
                'table' => 'menu_items', 'icon' => 'coffee', 'title' => 'admin.menu_items', 'sortable' => true, 'group' => 'food', 'filter' => 'category_id',
                'list' => [['media_id', 'image'], ['name', 'i18n', 'admin.f.name'], ['category_id', 'menu_category', 'admin.f.category'], ['price', 'price', 'admin.f.price'], ['active', 'bool', 'admin.f.active']],
                'fields' => [
                    ['name' => 'category_id', 'type' => 'select', 'label' => 'admin.f.category', 'source' => 'menu_categories', 'required' => true],
                    ['name' => 'name', 'type' => 'i18n', 'label' => 'admin.f.name', 'required' => true],
                    ['name' => 'description', 'type' => 'i18n', 'label' => 'admin.f.short_text'],
                    ['name' => 'price', 'type' => 'price', 'label' => 'admin.f.price', 'help' => 'admin.h.menu_price', 'half' => true],
                    ['name' => 'tag', 'type' => 'i18n', 'label' => 'admin.f.tag', 'help' => 'admin.h.tag', 'half' => true],
                    ['name' => 'media_id', 'type' => 'image', 'label' => 'admin.f.photo'],
                    ['name' => 'active', 'type' => 'bool', 'label' => 'admin.f.active', 'default' => 1],
                ],
            ],
            'attractions' => [
                'table' => 'attractions', 'icon' => 'landmark', 'title' => 'admin.attractions', 'sortable' => true, 'group' => 'site',
                'list' => [['media_id', 'image'], ['name', 'i18n', 'admin.f.name'], ['distance_km', 'number', 'admin.f.distance'], ['active', 'bool', 'admin.f.active']],
                'fields' => [
                    ['name' => 'name', 'type' => 'i18n', 'label' => 'admin.f.name', 'required' => true],
                    ['name' => 'description', 'type' => 'i18n_textarea', 'label' => 'admin.f.description'],
                    ['name' => 'distance_km', 'type' => 'number', 'label' => 'admin.f.distance', 'step' => '0.1', 'min' => 0, 'max' => 10000, 'half' => true],
                    ['name' => 'travel', 'type' => 'i18n', 'label' => 'admin.f.travel', 'half' => true, 'help' => 'admin.h.travel'],
                    ['name' => 'media_id', 'type' => 'image', 'label' => 'admin.f.photo'],
                    ['name' => 'latlng', 'type' => 'latlng', 'label' => 'admin.f.location', 'cols' => ['lat', 'lng'], 'help' => 'admin.h.latlng'],
                    ['name' => 'active', 'type' => 'bool', 'label' => 'admin.f.active', 'default' => 1],
                ],
            ],
            'reviews' => [
                'table' => 'reviews', 'icon' => 'quote', 'title' => 'admin.reviews', 'sortable' => true, 'group' => 'site',
                'list' => [['author', 'i18n', 'admin.f.author'], ['rating', 'stars', 'admin.f.rating'], ['source', 'text', 'admin.f.source'], ['active', 'bool', 'admin.f.active']],
                'fields' => [
                    ['name' => 'author', 'type' => 'i18n', 'label' => 'admin.f.author', 'required' => true, 'half' => true],
                    ['name' => 'origin', 'type' => 'i18n', 'label' => 'admin.f.origin', 'half' => true],
                    ['name' => 'rating', 'type' => 'select', 'label' => 'admin.f.rating', 'options' => ['5' => '★★★★★', '4' => '★★★★', '3' => '★★★', '2' => '★★', '1' => '★'], 'default' => '5', 'half' => true, 'raw_options' => true],
                    ['name' => 'source', 'type' => 'text', 'label' => 'admin.f.source', 'half' => true, 'help' => 'admin.h.source'],
                    ['name' => 'body', 'type' => 'i18n_textarea', 'label' => 'admin.f.review_text', 'required' => true],
                    ['name' => 'active', 'type' => 'bool', 'label' => 'admin.f.active', 'default' => 1],
                ],
            ],
            'faqs' => [
                'table' => 'faqs', 'icon' => 'circle-help', 'title' => 'admin.faqs', 'sortable' => true, 'group' => 'site',
                'list' => [['question', 'i18n', 'admin.f.question'], ['active', 'bool', 'admin.f.active']],
                'fields' => [
                    ['name' => 'question', 'type' => 'i18n', 'label' => 'admin.f.question', 'required' => true],
                    ['name' => 'answer', 'type' => 'i18n_rich', 'label' => 'admin.f.answer'],
                    ['name' => 'active', 'type' => 'bool', 'label' => 'admin.f.active', 'default' => 1],
                ],
            ],
            'pages' => [
                'table' => 'pages', 'icon' => 'file-text', 'title' => 'admin.pages', 'sortable' => true, 'group' => 'site',
                'list' => [['title', 'i18n', 'admin.f.title'], ['slug', 'text', 'admin.f.slug'], ['show_in_menu', 'bool', 'admin.f.in_menu'], ['show_in_footer', 'bool', 'admin.f.in_footer'], ['active', 'bool', 'admin.f.active']],
                'fields' => [
                    ['name' => 'title', 'type' => 'i18n', 'label' => 'admin.f.title', 'required' => true],
                    ['name' => 'slug', 'type' => 'slug', 'label' => 'admin.f.slug', 'from' => 'title'],
                    ['name' => 'excerpt', 'type' => 'i18n', 'label' => 'admin.f.excerpt'],
                    ['name' => 'body', 'type' => 'i18n_rich', 'label' => 'admin.f.body'],
                    ['name' => 'cover_id', 'type' => 'image', 'label' => 'admin.f.cover'],
                    ['name' => 'show_in_menu', 'type' => 'bool', 'label' => 'admin.f.in_menu', 'half' => true],
                    ['name' => 'show_in_footer', 'type' => 'bool', 'label' => 'admin.f.in_footer', 'half' => true],
                    ['name' => 'active', 'type' => 'bool', 'label' => 'admin.f.active', 'default' => 1],
                ],
                'timestamps_updated' => true,
            ],
        ];
    }

    public static function get(string $key): ?array
    {
        $all = self::all();
        if (!isset($all[$key])) {
            return null;
        }
        return $all[$key] + ['key' => $key];
    }

    /** Fields for each home page section. */
    public static function sectionFields(string $key): array
    {
        $common = [
            ['name' => 'kicker', 'type' => 'i18n', 'label' => 'admin.f.kicker'],
            ['name' => 'title', 'type' => 'i18n', 'label' => 'admin.f.title'],
            ['name' => 'subtitle', 'type' => 'i18n_textarea', 'label' => 'admin.f.subtitle'],
        ];
        $limit = ['name' => 'limit', 'type' => 'number', 'label' => 'admin.f.limit', 'min' => 1, 'max' => 24];
        return match ($key) {
            'hero' => [
                ['name' => 'kicker', 'type' => 'i18n', 'label' => 'admin.f.kicker'],
                ['name' => 'title', 'type' => 'i18n', 'label' => 'admin.f.title', 'help' => 'admin.h.hero_title'],
                ['name' => 'subtitle', 'type' => 'i18n_textarea', 'label' => 'admin.f.subtitle'],
                ['name' => 'cta_primary', 'type' => 'i18n', 'label' => 'admin.f.cta_primary', 'help' => 'admin.h.optional', 'half' => true],
                ['name' => 'cta_secondary', 'type' => 'i18n', 'label' => 'admin.f.cta_secondary', 'help' => 'admin.h.optional', 'half' => true],
            ],
            'intro' => [
                ['name' => 'kicker', 'type' => 'i18n', 'label' => 'admin.f.kicker'],
                ['name' => 'title', 'type' => 'i18n', 'label' => 'admin.f.title'],
                ['name' => 'body', 'type' => 'i18n_rich', 'label' => 'admin.f.body'],
                ['name' => 'image_id', 'type' => 'image', 'label' => 'admin.f.photo', 'half' => true],
                ['name' => 'image2_id', 'type' => 'image', 'label' => 'admin.f.photo2', 'half' => true],
                ['name' => 'stats', 'type' => 'stats', 'label' => 'admin.f.stats'],
            ],
            'rooms', 'gallery', 'attractions', 'faq' => array_merge($common, [$limit]),
            'dining' => array_merge($common, [
                ['name' => 'breakfast_title', 'type' => 'i18n', 'label' => 'admin.f.breakfast_title'],
                ['name' => 'breakfast_text', 'type' => 'i18n_textarea', 'label' => 'admin.f.breakfast_text'],
                ['name' => 'breakfast_image', 'type' => 'image', 'label' => 'admin.f.breakfast_image'],
                ['name' => 'cafe_title', 'type' => 'i18n', 'label' => 'admin.f.cafe_title'],
                ['name' => 'cafe_text', 'type' => 'i18n_textarea', 'label' => 'admin.f.cafe_text'],
                ['name' => 'cafe_image', 'type' => 'image', 'label' => 'admin.f.cafe_image'],
            ]),
            'tour' => [
                ['name' => 'kicker', 'type' => 'i18n', 'label' => 'admin.f.kicker'],
                ['name' => 'title', 'type' => 'i18n', 'label' => 'admin.f.title'],
                ['name' => 'body', 'type' => 'i18n_textarea', 'label' => 'admin.f.body'],
                ['name' => 'image_id', 'type' => 'pano', 'label' => 'admin.f.tour_preview', 'help' => 'admin.h.optional'],
            ],
            'cta' => [
                ['name' => 'title', 'type' => 'i18n', 'label' => 'admin.f.title'],
                ['name' => 'body', 'type' => 'i18n_textarea', 'label' => 'admin.f.body'],
                ['name' => 'image_id', 'type' => 'image', 'label' => 'admin.f.photo'],
            ],
            default => $common,
        };
    }

    /** Settings pages and their fields. Field names use "key>sub" for nested settings. */
    public static function settings(): array
    {
        return [
            'general' => ['icon' => 'house', 'title' => 'admin.set.general', 'fields' => [
                ['name' => 'site.name', 'type' => 'i18n', 'label' => 'admin.f.hotel_name', 'required' => true],
                ['name' => 'site.tagline', 'type' => 'i18n', 'label' => 'admin.f.tagline'],
                ['name' => 'site.city', 'type' => 'i18n', 'label' => 'admin.f.city', 'half' => true],
                ['name' => 'cafe.name', 'type' => 'i18n', 'label' => 'admin.f.cafe_name', 'half' => true],
                ['name' => 'site.stars', 'type' => 'select', 'label' => 'admin.f.stars', 'options' => ['1' => '★', '2' => '★★', '3' => '★★★', '4' => '★★★★', '5' => '★★★★★'], 'raw_options' => true, 'half' => true, 'int' => true],
                ['name' => 'site.established', 'type' => 'number', 'label' => 'admin.f.established', 'half' => true, 'min' => 1300, 'max' => 3000],
                ['name' => 'hotel.rooms_total', 'type' => 'number', 'label' => 'admin.f.rooms_total', 'half' => true, 'min' => 0, 'max' => 9999],
                ['name' => 'hotel.floors', 'type' => 'number', 'label' => 'admin.f.floors', 'half' => true, 'min' => 0, 'max' => 200],
                ['name' => 'hotel.checkin', 'type' => 'text', 'label' => 'admin.f.checkin', 'half' => true, 'ltr' => true],
                ['name' => 'hotel.checkout', 'type' => 'text', 'label' => 'admin.f.checkout', 'half' => true, 'ltr' => true],
                ['name' => 'hotel.breakfast_hours', 'type' => 'i18n', 'label' => 'admin.f.breakfast_hours', 'half' => true],
                ['name' => 'hotel.cafe_hours', 'type' => 'i18n', 'label' => 'admin.f.cafe_hours', 'half' => true],
                ['name' => 'site.logo', 'type' => 'image', 'label' => 'admin.f.logo', 'half' => true, 'help' => 'admin.h.logo'],
                ['name' => 'site.logo_light', 'type' => 'image', 'label' => 'admin.f.logo_light', 'half' => true, 'help' => 'admin.h.logo_light'],
                ['name' => 'site.favicon', 'type' => 'image', 'label' => 'admin.f.favicon', 'help' => 'admin.h.favicon'],
            ]],
            'contact' => ['icon' => 'phone', 'title' => 'admin.set.contact', 'fields' => [
                ['name' => 'contact.phones', 'type' => 'lines', 'label' => 'admin.f.phones', 'help' => 'admin.h.phones', 'ltr' => true],
                ['name' => 'contact.whatsapp', 'type' => 'text', 'label' => 'admin.f.whatsapp', 'help' => 'admin.h.whatsapp', 'half' => true, 'ltr' => true],
                ['name' => 'contact.telegram', 'type' => 'text', 'label' => 'admin.f.telegram', 'help' => 'admin.h.telegram', 'half' => true, 'ltr' => true],
                ['name' => 'contact.email', 'type' => 'text', 'label' => 'admin.f.email', 'half' => true, 'ltr' => true],
                ['name' => 'contact.postal_code', 'type' => 'text', 'label' => 'admin.f.postal_code', 'half' => true, 'ltr' => true],
                ['name' => 'contact.address', 'type' => 'i18n', 'label' => 'admin.f.address'],
                ['name' => 'social.instagram', 'type' => 'text', 'label' => 'admin.f.instagram', 'half' => true, 'ltr' => true],
                ['name' => 'social.telegram', 'type' => 'text', 'label' => 'admin.f.telegram_channel', 'half' => true, 'ltr' => true],
                ['name' => 'social.whatsapp', 'type' => 'text', 'label' => 'admin.f.whatsapp_social', 'half' => true, 'ltr' => true],
                ['name' => 'social.bale', 'type' => 'text', 'label' => 'admin.f.bale', 'half' => true, 'ltr' => true],
                ['name' => 'social.eitaa', 'type' => 'text', 'label' => 'admin.f.eitaa', 'half' => true, 'ltr' => true],
                ['name' => 'social.aparat', 'type' => 'text', 'label' => 'admin.f.aparat', 'half' => true, 'ltr' => true],
                ['name' => 'cta.whatsapp_text', 'type' => 'i18n', 'label' => 'admin.f.whatsapp_text', 'help' => 'admin.h.whatsapp_text'],
                ['name' => 'cta.show_call', 'type' => 'bool', 'label' => 'admin.f.show_call', 'half' => true],
                ['name' => 'cta.show_whatsapp', 'type' => 'bool', 'label' => 'admin.f.show_whatsapp', 'half' => true],
                ['name' => 'cta.show_telegram', 'type' => 'bool', 'label' => 'admin.f.show_telegram', 'half' => true],
                ['name' => 'form.enabled', 'type' => 'bool', 'label' => 'admin.f.form_enabled', 'half' => true],
                ['name' => 'form.notify_email', 'type' => 'text', 'label' => 'admin.f.notify_email', 'help' => 'admin.h.notify_email', 'ltr' => true],
            ]],
            'location' => ['icon' => 'map-pin', 'title' => 'admin.set.location', 'fields' => [
                ['name' => 'map.latlng', 'type' => 'latlng', 'label' => 'admin.f.hotel_location', 'settings' => ['map.lat', 'map.lng'], 'help' => 'admin.h.latlng'],
                ['name' => 'map.zoom', 'type' => 'number', 'label' => 'admin.f.zoom', 'min' => 3, 'max' => 19, 'half' => true],
                ['name' => 'map.show', 'type' => 'bool', 'label' => 'admin.f.show_map', 'half' => true],
                ['name' => 'map.link_neshan', 'type' => 'url', 'label' => 'admin.f.link_neshan', 'help' => 'admin.h.map_link'],
                ['name' => 'map.link_balad', 'type' => 'url', 'label' => 'admin.f.link_balad'],
                ['name' => 'map.link_google', 'type' => 'url', 'label' => 'admin.f.link_google'],
                ['name' => 'map.tiles', 'type' => 'text', 'label' => 'admin.f.tiles', 'help' => 'admin.h.tiles', 'ltr' => true],
            ]],
            'prices' => ['icon' => 'wallet', 'title' => 'admin.set.prices', 'fields' => [
                ['name' => 'price.currency', 'type' => 'i18n', 'label' => 'admin.f.currency', 'half' => true],
                ['name' => 'price.unit', 'type' => 'i18n', 'label' => 'admin.f.price_unit', 'half' => true],
                ['name' => 'price.empty_mode', 'type' => 'select', 'label' => 'admin.f.empty_mode', 'options' => ['call' => 'admin.empty.call', 'hide' => 'admin.empty.hide'], 'help' => 'admin.h.empty_mode'],
                ['name' => 'price.empty_label', 'type' => 'i18n', 'label' => 'admin.f.empty_label'],
                ['name' => 'price.note', 'type' => 'i18n_textarea', 'label' => 'admin.f.price_note_all'],
                ['name' => 'price.secondary', 'type' => 'secondary', 'label' => 'admin.f.secondary', 'help' => 'admin.h.secondary'],
                ['name' => 'rooms.group_by_beds', 'type' => 'bool', 'label' => 'admin.f.group_by_beds', 'help' => 'admin.h.group_by_beds'],
                ['name' => 'price.show_kids', 'type' => 'bool', 'label' => 'admin.f.show_kids', 'half' => true],
                ['name' => 'price.child_age', 'type' => 'number', 'label' => 'admin.f.child_age', 'min' => 1, 'max' => 18, 'half' => true, 'help' => 'admin.h.child_age'],
            ]],
            'appearance' => ['icon' => 'palette', 'title' => 'admin.set.appearance', 'fields' => [
                ['name' => 'theme.preset', 'type' => 'theme_presets', 'label' => 'admin.f.theme_presets'],
                ['name' => 'theme.primary', 'type' => 'color', 'label' => 'admin.f.c_primary', 'half' => true],
                ['name' => 'theme.accent', 'type' => 'color', 'label' => 'admin.f.c_accent', 'half' => true],
                ['name' => 'theme.accent2', 'type' => 'color', 'label' => 'admin.f.c_accent2', 'half' => true],
                ['name' => 'theme.bg', 'type' => 'color', 'label' => 'admin.f.c_bg', 'half' => true],
                ['name' => 'theme.ink', 'type' => 'color', 'label' => 'admin.f.c_ink', 'half' => true],
                ['name' => 'theme.radius', 'type' => 'range', 'label' => 'admin.f.radius', 'min' => 0, 'max' => 36, 'half' => true],
                ['name' => 'theme.hero_overlay', 'type' => 'range', 'label' => 'admin.f.hero_overlay', 'min' => 0, 'max' => 90, 'half' => true],
                ['name' => 'theme.animations', 'type' => 'bool', 'label' => 'admin.f.animations', 'half' => true],
                ['name' => 'theme.preloader', 'type' => 'bool', 'label' => 'admin.f.preloader', 'half' => true],
                ['name' => 'theme.marquee', 'type' => 'bool', 'label' => 'admin.f.marquee', 'half' => true],
            ]],
            'languages' => ['icon' => 'languages', 'title' => 'admin.set.languages', 'fields' => [
                ['name' => 'lang.enabled', 'type' => 'langs', 'label' => 'admin.f.langs_enabled'],
                ['name' => 'lang.default', 'type' => 'select', 'label' => 'admin.f.lang_default', 'options' => ['fa' => 'فارسی', 'en' => 'English', 'ar' => 'العربية'], 'raw_options' => true],
            ]],
            'navigation' => ['icon' => 'menu', 'title' => 'admin.set.navigation', 'fields' => [
                ['name' => 'nav.items', 'type' => 'nav', 'label' => 'admin.f.nav_items', 'help' => 'admin.h.nav'],
            ]],
            'pages' => ['icon' => 'file-text', 'title' => 'admin.set.pages', 'fields' => [
                ['name' => 'page.about>body', 'type' => 'i18n_rich', 'label' => 'admin.f.about_body'],
                ['name' => 'page.about>cover_id', 'type' => 'image', 'label' => 'admin.f.about_cover'],
                ['name' => 'policies', 'type' => 'i18n_rich', 'label' => 'admin.f.policies'],
                ['name' => 'page.breakfast>title', 'type' => 'i18n', 'label' => 'admin.f.breakfast_title'],
                ['name' => 'page.breakfast>body', 'type' => 'i18n_rich', 'label' => 'admin.f.breakfast_body'],
                ['name' => 'page.breakfast>cover_id', 'type' => 'image', 'label' => 'admin.f.breakfast_image', 'half' => true],
                ['name' => 'page.breakfast>gallery', 'type' => 'images', 'label' => 'admin.f.breakfast_gallery'],
                ['name' => 'page.cafe>title', 'type' => 'i18n', 'label' => 'admin.f.cafe_title'],
                ['name' => 'page.cafe>body', 'type' => 'i18n_rich', 'label' => 'admin.f.cafe_body'],
                ['name' => 'page.cafe>cover_id', 'type' => 'image', 'label' => 'admin.f.cafe_image', 'half' => true],
                ['name' => 'page.cafe>gallery', 'type' => 'images', 'label' => 'admin.f.cafe_gallery'],
            ]],
            'seo' => ['icon' => 'search', 'title' => 'admin.set.seo', 'fields' => [
                ['name' => 'seo.description', 'type' => 'i18n_textarea', 'label' => 'admin.f.seo_description', 'help' => 'admin.h.seo_description'],
                ['name' => 'seo.og_image', 'type' => 'image', 'label' => 'admin.f.og_image', 'help' => 'admin.h.og_image'],
                ['name' => 'seo.head_code', 'type' => 'code', 'label' => 'admin.f.head_code', 'help' => 'admin.h.code', 'admin_only' => true],
                ['name' => 'seo.footer_code', 'type' => 'code', 'label' => 'admin.f.footer_code', 'admin_only' => true],
            ]],
            'system' => ['icon' => 'settings', 'title' => 'admin.set.system', 'fields' => [
                ['name' => 'system.maintenance', 'type' => 'bool', 'label' => 'admin.f.maintenance', 'help' => 'admin.h.maintenance'],
                ['name' => 'system.maintenance_text', 'type' => 'i18n', 'label' => 'admin.f.maintenance_text'],
                ['name' => 'system.cache', 'type' => 'bool', 'label' => 'admin.f.cache', 'help' => 'admin.h.cache', 'half' => true],
                ['name' => 'system.analytics', 'type' => 'bool', 'label' => 'admin.f.analytics', 'help' => 'admin.h.analytics', 'half' => true],
            ]],
        ];
    }

    public static function themePresets(): array
    {
        return [
            'teal' => ['admin.theme.teal', ['theme.primary' => '#111316', 'theme.accent' => '#0b8a7a', 'theme.accent2' => '#e8913a', 'theme.bg' => '#f5f4f0', 'theme.ink' => '#111316']],
            'cobalt' => ['admin.theme.cobalt', ['theme.primary' => '#0f1424', 'theme.accent' => '#3355ff', 'theme.accent2' => '#ffb020', 'theme.bg' => '#f4f5f8', 'theme.ink' => '#0f1424']],
            'coral' => ['admin.theme.coral', ['theme.primary' => '#1a1a1a', 'theme.accent' => '#e2552f', 'theme.accent2' => '#f2b33d', 'theme.bg' => '#f7f5f2', 'theme.ink' => '#1a1a1a']],
            'sage' => ['admin.theme.sage', ['theme.primary' => '#1f2620', 'theme.accent' => '#4d7c5b', 'theme.accent2' => '#d79b4a', 'theme.bg' => '#f3f2ec', 'theme.ink' => '#1f2620']],
            'lapis' => ['admin.theme.lapis', ['theme.primary' => '#0f2742', 'theme.accent' => '#b88a3e', 'theme.accent2' => '#c79a4b', 'theme.bg' => '#f7f3ec', 'theme.ink' => '#14202e']],
        ];
    }
}
