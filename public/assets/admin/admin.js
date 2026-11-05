/* Venus Hotel – admin panel script (no build step, no framework). */
(function () {
  'use strict';
  var A = window.ADMIN || { t: {}, url: {} };
  var d = document, w = window, body = d.body;
  var $ = function (s, c) { return (c || d).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || d).querySelectorAll(s)); };
  var on = function (el, ev, fn, o) { if (el) el.addEventListener(ev, fn, o || false); };
  var T = A.t || {};

  function post(url, data, json) {
    var o = { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-Token': A.csrf, 'Accept': 'application/json', 'X-Requested-With': 'fetch' } };
    if (json) { o.headers['Content-Type'] = 'application/json'; o.body = JSON.stringify(data); }
    else { var f = data instanceof FormData ? data : new FormData(); if (!(data instanceof FormData)) Object.keys(data || {}).forEach(function (k) { f.append(k, data[k]); }); f.append('_csrf', A.csrf); o.body = f; }
    return fetch(url, o).then(function (r) { return r.json().catch(function () { return { error: r.status }; }); });
  }
  var scripts = {};
  function loadScript(src) { return scripts[src] || (scripts[src] = new Promise(function (res, rej) { var s = d.createElement('script'); s.src = src; s.onload = res; s.onerror = rej; d.head.appendChild(s); })); }
  function loadCss(href) { if (!$('link[href="' + href + '"]')) { var l = d.createElement('link'); l.rel = 'stylesheet'; l.href = href; d.head.appendChild(l); } }
  function toast(msg, err) {
    var box = $('.flashes') || (function () { var b = d.createElement('div'); b.className = 'flashes'; $('.main').insertBefore(b, $('.content')); return b; })();
    var f = d.createElement('div'); f.className = 'flash' + (err ? ' flash-err' : ''); f.textContent = msg; box.appendChild(f);
    setTimeout(function () { f.remove(); }, 4000);
  }
  function digitsToLatin(s) { return String(s).replace(/[۰-۹]/g, function (c) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf(c); }).replace(/[٠-٩]/g, function (c) { return '٠١٢٣٤٥٦٧٨٩'.indexOf(c); }); }

  // ---------- shell ----------
  on($('[data-sb-open]'), 'click', function () { body.classList.add('sb-open'); });
  on($('[data-sb-close]'), 'click', function () { body.classList.remove('sb-open'); });
  $$('[data-dismiss]').forEach(function (b) { on(b, 'click', function () { b.parentElement.remove(); }); });
  setTimeout(function () { $$('.flashes .flash:not(.flash-err)').forEach(function (f) { f.style.transition = 'opacity .4s'; f.style.opacity = '0'; setTimeout(function () { f.remove(); }, 400); }); }, 5000);
  var h1 = $('.content h1'); if (h1 && $('[data-page-title]')) $('[data-page-title]').textContent = h1.textContent.trim();
  on(d, 'click', function (e) {
    var b = e.target.closest('[data-confirm]');
    if (b && !w.confirm(b.getAttribute('data-confirm'))) { e.preventDefault(); e.stopImmediatePropagation(); }
  }, true);

  // ---------- toggles, sorting, search ----------
  $$('[data-toggle]').forEach(function (cb) {
    on(cb, 'change', function () {
      post(cb.getAttribute('data-toggle'), { col: cb.getAttribute('data-col') || 'active' }).then(function (r) {
        if (r.error) { cb.checked = !cb.checked; toast(r.error, true); return; }
        var item = cb.closest('.gal-item'); if (item) item.classList.toggle('is-off', !cb.checked);
      });
    });
  });
  function sortable(el, opts) {
    if (!el) return;
    var go = function () { if (!w.Sortable) return setTimeout(go, 100); w.Sortable.create(el, Object.assign({ animation: 180, handle: '.handle', ghostClass: 'sortable-ghost' }, opts)); };
    go();
  }
  $$('[data-sort-url]').forEach(function (el) {
    sortable(el, { onEnd: function () {
      var ids = $$('[data-id]', el).map(function (r) { return r.getAttribute('data-id'); });
      post(el.getAttribute('data-sort-url'), { ids: ids }, true).then(function (r) { if (r.ok) toast(T.saved); });
    } });
  });
  var secList = $('[data-sections]');
  if (secList) {
    sortable(secList, { onEnd: function () {
      post(secList.getAttribute('data-url'), { order: $$('[data-id]', secList).map(function (r) { return r.getAttribute('data-id'); }) }, true).then(function () { toast(T.saved); });
    } });
    $$('[data-section-toggle]', secList).forEach(function (cb) {
      on(cb, 'change', function () { post(secList.getAttribute('data-url'), { toggle: cb.getAttribute('data-section-toggle') }, true).then(function () { cb.closest('li').classList.toggle('is-off', !cb.checked); }); });
    });
  }
  $$('[data-sortable-list]').forEach(function (el) { sortable(el); });
  function searchRows(input, rows) {
    on(input, 'input', function () {
      var q = input.value.trim().toLowerCase();
      rows().forEach(function (r) { r.hidden = q && (r.getAttribute('data-search') || r.textContent).toLowerCase().indexOf(q) < 0; });
    });
  }
  $$('[data-list-search]').forEach(function (i) { searchRows(i, function () { return $$('tbody tr', i.closest('.card')); }); });
  $$('[data-tr-search]').forEach(function (i) { searchRows(i, function () { return $$('.tr-table tbody tr'); }); });
  $$('[data-list-filter]').forEach(function (s) {
    on(s, 'change', function () { var u = new URL(s.getAttribute('data-list-filter'), w.location.href); if (s.value) u.searchParams.set(s.getAttribute('data-param'), s.value); w.location.href = u.toString(); });
  });

  // ---------- form helpers ----------
  var form = $('[data-edit-form]');
  var dirty = false;
  if (form) {
    on(form, 'input', function () { dirty = true; });
    on(form, 'change', function () { dirty = true; });
    on(form, 'submit', function () { syncRte(); dirty = false; });
    on(w, 'beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = T.unsaved; } });
  }
  // tabs
  $$('[data-tab-btn]').forEach(function (b) {
    on(b, 'click', function () {
      $$('[data-tab-btn]').forEach(function (x) { x.setAttribute('aria-selected', x === b ? 'true' : 'false'); });
      $$('[data-tab]').forEach(function (p) { p.hidden = p.getAttribute('data-tab') !== b.getAttribute('data-tab-btn'); });
      w.dispatchEvent(new Event('resize'));
    });
  });
  // language switch for multilingual fields
  function setLang(l) {
    $$('[data-lang-switch] button').forEach(function (b) { b.setAttribute('aria-pressed', b.getAttribute('data-lang') === l ? 'true' : 'false'); });
    $$('.i18n-pane').forEach(function (p) { p.hidden = p.getAttribute('data-lang') !== l; });
    $$('[data-lang-input]').forEach(function (i) { i.hidden = i.getAttribute('data-lang-input') !== l; });
    try { localStorage.setItem('venus-admin-lang', l); } catch (e) { }
  }
  function missingDots() {
    $$('[data-lang-switch] button').forEach(function (b) {
      var l = b.getAttribute('data-lang'), missing = false;
      $$('.i18n').forEach(function (box) {
        var panes = $$('.i18n-pane', box), first = panes[0], mine = panes.filter(function (p) { return p.getAttribute('data-lang') === l; })[0];
        var val = function (p) { var i = $('input,textarea', p); return i ? i.value.trim() : ''; };
        if (first && mine && val(first) && !val(mine)) missing = true;
      });
      var dot = $('.dot', b); if (dot) dot.hidden = !missing;
    });
  }
  $$('[data-lang-switch] button').forEach(function (b) { on(b, 'click', function () { setLang(b.getAttribute('data-lang')); }); });
  if ($('[data-lang-switch]')) { var savedL = null; try { savedL = localStorage.getItem('venus-admin-lang'); } catch (e) { } if (savedL && $('[data-lang-switch] [data-lang="' + savedL + '"]')) setLang(savedL); missingDots(); on(d, 'input', missingDots); }
  // conditional fields
  $$('[data-show-if]').forEach(function (fld) {
    var rule = fld.getAttribute('data-show-if'), m = rule.match(/^(\w+)(!?=)(.*)$/);
    if (!m) return;
    var ctl = form && form.querySelector('[name="f[' + m[1] + ']"]');
    if (!ctl) return;
    var upd = function () { var eq = ctl.value === m[3]; fld.hidden = m[2] === '=' ? !eq : eq; };
    on(ctl, 'change', upd); upd();
  });
  // slug
  function slugify(s) { return String(s).toLowerCase().normalize('NFKD').replace(/[^\w\s-]/g, '').replace(/[\s_]+/g, '-').replace(/[^a-z0-9-]/g, '').replace(/-+/g, '-').replace(/^-|-$/g, '').slice(0, 80); }
  $$('[data-slugify]').forEach(function (b) {
    on(b, 'click', function () {
      var inp = b.parentElement.querySelector('input'), from = inp.getAttribute('data-slug-from');
      var src = form && (form.querySelector('[name="f[' + from + '][en]"]') || form.querySelector('[name="f[' + from + '][fa]"]'));
      inp.value = slugify(src ? src.value : '') || ('item-' + Math.random().toString(36).slice(2, 7));
      dirty = true;
    });
  });
  // colors + presets
  $$('[data-color-for]').forEach(function (c) {
    var t = d.getElementById(c.getAttribute('data-color-for'));
    on(c, 'input', function () { t.value = c.value; dirty = true; });
    on(t, 'input', function () { if (/^#[0-9a-f]{6}$/i.test(t.value)) c.value = t.value; });
  });
  $$('[data-theme-preset]').forEach(function (b) {
    on(b, 'click', function () {
      var vals = JSON.parse(b.getAttribute('data-theme-preset'));
      Object.keys(vals).forEach(function (k) {
        var inp = form.querySelector('[name="f[' + k + ']"]'); if (!inp) return;
        inp.value = vals[k]; var pick = form.querySelector('[data-color-for="' + inp.id + '"]'); if (pick) pick.value = vals[k];
      });
      dirty = true;
    });
  });
  // icon picker
  $$('[data-icon-picker]').forEach(function (p) {
    var grid = $('.icon-grid', p), input = $('input', p), cur = $('[data-icon-toggle]', p);
    on(cur, 'click', function () { grid.hidden = !grid.hidden; });
    $$('[data-icon]', grid).forEach(function (b) {
      on(b, 'click', function () {
        input.value = b.getAttribute('data-icon');
        cur.innerHTML = b.innerHTML + '<span>' + input.value + '</span>';
        $$('.is-on', grid).forEach(function (x) { x.classList.remove('is-on'); }); b.classList.add('is-on');
        grid.hidden = true; dirty = true;
      });
    });
    on(d, 'click', function (e) { if (!p.contains(e.target)) grid.hidden = true; });
  });
  // repeaters
  $$('[data-repeater]').forEach(function (rep) {
    var rows = $('.rep-rows', rep), tpl = $('template', rep), n = rows.children.length;
    on($('[data-rep-add]', rep), 'click', function () {
      var html = tpl.innerHTML.replace(/__i__/g, String(Date.now() % 100000 + n++));
      rows.insertAdjacentHTML('beforeend', html);
      var cur = $('[data-lang-switch] [aria-pressed="true"]'); if (cur) setLang(cur.getAttribute('data-lang'));
      bedPreview(rep); dirty = true;
    });
    on(rep, 'click', function (e) { var r = e.target.closest('[data-rep-remove]'); if (r) { r.closest('.rep-row').remove(); bedPreview(rep); dirty = true; } });
    on(rep, 'input', function () { bedPreview(rep); });
    on(rep, 'change', function () { bedPreview(rep); });
    bedPreview(rep);
  });
  function bedPreview(rep) {
    if (!rep.hasAttribute('data-bed-layout')) return;
    var out = $('[data-bed-preview]', rep); if (!out) return;
    var sizes = { single: [15, 28, 1], twin: [15, 28, 1], double: [26, 28, 2], queen: [28, 30, 2], king: [32, 30, 2], bunk: [15, 28, 1], sofa: [26, 14, 0], extra: [13, 24, 1] };
    var x = 2, h = 34, sh = '', total = 0;
    $$('.rep-row', rep).forEach(function (r) {
      var t = $('select', r).value, k = Math.min(12, +$('input', r).value || 0), sz = sizes[t] || sizes.single;
      for (var i = 0; i < k; i++, total++) {
        var y = h - sz[1] - 2;
        sh += '<rect x="' + x + '" y="' + y + '" width="' + sz[0] + '" height="' + sz[1] + '" rx="3"' + (t === 'extra' ? ' stroke-dasharray="2 2"' : '') + '/>';
        if (sz[2] === 1) sh += '<rect class="p" x="' + (x + 3) + '" y="' + (y + 2.5) + '" width="' + (sz[0] - 6) + '" height="5" rx="1.5"/>';
        if (sz[2] === 2) { var pw = (sz[0] - 9) / 2; sh += '<rect class="p" x="' + (x + 3) + '" y="' + (y + 2.5) + '" width="' + pw + '" height="5" rx="1.5"/><rect class="p" x="' + (x + 6 + pw) + '" y="' + (y + 2.5) + '" width="' + pw + '" height="5" rx="1.5"/>'; }
        x += sz[0] + 6;
      }
    });
    out.innerHTML = total ? '<svg viewBox="0 0 ' + (x - 4) + ' ' + h + '"><g fill="none" stroke="currentColor" stroke-width="1.3">' + sh + '</g></svg>' : '';
    // keep beds count in sync
    var beds = form && form.querySelector('[name="f[beds]"]');
    if (beds && total) { beds.value = $$('.rep-row', rep).reduce(function (s, r) { return s + (($('select', r).value === 'sofa' || $('select', r).value === 'extra') ? 0 : (+$('input', r).value || 0) * ($('select', r).value === 'bunk' ? 2 : 1)); }, 0) || total; }
  }
  // price inputs: thousands separators, Persian digits
  function fmtPrice(inp) { var v = digitsToLatin(inp.value).replace(/\D/g, ''); inp.value = v ? Number(v).toLocaleString('en-US') : ''; }
  on(d, 'focusout', function (e) { if (e.target.matches && e.target.matches('[data-price]')) fmtPrice(e.target); });
  // price rows follow min/max guests (+ extra people)
  var pg = $('[data-prices]');
  if (pg && form) {
    var mn = form.querySelector('[name="f[min_guests]"]'), mx = form.querySelector('[name="f[max_guests]"]');
    var upd = function () { var a = +mn.value || 1, b = Math.max(a, +mx.value || a); $$('.price-row', pg).forEach(function (r) { var g = +r.getAttribute('data-g'); r.hidden = g < a || g > b; }); };
    on(mn, 'input', upd); on(mx, 'input', upd); upd();
  }

  // ---------- rich text ----------
  function syncRte() { $$('[data-rte]').forEach(function (r) { $('textarea', r).value = $('.rte-area', r).innerHTML.trim() === '<br>' ? '' : $('.rte-area', r).innerHTML; }); }
  $$('[data-rte]').forEach(function (r) {
    var area = $('.rte-area', r);
    on(area, 'input', function () { syncRte(); dirty = true; });
    on(area, 'paste', function (e) { e.preventDefault(); var t = (e.clipboardData || w.clipboardData).getData('text/plain'); d.execCommand('insertText', false, t); });
    $$('[data-cmd]', r).forEach(function (b) {
      on(b, 'mousedown', function (e) { e.preventDefault(); });
      on(b, 'click', function () {
        var c = b.getAttribute('data-cmd');
        area.focus();
        if (c === 'h2' || c === 'h3' || c === 'blockquote') d.execCommand('formatBlock', false, c);
        else if (c === 'link') { var u = w.prompt('https://', 'https://'); if (u && /^(https?:\/\/|mailto:|tel:|\/)/.test(u)) d.execCommand('createLink', false, u); }
        else if (c === 'removeFormat') { d.execCommand('removeFormat'); d.execCommand('formatBlock', false, 'p'); }
        else d.execCommand(c);
        syncRte(); dirty = true;
      });
    });
  });

  // ---------- image preparation in the browser ----------
  function loadImage(file) {
    return new Promise(function (res, rej) {
      if (w.createImageBitmap) {
        createImageBitmap(file, { imageOrientation: 'from-image' }).then(res).catch(function () { viaImg(); });
      } else viaImg();
      function viaImg() { var u = URL.createObjectURL(file), im = new Image(); im.onload = function () { res(im); }; im.onerror = rej; im.src = u; }
    });
  }
  function toBlob(src, wv, hv, type, q) {
    return new Promise(function (res) {
      try {
        var c = d.createElement('canvas'); c.width = wv; c.height = hv;
        var g = c.getContext('2d'); g.imageSmoothingQuality = 'high'; g.drawImage(src, 0, 0, wv, hv);
        c.toBlob(function (b) { res(b); }, type, q);
      } catch (e) { res(null); }
    });
  }
  function prepPhoto(file) {
    if (!/^image\/(jpeg|png|webp)$/.test(file.type)) return Promise.resolve(file);
    return loadImage(file).then(function (im) {
      var W = im.width, H = im.height, max = 2560, s = Math.min(1, max / Math.max(W, H));
      if (s === 1 && file.size < 3.5 * 1048576) return file;
      var type = file.type === 'image/png' ? 'image/png' : 'image/jpeg';
      return toBlob(im, Math.round(W * s), Math.round(H * s), type, .9).then(function (b) { return b ? new File([b], file.name.replace(/\.\w+$/, type === 'image/png' ? '.png' : '.jpg'), { type: type }) : file; });
    }).catch(function () { return file; });
  }
  function prepPano(file) {
    return loadImage(file).then(function (im) {
      var W = im.width, H = im.height, out = { info: { w: W, h: H } };
      var mainW = Math.min(W, 5760), mobW = Math.min(W, 4096);
      return toBlob(im, mainW, Math.round(H * mainW / W), 'image/jpeg', .88).then(function (b) {
        out.main = b || file;
        return W > 4096 ? toBlob(im, mobW, Math.round(H * mobW / W), 'image/jpeg', .85) : null;
      }).then(function (b) { if (b) out.mobile = b; return toBlob(im, 1024, Math.round(H * 1024 / W), 'image/jpeg', .8); })
        .then(function (b) { if (b) out.thumb = b; return out; });
    }).catch(function () { return { main: file, info: null }; });
  }
  function xhrUpload(url, fd, onProgress) {
    return new Promise(function (res) {
      var x = new XMLHttpRequest(); x.open('POST', url); x.setRequestHeader('X-CSRF-Token', A.csrf); x.setRequestHeader('Accept', 'application/json');
      x.upload.onprogress = function (e) { if (e.lengthComputable && onProgress) onProgress(e.loaded / e.total); };
      x.onload = function () { try { res(JSON.parse(x.responseText)); } catch (e) { res({ error: x.status === 413 ? 'File too large for the server' : 'HTTP ' + x.status }); } };
      x.onerror = function () { res({ error: 'network' }); };
      fd.append('_csrf', A.csrf); x.send(fd);
    });
  }
  function uploadPhotos(files, progress) {
    var list = Array.prototype.slice.call(files), items = [], i = 0;
    function next() {
      if (i >= list.length) return Promise.resolve(items);
      var f = list[i++]; if (progress) progress((i - 1) / list.length, (A.t.preparing || '…') + ' ' + i + '/' + list.length);
      return prepPhoto(f).then(function (pf) {
        var fd = new FormData(); fd.append('files[]', pf, pf.name);
        return xhrUpload(A.url.upload, fd, function (p) { if (progress) progress((i - 1 + p) / list.length, T.uploading + ' ' + i + '/' + list.length); });
      }).then(function (r) {
        if (r.error) toast(f.name + ': ' + r.error, true);
        (r.items || []).forEach(function (it) { if (it.error) toast(it.error, true); else items.push(it); });
        return next();
      });
    }
    return next();
  }
  function uploadPano(file, progress) {
    if (progress) progress(0, A.t.preparing);
    return prepPano(file).then(function (p) {
      var fd = new FormData(); fd.append('main', p.main, 'pano.jpg'); if (p.mobile) fd.append('mobile', p.mobile, 'pano-m.jpg'); if (p.thumb) fd.append('thumb', p.thumb, 'pano-t.jpg');
      fd.append('name', file.name.replace(/\.\w+$/, ''));
      return xhrUpload(A.url.pano, fd, function (x) { if (progress) progress(x, T.uploading); }).then(function (r) { r.info = p.info; return r; });
    });
  }
  function progressBar(el) {
    return function (p, label) { if (!el) return; el.hidden = false; $('i', el).style.width = Math.round(p * 100) + '%'; $('span', el).textContent = label || ''; };
  }

  // ---------- media modal ----------
  var modal = $('[data-media-modal]'), mmState = null;
  function openPicker(field) {
    var kind = field.getAttribute('data-kind'), multi = field.getAttribute('data-multiple') === '1';
    mmState = { field: field, kind: kind, multi: multi, page: 1, picked: [] };
    $('[data-mm-file]', modal).multiple = multi && kind !== 'pano';
    $('[data-mm-grid]', modal).innerHTML = ''; $('[data-mm-search]', modal).value = '';
    modal.hidden = false; body.style.overflow = 'hidden';
    loadMedia(true); updatePick();
  }
  function closePicker() { modal.hidden = true; body.style.overflow = ''; mmState = null; }
  function loadMedia(reset) {
    if (!mmState) return;
    if (reset) { mmState.page = 1; $('[data-mm-grid]', modal).innerHTML = ''; }
    var u = new URL(A.url.mediaList, w.location.href); u.searchParams.set('kind', mmState.kind); u.searchParams.set('page', mmState.page);
    var q = $('[data-mm-search]', modal).value.trim(); if (q) u.searchParams.set('q', q);
    fetch(u, { credentials: 'same-origin', headers: { Accept: 'application/json' } }).then(function (r) { return r.json(); }).then(function (r) {
      var grid = $('[data-mm-grid]', modal);
      (r.items || []).forEach(function (it) { grid.insertAdjacentHTML('beforeend', it.html); });
      $('[data-mm-more]', modal).hidden = !r.more;
      if (!grid.children.length) grid.innerHTML = '<p class="muted">' + T.noMedia + '</p>';
      markPicked();
    });
  }
  function markPicked() { $$('[data-mm-grid] .thumb', modal).forEach(function (t) { t.classList.toggle('is-picked', mmState && mmState.picked.indexOf(t.getAttribute('data-id')) > -1); }); }
  function updatePick() { var n = mmState ? mmState.picked.length : 0; $('[data-mm-choose]', modal).disabled = !n; $('[data-mm-count]', modal).textContent = n ? n + ' ✓' : ''; }
  if (modal) {
    $$('[data-modal-close]', modal).forEach(function (b) { on(b, 'click', closePicker); });
    on(modal, 'click', function (e) { if (e.target === modal) closePicker(); });
    on(d, 'keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) closePicker(); });
    on($('[data-mm-more]', modal), 'click', function () { mmState.page++; loadMedia(false); });
    var st; on($('[data-mm-search]', modal), 'input', function () { clearTimeout(st); st = setTimeout(function () { loadMedia(true); }, 300); });
    on($('[data-mm-grid]', modal), 'click', function (e) {
      var t = e.target.closest('.thumb'); if (!t || !mmState) return;
      var id = t.getAttribute('data-id'), i = mmState.picked.indexOf(id);
      if (i > -1) mmState.picked.splice(i, 1); else { if (!mmState.multi) mmState.picked = []; mmState.picked.push(id); }
      markPicked(); updatePick();
    });
    on($('[data-mm-grid]', modal), 'dblclick', function (e) { var t = e.target.closest('.thumb'); if (t && mmState && !mmState.multi) { mmState.picked = [t.getAttribute('data-id')]; choose(); } });
    function choose() {
      var f = mmState.field, thumbs = $('.media-thumbs', f);
      if (!mmState.multi) thumbs.innerHTML = '';
      mmState.picked.forEach(function (id) {
        if ($('.thumb[data-id="' + id + '"]', thumbs)) return;
        var src = $('[data-mm-grid] .thumb[data-id="' + id + '"]', modal); if (src) thumbs.appendChild(src.cloneNode(true));
      });
      $$('.thumb', thumbs).forEach(function (t) { t.classList.remove('is-picked'); });
      syncMedia(f); closePicker(); dirty = true;
      f.dispatchEvent(new CustomEvent('media:change', { bubbles: true }));
    }
    on($('[data-mm-choose]', modal), 'click', choose);
    var status = $('[data-mm-status]', modal);
    function handleFiles(files) {
      if (!files.length || !mmState) return;
      var prog = function (p, l) { status.textContent = l + ' ' + Math.round(p * 100) + '%'; };
      var job = mmState.kind === 'pano' ? uploadPano(files[0], prog).then(function (r) { if (r.error) toast(r.error, true); if (r.info) panoAdvice(r.info); return r.items || []; }) : uploadPhotos(files, prog);
      job.then(function (items) {
        status.textContent = '';
        var grid = $('[data-mm-grid]', modal); var empty = $('p.muted', grid); if (empty) empty.remove();
        items.forEach(function (it) { grid.insertAdjacentHTML('afterbegin', it.html); if (!mmState.multi) mmState.picked = []; mmState.picked.push(String(it.id)); });
        markPicked(); updatePick();
      });
    }
    on($('[data-mm-file]', modal), 'change', function (e) { handleFiles(e.target.files); e.target.value = ''; });
    dropzone($('[data-mm-drop]', modal), handleFiles);
  }
  function dropzone(z, fn) {
    if (!z) return;
    ['dragenter', 'dragover'].forEach(function (ev) { on(z, ev, function (e) { e.preventDefault(); z.classList.add('is-over'); }); });
    ['dragleave', 'drop'].forEach(function (ev) { on(z, ev, function (e) { e.preventDefault(); z.classList.remove('is-over'); }); });
    on(z, 'drop', function (e) { fn(e.dataTransfer.files); });
  }
  function syncMedia(f) { $('input[type="hidden"]', f).value = $$('.media-thumbs .thumb', f).map(function (t) { return t.getAttribute('data-id'); }).join(','); }
  $$('[data-media-field]').forEach(function (f) {
    on($('[data-media-pick]', f), 'click', function () { openPicker(f); });
    on(f, 'click', function (e) { var x = e.target.closest('[data-remove]'); if (x) { x.closest('.thumb').remove(); syncMedia(f); dirty = true; f.dispatchEvent(new CustomEvent('media:change', { bubbles: true })); } });
    var th = $('[data-sortable]', f); if (th) { var go = function () { if (!w.Sortable) return setTimeout(go, 100); w.Sortable.create(th, { animation: 180, onEnd: function () { syncMedia(f); dirty = true; } }); }; go(); }
  });
  function panoAdvice(info) {
    if (!info || !form) return;
    var ratio = info.w / info.h, type = form.querySelector('[name="f[type]"]');
    if (Math.abs(ratio - 2) < .05) { toast(T.panoFull); if (type && type.value === 'partial') { type.value = 'equirect'; type.dispatchEvent(new Event('change')); } }
    else {
      toast(T.panoPartial, true);
      if (type) { type.value = 'partial'; type.dispatchEvent(new Event('change')); }
      var haov = form.querySelector('[name="f[haov]"]'), vaov = form.querySelector('[name="f[vaov]"]');
      if (haov && !+haov.value) haov.value = 360;
      if (haov && vaov) vaov.value = Math.min(180, Math.round((+haov.value || 360) / ratio * 10) / 10);
    }
  }

  // ---------- gallery bulk upload & library ----------
  var bulk = $('[data-bulk-form]');
  if (bulk) {
    var bprog = progressBar($('[data-bulk-progress]', bulk));
    var run = function (files) {
      if (!files.length) return;
      uploadPhotos(files, bprog).then(function (items) {
        if (!items.length) return;
        $('[data-bulk-ids]', bulk).value = items.map(function (i) { return i.id; }).join(',');
        bprog(1, '✓'); bulk.submit();
      });
    };
    on($('[data-bulk-file]', bulk), 'change', function (e) { run(e.target.files); });
    dropzone($('[data-bulk-drop]', bulk), run);
  }
  var libProg = progressBar($('[data-lib-progress]'));
  on($('[data-lib-upload]'), 'change', function (e) { uploadPhotos(e.target.files, libProg).then(function () { w.location.reload(); }); });
  on($('[data-lib-pano]'), 'change', function (e) { if (e.target.files[0]) uploadPano(e.target.files[0], libProg).then(function (r) { if (r.error) toast(r.error, true); else w.location.reload(); }); });

  // ---------- map picker ----------
  $$('[data-latlng]').forEach(function (box) {
    var lat = $('[data-lat]', box), lng = $('[data-lng]', box), mapEl = $('[data-map-pick]', box), map = null, marker = null;
    function setPos(a, b, pan) {
      lat.value = (+a).toFixed(6); lng.value = (+b).toFixed(6); dirty = true;
      if (map) { if (!marker) marker = w.L.marker([a, b], { draggable: true }).addTo(map).on('dragend', function (e) { var p = e.target.getLatLng(); setPos(p.lat, p.lng); }); else marker.setLatLng([a, b]); if (pan) map.setView([a, b], Math.max(map.getZoom(), 16)); }
    }
    function init() {
      loadCss(A.assets + 'vendor/leaflet/leaflet.css');
      loadScript(A.assets + 'vendor/leaflet/leaflet.js').then(function () {
        var c = JSON.parse(box.getAttribute('data-center') || '[38.08,46.29]');
        map = w.L.map(mapEl).setView(c, 15);
        w.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
        if (lat.value && lng.value) setPos(lat.value, lng.value);
        map.on('click', function (e) { setPos(e.latlng.lat, e.latlng.lng); });
        setTimeout(function () { map.invalidateSize(); }, 300);
        w.addEventListener('resize', function () { map.invalidateSize(); });
      }).catch(function () { mapEl.textContent = 'Map could not load – type the coordinates.'; });
    }
    var tab = box.closest('[data-tab]');
    if (tab && tab.hidden) { var o = new MutationObserver(function () { if (!tab.hidden) { o.disconnect(); init(); } }); o.observe(tab, { attributes: true }); } else init();
    [lat, lng].forEach(function (i) { on(i, 'change', function () { if (+lat.value && +lng.value) setPos(lat.value, lng.value, true); }); });
    on($('[data-geo]', box), 'click', function () { if (navigator.geolocation) navigator.geolocation.getCurrentPosition(function (p) { setPos(p.coords.latitude, p.coords.longitude, true); }); });
    on($('[data-maplink-go]', box), 'click', function () {
      var s = digitsToLatin($('[data-maplink]', box).value), m = s.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/) || s.match(/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/) || s.match(/[?&](?:q|query|ll|destination)=(-?\d+\.\d+)(?:,|%2C)\s*(-?\d+\.\d+)/) || s.match(/latitude=(-?\d+\.\d+).*longitude=(-?\d+\.\d+)/) || s.match(/(-?\d{1,2}\.\d{3,})\s*,\s*(-?\d{1,3}\.\d{3,})/);
      if (m) setPos(m[1], m[2], true); else toast(T.linkBad, true);
    });
  });

  // ---------- 360° hotspot editor ----------
  $$('[data-hotspots]').forEach(function (ed) {
    var cfg = JSON.parse(ed.getAttribute('data-config') || '{}'), hs = cfg.hotspots || [], viewer = null;
    var jsonIn = $('[data-hs-json]', ed), yawIn = $('[data-hs-yaw]', ed), pitchIn = $('[data-hs-pitch]', ed), hfovIn = $('[data-hs-hfov]', ed);
    var list = $('[data-hs-list]', ed), stage = $('[data-hs-viewer]', ed), readout = $('[data-hs-readout]', ed);
    function save() { jsonIn.value = JSON.stringify(hs); dirty = true; }
    function panoField() { return form && form.querySelector('[data-media-field][data-kind="pano"]'); }
    function panoUrl() { var f = panoField(), t = f && $('.thumb', f); return t ? t.getAttribute('data-full') : ''; }
    function panoInfo() { var f = panoField(), t = f && $('.thumb', f); return t ? { w: +t.getAttribute('data-w'), h: +t.getAttribute('data-h') } : null; }
    function val(name) { var i = form.querySelector('[name="f[' + name + ']"]'); return i ? i.value : ''; }
    function pin(div, i) { div.classList.add(hs[i].type === 'scene' ? 'hs-pin-scene' : 'hs-pin-info'); div.textContent = String(i + 1); }
    function build() {
      var url = panoUrl();
      if (viewer) { try { viewer.destroy(); } catch (e) { } viewer = null; }
      stage.innerHTML = '';
      if (!url) { stage.innerHTML = '<p class="hs-empty">' + (ed.querySelector('.hs-empty') ? '' : '') + '—</p>'; return; }
      loadCss(A.assets + 'vendor/pannellum/pannellum.css');
      loadScript(A.assets + 'vendor/pannellum/pannellum.js').then(function () {
        var c = { type: 'equirectangular', panorama: url, autoLoad: true, showControls: true, compass: false, yaw: +yawIn.value || 0, pitch: +pitchIn.value || 0, hfov: +hfovIn.value || 100, hotSpots: [] };
        if (val('type') === 'partial') {
          var info = panoInfo(), haov = +val('haov') || 360, vaov = +val('vaov') || (info ? Math.min(180, haov * info.h / info.w) : 90);
          c.haov = haov; c.vaov = vaov; c.vOffset = +val('voffset') || 0;
        }
        viewer = w.pannellum.viewer(stage, c);
        viewer.on('load', drawSpots);
        var rd = function () { if (viewer) readout.textContent = 'yaw ' + viewer.getYaw().toFixed(1) + '°  pitch ' + viewer.getPitch().toFixed(1) + '°  fov ' + viewer.getHfov().toFixed(0) + '°'; };
        viewer.on('animatefinished', rd); viewer.on('mouseup', rd); viewer.on('touchend', rd);
      });
    }
    function drawSpots() {
      if (!viewer) return;
      (viewer.__ids || []).forEach(function (id) { try { viewer.removeHotSpot(id); } catch (e) { } });
      viewer.__ids = [];
      hs.forEach(function (h, i) { var id = 'h' + i + '-' + Date.now(); viewer.__ids.push(id); viewer.addHotSpot({ id: id, pitch: h.pitch, yaw: h.yaw, cssClass: 'x', createTooltipFunc: function (div) { pin(div, i); } }); });
    }
    function renderList() {
      list.innerHTML = '';
      hs.forEach(function (h, i) {
        var row = d.createElement('div'); row.className = 'hs-item';
        var sel = '<select class="inp" data-k="type"><option value="scene"' + (h.type === 'scene' ? ' selected' : '') + '>' + T.hsScene + '</option><option value="info"' + (h.type === 'info' ? ' selected' : '') + '>' + T.hsInfo + '</option></select>';
        var tgt = '<select class="inp" data-k="target"' + (h.type === 'scene' ? '' : ' hidden') + '><option value="">' + T.hsTarget + '</option>' + (cfg.scenes || []).map(function (s) { return '<option value="' + s.slug + '"' + (h.target === s.slug ? ' selected' : '') + '>' + s.title + '</option>'; }).join('') + '</select>';
        var texts = (cfg.langs || ['fa']).map(function (l) { return '<input class="inp" data-lang-input="' + l + '" data-t="' + l + '" placeholder="' + T.hsText + ' (' + l + ')" value="' + ((h.text || {})[l] || '').replace(/"/g, '&quot;') + '"' + (l !== (cfg.langs || ['fa'])[0] ? ' hidden' : '') + '>'; }).join('');
        row.innerHTML = '<span class="hs-num">' + (i + 1) + '</span><div class="hs-texts">' + sel + tgt + '</div><div class="hs-texts">' + texts + '</div>' +
          '<button type="button" class="btn btn-sm" data-look title="' + T.hsLook + '">◎</button><button type="button" class="btn btn-sm btn-ghost" data-del title="' + T.hsRemove + '">✕</button>';
        on($('[data-k="type"]', row), 'change', function (e) { h.type = e.target.value; $('[data-k="target"]', row).hidden = h.type !== 'scene'; save(); drawSpots(); });
        on($('[data-k="target"]', row), 'change', function (e) { h.target = e.target.value; save(); });
        $$('[data-t]', row).forEach(function (inp) { on(inp, 'input', function () { h.text = h.text || {}; h.text[inp.getAttribute('data-t')] = inp.value; save(); }); });
        on($('[data-look]', row), 'click', function () { if (viewer) viewer.lookAt(h.pitch, h.yaw, viewer.getHfov(), 600); });
        on($('[data-del]', row), 'click', function () { hs.splice(i, 1); save(); renderList(); drawSpots(); });
        list.appendChild(row);
      });
      var cur = $('[data-lang-switch] [aria-pressed="true"]'); if (cur) setLang(cur.getAttribute('data-lang'));
    }
    $$('[data-hs-add]', ed).forEach(function (b) {
      on(b, 'click', function () {
        if (!viewer) return;
        hs.push({ type: b.getAttribute('data-hs-add'), yaw: +viewer.getYaw().toFixed(2), pitch: +viewer.getPitch().toFixed(2), text: {}, target: '' });
        save(); renderList(); drawSpots();
      });
    });
    on($('[data-hs-setview]', ed), 'click', function () {
      if (!viewer) return;
      yawIn.value = viewer.getYaw().toFixed(2); pitchIn.value = viewer.getPitch().toFixed(2); hfovIn.value = viewer.getHfov().toFixed(2);
      dirty = true; toast(T.hsViewSet);
    });
    renderList();
    var tab = ed.closest('[data-tab]');
    var start = function () { build(); };
    if (tab && tab.hidden) { var o = new MutationObserver(function () { if (!tab.hidden && !viewer) start(); }); o.observe(tab, { attributes: true }); } else start();
    on(d, 'media:change', function (e) { if (e.target === panoField() && tab && !tab.hidden) build(); else if (e.target === panoField()) viewer = null; });
  });

  // ---------- prices page: bulk change ----------
  var grid = $('[data-price-grid]');
  if (grid) {
    var inputs = $$('[data-price]', grid);
    inputs.forEach(function (i) { i.setAttribute('data-orig', i.value); on(i, 'input', function () { i.classList.toggle('is-dirty', i.value !== i.getAttribute('data-orig')); }); });
    $$('[data-bulk-apply]', grid).forEach(function (b) {
      on(b, 'click', function () {
        var pct = parseFloat(digitsToLatin($('[data-bulk-pct]', grid).value)) || 0, rnd = +$('[data-bulk-round]', grid).value || 1;
        var k = b.getAttribute('data-bulk-apply') === 'up' ? 1 + pct / 100 : 1 - pct / 100;
        inputs.forEach(function (i) {
          var v = +digitsToLatin(i.value).replace(/\D/g, ''); if (!v) return;
          var n = Math.max(rnd, Math.round(v * k / rnd) * rnd);
          i.value = n.toLocaleString('en-US'); i.classList.toggle('is-dirty', i.value !== i.getAttribute('data-orig'));
        });
      });
    });
    on($('[data-bulk-reset]', grid), 'click', function () { inputs.forEach(function (i) { i.value = i.getAttribute('data-orig'); i.classList.remove('is-dirty'); }); });
  }

  // ---------- dashboard chart tooltip ----------
  $$('[data-chart]').forEach(function (c) {
    var tip = $('[data-chart-tip]', c);
    $$('.bar', c).forEach(function (b) {
      on(b, 'mouseenter', function () {
        var r = b.getBoundingClientRect(), cr = c.getBoundingClientRect();
        tip.textContent = b.getAttribute('data-tip'); tip.hidden = false;
        tip.style.left = (r.left - cr.left + r.width / 2) + 'px'; tip.style.top = (b.querySelector('.col') ? b.querySelector('.col').getBoundingClientRect().top - cr.top : r.bottom - cr.top - 20) + 'px';
      });
      on(b, 'mouseleave', function () { tip.hidden = true; });
    });
  });
})();
