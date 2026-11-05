<?php /** @var array $groups @var string $group @var array $def */ ?>
<form method="post" class="edit-form" data-edit-form novalidate>
  <?= csrf_field() ?>
  <div class="page-head page-head--sticky">
    <div><h1><?= icon('settings') ?><?= e(__('admin.settings')) ?></h1></div>
    <div class="page-actions"><?= View::partial('admin/partials/lang-switch') ?><button class="btn btn-accent" type="submit"><?= icon('save') ?><?= e(__('admin.save')) ?></button></div>
  </div>
  <div class="tabs tabs--links">
    <?php foreach ($groups as $k => $g): ?><a href="<?= e(admin_url('settings/' . $k)) ?>"<?= $k === $group ? ' aria-selected="true"' : '' ?>><?= icon($g['icon']) ?><?= e(__($g['title'])) ?></a><?php endforeach; ?>
  </div>
  <section class="card form-card">
    <h2 class="card-title"><?= e(__($def['title'])) ?></h2>
    <div class="fields">
      <?php foreach ($def['fields'] as $f):
        if (!empty($f['admin_only']) && !Auth::isAdmin()) continue;
        if (str_contains($f['name'], '>')) { [$k, $sub] = explode('>', $f['name'], 2); $val = ((array)setting($k, []))[$sub] ?? null; }
        else { $val = setting($f['name']); }
        echo AdminForm::field($f, $val, []);
      endforeach; ?>
    </div>
    <?php if ($group === 'system'): ?>
      <div class="note"><?= icon('info') ?><div><b><?= e(__('admin.cache_status')) ?></b><p><?php $cs = Cache::size(); ?><?= e(__('admin.cache_files', ['n' => $cs['files']])) ?> · PHP <?= e(PHP_VERSION) ?> · <?= e(DB::$driver) ?> · v<?= e(VENUS_VERSION) ?></p></div></div>
    <?php endif; ?>
  </section>
  <div class="savebar"><button class="btn btn-accent" type="submit"><?= icon('save') ?><?= e(__('admin.save')) ?></button></div>
</form>
