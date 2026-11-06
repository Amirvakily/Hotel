<?php /** @var array $sections */ ?>
<div class="page-head">
  <div><h1><?= icon('layout-grid') ?><?= e(__('admin.sections')) ?></h1><p class="muted"><?= e(__('admin.sections_help')) ?></p></div>
  <div class="page-actions"><a class="btn" href="<?= e(admin_url('r/slides')) ?>"><?= icon('images') ?><?= e(__('admin.slides')) ?></a><a class="btn btn-ghost" href="<?= e(url('', [], I18n::$default)) ?>" target="_blank" rel="noopener"><?= icon('external-link') ?><?= e(__('admin.view_site')) ?></a></div>
</div>
<ul class="section-list" data-sections data-url="<?= e(admin_url('sections')) ?>">
  <?php foreach ($sections as $s): $d = json_dec($s['data'], []); ?>
    <li data-id="<?= (int)$s['id'] ?>" class="<?= $s['active'] ? '' : 'is-off' ?>">
      <span class="handle"><?= icon('grip-vertical') ?></span>
      <?php
        $page = ['rooms' => 'rooms', 'gallery' => 'gallery', 'attractions' => 'attractions'][$s['skey']] ?? null;
        $why = '';
        if ($s['skey'] === 'tour' && !Content::tourOn()) {
            $why = __('admin.hidden_tour_off');
        } elseif ($s['skey'] === 'dining' && !Content::navEnabled('breakfast') && !Content::navEnabled('cafe')) {
            $why = __('admin.hidden_page_off', ['page' => __('nav.breakfast') . ' / ' . __('nav.cafe')]);
        } elseif ($page && !Content::navEnabled($page)) {
            $why = __('admin.hidden_page_off', ['page' => __('nav.' . $page)]);
        }
      ?>
      <div class="sl-text"><b><?= e(__('admin.sec.' . $s['skey'])) ?></b><small class="muted"><?= e(tr($d['title'] ?? '') ?: '—') ?></small><?php if ($why !== '' && $s['active']): ?><small class="sl-why"><?= icon('eye-off') ?><?= e($why) ?></small><?php endif; ?></div>
      <label class="switch switch-sm" title="<?= e(__('admin.f.active')) ?>"><input type="checkbox" data-section-toggle="<?= (int)$s['id'] ?>"<?= $s['active'] ? ' checked' : '' ?>><span class="switch-ui"></span></label>
      <a class="btn btn-sm" href="<?= e(admin_url('sections/' . $s['skey'])) ?>"><?= icon('pencil') ?><span><?= e(__('admin.edit')) ?></span></a>
    </li>
  <?php endforeach; ?>
</ul>
