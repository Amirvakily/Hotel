/* Venus Hotel – public site script (no dependencies). */
(function () {
  'use strict';
  var V = window.VENUS || { i18n: {}, assets: '/assets/', lang: 'fa', rtl: true };
  var d = document, w = window, root = d.documentElement, body = d.body;
  var reduce = w.matchMedia && w.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var $ = function (s, c) { return (c || d).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || d).querySelectorAll(s)); };
  var on = function (el, ev, fn, opt) { if (el) el.addEventListener(ev, fn, opt || false); };

  // ---------- helpers ----------
  function digits(s) {
    s = String(s);
    if (!V.digits) return s;
    var map = Array.from(V.digits);
    return s.replace(/[0-9]/g, function (c) { return map[+c]; });
  }
  function plural(tpl, n) {
    tpl = tpl || '';
    if (tpl.indexOf('|') > -1) {
      var f = tpl.split('|'), i = 0;
      if (V.lang === 'en') i = n === 1 ? 0 : 1;
      else if (V.lang === 'ar') i = n === 1 ? 0 : (n === 2 ? 1 : (n >= 3 && n <= 10 ? 2 : 3));
      tpl = f[Math.min(i, f.length - 1)];
    }
    return tpl.replace(/\{n\}/g, digits(n));
  }
  function store(k, v) {
    try { if (v === undefined) return w.localStorage.getItem(k); w.localStorage.setItem(k, v); } catch (e) { return null; }
  }
  function loadCss(href) {
    if ($('link[href="' + href + '"]')) return;
    var l = d.createElement('link'); l.rel = 'stylesheet'; l.href = href; d.head.appendChild(l);
  }
  var scripts = {};
  function loadScript(src) {
    if (!scripts[src]) {
      scripts[src] = new Promise(function (res, rej) {
        var s = d.createElement('script'); s.src = src; s.async = true;
        s.onload = res; s.onerror = function () { delete scripts[src]; rej(new Error('load ' + src)); };
        d.head.appendChild(s);
      });
    }
    return scripts[src];
  }
  function whenNear(el, fn, margin) {
    if (!el) return;
    if (!('IntersectionObserver' in w)) { fn(); return; }
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) { io.disconnect(); fn(); } });
    }, { rootMargin: margin || '300px' });
    io.observe(el);
  }

  // ---------- preloader (home, first visit in this tab) ----------
  (function () {
    if (!V.preloader || reduce || !body.classList.contains('page-home')) return;
    var seen = null;
    try { seen = w.sessionStorage.getItem('venus-seen'); w.sessionStorage.setItem('venus-seen', '1'); } catch (e) { seen = '1'; }
    if (seen) return;
    var p = d.createElement('div');
    p.className = 'preloader';
    p.setAttribute('aria-hidden', 'true');
    p.innerHTML = '<svg viewBox="0 0 48 48"><g fill="none" stroke="currentColor" stroke-width="1.2"><rect x="11.5" y="11.5" width="25" height="25"/><rect x="11.5" y="11.5" width="25" height="25" transform="rotate(45 24 24)"/><circle cx="24" cy="24" r="7"/></g></svg>';
    body.appendChild(p);
    body.classList.add('is-preloading');
    var done = false;
    function finish() {
      if (done) return; done = true;
      p.classList.add('is-done');
      body.classList.remove('is-preloading');
      setTimeout(function () { p.remove(); }, 800);
    }
    on(w, 'load', function () { setTimeout(finish, 350); });
    setTimeout(finish, 1600);
  })();

  // ---------- header ----------
  var header = $('[data-header]');
  (function () {
    if (!header) return;
    var last = -1, ticking = false;
    function update() {
      var y = w.scrollY;
      if (last < 0) last = y;
      header.classList.toggle('is-scrolled', y > 40);
      var hide = y > 220 && y > last + 4 && !body.classList.contains('menu-open');
      var show = y < last - 4 || y < 220;
      if (hide) { header.classList.add('is-hidden'); body.classList.add('header-hidden'); }
      else if (show) { header.classList.remove('is-hidden'); body.classList.remove('header-hidden'); }
      last = y; ticking = false;
    }
    on(w, 'scroll', function () { if (!ticking) { ticking = true; w.requestAnimationFrame(update); } }, { passive: true });
    w.requestAnimationFrame(update);
  })();

  // ---------- mobile menu ----------
  (function () {
    var btn = $('[data-burger]'), menu = $('[data-mobile-menu]');
    if (!btn || !menu) return;
    function set(open) {
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      body.classList.toggle('menu-open', open);
      if (open) {
        menu.hidden = false;
        w.requestAnimationFrame(function () { menu.classList.add('is-open'); });
        header && header.classList.remove('is-hidden');
      } else {
        menu.classList.remove('is-open');
        menu.hidden = true;
      }
    }
    on(btn, 'click', function () { set(btn.getAttribute('aria-expanded') !== 'true'); });
    on(d, 'keydown', function (e) { if (e.key === 'Escape' && body.classList.contains('menu-open')) { set(false); btn.focus(); } });
    $$('a', menu).forEach(function (a) { on(a, 'click', function () { set(false); }); });
  })();

  // ---------- dropdowns (language) ----------
  $$('[data-dropdown]').forEach(function (dd) {
    var b = $('[data-dropdown-btn]', dd), m = $('[data-dropdown-menu]', dd);
    if (!b || !m) return;
    function close() { m.hidden = true; b.setAttribute('aria-expanded', 'false'); }
    on(b, 'click', function (e) { e.stopPropagation(); var open = m.hidden; m.hidden = !open; b.setAttribute('aria-expanded', open ? 'true' : 'false'); });
    on(d, 'click', function (e) { if (!dd.contains(e.target)) close(); });
    on(d, 'keydown', function (e) { if (e.key === 'Escape') close(); });
  });

  // ---------- reveal on scroll ----------
  (function () {
    var els = $$('[data-reveal]');
    if (!els.length) return;
    if (reduce || !('IntersectionObserver' in w) || body.classList.contains('no-anim')) { els.forEach(function (e) { e.classList.add('is-in'); }); return; }
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });
    // Anything already on the first screen (even at its bottom edge) comes in straight away
    function inView() { $$('[data-reveal]:not(.is-in)').forEach(function (e) { var r = e.getBoundingClientRect(); if (r.top < w.innerHeight && r.bottom > 0) { e.classList.add('is-in'); io.unobserve(e); } }); }
    els.forEach(function (e) { io.observe(e); });
    w.requestAnimationFrame(inView);
    // Never leave content hidden if something goes wrong
    setTimeout(inView, 2000);
  })();

  // ---------- hero slider ----------
  (function () {
    var hero = $('[data-hero]');
    if (!hero) return;
    var slides = $$('[data-slide]', hero), dots = $$('[data-dot]', hero), cap = $('[data-hero-caption]', hero);
    if (slides.length < 2) return;
    var i = 0, ms = 6500, timer = null;
    hero.style.setProperty('--slide-ms', ms + 'ms');
    function go(n) {
      slides[i].classList.remove('is-active');
      if (dots[i]) { dots[i].classList.remove('is-active'); dots[i].setAttribute('aria-selected', 'false'); }
      dots.forEach(function (dt, k) { dt.classList.toggle('is-done', k < n); });
      i = (n + slides.length) % slides.length;
      slides[i].classList.add('is-active');
      // Restart the zoom animation
      var img = $('img', slides[i]); if (img) { img.style.animation = 'none'; void img.offsetWidth; img.style.animation = ''; }
      if (dots[i]) { dots[i].classList.remove('is-active'); void dots[i].offsetWidth; dots[i].classList.add('is-active'); dots[i].setAttribute('aria-selected', 'true'); }
      if (cap) cap.textContent = slides[i].getAttribute('data-caption') || '';
      schedule();
    }
    function schedule() { clearTimeout(timer); if (!reduce) timer = setTimeout(function () { go(i + 1); }, ms); }
    dots.forEach(function (dt, k) { on(dt, 'click', function () { go(k); }); });
    on(d, 'visibilitychange', function () { if (d.hidden) clearTimeout(timer); else schedule(); });
    // Load the other slides only after the first one is shown
    on(w, 'load', function () { slides.slice(1).forEach(function (s) { var im = $('img', s); if (im) im.loading = 'eager'; }); });
    schedule();
  })();

  // ---------- parallax ----------
  (function () {
    var els = $$('[data-parallax]');
    if (!els.length || reduce || w.innerWidth < 860) return;
    var ticking = false;
    function update() {
      var vh = w.innerHeight;
      els.forEach(function (el) {
        var r = el.parentElement.getBoundingClientRect();
        if (r.bottom < 0 || r.top > vh) return;
        var p = (r.top + r.height / 2 - vh / 2) / vh;
        el.style.transform = 'translate3d(0,' + (p * -60).toFixed(1) + 'px,0)';
      });
      ticking = false;
    }
    on(w, 'scroll', function () { if (!ticking) { ticking = true; w.requestAnimationFrame(update); } }, { passive: true });
    w.requestAnimationFrame(update);
  })();

  // ---------- counters ----------
  (function () {
    var els = $$('[data-count]');
    if (!els.length || reduce || !('IntersectionObserver' in w)) return;
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        if (!e.isIntersecting) return;
        io.unobserve(e.target);
        var el = e.target, end = parseInt(el.getAttribute('data-count'), 10), orig = el.textContent;
        if (!end || end > 100000 || /[^0-9۰-۹٠-٩\s]/.test(orig)) return;
        var t0 = null, dur = 1400;
        function step(t) {
          if (!t0) t0 = t;
          var k = Math.min(1, (t - t0) / dur), v = Math.round(end * (1 - Math.pow(1 - k, 3)));
          el.textContent = digits(v);
          if (k < 1) w.requestAnimationFrame(step); else el.textContent = orig;
        }
        w.requestAnimationFrame(step);
      });
    }, { threshold: 0.6 });
    els.forEach(function (e) { io.observe(e); });
  })();

  // ---------- room prices by guest count ----------
  function json(el, attr) { try { return JSON.parse(el.getAttribute(attr) || '{}'); } catch (e) { return {}; } }
  function updateRoom(el, g) {
    var min = +el.getAttribute('data-min') || 1, max = +el.getAttribute('data-max') || 1;
    var prices = json(el, 'data-prices'), sec = json(el, 'data-sec');
    var box = $('[data-price-box]', el), label = $('[data-price-label]', el), price = $('[data-price]', el), ps = $('[data-price-sec]', el);
    var unfit = $('[data-unfit]', el), from = el.getAttribute('data-from') || '';
    var empty = V.i18n.empty || '';
    var fits = !g || (g >= min && g <= max);
    el.classList.toggle('is-unfit', !!g && !fits);
    el.classList.toggle('is-fit', !!g && fits);
    if (unfit) { unfit.hidden = fits; var sp = $('span', unfit); if (sp) sp.textContent = plural(V.i18n.unfit, g); }
    if (!box) return fits;
    var p, s, l;
    if (!g) { p = from || empty; s = from ? secFor(prices, sec, from) : ''; l = from ? V.i18n.from : ''; }
    else if (!fits) { p = ''; s = ''; l = ''; }
    else { p = prices[g] || empty; s = prices[g] ? (sec[g] || '') : ''; l = prices[g] ? plural(V.i18n.forGuests, g) : ''; }
    var hide = (!p && !l) || (!g && !from && !empty) || (g && !fits);
    box.hidden = hide;
    if (!hide && price && price.textContent !== p) {
      box.classList.add('is-changing');
      setTimeout(function () { price.textContent = p; box.classList.remove('is-changing'); }, 140);
    }
    if (label) label.textContent = l;
    if (ps) ps.textContent = s || '';
    var px = $('[data-price-extra]', el), ex = json(el, 'data-extra');
    if (px) { var n = g && fits && prices[g] ? ex[g] : 0; px.hidden = !n; px.textContent = n ? plural(V.i18n.withExtra, n) : ''; }
    return fits;
  }
  function secFor(prices, sec, from) {
    // the second-currency text that belongs to the "from" price
    var key = Object.keys(prices).filter(function (k) { return prices[k] === from; })[0];
    return key ? (sec[key] || '') : '';
  }

  function kidsCount() { return Math.max(0, Math.min(6, parseInt(store('venus-kids') || '0', 10) || 0)); }
  (function kids() {
    var boxes = $$('[data-kids]');
    if (!boxes.length) return;
    function show() { var k = kidsCount(); boxes.forEach(function (b) { var o = $('[data-kids-value]', b); if (o) o.textContent = digits(k); }); }
    boxes.forEach(function (b) {
      $$('[data-kids-step]', b).forEach(function (btn) {
        on(btn, 'click', function () {
          var k = Math.max(0, Math.min(6, kidsCount() + (+btn.getAttribute('data-kids-step'))));
          store('venus-kids', String(k)); show(); d.dispatchEvent(new Event('venus:kids'));
        });
      });
    });
    show();
  })();

  (function roomsPage() {
    var page = $('[data-rooms-page]');
    if (!page) return;
    var picker = $('[data-guest-picker]', page), status = $('[data-guest-status]', page);
    var cards = $$('[data-room]', page);
    var current = 0;
    var groupBtn = $('[data-group]', picker), groupNote = $('[data-group-note]', page);
    var groupN = groupBtn ? +groupBtn.getAttribute('data-guests') : 999;
    function apply(g) {
      current = g;
      $$('button', picker).forEach(function (b) { b.setAttribute('aria-checked', (+b.getAttribute('data-guests') === g) ? 'true' : 'false'); });
      var isGroup = g >= groupN;
      if (groupNote) groupNote.hidden = !isGroup;
      if (isGroup) { cards.forEach(function (c) { updateRoom(c, 0); }); if (status) status.textContent = ''; store('venus-guests', String(g)); return; }
      var fitCount = 0;
      cards.forEach(function (c) { if (updateRoom(c, g) && c.classList.contains('room-card')) fitCount++; });
      $$('.price-table [data-col]', page).forEach(function (c) { c.classList.toggle('is-col', +c.getAttribute('data-col') === g); });
      if (status) status.textContent = g ? plural(V.i18n.guestsN, g) + ' · ' + digits(fitCount) + ' ✓' : '';
      store('venus-guests', String(g));
    }
    $$('button', picker).forEach(function (b) { on(b, 'click', function () { apply(+b.getAttribute('data-guests')); }); });
    var fromUrl = (w.location.hash.match(/guests=(\d+)/) || w.location.search.match(/guests=(\d+)/) || [])[1];
    var saved = parseInt(fromUrl || store('venus-guests') || '0', 10);
    var maxBtn = groupN;
    apply(saved > 0 && saved <= maxBtn ? saved : 0);

    // view tabs
    var tabs = $$('[data-view-btn]', page), views = $$('[data-view]', page);
    function show(name) {
      tabs.forEach(function (t) { t.setAttribute('aria-selected', t.getAttribute('data-view-btn') === name ? 'true' : 'false'); });
      views.forEach(function (v) { v.hidden = v.getAttribute('data-view') !== name; });
      if (name !== 'cards') { var tb = $('[data-toolbar]', page); if (tb && tb.getBoundingClientRect().top < 0) tb.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth' }); }
    }
    tabs.forEach(function (t) { on(t, 'click', function () { show(t.getAttribute('data-view-btn')); }); });

    // compare filter by bed count
    var cf = $('[data-compare-filter]', page);
    function filterBeds(n) {
      if (cf) $$('[data-filter-beds]', cf).forEach(function (b) { b.classList.toggle('is-active', +b.getAttribute('data-filter-beds') === n); });
      $$('.compare-table [data-beds]', page).forEach(function (c) { c.hidden = !!n && +c.getAttribute('data-beds') !== n; });
    }
    if (cf) $$('[data-filter-beds]', cf).forEach(function (b) { on(b, 'click', function () { filterBeds(+b.getAttribute('data-filter-beds')); }); });
    $$('[data-compare-beds]', page).forEach(function (b) {
      on(b, 'click', function () { filterBeds(+b.getAttribute('data-compare-beds')); show('compare'); });
    });
  })();

  // price cards on room pages and home cards
  $$('[data-price-card]').forEach(function (card) {
    var picker = $('[data-guest-picker]', card), wa = $('[data-wa-link]', card.parentElement || card);
    var name = card.getAttribute('data-name') || '';
    function apply(g) {
      updateRoom(card, g);
      if (picker) $$('button', picker).forEach(function (b) { b.setAttribute('aria-checked', +b.getAttribute('data-guests') === g ? 'true' : 'false'); });
      $$('[data-price-row]', card).forEach(function (r) { r.classList.toggle('is-active', +r.getAttribute('data-price-row') === g); });
      cur = g;
      waUpdate();
    }
    var cur = 0;
    function waUpdate() {
      wa = wa || $('[data-wa-link]', card);
      if (wa && V.wa) {
        var kids = kidsCount();
        var text = (V.waText || '').replace('{room}', name).replace('{guests}', digits(cur));
        if (kids) text += ' ' + plural(V.i18n.waKids, kids);
        wa.href = 'https://wa.me/' + V.wa + '?text=' + encodeURIComponent(text);
      }
    }
    on(d, 'venus:kids', waUpdate);
    var max = +card.getAttribute('data-max') || 1, min = +card.getAttribute('data-min') || 1, def = +card.getAttribute('data-default') || max;
    var saved = parseInt(store('venus-guests') || '0', 10);
    if (picker) $$('button', picker).forEach(function (b) { on(b, 'click', function () { var g = +b.getAttribute('data-guests'); apply(g); store('venus-guests', String(g)); }); });
    apply(saved >= min && saved <= max ? saved : def);
  });

  // ---------- gallery filter ----------
  (function () {
    var bar = $('[data-gallery-filter]');
    if (!bar) return;
    var items = $$('[data-cat]');
    $$('[data-filter]', bar).forEach(function (b) {
      on(b, 'click', function () {
        var f = b.getAttribute('data-filter');
        $$('[data-filter]', bar).forEach(function (x) { x.classList.toggle('is-active', x === b); });
        items.forEach(function (it, k) {
          var show = !f || it.getAttribute('data-cat') === f;
          it.hidden = !show;
          if (show) { it.style.animationDelay = (Math.min(k, 12) * 30) + 'ms'; it.style.animationName = 'none'; void it.offsetWidth; it.style.animationName = ''; }
        });
        try { var u = new URL(w.location.href); if (f) u.searchParams.set('cat', f); else u.searchParams.delete('cat'); w.history.replaceState(null, '', u); } catch (e) { }
      });
    });
  })();

  // ---------- lightbox ----------
  (function () {
    var lb = $('[data-lightbox]');
    if (!lb) return;
    var img = $('[data-lb-img]', lb), cap = $('[data-lb-cap]', lb), cnt = $('[data-lb-count]', lb);
    var list = [], idx = 0, lastFocus = null;
    function visible(a) { return !a.hidden && !(a.closest('[hidden]') && !a.classList.contains('rg-hidden')); }
    function open(group, start) {
      list = group; idx = start; lastFocus = d.activeElement;
      lb.hidden = false; body.classList.add('lb-open'); body.style.overflow = 'hidden';
      show();
      $('[data-lb-close]', lb).focus();
    }
    function close() {
      lb.hidden = true; body.classList.remove('lb-open'); body.style.overflow = '';
      img.removeAttribute('src');
      if (lastFocus) lastFocus.focus();
    }
    function show() {
      var a = list[idx]; if (!a) return;
      img.classList.add('is-loading');
      var src = a.getAttribute('href');
      var pre = new Image();
      pre.onload = pre.onerror = function () { img.src = src; img.classList.remove('is-loading'); };
      pre.src = src;
      img.alt = a.getAttribute('data-caption') || '';
      cap.textContent = a.getAttribute('data-caption') || '';
      cnt.textContent = digits(idx + 1) + ' / ' + digits(list.length);
      var nxt = list[(idx + 1) % list.length]; if (nxt) { var p2 = new Image(); p2.src = nxt.getAttribute('href'); }
    }
    function move(k) { idx = (idx + k + list.length) % list.length; show(); }
    on(d, 'click', function (e) {
      var a = e.target.closest('[data-lb]');
      var opener = e.target.closest('[data-lb-open]');
      if (opener) {
        var g0 = opener.closest('[data-gallery]');
        var items0 = $$('[data-lb]', g0 || d);
        if (items0.length) { e.preventDefault(); open(items0, 0); }
        return;
      }
      if (!a) return;
      e.preventDefault();
      var g = a.closest('[data-gallery]');
      var items = $$('[data-lb]', g || d).filter(visible);
      open(items, Math.max(0, items.indexOf(a)));
    });
    on($('[data-lb-close]', lb), 'click', close);
    on($('[data-lb-prev]', lb), 'click', function () { move(V.rtl ? 1 : -1); });
    on($('[data-lb-next]', lb), 'click', function () { move(V.rtl ? -1 : 1); });
    on(lb, 'click', function (e) { if (e.target === lb) close(); });
    on(d, 'keydown', function (e) {
      if (lb.hidden) return;
      if (e.key === 'Escape') close();
      else if (e.key === 'ArrowLeft') move(V.rtl ? 1 : -1);
      else if (e.key === 'ArrowRight') move(V.rtl ? -1 : 1);
      else if (e.key === 'Tab') { // keep focus inside
        var f = $$('button', lb); var i = f.indexOf(d.activeElement);
        if (e.shiftKey && i <= 0) { e.preventDefault(); f[f.length - 1].focus(); }
        else if (!e.shiftKey && i === f.length - 1) { e.preventDefault(); f[0].focus(); }
      }
    });
    var x0 = null;
    on(lb, 'touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
    on(lb, 'touchend', function (e) {
      if (x0 === null) return;
      var dx = e.changedTouches[0].clientX - x0; x0 = null;
      if (Math.abs(dx) > 50) move((dx < 0 ? 1 : -1) * (V.rtl ? -1 : 1));
    });
  })();

  // ---------- map (Leaflet, loaded only when needed) ----------
  $$('[data-map]').forEach(function (el) {
    whenNear(el, function () {
      loadCss(V.assets + 'vendor/leaflet/leaflet.css');
      loadScript(V.assets + 'vendor/leaflet/leaflet.js').then(function () {
        var L = w.L, lat = +el.getAttribute('data-lat'), lng = +el.getAttribute('data-lng'), z = +el.getAttribute('data-zoom') || 15;
        el.innerHTML = '';
        var map = L.map(el, { scrollWheelZoom: false, tap: true }).setView([lat, lng], z);
        L.tileLayer(el.getAttribute('data-tiles') || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>'
        }).addTo(map);
        var pin = L.divIcon({ className: 'venus-pin', html: '<svg viewBox="0 0 46 46"><path d="M23 44s15-14.5 15-26A15 15 0 0 0 8 18c0 11.5 15 26 15 26z" fill="#0b1f35" stroke="#c6a15b" stroke-width="1.5"/><g fill="none" stroke="#c6a15b" stroke-width="1.2"><rect x="17" y="12" width="12" height="12"/><rect x="17" y="12" width="12" height="12" transform="rotate(45 23 18)"/></g></svg>', iconSize: [46, 46] });
        L.marker([lat, lng], { icon: pin, title: el.getAttribute('data-title') || '' }).addTo(map).bindPopup('<b>' + (el.getAttribute('data-title') || '') + '</b>');
        var pts = [[lat, lng]];
        var ms = [];
        try { ms = JSON.parse(el.getAttribute('data-markers') || '[]') || []; } catch (e) { ms = []; }
        ms.forEach(function (m) {
          var dot = L.divIcon({ className: 'venus-dot', iconSize: [14, 14] });
          L.marker([m.lat, m.lng], { icon: dot, title: m.title }).addTo(map).bindTooltip(m.title, { direction: 'top', offset: [0, -8] });
          pts.push([m.lat, m.lng]);
        });
        if (pts.length > 1) map.fitBounds(pts, { padding: [40, 40], maxZoom: 15 });
        on(el, 'click', function () { map.scrollWheelZoom.enable(); });
        on(el, 'mouseleave', function () { map.scrollWheelZoom.disable(); });
      }).catch(function () {
        var ph = $('.map-placeholder span', el); if (ph) ph.textContent = '—';
      });
    });
  });

  // ---------- 360° panoramas (Pannellum) ----------
  function loadPannellum() {
    loadCss(V.assets + 'vendor/pannellum/pannellum.css');
    return loadScript(V.assets + 'vendor/pannellum/pannellum.js');
  }
  function maxTexture() {
    try { var c = d.createElement('canvas'), g = c.getContext('webgl') || c.getContext('experimental-webgl'); return g ? g.getParameter(g.MAX_TEXTURE_SIZE) : 0; } catch (e) { return 0; }
  }
  function tooltip(div, text) {
    if (!text) return;
    var s = d.createElement('span'); s.className = 'hs-label'; s.textContent = text; div.appendChild(s);
  }
  function buildConfig(raw) {
    var small = w.innerWidth < 900 || maxTexture() < 8192;
    var scenes = {};
    Object.keys(raw.scenes || {}).forEach(function (id) {
      var s = raw.scenes[id], c = {};
      Object.keys(s).forEach(function (k) { if (['panoramaMobile', 'thumb', 'preview'].indexOf(k) < 0) c[k] = s[k]; });
      if (small && s.panoramaMobile) c.panorama = s.panoramaMobile;
      c.hotSpots = (s.hotSpots || []).map(function (h) {
        var o = { pitch: h.pitch, yaw: h.yaw, type: h.type, cssClass: h.type === 'scene' ? 'hs-scene' : 'hs-info', createTooltipFunc: tooltip, createTooltipArgs: h.text };
        if (h.type === 'scene') { o.sceneId = h.sceneId; if (h.targetYaw !== undefined) o.targetYaw = h.targetYaw; }
        return o;
      });
      scenes[id] = c;
    });
    return {
      default: { firstScene: raw.default.firstScene, sceneFadeDuration: 900, autoLoad: true, showControls: false, compass: false, mouseZoom: true, keyboardZoom: true, friction: 0.15, strings: { loadingLabel: V.i18n.loading } },
      scenes: scenes
    };
  }

  (function tourPage() {
    var stage = $('[data-tour]');
    if (!stage) return;
    var raw = json(stage, 'data-config'), view = $('[data-pano-view]', stage);
    var titleEl = $('[data-tour-title]', stage), loading = $('[data-tour-loading]', stage), hint = $('[data-tour-hint]', stage);
    var rail = $$('[data-scene]');
    var params = new URLSearchParams(w.location.search);
    if (params.get('scene') && raw.scenes[params.get('scene')]) raw.default.firstScene = params.get('scene');
    loadPannellum().then(function () {
      var viewer = w.pannellum.viewer(view, buildConfig(raw));
      function mark(id) {
        rail.forEach(function (b) { b.setAttribute('aria-selected', b.getAttribute('data-scene') === id ? 'true' : 'false'); });
        var sc = raw.scenes[id]; if (titleEl && sc) titleEl.textContent = sc.title || '';
        var act = rail.filter(function (b) { return b.getAttribute('data-scene') === id; })[0];
        var box = act && act.parentElement;
        if (box && box.scrollTo) box.scrollTo({ left: act.offsetLeft - box.clientWidth / 2 + act.clientWidth / 2, behavior: reduce ? 'auto' : 'smooth' });
        try { var u = new URL(w.location.href); u.searchParams.set('scene', id); w.history.replaceState(null, '', u); } catch (e) { }
      }
      viewer.on('load', function () { loading && loading.classList.add('is-done'); mark(viewer.getScene()); });
      viewer.on('scenechange', function (id) { mark(id); });
      viewer.on('error', function () { if (loading) { loading.innerHTML = V.i18n.tourError || 'Error'; } });
      ['mousedown', 'touchstart'].forEach(function (ev) { viewer.on(ev, function () { hint && hint.classList.add('is-gone'); }); });
      rail.forEach(function (b) { on(b, 'click', function () { viewer.loadScene(b.getAttribute('data-scene')); }); });
      $$('[data-tour-zoom]', stage).forEach(function (b) {
        on(b, 'click', function () { viewer.setHfov(viewer.getHfov() + (b.getAttribute('data-tour-zoom') === 'in' ? -15 : 15)); });
      });
      var rot = $('[data-tour-rotate]', stage);
      on(rot, 'click', function () {
        var p = rot.getAttribute('aria-pressed') === 'true';
        if (p) viewer.stopAutoRotate(); else viewer.startAutoRotate(-3);
        rot.setAttribute('aria-pressed', p ? 'false' : 'true');
      });
      on($('[data-tour-fs]', stage), 'click', function () {
        if (d.fullscreenEnabled || d.webkitFullscreenEnabled) viewer.toggleFullscreen();
        else { stage.classList.toggle('is-fake-fs'); viewer.resize && viewer.resize(); }
      });
      var gyro = $('[data-tour-gyro]', stage);
      if (gyro && viewer.isOrientationSupported && viewer.isOrientationSupported()) {
        gyro.hidden = false;
        on(gyro, 'click', function () {
          var p = gyro.getAttribute('aria-pressed') === 'true';
          if (p) viewer.stopOrientation(); else viewer.startOrientation();
          gyro.setAttribute('aria-pressed', p ? 'false' : 'true');
        });
      }
      setTimeout(function () { hint && hint.classList.add('is-gone'); }, 6000);
    }).catch(function () { if (loading) loading.textContent = V.i18n.tourError || 'Error'; });
  })();

  $$('[data-pano]').forEach(function (box) {
    var btn = $('[data-pano-start]', box);
    on(btn, 'click', function () {
      btn.disabled = true; btn.textContent = V.i18n.loading;
      loadPannellum().then(function () {
        box.classList.add('is-live');
        w.pannellum.viewer($('[data-pano-view]', box), buildConfig(json(box, 'data-config')));
      }).catch(function () { btn.textContent = V.i18n.tourError || 'Error'; });
    });
  });

  // external 3D embeds (Matterport, Kuula, Polycam...) load only on request
  $$('[data-embed]').forEach(function (box) {
    var b = $('[data-embed-load]', box);
    on(b, 'click', function () {
      var src = box.getAttribute('data-embed') || '';
      if (!/^https:\/\//i.test(src)) return;
      var f = d.createElement('iframe');
      f.src = src; f.allow = 'fullscreen; xr-spatial-tracking; gyroscope; accelerometer'; f.allowFullscreen = true; f.loading = 'lazy';
      f.setAttribute('referrerpolicy', 'no-referrer-when-downgrade');
      box.innerHTML = ''; box.appendChild(f);
    });
  });

  // ---------- contact form: friendly checks before sending ----------
  (function () {
    var f = $('[data-contact-form]');
    if (!f) return;
    on(f, 'submit', function (e) {
      var bad = null;
      $$('[required]', f).forEach(function (i) {
        var ok = i.value.trim().length >= (+i.getAttribute('minlength') || 1);
        i.setAttribute('aria-invalid', ok ? 'false' : 'true');
        if (!ok && !bad) bad = i;
      });
      var ph = f.elements.phone, em = f.elements.email;
      if (ph && em && !ph.value.trim() && !em.value.trim()) { ph.setAttribute('aria-invalid', 'true'); bad = bad || ph; }
      if (bad) { e.preventDefault(); bad.focus(); return; }
      var btn = $('button[type="submit"]', f); if (btn) { btn.disabled = true; btn.style.opacity = '.7'; }
    });
  })();

  // ---------- hero guest bar ----------
  $$('[data-guest-bar]').forEach(function (form) {
    var input = form.querySelector('input[name="guests"]');
    var btns = $$('[data-guests]', form);
    var saved = parseInt(store('venus-guests') || '0', 10);
    function pick(g) {
      btns.forEach(function (b) { b.setAttribute('aria-checked', +b.getAttribute('data-guests') === g ? 'true' : 'false'); });
      if (input) input.value = g;
    }
    if (saved > 0 && saved <= btns.length) pick(saved);
    btns.forEach(function (b) { on(b, 'click', function () { pick(+b.getAttribute('data-guests')); }); });
    on(form, 'submit', function (e) {
      e.preventDefault();
      var g = input ? input.value : '';
      store('venus-guests', String(g));
      w.location.href = form.getAttribute('action') + '#guests=' + g;
    });
  });

  // ---------- carousels ----------
  $$('[data-carousel]').forEach(function (c) {
    var track = $('.carousel-track', c), prev = $('[data-carousel-prev]', c), next = $('[data-carousel-next]', c);
    if (!track) return;
    function step() { var f = track.firstElementChild; return f ? f.getBoundingClientRect().width + 18 : 300; }
    function state() {
      var max = track.scrollWidth - track.clientWidth;
      c.classList.toggle('is-static', max < 8);
      var pos = Math.abs(track.scrollLeft);
      if (prev) prev.disabled = pos < 4;
      if (next) next.disabled = pos > max - 4;
    }
    var dir = V.rtl ? -1 : 1;
    on(prev, 'click', function () { track.scrollBy({ left: -step() * dir, behavior: reduce ? 'auto' : 'smooth' }); });
    on(next, 'click', function () { track.scrollBy({ left: step() * dir, behavior: reduce ? 'auto' : 'smooth' }); });
    on(track, 'scroll', function () { w.requestAnimationFrame(state); }, { passive: true });
    on(w, 'resize', state);
    w.requestAnimationFrame(state);
  });

  root.classList.add('is-ready');
})();
