// Logs in and opens every admin page; reports HTTP errors, PHP warnings and JS errors.
// Usage: node tests/admin-smoke.cjs http://127.0.0.1:8080 admin venus12345 [shotDir]
const { chromium } = require('playwright');
const [base, user, pass, shots] = process.argv.slice(2);
(async () => {
  const b = await chromium.launch();
  const ctx = await b.newContext({ viewport: { width: 1360, height: 900 } });
  const p = await ctx.newPage();
  const problems = [];
  p.on('pageerror', e => problems.push(`[js] ${p.url()} ${e.message}`));
  p.on('console', m => { if (m.type() === 'error' && !/tile\.openstreetmap|ERR_|net::/.test(m.text())) problems.push(`[console] ${p.url()} ${m.text().slice(0, 160)}`); });
  await p.goto(base + '/admin/login');
  await p.fill('input[name=username]', user); await p.fill('input[name=password]', pass);
  await Promise.all([p.waitForNavigation(), p.click('button[type=submit]')]);
  if (!p.url().endsWith('/admin/')) { console.log('LOGIN FAILED', p.url()); process.exit(1); }
  const pages = ['', 'r/rooms', 'r/rooms/new', 'r/rooms/1', 'r/rooms/3', 'prices', 'r/amenities', 'r/amenities/1', 'sections', 'sections/hero', 'sections/intro', 'sections/dining',
    'r/slides', 'r/slides/1', 'r/gallery_items', 'r/gallery_items/1', 'r/gallery_categories', 'r/scenes', 'r/scenes/1', 'r/scenes/new', 'media', 'media?kind=pano',
    'r/menu_items', 'r/menu_items?category_id=cafe', 'r/menu_items/1', 'r/menu_categories', 'r/attractions', 'r/attractions/1', 'r/reviews', 'r/reviews/1', 'r/faqs', 'r/faqs/1', 'r/pages', 'r/pages/1',
    'messages', 'settings/general', 'settings/contact', 'settings/location', 'settings/prices', 'settings/appearance', 'settings/languages', 'settings/navigation', 'settings/pages', 'settings/seo', 'settings/system',
    'translations', 'users', 'users/1', 'users/new', 'account', 'backup'];
  for (const path of pages) {
    const r = await p.goto(base + '/admin/' + path, { waitUntil: 'networkidle' });
    const html = await p.content();
    const status = r.status();
    if (status !== 200) problems.push(`[http ${status}] ${path}`);
    const m = html.match(/(Warning|Notice|Deprecated|Fatal error|Uncaught)[^<]{0,200}/);
    if (m) problems.push(`[php] ${path}: ${m[0]}`);
    if (shots && ['', 'r/rooms', 'r/rooms/3', 'prices', 'r/scenes/1', 'sections', 'r/gallery_items', 'settings/appearance'].includes(path)) {
      await p.screenshot({ path: `${shots}/admin-${path.replace(/\W+/g, '_') || 'dash'}.png`, fullPage: false });
    }
  }
  console.log(problems.length ? problems.join('\n') : 'ALL ADMIN PAGES OK (' + pages.length + ')');
  await b.close();
})();
