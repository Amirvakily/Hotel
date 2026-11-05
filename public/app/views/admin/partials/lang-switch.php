<?php $langs = AdminForm::langs(); if (count($langs) > 1): ?>
<div class="lang-tabs" role="tablist" aria-label="<?= e(__('admin.editing_lang')) ?>" data-lang-switch>
  <?php foreach ($langs as $i => $l): ?><button type="button" data-lang="<?= e($l) ?>" aria-pressed="<?= $i ? 'false' : 'true' ?>"><?= e(I18n::LANGS[$l]['name']) ?><i class="dot" hidden></i></button><?php endforeach; ?>
</div>
<?php endif; ?>
