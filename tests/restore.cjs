// Restores the newest backup through the admin and checks the site. Usage: node tests/restore.cjs <base> <zip>
const { chromium } = require('playwright');
const [base, zip] = process.argv.slice(2);
(async () => {
  const b = await chromium.launch(); const p = await b.newPage(); p.on('dialog', d => d.accept());
  await p.goto(base + '/admin/login'); await p.fill('input[name=username]', 'admin'); await p.fill('input[name=password]', 'venus12345');
  await Promise.all([p.waitForNavigation(), p.click('button[type=submit]')]);
  // change something, then restore and check it is back
  await p.goto(base + '/admin/r/faqs');
  const before = await p.locator('tbody tr').count();
  await p.goto(base + '/admin/r/faqs/1');
  await Promise.all([p.waitForNavigation(), p.click('button[form="del-form"]')]);
  await p.goto(base + '/admin/backup');
  await p.setInputFiles('input[name=backup]', zip);
  await Promise.all([p.waitForNavigation({ timeout: 120000 }), p.click('form[action$="backup/restore"] button')]);
  console.log('flash:', (await p.locator('.flash').allTextContents()).join(' | '));
  await p.goto(base + '/admin/r/faqs');
  const after = await p.locator('tbody tr').count();
  console.log(after === before ? `RESTORE OK (faqs ${before} → deleted → ${after})` : `RESTORE FAILED ${before} vs ${after}`);
  const r = await p.goto(base + '/rooms'); console.log('site after restore:', r.status());
  await b.close();
})();
