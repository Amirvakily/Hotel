// Renders the demo photos + 360 panoramas into public/app/seed/media/.
// Usage: node tools/render/render.mjs [--preview] [--only=name1,name2] [--out=dir]
import http from 'node:http';
import fs from 'node:fs';
import path from 'node:path';
import { createRequire } from 'node:module';
const require = createRequire(import.meta.url);
const { chromium } = require('playwright');

const ROOT = path.resolve(path.dirname(new URL(import.meta.url).pathname), '../..');
const args = Object.fromEntries(process.argv.slice(2).map(a => { const [k, v] = a.replace(/^--/, '').split('='); return [k, v ?? true]; }));
const OUT = path.resolve(args.out || path.join(ROOT, 'public/app/seed/media'));
fs.mkdirSync(OUT, { recursive: true });
const preview = !!args.preview;

// [output name, scene, shot]  –  photos are 2400×1600
const PHOTOS = [
  ['room-classic', 'room-classic', 'main'], ['room-classic-2', 'room-classic', 'alt'],
  ['room-comfort', 'room-comfort', 'main'], ['room-comfort-2', 'room-comfort', 'alt'],
  ['room-deluxe', 'room-deluxe', 'main'], ['room-deluxe-2', 'room-deluxe', 'alt'], ['room-deluxe-bath', 'bathroom-tub', 'main'],
  ['room-quad', 'room-quad', 'main'], ['room-quad-2', 'room-quad', 'alt'],
  ['room-suite', 'suite-living', 'main'], ['room-suite-2', 'room-suite-bed', 'main'], ['room-suite-3', 'room-suite-kids', 'main'],
  ['bathroom', 'bathroom', 'main'], ['corridor', 'corridor', 'main'],
  ['lobby', 'lobby', 'main'], ['lobby-lounge', 'lobby', 'lounge'], ['reception', 'lobby', 'reception'],
  ['breakfast-hall', 'breakfast', 'main'], ['breakfast-buffet', 'breakfast', 'buffet'],
  ['cafe', 'cafe', 'main'], ['cafe-counter', 'cafe', 'counter'], ['cafe-window', 'cafe', 'window'],
  ['exterior', 'exterior-dusk', 'main'], ['exterior-day', 'exterior-day', 'main'],
];
const PANOS = [['pano-lobby', 'lobby'], ['pano-room-deluxe', 'room-deluxe'], ['pano-room-quad', 'room-quad'], ['pano-breakfast', 'breakfast'], ['pano-cafe', 'cafe']];

const only = args.only ? String(args.only).split(',') : null;
const mime = { '.html': 'text/html', '.js': 'text/javascript', '.mjs': 'text/javascript', '.woff2': 'font/woff2', '.json': 'application/json' };
const server = http.createServer((req, res) => {
  const p = path.join(ROOT, decodeURIComponent(req.url.split('?')[0]));
  if (!p.startsWith(ROOT) || !fs.existsSync(p) || fs.statSync(p).isDirectory()) { res.writeHead(404); return res.end(); }
  res.writeHead(200, { 'Content-Type': mime[path.extname(p)] || 'application/octet-stream' });
  fs.createReadStream(p).pipe(res);
});
await new Promise(r => server.listen(0, '127.0.0.1', r));
const port = server.address().port;

const browser = await chromium.launch({ args: ['--enable-unsafe-swiftshader', '--ignore-gpu-blocklist', '--use-angle=swiftshader'] });
const save = (file, dataUrl) => fs.writeFileSync(file, Buffer.from(dataUrl.split(',')[1], 'base64'));
let page = null;
async function fresh() {
  if (page) await page.close();
  page = await browser.newPage({ viewport: { width: 800, height: 600 } });
  page.on('pageerror', e => console.log('  pageerror:', e.message));
  page.on('console', m => { if (m.type() === 'error') console.log('  console:', m.text().slice(0, 200)); });
  await page.goto(`http://127.0.0.1:${port}/tools/render/index.html`);
  await page.waitForFunction(() => window.ready, null, { timeout: 60000 });
}
let current = null;
for (const [out, scene, shot] of PHOTOS) {
  if (only && !only.includes(out) && !only.includes(scene)) continue;
  if (current !== scene) { await fresh(); current = scene; }
  const t = Date.now();
  const [w, h, ss] = preview ? [960, 640, 1] : [2400, 1600, 1.25];
  const opts = args.tone ? { tone: args.tone } : {};
  const data = await page.evaluate(([s, sh, w, h, ss, o]) => window.renderPhoto(s, sh, w, h, ss, o), [scene, shot, w, h, ss, opts]);
  save(path.join(OUT, out + (preview ? '.preview' + (args.tone ? '-' + args.tone : '') : '') + '.jpg'), data);
  console.log(`photo ${out} ${((Date.now() - t) / 1000).toFixed(1)}s`);
}
if (!preview || args.panos) {
  for (const [out, scene] of PANOS) {
    if (only && !only.includes(out) && !only.includes(scene)) continue;
    await fresh(); current = scene;
    const t = Date.now();
    const sizes = await page.evaluate(([s, f, w]) => window.renderPano(s, f, w), [scene, preview ? 512 : 1536, preview ? 2048 : 4096]);
    save(path.join(OUT, out + '.jpg'), sizes.main);
    save(path.join(OUT, out + '-m.jpg'), sizes.mobile);
    save(path.join(OUT, out + '-t.jpg'), sizes.thumb);
    console.log(`pano ${out} ${((Date.now() - t) / 1000).toFixed(1)}s`);
  }
}
await browser.close();
server.close();
