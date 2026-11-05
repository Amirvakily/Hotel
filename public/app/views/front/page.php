<?php
/** @var array $p */
?>
<?= View::partial('front/partials/page-hero', ['title' => tr($p['title']), 'subtitle' => tr($p['excerpt']), 'image' => $p['cover_id']]) ?>
<section class="section">
  <div class="container container-narrow">
    <div class="prose prose-lg" data-reveal><?= Sanitizer::html(tr($p['body'])) ?></div>
  </div>
</section>
