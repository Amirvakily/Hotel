// Builds public/assets/img/icons.svg (sprite) and app/icons.json (names for the admin icon picker)
// from Lucide icons (ISC license). Usage: node tools/build-icons.mjs <path-to-lucide-static/icons>
import fs from 'node:fs';
import path from 'node:path';

const src = process.argv[2];
if (!src || !fs.existsSync(src)) { console.error('Pass the lucide-static/icons folder'); process.exit(1); }
const root = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..', 'public');

// Icons used by templates + a picker set for amenities/menus
const names = `bed bed-double bed-single users user wifi coffee utensils croissant car square-parking snowflake tv bath shower-head wind
refrigerator vault phone mail map-pin map navigation clock calendar star arrow-left arrow-right chevron-left chevron-right chevron-down
chevron-up x menu plus minus check info maximize maximize-2 play camera image images rotate-3d compass mountain building building-2
landmark sun moon sparkles shield-check concierge-bell baby cigarette-off plane luggage accessibility heart sofa armchair eye ruler
layers globe message-circle send quote chef-hat cup-soda cake-slice egg cookie sandwich milk glass-water leaf flame key-round door-open
bell washing-machine hand-platter layout-dashboard settings file-text message-square upload trash-2 pencil grip-vertical eye-off
log-out save search download refresh-cw external-link link palette languages house circle-help database archive scan-eye move-3d
locate list layout-grid table arrow-up arrow-down bus train-front tree-pine shopping-bag church scroll hourglass circle-check
circle-alert circle-x square-pen copy panel-left award trees utensils-crossed martini wine soup salad ice-cream-cone beef dessert
handshake headset smartphone wallet badge-percent filter sliders-horizontal arrow-up-right arrow-up-left zoom-in zoom-out rotate-ccw focus circle-dot
lock lock-open shirt dumbbell gift music printer usb tv-minimal fan heater lamp lamp-desk bath sun-snow wifi-high cctv flower-2 trees
mosque-placeholder`.split(/\s+/).filter(Boolean);

let out = '<svg xmlns="http://www.w3.org/2000/svg">\n';
const used = [];
for (const n of [...new Set(names)]) {
  const f = path.join(src, n + '.svg');
  if (!fs.existsSync(f)) continue;
  const svg = fs.readFileSync(f, 'utf8');
  const inner = svg.replace(/^[\s\S]*?<svg[^>]*>/, '').replace(/<\/svg>\s*$/, '').replace(/\s*\n\s*/g, '').trim();
  out += `<symbol id="i-${n}" viewBox="0 0 24 24">${inner}</symbol>\n`;
  used.push(n);
}
out += '</svg>\n';
fs.mkdirSync(path.join(root, 'assets/img'), { recursive: true });
fs.writeFileSync(path.join(root, 'assets/img/icons.svg'), out);
fs.writeFileSync(path.join(root, 'app/icons.json'), JSON.stringify(used));
console.log('icons:', used.length, 'bytes:', out.length);
