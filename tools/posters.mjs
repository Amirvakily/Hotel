// Draws flat illustrations of Tabriz sights (demo images for the "Tabriz guide").
// Usage: node tools/posters.mjs   → writes public/app/seed/media/att-*.jpg
import fs from 'node:fs';
import path from 'node:path';
import { createRequire } from 'node:module';
const require = createRequire(import.meta.url);
const { chromium } = require('playwright');
const OUT = path.resolve(path.dirname(new URL(import.meta.url).pathname), '../public/app/seed/media');
const W = 1200, H = 900;

const arch = (x, y, w, h) => `M${x},${y + h}V${y + w * .55}C${x},${y + w * .18} ${x + w * .32},${y + w * .05} ${x + w / 2},${y}C${x + w * .68},${y + w * .05} ${x + w},${y + w * .18} ${x + w},${y + w * .55}V${y + h}Z`;
const dome = (cx, by, r, c) => `<path d="M${cx - r},${by}A${r},${r * 1.05} 0 0 1 ${cx + r},${by}Z" fill="${c}"/>`;
function sky(top, mid, bot) { return `<defs><linearGradient id="sky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="${top}"/><stop offset=".6" stop-color="${mid}"/><stop offset="1" stop-color="${bot}"/></linearGradient></defs><rect width="${W}" height="${H}" fill="url(#sky)"/>`; }
function hills(c1, c2, y = 560) {
  return `<path d="M0,${y}C200,${y - 120} 360,${y - 60} 520,${y - 130}S860,${y - 40} 1000,${y - 110} 1200,${y - 60} 1200,${y - 60}V${H}H0Z" fill="${c1}"/>` +
    `<path d="M0,${y + 60}C240,${y} 420,${y + 50} 640,${y + 10}S1000,${y + 70} 1200,${y + 20}V${H}H0Z" fill="${c2}"/>`;
}
const sun = (x, y, r, c) => `<circle cx="${x}" cy="${y}" r="${r}" fill="${c}"/>`;
const ground = (y, c) => `<rect y="${y}" width="${W}" height="${H - y}" fill="${c}"/>`;
const bricks = (x, y, w, h, c) => { let s = ''; for (let yy = y + 8; yy < y + h; yy += 16) s += `<line x1="${x}" y1="${yy}" x2="${x + w}" y2="${yy}" stroke="${c}" stroke-width="2" opacity=".35"/>`; return s; };

