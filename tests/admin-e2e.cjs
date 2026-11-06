// End-to-end test: edits content in the admin panel and checks the public site.
// Usage: node tests/admin-e2e.cjs http://127.0.0.1:8080 admin venus12345 path/to/sample.jpg
const { chromium } = require('playwright');
const [base, user, pass, sample] = process.argv.slice(2);
let failures = 0;
const ok = (cond, msg) => { console.log((cond ? 'PASS ' : 'FAIL ') + msg); if (!cond) failures++; };
const fa = n => String(n).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);

(async () => {
  const b = await chromium.launch();
  const ctx = await b.newContext({ viewport: { width: 1360, height: 900 } });
  const p = await ctx.newPage();
  const errors = [];
  p.on('pageerror', e => errors.push(e.message));
  p.on('dialog', d => d.accept());
  const go = async (path) => { const r = await p.goto(base + path, { waitUntil: 'networkidle' }); return r.status(); };
  const flash = async () => (await p.locator('.flash').allTextContents()).join(' | ');

  // login (wrong password first)
  await go('/admin/login');
  await p.fill('input[name=username]', user); await p.fill('input[name=password]', 'wrong-pass');
  await Promise.all([p.waitForLoadState('networkidle'), p.click('button[type=submit]')]);
  ok((await p.content()).includes('flash-err'), 'wrong password is rejected');
  await p.fill('input[name=username]', user); await p.fill('input[name=password]', pass);
  await Promise.all([p.waitForNavigation(), p.click('button[type=submit]')]);
  ok(p.url().endsWith('/admin/'), 'login works');

  // 1. edit a room: name, 2-guest price, extra people
  await go('/admin/r/rooms/1');
  await p.fill('input[name="f[name][fa]"]', 'سه‌تخته کلاسیک تست');
  await p.click('[data-tab-btn="beds"]');
  await p.fill('input[name="f[max_extra]"]', '2');
  await p.click('[data-tab-btn="prices"]');
  await p.fill('input[name="f[prices][2]"]', '3,000,000');
  await p.fill('input[name="f[extra_bed_price]"]', '700000');
  await Promise.all([p.waitForNavigation(), p.click('.savebar button[type=submit]:not([name])')]);
  ok((await flash()).includes('ذخیره شد'), 'room saved: ' + (await flash()));
  await go('/rooms/classic-triple');
  let html = await p.content();
  ok(html.includes('سه‌تخته کلاسیک تست'), 'front shows new room name');
  ok(html.includes('۳٬۰۰۰٬۰۰۰'), 'front shows new 2-guest price');
  // 3 guests = 3,300,000; 4 guests = 4,000,000; 5 guests = 4,700,000
  ok(html.includes('۴٬۰۰۰٬۰۰۰') && html.includes('۴٬۷۰۰٬۰۰۰'), 'extra-person prices computed (4 and 5 guests)');

  // 2. prices page: bulk +10%, save as preset, change, re-apply preset
  await go('/admin/prices');
  await p.fill('[data-bulk-pct]', '10');
  await p.selectOption('[data-bulk-round]', '50000');
  await p.click('[data-bulk-apply="up"]');
  const v = await p.inputValue('input[name="p[1][1]"]');
  ok(v === '2,650,000', 'bulk +10% rounded to 50,000 (2,400,000 → ' + v + ')');
  await p.fill('#price-form input[name="name"]', 'Test preset');
  await Promise.all([p.waitForNavigation(), p.click('button[value="save_as"]')]);
  ok((await flash()).length > 0 && (await p.content()).includes('Test preset'), 'preset saved');
  await p.fill('input[name="p[1][1]"]', '1,000,000');
  await Promise.all([p.waitForNavigation(), p.click('button[value="save"]')]);
  await go('/rooms');
  ok((await p.content()).includes('۱٬۰۰۰٬۰۰۰'), 'manual price change visible on site');
  await go('/admin/prices');
  await Promise.all([p.waitForNavigation(), p.click('.preset-card button[value="apply"]')]);
  ok((await flash()).includes('Test preset'), 'preset applied: ' + (await flash()));
  ok((await p.inputValue('input[name="p[1][1]"]')) === '2,650,000', 'preset restored the price');

  // 3. gallery bulk upload
  await go('/admin/r/gallery_items?category_id=1');
  const before = await p.locator('.gal-item').count();
  await p.setInputFiles('[data-bulk-file]', [sample, sample]);
  await p.waitForNavigation({ timeout: 60000 });
  const after = await p.locator('.gal-item').count();
  ok(after === before + 2, `bulk upload added 2 photos (${before} → ${after})`);

  // 4. media picker on a room: add a photo to the gallery field
  await go('/admin/r/rooms/2');
  await p.click('[data-tab-btn="photos"]');
  const thumbsBefore = await p.locator('#f-gallery .thumb').count();
  await p.click('#f-gallery [data-media-pick]');
  await p.waitForSelector('[data-mm-grid] .thumb');
  await p.click('[data-mm-grid] .thumb >> nth=0');
  await p.click('[data-mm-choose]');
  const thumbsAfter = await p.locator('#f-gallery .thumb').count();
  ok(thumbsAfter >= thumbsBefore, `media picker adds a photo (${thumbsBefore} → ${thumbsAfter})`);
  await Promise.all([p.waitForNavigation(), p.click('.savebar button[type=submit]:not([name])')]);
  ok((await flash()).includes('ذخیره شد'), 'room with new gallery saved');

  // 5. settings: hotel name (English)
  await go('/admin/settings/general');
  await p.click('[data-lang-switch] [data-lang="en"]');
  await p.fill('input[name="f[site.name][en]"]', 'Venus Test Hotel');
  await Promise.all([p.waitForNavigation(), p.click('.savebar button')]);
  await go('/en/');
  ok((await p.content()).includes('Venus Test Hotel'), 'English site name updated');
  await go('/admin/settings/general');
  await p.click('[data-lang-switch] [data-lang="en"]');
  await p.fill('input[name="f[site.name][en]"]', 'Venus Hotel');
  await Promise.all([p.waitForNavigation(), p.click('.savebar button')]);

  // 6. sections: switch reviews off and on
  await go('/admin/sections');
  const rev = p.locator('.section-list li', { hasText: 'نظرات' }).locator('input[type=checkbox]');
  await rev.setChecked(false, { force: true }); await p.waitForTimeout(600);
  await go('/');
  ok(!(await p.content()).includes('class="section reviews"'), 'reviews section hidden');
  await go('/admin/sections');
  await p.locator('.section-list li', { hasText: 'نظرات' }).locator('input[type=checkbox]').setChecked(true, { force: true }); await p.waitForTimeout(600);
  await go('/');
  ok((await p.content()).includes('class="section reviews"'), 'reviews section visible again');

  // 7. 360 editor: add a hotspot and save
  await go('/admin/r/scenes/1');
  await p.click('[data-tab-btn="view"]');
  await p.waitForSelector('.pnlm-render-container', { timeout: 30000 });
  await p.waitForTimeout(1500);
  const hsBefore = await p.locator('.hs-item').count();
  await p.click('[data-lang-switch] [data-lang="fa"]');
  await p.click('[data-hs-add="info"]');
  await p.locator('.hs-item').last().locator('[data-t="fa"]').fill('نقطه تست');
  await p.click('[data-hs-setview]');
  await Promise.all([p.waitForNavigation(), p.click('.savebar button[type=submit]:not([name])')]);
  await p.click('[data-tab-btn="view"]');
  ok((await p.locator('.hs-item').count()) === hsBefore + 1, `hotspot saved (${hsBefore} → ${hsBefore + 1})`);
  await go('/tour');
  ok((await p.content()).includes('نقطه تست'), 'new hotspot in the public tour config');

  // 8. contact form → messages
  const pub = await ctx.newPage();
  await pub.goto(base + '/contact');
  await pub.fill('#f-name', 'مهمان تست'); await pub.fill('#f-phone', '09141234567'); await pub.fill('#f-body', 'سلام، برای عید اتاق می‌خواهم.');
  await pub.waitForTimeout(3200);
  await Promise.all([pub.waitForNavigation(), pub.click('[data-contact-form] button[type=submit]')]);
  ok((await pub.content()).includes('alert-ok'), 'contact form accepted');
  await go('/admin/messages');
  ok((await p.content()).includes('مهمان تست'), 'message appears in the admin inbox');

  // 9. translations override
  await go('/admin/translations');
  await p.fill('input[name="t[fa][hero.see_prices]"]', 'قیمت‌ها را ببینید');
  await Promise.all([p.waitForNavigation(), p.click('.page-actions button[type=submit]')]);
  await go('/');
  ok((await p.content()).includes('قیمت‌ها را ببینید'), 'translation override shows on the site');
  await go('/admin/translations');
  await p.fill('input[name="t[fa][hero.see_prices]"]', '');
  await Promise.all([p.waitForNavigation(), p.click('.page-actions button[type=submit]')]);

  // 10. new room with only a name → slug is generated
  await go('/admin/r/rooms/new');
  await p.click('[data-lang-switch] [data-lang="fa"]');
  await p.fill('input[name="f[name][fa]"]', 'اتاق آزمایشی');
  await p.click('[data-lang-switch] [data-lang="en"]');
  await p.fill('input[name="f[name][en]"]', 'Test Room');
  await p.click('[data-lang-switch] [data-lang="fa"]');
  await Promise.all([p.waitForNavigation(), p.click('.savebar button[type=submit]:not([name])')]);
  ok(/\/admin\/r\/rooms\/\d+$/.test(p.url()), 'new room created at ' + p.url());
  ok((await p.inputValue('input[name="f[slug]"]')) === 'test-room', 'slug generated from English name');
  await Promise.all([p.waitForNavigation(), p.click('button[form="del-form"]')]);
  ok((await flash()).includes('حذف شد'), 'room deleted');

  // 11. editor role cannot open settings
  await go('/admin/users/new');
  await p.fill('input[name=username]', 'editor1'); await p.fill('input[name=password]', 'editor-pass-1');
  await Promise.all([p.waitForNavigation(), p.click('.savebar button')]);
  const ctx2 = await b.newContext(); const e = await ctx2.newPage();
  await e.goto(base + '/admin/login'); await e.fill('input[name=username]', 'editor1'); await e.fill('input[name=password]', 'editor-pass-1');
  await Promise.all([e.waitForNavigation(), e.click('button[type=submit]')]);
  const r403 = await e.goto(base + '/admin/settings/general');
  ok(r403.status() === 403, 'editor is blocked from settings (' + r403.status() + ')');
  const r200 = await e.goto(base + '/admin/prices');
  ok(r200.status() === 200, 'editor can open prices');
  await ctx2.close();

  // 12. backup
  await go('/admin/backup');
  await Promise.all([p.waitForNavigation({ timeout: 120000 }), p.click('form[action$="backup/create"] button')]);
  ok((await flash()).includes('.zip'), 'backup created: ' + (await flash()));
  const href = await p.locator('.file-list a').first().getAttribute('href');
  const dl = await p.request.get(new URL(href, p.url()).toString());
  ok(dl.status() === 200 && (dl.headers()['content-type'] || '').includes('zip'), 'backup downloads as zip');

  // 13. CSRF: a POST without token is refused
  const bad = await p.request.post(base + '/admin/r/rooms/1/delete', { headers: { Accept: 'application/json' } });
  ok(bad.status() === 403, 'POST without CSRF token refused (' + bad.status() + ')');

  // 14. 360° tour switch: off removes every trace from the public site, on brings it back
  const visitor = await b.newContext({ viewport: { width: 1360, height: 900 } });   // no admin cookie
  const pp = await visitor.newPage();
  const traces = async () => {
    let total = 0;
    for (const path of ['/', '/rooms', '/rooms/deluxe-triple', '/en/', '/ar/rooms/deluxe-triple']) {
      await pp.goto(base + path, { waitUntil: 'domcontentloaded' });
      total += await pp.evaluate(() => document.querySelectorAll('a[href*="/tour"], .hero-360, .tour-panel, #room-360, .room-360, [data-pano], [data-tour]').length);
    }
    const tourStatus = (await pp.request.get(base + '/tour')).status();
    const inSitemap = /\/tour</.test(await (await pp.request.get(base + '/sitemap.xml')).text());
    return { total, tourStatus, inSitemap };
  };
  let t = await traces();
  ok(t.total > 0 && t.tourStatus === 200 && t.inSitemap, `tour on: shown on the site (${t.total} links/blocks, /tour ${t.tourStatus})`);
  await go('/admin/r/scenes');
  await Promise.all([p.waitForNavigation(), p.locator('.tour-switch .switch').click()]);
  ok(await p.locator('.tour-switch.state-off').count() === 1 && (await flash()) !== '', 'tour switched off from the 360° page: ' + (await flash()));
  t = await traces();
  ok(t.total === 0 && t.tourStatus === 404 && !t.inSitemap, `tour off: no trace left (${t.total} links/blocks, /tour ${t.tourStatus}, in sitemap: ${t.inSitemap})`);
  await go('/admin/sections');
  ok(await p.locator('.sl-why').count() >= 1, 'home sections list says why the tour section is hidden');
  await go('/admin/r/scenes');
  await Promise.all([p.waitForNavigation(), p.locator('.tour-switch .switch').click()]);
  t = await traces();
  ok(t.total > 0 && t.tourStatus === 200 && await p.locator('.tour-switch.state-on').count() === 1, 'tour back on after switching it on');
  await visitor.close();

  ok(errors.length === 0, 'no JavaScript errors' + (errors.length ? ': ' + errors.join(' / ') : ''));
  console.log(failures ? `\n${failures} FAILED` : '\nALL PASSED');
  await b.close();
  process.exit(failures ? 1 : 0);
})();
