<?php
/** @var array $rooms @var array $presets @var array $current @var int $active */
$maxG = 1;
foreach ($rooms as $r) { $maxG = max($maxG, (int)$r['max_guests']); }
$activePreset = null;
foreach ($presets as $p) { if ((int)$p['id'] === $active) { $activePreset = $p; } }
?>
<div class="page-head">
  <div><h1><?= icon('wallet') ?><?= e(__('admin.prices')) ?></h1><p class="muted"><?= e(__('admin.prices_help')) ?></p></div>
  <div class="page-actions"><a class="btn" href="<?= e(admin_url('settings/prices')) ?>"><?= icon('settings') ?><?= e(__('admin.price_settings')) ?></a></div>
</div>

<form method="post" class="card" data-price-grid id="price-form">
  <?= csrf_field() ?>
  <div class="card-head">
    <h2 class="card-title"><?= e(__('admin.current_prices')) ?>
      <?php if ($activePreset): ?><span class="pill pill-accent"><?= icon('check') ?><?= e(__('admin.from_preset', ['name' => $activePreset['name']])) ?></span><?php else: ?><span class="pill"><?= e(__('admin.custom_prices')) ?></span><?php endif; ?>
    </h2>
    <p class="muted small"><?= e(__('admin.empty_price_hint')) ?></p>
  </div>
  <div class="bulk-tools">
    <b><?= icon('sliders-horizontal') ?><?= e(__('admin.bulk_change')) ?></b>
    <div class="inp-group"><input class="inp inp-num" type="number" step="0.5" value="10" data-bulk-pct dir="ltr"><span class="inp-suffix">%</span></div>
    <select class="inp" data-bulk-round>
      <option value="1"><?= e(__('admin.round_none')) ?></option>
      <option value="10000"><?= e(__('admin.round_to', ['n' => num(10000)])) ?></option>
      <option value="50000" selected><?= e(__('admin.round_to', ['n' => num(50000)])) ?></option>
      <option value="100000"><?= e(__('admin.round_to', ['n' => num(100000)])) ?></option>
    </select>
    <button type="button" class="btn btn-sm" data-bulk-apply="up"><?= icon('arrow-up') ?><?= e(__('admin.increase')) ?></button>
    <button type="button" class="btn btn-sm" data-bulk-apply="down"><?= icon('arrow-down') ?><?= e(__('admin.decrease')) ?></button>
    <button type="button" class="btn btn-sm btn-ghost" data-bulk-reset><?= icon('rotate-ccw') ?><?= e(__('admin.undo_changes')) ?></button>
  </div>
  <div class="table-wrap">
    <table class="tbl price-grid">
      <thead><tr><th><?= e(__('admin.f.name')) ?></th><?php for ($g = 1; $g <= $maxG; $g++): ?><th><?= e(__('rooms.n_people', ['n' => $g])) ?></th><?php endfor; ?><th><?= e(__('admin.f.extra_person')) ?></th></tr></thead>
      <tbody>
        <?php foreach ($rooms as $r): $pr = $current['prices'][(int)$r['id']] ?? []; ?>
          <tr style="--row-color:<?= e($r['color'] ?: '#0b8a7a') ?>" class="<?= $r['active'] ? '' : 'is-off' ?>">
            <th><a href="<?= e(admin_url('r/rooms/' . $r['id'])) ?>"><?= e(tr($r['name'])) ?></a><?php if (tr($r['badge']) !== ''): ?><small class="muted d-block"><?= e(tr($r['badge'])) ?></small><?php endif; ?></th>
            <?php for ($g = 1; $g <= $maxG; $g++): ?>
              <?php $fits = $g >= (int)$r['min_guests'] && $g <= (int)$r['max_guests']; ?>
              <td data-label="<?= e(__('rooms.n_people', ['n' => $g])) ?>"<?= $fits ? '' : ' class="is-na"' ?>><?php if ($fits): ?><?= AdminForm::priceInput('p[' . (int)$r['id'] . '][' . $g . ']', $pr[(string)$g] ?? null) ?><?php else: ?><span class="muted" title="<?= e(__('rooms.unfit', ['n' => $g])) ?>">—</span><?php endif; ?></td>
            <?php endfor; ?>
            <td data-label="<?= e(__('admin.f.extra_person')) ?>"<?= (int)($r['max_extra'] ?? 0) > 0 ? '' : ' class="is-na"' ?>><?php if ((int)($r['max_extra'] ?? 0) > 0): ?><?= AdminForm::priceInput('x[' . (int)$r['id'] . ']', $current['extra'][(int)$r['id']] ?? null) ?><?php else: ?><span class="muted" title="<?= e(__('admin.no_extra')) ?>">—</span><input type="hidden" name="x[<?= (int)$r['id'] ?>]" value="<?= e($current['extra'][(int)$r['id']] ?? '') ?>"><?php endif; ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="savebar savebar--inline">
    <button class="btn btn-accent" type="submit" name="do" value="save"><?= icon('save') ?><?= e(__('admin.save_prices')) ?></button>
    <?php if ($activePreset): ?><button class="btn" type="submit" name="do" value="update" data-preset-update><?= icon('refresh-cw') ?><?= e(__('admin.update_preset', ['name' => $activePreset['name']])) ?></button><input type="hidden" name="preset" value="<?= (int)$activePreset['id'] ?>"><?php endif; ?>
    <span class="spacer"></span>
    <div class="inp-group save-as"><input class="inp" type="text" name="name" placeholder="<?= e(__('admin.preset_name_ph')) ?>" maxlength="80"><button class="btn" type="submit" name="do" value="save_as"><?= icon('plus') ?><?= e(__('admin.save_as_preset')) ?></button></div>
  </div>