const posters = {
  'att-bazaar': () => {
    let s = sky('#f6d7b0', '#f3e6d2', '#efe9df') + sun(900, 220, 90, '#f2b65a') + hills('#d9c3a5', '#cdb08a', 600);
    for (let i = 0; i < 7; i++) { const x = 40 + i * 165; s += `<rect x="${x}" y="430" width="160" height="300" fill="#b5653f"/>` + dome(x + 80, 432, 72, '#c27650') + `<circle cx="${x + 80}" cy="372" r="10" fill="#f3e6d2"/>` + `<path d="${arch(x + 35, 520, 90, 210)}" fill="#3b2a24"/>` + `<path d="${arch(x + 52, 560, 56, 170)}" fill="#f2b65a" opacity=".8"/>` + bricks(x, 430, 160, 300, '#7c3f25'); }
    return s + ground(730, '#8d5a3c') + `<rect y="730" width="${W}" height="18" fill="#6e4430"/>`;
  },
  'att-blue-mosque': () => {
    let s = sky('#cfe3ea', '#e8f0ee', '#f1efe9') + sun(260, 200, 70, '#f6c46b') + hills('#b9cbc6', '#a7bdb6', 620);
    s += dome(760, 360, 190, '#2e7f8a') + `<rect x="560" y="360" width="400" height="380" fill="#25606b"/>`;
    s += `<rect x="330" y="250" width="420" height="490" fill="#1f4e5a"/>` + `<path d="${arch(390, 300, 300, 440)}" fill="#2e9aa6"/>`;
    for (let r = 0; r < 9; r++) for (let c = 0; c < 6; c++) { const x = 410 + c * 45, y = 450 + r * 32; s += `<rect x="${x}" y="${y}" width="20" height="20" transform="rotate(45 ${x + 10} ${y + 10})" fill="#f3e3b5" opacity=".55"/>`; }
    s += `<path d="${arch(470, 430, 140, 310)}" fill="#163a44"/>` + `<rect x="330" y="250" width="420" height="28" fill="#f3e3b5" opacity=".7"/>`;
    return s + ground(740, '#cdb89a') + `<rect y="740" width="${W}" height="14" fill="#a8916f"/>`;
  },
  'att-arg': () => {
    let s = sky('#f3c9a7', '#f1dfcb', '#efe7dc') + sun(950, 260, 100, '#ee9a5a') + hills('#d6b79a', '#c9a487', 650);
    s += `<path d="M380,760L420,160H800L840,760Z" fill="#a85d3c"/>` + bricks(400, 160, 430, 600, '#6f3520');
    s += `<path d="${arch(520, 240, 180, 360)}" fill="#7a3b23"/>` + `<path d="${arch(560, 300, 100, 300)}" fill="#5a2a18"/>`;
    s += `<rect x="150" y="600" width="230" height="160" fill="#c58a62"/><rect x="840" y="580" width="240" height="180" fill="#c58a62"/>`;
    return s + ground(760, '#9c6b4b');
  },
  'att-elgoli': () => {
    let s = sky('#2f4f6e', '#e59e74', '#f4cfa2') + sun(600, 330, 80, '#ffd391') + hills('#55617a', '#3f4c63', 470);
    s += ground(520, '#2b6a74');
    s += `<rect x="440" y="330" width="320" height="170" fill="#efe3cf"/><rect x="470" y="270" width="260" height="70" fill="#efe3cf"/><rect x="430" y="320" width="340" height="16" fill="#b08a62"/><rect x="460" y="262" width="280" height="14" fill="#b08a62"/>`;
    for (let i = 0; i < 5; i++) s += `<path d="${arch(462 + i * 58, 380, 40, 120)}" fill="#c47f53"/>`;
    for (let i = 0; i < 4; i++) s += `<rect x="${490 + i * 60}" y="292" width="34" height="40" rx="17" fill="#c47f53"/>`;
    s += `<g opacity=".35" transform="translate(0 1020) scale(1 -1)"><rect x="440" y="330" width="320" height="170" fill="#efe3cf"/><rect x="470" y="270" width="260" height="70" fill="#efe3cf"/></g>`;
    for (const x of [140, 260, 940, 1060]) s += `<rect x="${x - 6}" y="420" width="12" height="100" fill="#3a2f2a"/><circle cx="${x}" cy="400" r="58" fill="#2f5a3e"/><circle cx="${x + 28}" cy="430" r="38" fill="#3a6b49"/>`;
    for (let i = 0; i < 12; i++) s += `<rect x="${80 + i * 95}" y="${560 + (i % 3) * 40}" width="${60 + (i % 4) * 20}" height="4" fill="#ffd391" opacity=".35"/>`;
    return s;
  },
  'att-kandovan': () => {
    let s = sky('#bcd6e8', '#e3ecf0', '#efefe9') + sun(980, 170, 60, '#f6d27a');
    s += `<path d="M0,520L180,300L330,430L520,220L720,420L900,260L1200,470V900H0Z" fill="#a9b7c2"/><path d="M460,250L520,220L585,260L550,262L520,240Z" fill="#f4f6f7"/>`;
    const cones = [[160, 380, 150], [330, 330, 170], [520, 360, 160], [700, 320, 180], [890, 370, 160], [1070, 400, 140]];
    for (const [x, top, w] of cones) {
      s += `<path d="M${x - w / 2},800C${x - w / 2},${top + 120} ${x - 20},${top} ${x},${top}C${x + 20},${top} ${x + w / 2},${top + 120} ${x + w / 2},800Z" fill="#b48a67"/><path d="M${x},${top}C${x + 20},${top} ${x + w / 2},${top + 120} ${x + w / 2},800H${x + 10}C${x + 30},${top + 200} ${x + 20},${top + 60} ${x},${top}Z" fill="#9c7556"/>`;
      for (let k = 0; k < 3; k++) s += `<rect x="${x - 18 + (k % 2) * 22}" y="${top + 150 + k * 90}" width="18" height="24" rx="4" fill="#4b3427"/>`;
      s += `<path d="${arch(x - 16, 720, 32, 80)}" fill="#3f2b20"/>`;
    }
    return s + ground(800, '#8a6a4e');
  },
  'att-saat': () => {
    let s = sky('#d6e6ef', '#eef1ee', '#f2efe8') + sun(220, 210, 70, '#f6c46b') + hills('#c9d2cf', '#b8c3bf', 640);
    s += `<rect x="250" y="420" width="700" height="330" fill="#e4d6bf"/><rect x="250" y="410" width="700" height="20" fill="#b9a17f"/>`;
    for (let i = 0; i < 8; i++) s += `<path d="${arch(280 + i * 84, 470, 50, 120)}" fill="#7f6a52"/><path d="${arch(280 + i * 84, 620, 50, 110)}" fill="#7f6a52"/>`;
    s += `<rect x="530" y="200" width="140" height="230" fill="#d9c7aa"/><circle cx="600" cy="290" r="48" fill="#f7f2e7" stroke="#7f6a52" stroke-width="8"/><line x1="600" y1="290" x2="600" y2="258" stroke="#2b2b2b" stroke-width="6"/><line x1="600" y1="290" x2="624" y2="300" stroke="#2b2b2b" stroke-width="5"/>`;
    s += `<path d="M520,200L600,130L680,200Z" fill="#7f6a52"/>`;
    return s + ground(750, '#c4b49b') + `<rect x="420" y="760" width="360" height="40" rx="20" fill="#5f8f8a" opacity=".6"/>`;
  },
  'att-constitution': () => {
    let s = sky('#f2d3c2', '#f1e6db', '#efe9e1') + sun(950, 220, 80, '#f0a868');
    s += `<rect x="200" y="300" width="800" height="440" fill="#c9a27c"/>` + bricks(200, 300, 800, 440, '#8a6345');
    const glass = ['#d9534f', '#3b82c4', '#f2c14e', '#4caf7d'];
    for (let i = 0; i < 5; i++) {
      const x = 250 + i * 150;
      s += `<path d="${arch(x, 360, 110, 300)}" fill="#5a3d2b"/>`;
      for (let r = 0; r < 4; r++) for (let c = 0; c < 3; c++) s += `<rect x="${x + 12 + c * 30}" y="${470 + r * 45}" width="24" height="38" fill="${glass[(r + c + i) % 4]}" opacity=".85"/>`;
      s += `<circle cx="${x + 55}" cy="420" r="26" fill="${glass[i % 4]}" opacity=".8"/>`;
    }
    s += `<rect x="180" y="290" width="840" height="20" fill="#8a6345"/>`;
    return s + ground(740, '#d8c9b2') + `<rect x="380" y="770" width="440" height="60" rx="8" fill="#5c9aa8"/>`;
  },
  'att-museum': () => {
    let s = sky('#d3e2ec', '#edf0ee', '#f2efe9') + sun(260, 190, 64, '#f6c46b') + hills('#c3ccd0', '#b2bdc1', 650);
    s += `<rect x="220" y="360" width="760" height="390" fill="#ece5d8"/><rect x="200" y="330" width="800" height="40" fill="#b8ab96"/><path d="M200,330L600,220L1000,330Z" fill="#d9cfbe"/>`;
    for (let i = 0; i < 9; i++) s += `<rect x="${255 + i * 82}" y="390" width="30" height="330" fill="#d8cfbf"/><rect x="${249 + i * 82}" y="380" width="42" height="14" fill="#b8ab96"/>`;
    s += `<rect x="540" y="560" width="120" height="190" fill="#4c5b61"/>`;
    return s + ground(750, '#c7baa3') + `<rect x="200" y="750" width="800" height="22" fill="#a89a82"/>`;
  },
};

const browser = await chromium.launch();
const page = await browser.newPage({ viewport: { width: W, height: H } });
for (const [name, fn] of Object.entries(posters)) {
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${W} ${H}" width="${W}" height="${H}">${fn()}</svg>`;
  await page.setContent(`<html><body style="margin:0">${svg}</body></html>`);
  await page.screenshot({ path: path.join(OUT, name + '.jpg'), type: 'jpeg', quality: 88, clip: { x: 0, y: 0, width: W, height: H } });
  console.log('poster', name);
}
await browser.close();
