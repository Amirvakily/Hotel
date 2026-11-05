<?php $age = (int)setting('price.child_age', 5); ?>
<div class="kids" data-kids>
  <span class="kids-label"><?= icon('baby') ?><?= e(__('rooms.kids_label', ['age' => $age])) ?></span>
  <span class="stepper">
    <button type="button" data-kids-step="-1" aria-label="−"><?= icon('minus') ?></button>
    <output data-kids-value aria-live="polite"><?= e(num(0)) ?></output>
    <button type="button" data-kids-step="1" aria-label="+"><?= icon('plus') ?></button>
  </span>
  <span class="pill pill-accent"><?= e(__('common.free')) ?></span>
</div>
