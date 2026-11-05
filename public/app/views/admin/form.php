<?php
/** @var array $res @var ?array $row @var bool $isNew @var array $errors */
$key = $res['key'];
$tabs = $res['tabs'] ?? ['basic' => 'admin.tab.basic'];
$fieldsByTab = [];
foreach ($res['fields'] as $f) {
    $fieldsByTab[$f['tab'] ?? array_key_first($tabs)][] = $f;
}
$titleField = $res['fields'][0]['type'] === 'i18n' ? $res['fields'][0]['name'] : (in_array('name', array_column($res['fields'], 'name'), true) ? 'name' : 'title');
$heading = $isNew ? __('admin.new_item') : (tr($row[$titleField] ?? '') ?: __('admin.edit'));
?>
<form method="post" class="edit-form" data-edit-form id="edit-form" novalidate>
  <?= csrf_field() ?>
  <div class="page-head page-head--sticky">
    <div>
      <a class="back" href="<?= e(admin_url('r/' . $key)) ?>"><?= icon(I18n::isRtl() ? 'arrow-right' : 'arrow-left') ?><?= e(__($res['title'])) ?></a>
      <h1><?= e($heading) ?></h1>
    </div>
    <div class="page-actions">
      <?= View::partial('admin/partials/lang-switch') ?>
      <?php if ($key === 'rooms' && !$isNew): ?><a class="btn btn-ghost" href="<?= e(url('rooms/' . $row['slug'], [], I18n::$default)) ?>" target="_blank" rel="noopener"><?= icon('external-link') ?><span class="hide-sm"><?= e(__('admin.view')) ?></span></a><?php endif; ?>
      <button class="btn btn-accent" type="submit"><?= icon('save') ?><?= e(__('admin.save')) ?></button>
    </div>
  </div>
  <?php if (count($tabs) > 1): ?>
  <div class="tabs" role="tablist">
    <?php $i = 0; foreach ($tabs as $tk => $tl): $hasErr = count(array_intersect(array_column($fieldsByTab[$tk] ?? [], 'name'), array_keys($errors))) > 0; ?>
      <button type="button" role="tab" data-tab-btn="<?= e($tk) ?>" aria-selected="<?= $i++ === 0 ? 'true' : 'false' ?>"<?= $hasErr ? ' class="has-error"' : '' ?>><?= e(__($tl)) ?></button>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <?php $i = 0; foreach ($tabs as $tk => $tl): ?>
    <section class="card form-card" data-tab="<?= e($tk) ?>"<?= $i++ ? ' hidden' : '' ?>>
      <div class="fields">
        <?php foreach ($fieldsByTab[$tk] ?? [] as $f): ?>
          <?php $raw = $row[$f['name']] ?? null; echo AdminForm::field($f, $row === null && !array_key_exists($f['name'], $row ?? []) ? null : AdminForm::fromColumn($f, $raw), $row ?? []); ?>
          <?php if (isset($errors[$f['name']])): ?><p class="fld-error"><?= e(__($f['label'])) ?>: <?= e($errors[$f['name']]) ?></p><?php endif; ?>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>
  <div class="savebar">
    <button class="btn btn-accent" type="submit"><?= icon('save') ?><?= e(__('admin.save')) ?></button>
    <button class="btn" type="submit" name="back" value="1"><?= e(__('admin.save_close')) ?></button>
    <?php if (!$isNew): ?>
      <span class="spacer"></span>
      <button class="btn btn-ghost" type="submit" form="dup-form"><?= icon('copy') ?><span class="hide-sm"><?= e(__('admin.duplicate')) ?></span></button>
      <button class="btn btn-danger" type="submit" form="del-form" data-confirm="<?= e(__('admin.confirm_delete')) ?>"><?= icon('trash-2') ?><span class="hide-sm"><?= e(__('admin.delete')) ?></span></button>
    <?php endif; ?>
  </div>
</form>
<?php if (!$isNew): ?>
<form method="post" id="del-form" action="<?= e(admin_url('r/' . $key . '/' . $row['id'] . '/delete')) ?>"><?= csrf_field() ?></form>
<form method="post" id="dup-form" action="<?= e(admin_url('r/' . $key . '/' . $row['id'] . '/duplicate')) ?>"><?= csrf_field() ?></form>
<?php endif; ?>
