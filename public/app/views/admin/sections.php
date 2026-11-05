<?php /** @var array $sections */ ?>
<div class="page-head">
  <div><h1><?= icon('layout-grid') ?><?= e(__('admin.sections')) ?></h1><p class="muted"><?= e(__('admin.sections_help')) ?></p></div>
  <div class="page-actions"><a class="btn" href="<?= e(admin_url('r/slides')) ?>"><?= icon('images') ?><?= e(__('admin.slides')) ?></a><a class="btn btn-ghost" href="<?= e(url('', [], I18n::$default)) ?>" target="_blank" rel="noopener"><?= icon('external-link') ?><?= e(__('admin.view_site')) ?></a></div>
</div>
<ul class="section-list" data-sections data-url="<?= e(admin_url('sections')) ?>">
  <?php foreach ($sections as $s): $d = json_dec($s['data'], []); ?>
    <li data-id="<?= (int)$s['id'] ?>" class="<?= $s['active'] ? '' : 'is-off' ?>">
      <span class="handle"><?= icon('grip-vertical') ?></span>
      <div class="sl-text"><b><?= e(__('admin.sec.' . $s['skey'])) ?></b><small class="muted"><?= e(tr($d['title'] ?? '') ?: '—') ?></small></div>
      <label class="switch switch-sm" title="<?= e(__('admin.f.active')) ?>"><input type="checkbox" data-section-toggle="<?= (int)$s['id'] ?>"<?= $s['active'] ? ' checked' : '' ?>><span class="switch-ui"></span></label>
      <a class="btn btn-sm" href="<?= e(admin_url('sections/' . $s['skey'])) ?>"><?= icon('pencil') ?><span><?= e(__('admin.edit')) ?></span></a>
    </li>
  <?php endforeach; ?>
</ul>