</form>

<section class="card">
  <h2 class="card-title"><?= icon('archive') ?><?= e(__('admin.presets')) ?></h2>
  <p class="muted small"><?= e(__('admin.presets_help')) ?></p>
  <?php if (!$presets): ?><div class="empty-state small"><p><?= e(__('admin.no_presets')) ?></p></div><?php endif; ?>
  <div class="preset-list">
    <?php foreach ($presets as $p):
      $d = json_dec($p['data'], []); $sample = [];
      foreach ($rooms as $r) { $v = array_filter((array)($d['prices'][$r['id']] ?? [])); if ($v) { $sample[] = tr($r['name']) . ': ' . money(min($v)); } if (count($sample) >= 3) break; } ?>
      <div class="preset-card<?= (int)$p['id'] === $active ? ' is-active' : '' ?>">
        <div class="preset-info">
          <b><?= e($p['name']) ?></b><?php if ((int)$p['id'] === $active): ?> <span class="pill pill-accent"><?= e(__('admin.active')) ?></span><?php endif; ?>
          <small class="muted d-block"><?= e(__('admin.updated_at', ['d' => local_date($p['updated_at'], true)])) ?></small>
          <small class="muted d-block"><?= e(implode(' · ', $sample)) ?></small>
        </div>
        <div class="preset-actions">
          <form method="post"><?= csrf_field() ?><input type="hidden" name="preset" value="<?= (int)$p['id'] ?>"><button class="btn btn-accent btn-sm" name="do" value="apply" data-confirm="<?= e(__('admin.confirm_apply', ['name' => $p['name']])) ?>"><?= icon('check') ?><?= e(__('admin.apply')) ?></button></form>
          <form method="post" class="inp-group rename"><?= csrf_field() ?><input type="hidden" name="preset" value="<?= (int)$p['id'] ?>"><input class="inp" name="name" value="<?= e($p['name']) ?>" maxlength="80" aria-label="<?= e(__('admin.rename')) ?>"><button class="btn btn-sm" name="do" value="rename"><?= icon('pencil') ?></button></form>
          <form method="post"><?= csrf_field() ?><input type="hidden" name="preset" value="<?= (int)$p['id'] ?>"><button class="btn btn-sm btn-ghost" name="do" value="delete" data-confirm="<?= e(__('admin.confirm_delete')) ?>"><?= icon('trash-2') ?></button></form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
