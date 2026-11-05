// Runs the web installer in a browser. Usage: node tests/web-install.cjs <base-url> [mysql host name user pass]
const { chromium } = require('playwright');
const [base, myHost, myName, myUser, myPass] = process.argv.slice(2);
(async () => {
  const b = await chromium.launch(); const p = await b.newPage();
  await p.goto(base + '/install/', { waitUntil: 'load' }); await p.waitForTimeout(1500);
  await p.waitForFunction(() => /✓|Clean URLs|آدرس/.test(document.getElementById('rw')?.textContent || ''), null, { timeout: 15000 }).catch(() => {});
  console.log('rewrite check:', await p.textContent('#rw'));
  if (myHost) {
    await p.check('input[name=driver][value=mysql]');
    await p.fill('input[name=mysql_host]', myHost); await p.fill('input[name=mysql_name]', myName);
    await p.fill('input[name=mysql_user]', myUser); await p.fill('input[name=mysql_pass]', myPass);
  }
  await p.fill('input[name=name]', 'Owner'); await p.fill('input[name=user]', 'admin');
  await p.fill('input[name=pass]', 'venus12345'); await p.fill('input[name=pass2]', 'venus12345');
  await Promise.all([p.waitForNavigation({ timeout: 300000 }), p.click('button[type=submit]')]);
  const txt = await p.textContent('body');
  console.log(/Installed successfully|نصب با موفقیت/.test(txt) ? 'INSTALL OK' : 'INSTALL FAILED: ' + txt.slice(0, 600));
  await b.close();
})();
