<?php
/** @var array $res @var array $rows @var string $filterValue */
$key = $res['key'];
$sortable = !empty($res['sortable']) && $filterValue === '';
$filterOpts = [];
if (($res['filter'] ?? '') === 'section') {
    $filterOpts = ['cafe' => __('admin.section.cafe'), 'breakfast' => __('admin.section.breakfast')];
} elseif ($key === 'menu_items') {
    $filterOpts = ['cafe' => __('admin.section.cafe'), 'breakfast' => __('admin.section.breakfast')];
    foreach (DB::all('SELECT id, section, name FROM menu_categories ORDER BY section, sort') as $c) {
        $filterOpts[$c['id']] = '— ' . tr($c['name']);
    }
}
?>
<div class="page-head">
  <div><h1><?= icon($res['icon']) ?><?= e(__($res['title'])) ?></h1><p class="muted"><?= e(__('admin.count_items', ['n' => count($rows)])) ?><?= $sortable && count($rows) > 1 ? ' · ' . e(__('admin.drag_to_sort')) : '' ?></p></div>
  <div class="page-actions">
    <?php if ($key === 'rooms'): ?><a class="btn" href="<?= e(admin_url('prices')) ?>"><?= icon('wallet') ?><?= e(__('admin.prices')) ?></a><?php endif; ?>
    <?php if ($key === 'menu_items'): ?><a class="btn" href="<?= e(admin_url('r/menu_categories')) ?>"><?= icon('list') ?><?= e(__('admin.menu_categories')) ?></a><?php endif; ?>
    <a class="btn btn-accent" href="<?= e(admin_url('r/' . $key . '/new')) ?>"><?= icon('plus') ?><?= e(__('admin.add_new')) ?></a>
  </div>
</div>
<div class="card card-flush">
  <div class="list-tools">
    <input class="inp" type="search" placeholder="<?= e(__('admin.search')) ?>" data-list-search>
    <?php if ($filterOpts): ?>
      <select class="inp" data-list-filter="<?= e(admin_url('r/' . $key)) ?>" data-param="<?= e($res['filter']) ?>">
        <option value=""><?= e(__('admin.all')) ?></option>
        <?php foreach ($filterOpts as $k => $l): ?><option value="<?= e($k) ?>"<?= (string)$k === (string)$filterValue ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
      </select>
    <?php endif; ?>
  </div>
  <?php if (!$rows): ?>
    <div class="empty-state"><?= icon($res['icon']) ?><p><?= e(__('admin.empty_list')) ?></p><a class="btn btn-accent" href="<?= e(admin_url('r/' . $key . '/new')) ?>"><?= icon('plus') ?><?= e(__('admin.add_new')) ?></a></div>
  <?php else: ?>
  <div class="table-wrap">
    <table class="tbl">
      <thead><tr><?php if ($sortable): ?><th class="w-handle"></th><?php endif; ?><?php foreach ($res['list'] as $c): ?><th><?= isset($c[2]) ? e(__($c[2])) : '' ?></th><?php endforeach; ?><th class="w-actions"></th></tr></thead>
      <tbody<?= $sortable ? ' data-sort-url="' . e(admin_url('r/' . $key . '/sort')) . '"' : '' ?>>
        <?php foreach ($rows as $row): ?>
          <tr data-id="<?= (int)$row['id'] ?>" data-search="<?= e(mb_strtolower(implode(' ', array_map(fn($c) => is_string($row[$c[0]] ?? null) ? strip_tags(implode(' ', (array)json_dec($row[$c[0]], [$row[$c[0]]]))) : '', $res['list'])))) ?>"<?= isset($row['color']) && $row['color'] ? ' style="--row-color:' . e($row['color']) . '"' : '' ?>>
            <?php if ($sortable): ?><td class="w-handle"><span class="handle" title="<?= e(__('admin.drag_to_sort')) ?>"><?= icon('grip-vertical') ?></span></td><?php endif; ?>
            <?php foreach ($res['list'] as $c): ?><td data-label="<?= isset($c[2]) ? e(__($c[2])) : '' ?>" class="c-<?= e($c[1]) ?>"><?= View::partial('admin/partials/cell', ['col' => $c, 'row' => $row, 'res' => $res]) ?></td><?php endforeach; ?>
            <td class="w-actions"><a class="btn btn-sm" href="<?= e(admin_url('r/' . $key . '/' . $row['id'])) ?>"><?= icon('pencil') ?><span><?= e(__('admin.edit')) ?></span></a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
