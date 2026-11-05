<div class="modal" data-media-modal hidden role="dialog" aria-modal="true" aria-labelledby="mm-title">
  <div class="modal-box modal-lg">
    <header class="modal-head">
      <h2 id="mm-title"><?= e(__('admin.media_library')) ?></h2>
      <button type="button" class="icon-btn" data-modal-close aria-label="close"><?= icon('x') ?></button>
    </header>
    <div class="modal-tools">
      <label class="btn btn-accent upload-btn"><?= icon('upload') ?><span><?= e(__('admin.upload_new')) ?></span><input type="file" accept="image/jpeg,image/png,image/webp" multiple data-mm-file hidden></label>
      <input class="inp" type="search" placeholder="<?= e(__('admin.search')) ?>" data-mm-search>
      <span class="muted small" data-mm-status></span>
    </div>
    <div class="dropzone" data-mm-drop><?= icon('upload') ?><span><?= e(__('admin.drop_here')) ?></span></div>
    <div class="mm-grid" data-mm-grid></div>
    <footer class="modal-foot">
      <button type="button" class="btn btn-ghost" data-mm-more hidden><?= e(__('admin.load_more')) ?></button>
      <span class="muted small" data-mm-count></span>
      <button type="button" class="btn btn-accent" data-mm-choose disabled><?= icon('check') ?><?= e(__('admin.select')) ?></button>
    </footer>
  </div>
</div>
