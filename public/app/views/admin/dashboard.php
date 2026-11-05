<?php
/** @var array $days @var array $stats @var array $top @var array $messages @var array $checklist */
$max = max(1, max(array_column($days, 'views')));
// clean axis ticks
$step = max(1, (int)pow(10, floor(log10($max))));
foreach ([1, 2, 5, 10] as $m) { if ($max / ($step * $m) <= 4) { $step *= $m; break; } }
$top_val = (int)ceil($max / $step) * $step;
$W = 720; $H = 220; $padL = 36; $padB = 26; $padT = 10;
$slot = ($W - $padL) / count($days);
$bw = min(18, $slot - 2);
$done = count(array_filter($checklist, fn($c) => $c[1]));
?>
<div class="page-head">
  <div><h1><?= e(__('admin.dashboard')) ?></h1><p class="muted"><?= e(__('admin.welcome', ['name' => Auth::user()['name'] ?: Auth::user()['username']])) ?></p></div>
  <div class="page-actions"><a class="btn btn-accent" href="<?= e(admin_url('prices')) ?>"><?= icon('wallet') ?><?= e(__('admin.edit_prices')) ?></a><a class="btn" href="<?= e(admin_url('r/gallery_items')) ?>"><?= icon('upload') ?><?= e(__('admin.add_photos')) ?></a></div>
</div>
<div class="tiles">
  <a class="tile" href="<?= e(admin_url('messages')) ?>"><small><?= e(__('admin.unread_messages')) ?></small><b><?= e(num($stats['unread'])) ?></b><?= icon('message-square') ?></a>
  <div class="tile"><small><?= e(__('admin.visits_today')) ?></small><b><?= e(num($stats['today'])) ?></b><?= icon('eye') ?></div>
  <div class="tile"><small><?= e(__('admin.visits_30')) ?></small><b><?= e(num($stats['month'])) ?></b><?= icon('calendar') ?></div>
  <a class="tile" href="<?= e(admin_url('r/rooms')) ?>"><small><?= e(__('admin.active_rooms')) ?></small><b><?= e(num($stats['rooms'])) ?></b><?= icon('bed-double') ?></a>
  <a class="tile" href="<?= e(admin_url('media')) ?>"><small><?= e(__('admin.photos')) ?></small><b><?= e(num($stats['photos'])) ?></b><?= icon('images') ?></a>
  <a class="tile" href="<?= e(admin_url('r/scenes')) ?>"><small><?= e(__('admin.scenes')) ?></small><b><?= e(num($stats['scenes'])) ?></b><?= icon('rotate-3d') ?></a>
</div>
<div class="grid-2">
  <section class="card">
    <h2 class="card-title"><?= e(__('admin.visits_chart')) ?></h2>
    <div class="chart" data-chart>
      <svg viewBox="0 0 <?= $W ?> <?= $H ?>" class="chart-svg" role="img" aria-label="<?= e(__('admin.visits_chart')) ?>">
        <?php for ($v = 0; $v <= $top_val; $v += $step): $y = $H - $padB - ($H - $padB - $padT) * $v / $top_val; ?>
          <line x1="<?= $padL ?>" x2="<?= $W ?>" y1="<?= round($y, 1) ?>" y2="<?= round($y, 1) ?>" class="grid"/>
          <text x="<?= $padL - 6 ?>" y="<?= round($y + 4, 1) ?>" class="tick" text-anchor="end"><?= e(num($v)) ?></text>
        <?php endfor; ?>
        <?php foreach ($days as $i => $d):
          $h = ($H - $padB - $padT) * $d['views'] / $top_val; $x = $padL + $i * $slot + ($slot - $bw) / 2; $y = $H - $padB - $h; $r = min(4, $h, $bw / 2);
          $label = local_date($d['day']) . ' · ' . num($d['views']); ?>
          <g class="bar" data-tip="<?= e($label) ?>">
            <rect class="hit" x="<?= round($padL + $i * $slot, 1) ?>" y="<?= $padT ?>" width="<?= round($slot, 1) ?>" height="<?= $H - $padB - $padT ?>"/>
            <?php if ($d['views'] > 0): ?><path class="col" d="M<?= round($x, 1) ?>,<?= $H - $padB ?>V<?= round($y + $r, 1) ?>Q<?= round($x, 1) ?>,<?= round($y, 1) ?> <?= round($x + $r, 1) ?>,<?= round($y, 1) ?>H<?= round($x + $bw - $r, 1) ?>Q<?= round($x + $bw, 1) ?>,<?= round($y, 1) ?> <?= round($x + $bw, 1) ?>,<?= round($y + $r, 1) ?>V<?= $H - $padB ?>Z"/><?php endif; ?>
          </g>
          <?php if ($i % 7 === 0 || $i === count($days) - 1): ?><text x="<?= round($padL + $i * $slot + $slot / 2, 1) ?>" y="<?= $H - 8 ?>" class="tick" text-anchor="middle"><?= e(local_date($d['day'])) ?></text><?php endif; ?>
        <?php endforeach; ?>
        <line x1="<?= $padL ?>" x2="<?= $W ?>" y1="<?= $H - $padB ?>" y2="<?= $H - $padB ?>" class="axis"/>
      </svg>
      <div class="chart-tip" data-chart-tip hidden></div>
    </div>
    <table class="sr-only"><caption><?= e(__('admin.visits_chart')) ?></caption><?php foreach ($days as $d): ?><tr><td><?= e(local_date($d['day'])) ?></td><td><?= e(num($d['views'])) ?></td></tr><?php endforeach; ?></table>
    <p class="muted small"><?= e(__('admin.visits_note')) ?></p>
  </section>
  <section class="card">
    <h2 class="card-title"><?= e(__('admin.checklist')) ?> <span class="pill"><?= e(num($done)) ?>/<?= e(num(count($checklist))) ?></span></h2>
    <ul class="checklist">
      <?php foreach ($checklist as [$label, $ok, $link]): ?>
        <li class="<?= $ok ? 'is-done' : '' ?>"><span class="ck"><?= icon($ok ? 'circle-check' : 'circle-dot') ?></span><a href="<?= e($link) ?>"><?= e(__($label)) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </section>
</div>
<div class="grid-2">
  <section class="card">
    <h2 class="card-title"><?= e(__('admin.latest_messages')) ?><a class="link" href="<?= e(admin_url('messages')) ?>"><?= e(__('admin.see_all')) ?></a></h2>
    <?php if (!$messages): ?><p class="muted"><?= e(__('admin.no_messages')) ?></p><?php endif; ?>
    <ul class="msg-list">
      <?php foreach ($messages as $m): ?>
        <li class="<?= $m['is_read'] ? '' : 'is-unread' ?>"><a href="<?= e(admin_url('messages/' . $m['id'])) ?>"><b><?= e($m['name']) ?></b><span><?= e(str_limit($m['body'], 90)) ?></span><small><?= e(local_date($m['created_at'], true)) ?></small></a></li>
      <?php endforeach; ?>
    </ul>
  </section>
  <section class="card">
    <h2 class="card-title"><?= e(__('admin.top_pages')) ?></h2>
    <?php if (!$top): ?><p class="muted"><?= e(__('admin.no_visits')) ?></p><?php endif; ?>
    <ul class="bars-list">
      <?php $tmax = max(1, (int)($top[0]['views'] ?? 1)); foreach ($top as $t): ?>
        <li><span dir="ltr"><?= e($t['path']) ?></span><b><?= e(num($t['views'])) ?></b><i style="--w:<?= round(100 * $t['views'] / $tmax) ?>%"></i></li>
      <?php endforeach; ?>
    </ul>
  </section>
</div>
