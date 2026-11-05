<?php /** @var array $rows @var string $kind @var int $bytes */ ?>
<div class="page-head">
  <div><h1><?= icon('archive') ?><?= e(__('admin.media')) ?></h1><p class="muted"><?= e(__('admin.count_items', ['n' => count($rows)])) ?> · <?= e(num(round($bytes / 1048576, 1), 1)) ?> MB</p></div>
  <div class="page-actions">
    <?php if ($kind === 'pano'): ?>
      <label class="btn btn-accent upload-btn"><?= icon('rotate-3d') ?><span><?= e(__('admin.upload_pano')) ?></span><input type="file" accept="image/jpeg,image/png,image/webp" data-lib-pano hidden></label>
    <?php else: ?>
      <label class="btn btn-accent upload-btn"><?= icon('upload') ?><span><?= e(__('admin.upload_new')) ?></span><input type="file" accept="image/jpeg,image/png,image/webp" multiple data-lib-upload hidden></label>
    <?php endif; ?>
  </div>
</div>
<div class="chips">
  <a class="chip<?= $kind === 'image' ? ' is-active' : '' ?>" href="<?= e(admin_url('media')) ?>"><?= icon('image') ?><?= e(__('admin.photos')) ?></a>
  <a class="chip<?= $kind === 'pano' ? ' is-active' : '' ?>" href="<?= e(admin_url('media', ['kind' => 'pano'])) ?>"><?= icon('rotate-3d') ?><?= e(__('admin.panoramas')) ?></a>
</div>
<div class="upload-progress" data-lib-progress hidden><div><i></i></div><span></span></div>
<?php if ($kind === 'pano'): ?><div class="note"><?= icon('info') ?><div><p><?= e(__('admin.pano_tips')) ?></p></div></div><?php endif; ?>
<div class="lib-grid">
  <?php foreach ($rows as $m): ?>
    <details class="lib-item">
      <summary><img src="<?= e(Media::src($m, 480)) ?>" alt="" loading="lazy"><span class="lib-cap"><?= e($m['title'] ?: '#' . $m['id']) ?><small dir="ltr"><?= (int)$m['width'] ?>×<?= (int)$m['height'] ?></small></span></summary>
      <form method="post" action="<?= e(admin_url('media/' . $m['id'])) ?>" class="lib-edit">
        <?= csrf_field() ?>
        <label class="fld"><span class="fld-label"><?= e(__('admin.f.title')) ?></span><input class="inp" name="title" value="<?= e($m['title']) ?>"></label>
        <?= AdminForm::field(['name' => 'alt', 'type' => 'i18n', 'label' => 'admin.f.alt', 'help' => 'admin.h.alt'], json_dec($m['alt'], [])) ?>
        <div class="row-inline"><button class="btn btn-sm btn-accent"><?= icon('save') ?><?= e(__('admin.save')) ?></button>
          <a class="btn btn-sm btn-ghost" href="<?= e($m['kind'] === 'pano' ? upload_url(Media::variants($m)['main'] ?? '') : Media::src($m, 2400)) ?>" target="_blank" rel="noopener"><?= icon('external-link') ?></a>
          <button class="btn btn-sm btn-danger" type="submit" formaction="<?= e(admin_url('media/' . $m['id'] . '/delete')) ?>" data-confirm="<?= e(__('admin.confirm_delete_media')) ?>"><?= icon('trash-2') ?></button></div>
      </form>
    </details>
  <?php endforeach; ?>
</div>
<?php if (!$rows): ?><div class="empty-state"><?= icon('images') ?><p><?= e(__('admin.no_media')) ?></p></div><?php endif; ?>
