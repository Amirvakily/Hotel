<?php
/** @var array $col @var array $row @var array $res */
[$key, $type] = $col;
$v = $row[$key] ?? null;
switch ($type) {
    case 'image':
        $m = media($v);
        echo $m ? '<img class="cell-img" src="' . e(Media::src($m, 480)) . '" alt="" loading="lazy">' : '<span class="cell-img cell-img--empty">' . icon('image') . '</span>';
        break;
    case 'i18n':
        echo '<b>' . e(tr($v) ?: '—') . '</b>';
        if ($key === 'name' && $res['key'] === 'rooms' && tr($row['highlight'] ?? '') !== '') {
            echo '<small class="muted d-block">' . e(str_limit(tr($row['highlight']), 70)) . '</small>';
        }
        break;
    case 'number':
        echo e($v === null || $v === '' ? '—' : num((float)$v, fmod((float)$v, 1) ? 1 : 0));
        break;
    case 'bool':
        echo '<label class="switch switch-sm"><input type="checkbox" data-toggle="' . e(admin_url('r/' . $res['key'] . '/' . $row['id'] . '/toggle')) . '" data-col="' . e($key) . '"' . ($v ? ' checked' : '') . '><span class="switch-ui"></span></label>';
        break;
    case 'guests':
        echo e(num((int)$row['min_guests']) . '–' . num((int)$row['max_guests']));
        break;
    case 'from_price':
        $p = array_filter(json_dec($v, []));
        echo $p ? e(money(min($p))) : '<span class="muted">' . e(__('admin.no_price')) . '</span>';
        break;
    case 'price':
        echo e($v ? money((int)$v) : '—');
        break;
    case 'scope':
        echo '<span class="pill">' . e(__('admin.scope.' . $v)) . '</span>';
        break;
    case 'section':
        echo '<span class="pill">' . e(__('admin.section.' . $v)) . '</span>';
        break;
    case 'scene_type':
        echo '<span class="pill">' . e(__('admin.scene.' . $v)) . '</span>';
        break;
    case 'category':
        static $gc = null;
        $gc = $gc ?? array_column(DB::all('SELECT id, name FROM gallery_categories'), 'name', 'id');
        echo e(isset($gc[$v]) ? tr($gc[$v]) : '—');
        break;
    case 'menu_category':
        static $mc = null;
        $mc = $mc ?? array_column(DB::all('SELECT id, name FROM menu_categories'), 'name', 'id');
        echo e(isset($mc[$v]) ? tr($mc[$v]) : '—');
        break;
    case 'icon':
        echo '<span class="cell-icon">' . icon($v ?: 'sparkles') . '</span>';
        break;
    case 'stars':
        echo '<span class="stars-txt">' . str_repeat('★', (int)$v) . '</span>';
        break;
    default:
        echo e($v === null || $v === '' ? '—' : $v);
}
