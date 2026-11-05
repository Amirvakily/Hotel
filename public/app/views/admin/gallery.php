<?php
/** @var array $res @var array $rows @var string $filterValue */
$cats = DB::all('SELECT * FROM gallery_categories ORDER BY sort, id');
$sortable = $filterValue === '';
?>
<div class="page-head">
  <div><h1><?= icon('image') ?><?= e(__('admin.gallery')) ?></h1><p class="muted"><?= e(__('admin.count_items', ['n' => count($rows)])) ?> · <?= e(__('admin.gallery_help')) ?></p></div>
  <div class="page-actions"><a class="btn" href="<?= e(admin_url('r/gallery_categories')) ?>"><?= icon('layout-grid') ?><?= e(__('admin.gallery_categories')) ?></a></div>
</div>
<form class="card bulk-upload" method="post" action="<?= e(admin_url('r/gallery_items/bulk')) ?>" data-bulk-form>
  <?= csrf_field() ?>
  <h2 class="card-title"><?= icon('upload') ?><?= e(__('admin.bulk_upload')) ?></h2>
  <div class="row-inline">
    <label class="fld"><span class="fld-label"><?= e(__('admin.f.category')) ?></span>
      <select class="inp" name="category_id"><?php foreach ($cats as $c): ?><option value="<?= (int)$c['id'] ?>"<?= (string)$c['id'] === (string)$filterValue ? ' selected' : '' ?>><?= e(tr($c['name'])) ?></option><?php endforeach; ?></select>
    </label>
    <label class="btn btn-accent upload-btn"><?= icon('upload') ?><span><?= e(__('admin.choose_files')) ?></span><input type="file" accept="image/jpeg,image/png,image/webp" multiple data-bulk-file hidden></label>
  </div>
  <div class="dropzone" data-bulk-drop><?= icon('images') ?><span><?= e(__('admin.drop_here')) ?></span></div>
  <div class="upload-progress" data-bulk-progress hidden><div><i></i></div><span></span></div>
  <input type="hidden" name="media_ids" data-bulk-ids>
</form>
<div class="chips">
  <a class="chip<?= $filterValue === '' ? ' is-active' : '' ?>" href="<?= e(admin_url('r/gallery_items')) ?>"><?= e(__('admin.all')) ?></a>
  <?php foreach ($cats as $c): ?><a class="chip<?= (string)$c['id'] === (string)$filterValue ? ' is-active' : '' ?>" href="<?= e(admin_url('r/gallery_items', ['category_id' => $c['id']])) ?>"><?= e(tr($c['name'])) ?></a><?php endforeach; ?>
</div>
<div class="gal-grid"<?= $sortable ? ' data-sort-url="' . e(admin_url('r/gallery_items/sort')) . '"' : '' ?>>
  <?php foreach ($rows as $row): $m = media($row['media_id']); ?>
    <div class="gal-item<?= $row['active'] ? '' : ' is-off' ?>" data-id="<?= (int)$row['id'] ?>">
      <?php if ($sortable): ?><span class="handle"><?= icon('grip-vertical') ?></span><?php endif; ?>
      <a href="<?= e(admin_url('r/gallery_items/' . $row['id'])) ?>"><?= $m ? '<img src="' . e(Media::src($m, 480)) . '" alt="" loading="lazy">' : '<span class="cell-img--empty">' . icon('image') . '</span>' ?></a>
      <div class="gal-meta"><span><?= e(tr($row['caption']) ?: '—') ?></span>
        <label class="switch switch-sm"><input type="checkbox" data-toggle="<?= e(admin_url('r/gallery_items/' . $row['id'] . '/toggle')) ?>" data-col="active"<?= $row['active'] ? ' checked' : '' ?>><span class="switch-ui"></span></label></div>
    </div>
  <?php endforeach; ?>
</div>
<?php if (!$rows): ?><div class="empty-state"><?= icon('images') ?><p><?= e(__('admin.empty_gallery')) ?></p></div><?php endif; ?>
