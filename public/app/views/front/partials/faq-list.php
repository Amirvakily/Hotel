<?php /** @var array $faqs */ ?>
<div class="faq-list">
  <?php foreach ($faqs as $i => $f): ?>
    <details class="faq" data-reveal style="--d:<?= $i % 3 ?>">
      <summary><span><?= e(tr($f['question'])) ?></span><i aria-hidden="true"></i></summary>
      <div class="faq-a prose"><?= Sanitizer::html(tr($f['answer'])) ?></div>
    </details>
  <?php endforeach; ?>
</div>
