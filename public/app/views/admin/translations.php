<?php /** @var array $keys @var array $base @var array $over @var array $langs */ ?>
<form method="post" class="edit-form" data-edit-form>
  <?= csrf_field() ?>
  <div class="page-head page-head--sticky">
    <div><h1><?= icon('languages') ?><?= e(__('admin.translations')) ?></h1><p class="muted"><?= e(__('admin.translations_help')) ?></p></div>
    <div class="page-actions"><input class="inp" type="search" placeholder="<?= e(__('admin.search')) ?>" data-tr-search><button class="btn btn-accent" type="submit"><?= icon('save') ?><?= e(__('admin.save')) ?></button></div>
  </div>
  <div class="card card-flush">
    <div class="table-wrap">
      <table class="tbl tr-table">
        <thead><tr><th><?= e(__('admin.key')) ?></th><?php foreach ($langs as $l): ?><th><?= e(I18n::LANGS[$l]['name']) ?></th><?php endforeach; ?></tr></thead>
        <tbody>
          <?php foreach ($keys as $k): ?>
            <tr data-search="<?= e(mb_strtolower($k . ' ' . ($base['fa'][$k] ?? '') . ' ' . ($base['en'][$k] ?? ''))) ?>">
              <td><code dir="ltr"><?= e($k) ?></code></td>
              <?php foreach ($langs as $l): $cur = $over[$l][$k] ?? ''; ?>
                <td><input class="inp<?= $cur !== '' ? ' is-changed' : '' ?>" name="t[<?= e($l) ?>][<?= e($k) ?>]" value="<?= e($cur) ?>" placeholder="<?= e($base[$l][$k] ?? '') ?>" dir="<?= e(I18n::LANGS[$l]['dir']) ?>" lang="<?= e($l) ?>"></td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</form>
