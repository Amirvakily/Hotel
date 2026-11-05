<?php /** @var array $row @var array $fields @var array $data */ ?>
<form method="post" class="edit-form" data-edit-form novalidate>
  <?= csrf_field() ?>
  <div class="page-head page-head--sticky">
    <div><a class="back" href="<?= e(admin_url('sections')) ?>"><?= icon(I18n::isRtl() ? 'arrow-right' : 'arrow-left') ?><?= e(__('admin.sections')) ?></a><h1><?= e(__('admin.sec.' . $row['skey'])) ?></h1></div>
    <div class="page-actions"><?= View::partial('admin/partials/lang-switch') ?><button class="btn btn-accent" type="submit"><?= icon('save') ?><?= e(__('admin.save')) ?></button></div>
  </div>
  <section class="card form-card">
    <div class="fields">
      <div class="fld"><label class="switch"><input type="checkbox" name="active" value="1"<?= $row['active'] ? ' checked' : '' ?>><span class="switch-ui"></span><span><?= e(__('admin.section_visible')) ?></span></label></div>
      <?php foreach ($fields as $f): echo AdminForm::field($f, $data[$f['name']] ?? null, []); endforeach; ?>
      <?php if ($row['skey'] === 'hero'): ?><div class="note"><?= icon('images') ?><div><p><?= e(__('admin.hero_slides_note')) ?></p><a class="btn btn-sm" href="<?= e(admin_url('r/slides')) ?>"><?= e(__('admin.slides')) ?></a></div></div><?php endif; ?>
      <?php if ($row['skey'] === 'highlights'): ?><div class="note"><?= icon('sparkles') ?><div><p><?= e(__('admin.highlights_note')) ?></p><a class="btn btn-sm" href="<?= e(admin_url('r/amenities')) ?>"><?= e(__('admin.amenities')) ?></a></div></div><?php endif; ?>
      <?php if (in_array($row['skey'], ['reviews', 'faq', 'attractions', 'gallery', 'rooms'], true)): ?><div class="note"><?= icon('info') ?><div><p><?= e(__('admin.section_items_note')) ?></p></div></div><?php endif; ?>
    </div>
  </section>
  <div class="savebar"><button class="btn btn-accent" type="submit"><?= icon('save') ?><?= e(__('admin.save')) ?></button></div>
</form>
