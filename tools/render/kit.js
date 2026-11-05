// Scene kit for the demo renders: procedural textures, materials and modern hotel furniture.
// Units are metres. Rooms are centred on the origin: x = width, z = depth, y = up.
// Wall names: n (z = -d/2, the "front" = 360° yaw 0), s (z = +d/2), e (x = +w/2), w (x = -w/2).
import * as THREE from 'three';
import { RoundedBoxGeometry } from 'three/addons/geometries/RoundedBoxGeometry.js';

// ---------------------------------------------------------------- random + noise
export function rng(seed = 1) {
  let s = seed >>> 0 || 1;
  return () => ((s = (s * 1664525 + 1013904223) >>> 0) / 4294967296);
}
function valueNoise(seed) {
  const r = rng(seed), P = new Float32Array(256 * 256);
  for (let i = 0; i < P.length; i++) P[i] = r();
  const at = (x, y) => P[((y & 255) << 8) | (x & 255)];
  const sm = t => t * t * (3 - 2 * t);
  return (x, y) => {
    const xi = Math.floor(x), yi = Math.floor(y), xf = sm(x - xi), yf = sm(y - yi);
    const a = at(xi, yi), b = at(xi + 1, yi), c = at(xi, yi + 1), d = at(xi + 1, yi + 1);
    return a + (b - a) * xf + (c - a) * yf + (a - b - c + d) * xf * yf;
  };
}
function fbm(n, x, y, oct = 4) {
  let v = 0, a = .5, f = 1;
  for (let i = 0; i < oct; i++) { v += a * n(x * f, y * f); a *= .5; f *= 2; }
  return v;
}

// ---------------------------------------------------------------- canvas textures
function canvas(w, h) { const c = document.createElement('canvas'); c.width = w; c.height = h; return c; }
function toTex(c, repeat = [1, 1], srgb = true) {
  const t = new THREE.CanvasTexture(c);
  t.wrapS = t.wrapT = THREE.RepeatWrapping;
  t.repeat.set(repeat[0], repeat[1]);
  t.anisotropy = 8;
  if (srgb) t.colorSpace = THREE.SRGBColorSpace;
  t.needsUpdate = true;
  return t;
}
function hex(c) { return new THREE.Color(c); }
function shade(c, k) { const col = hex(c); col.multiplyScalar(k); return '#' + col.getHexString(); }

export function texNoise(color, { size = 512, amount = 10, scale = 3, seed = 3, repeat = [1, 1] } = {}) {
  const c = canvas(size, size), g = c.getContext('2d'), img = g.createImageData(size, size);
  const n = valueNoise(seed), col = hex(color), r = rng(seed + 9);
  for (let y = 0; y < size; y++) for (let x = 0; x < size; x++) {
    const v = (fbm(n, x / size * scale * 8, y / size * scale * 8) - .5) * amount + (r() - .5) * amount * .6;
    const i = (y * size + x) * 4;
    img.data[i] = Math.max(0, Math.min(255, col.r * 255 + v));
    img.data[i + 1] = Math.max(0, Math.min(255, col.g * 255 + v));
    img.data[i + 2] = Math.max(0, Math.min(255, col.b * 255 + v));
    img.data[i + 3] = 255;
  }
  g.putImageData(img, 0, 0);
  return toTex(c, repeat);
}

export function texWood({ base = '#c8a27c', planks = 6, seed = 2, size = 1024, repeat = [1, 1], herringbone = false } = {}) {
  const c = canvas(size, size), g = c.getContext('2d'), r = rng(seed), n = valueNoise(seed);
  const ph = size / planks;
  for (let p = 0; p < planks; p++) {
    let x = 0;
    while (x < size) {
      const len = size * (0.35 + r() * 0.5);
      const tint = 0.86 + r() * 0.22;
      g.fillStyle = shade(base, tint);
      g.fillRect(x, p * ph, len, ph);
      // grain
      for (let k = 0; k < 26; k++) {
        const yy = p * ph + r() * ph;
        g.strokeStyle = `rgba(60,35,15,${0.03 + r() * 0.07})`;
        g.lineWidth = 0.6 + r() * 1.6;
        g.beginPath();
        for (let xx = x; xx < x + len; xx += 8) {
          const off = (fbm(n, xx / 140, yy / 30 + k) - .5) * 10;
          xx === x ? g.moveTo(xx, yy + off) : g.lineTo(xx, yy + off);
        }
        g.stroke();
      }
      g.fillStyle = 'rgba(40,25,10,.35)';
      g.fillRect(x + len - 1.5, p * ph, 1.5, ph);
      x += len;
    }
    g.fillStyle = 'rgba(40,25,10,.4)';
    g.fillRect(0, p * ph, size, 1.5);
  }
  return toTex(c, repeat);
}

export function texTerrazzo({ base = '#e9e5de', chips = ['#b9b2a7', '#8f8a83', '#d9cbb8', '#5f7c77', '#ffffff'], size = 1024, seed = 4, repeat = [1, 1] } = {}) {
  const c = canvas(size, size), g = c.getContext('2d'), r = rng(seed);
  g.fillStyle = base; g.fillRect(0, 0, size, size);
  for (let i = 0; i < 2600; i++) {
    const x = r() * size, y = r() * size, s = 2 + r() * r() * 16;
    g.fillStyle = chips[Math.floor(r() * chips.length)];
    g.globalAlpha = 0.55 + r() * 0.45;
    g.beginPath();
    const k = 3 + Math.floor(r() * 4);
    for (let j = 0; j < k; j++) { const a = j / k * Math.PI * 2 + r(), rr = s * (0.6 + r() * 0.5); j ? g.lineTo(x + Math.cos(a) * rr, y + Math.sin(a) * rr) : g.moveTo(x + Math.cos(a) * rr, y + Math.sin(a) * rr); }
    g.fill();
  }
  g.globalAlpha = 1;
  return toTex(c, repeat);
}

export function texMarble({ base = '#efebe5', vein = '#a8a29a', size = 1024, seed = 6, repeat = [1, 1] } = {}) {
  const c = canvas(size, size), g = c.getContext('2d'), r = rng(seed), n = valueNoise(seed);
  g.fillStyle = base; g.fillRect(0, 0, size, size);
  for (let k = 0; k < 14; k++) {
    let x = r() * size, y = -20, a = 0.6 + r() * 1.2;
    g.strokeStyle = vein; g.globalAlpha = 0.08 + r() * 0.25; g.lineWidth = 0.6 + r() * 2.5;
    g.beginPath(); g.moveTo(x, y);
    for (let t = 0; t < 120; t++) { a += (fbm(n, x / 200, y / 200) - .5) * .9; x += Math.cos(a) * 12; y += Math.abs(Math.sin(a)) * 12 + 3; g.lineTo(x, y); }
    g.stroke();
  }
  g.globalAlpha = 1;
  return toTex(c, repeat);
}

export function texTiles({ color = '#eceae6', grout = '#cfcac2', tw = 4, th = 8, size = 1024, repeat = [1, 1], seed = 7, vary = 0.06 } = {}) {
  const c = canvas(size, size), g = c.getContext('2d'), r = rng(seed);
  g.fillStyle = grout; g.fillRect(0, 0, size, size);
  const w = size / tw, h = size / th;
  for (let y = 0; y < th; y++) for (let x = 0; x < tw; x++) {
    g.fillStyle = shade(color, 1 - vary / 2 + r() * vary);
    g.fillRect(x * w + 2, y * h + 2, w - 4, h - 4);
  }
  return toTex(c, repeat);
}

export function texBrick({ color = '#b5653f', mortar = '#d9cfc2', size = 1024, rows = 16, cols = 6, seed = 8, repeat = [1, 1], paint = 0 } = {}) {
  const c = canvas(size, size), g = c.getContext('2d'), r = rng(seed);
  g.fillStyle = mortar; g.fillRect(0, 0, size, size);
  const h = size / rows, w = size / cols;
  for (let y = 0; y < rows; y++) for (let x = -1; x <= cols; x++) {
    const ox = (y % 2) * w / 2;
    g.fillStyle = shade(color, 0.82 + r() * 0.3);
    g.fillRect(x * w + ox + 3, y * h + 3, w - 6, h - 6);
  }
  if (paint) { g.fillStyle = `rgba(245,242,236,${paint})`; g.fillRect(0, 0, size, size); }
  return toTex(c, repeat);
}

export function texRug({ colors = ['#bfb3a3', '#8f8373', '#3f5550', '#d9d0c2'], size = 1024, seed = 9, style = 'lines' } = {}) {
  const c = canvas(size, size), g = c.getContext('2d'), r = rng(seed);
  const [bg, mid, dark, light] = colors;
  g.fillStyle = bg; g.fillRect(0, 0, size, size);
  if (style === 'lines') {
    g.strokeStyle = mid; g.lineWidth = 6;
    for (let i = 0; i < 6; i++) { const m = 40 + i * 14; g.strokeRect(m, m, size - 2 * m, size - 2 * m); }
    g.strokeStyle = dark; g.lineWidth = 3;
    for (let i = -size; i < size * 2; i += 48) { g.beginPath(); g.moveTo(i, 160); g.lineTo(i + size * .35, size - 160); g.stroke(); }
  } else {
    // modern take on a medallion
    g.save(); g.translate(size / 2, size / 2);
    for (let k = 8; k > 0; k--) {
      g.fillStyle = k % 2 ? mid : light; g.globalAlpha = .55;
      g.beginPath();
      for (let j = 0; j < 16; j++) { const a = j / 16 * Math.PI * 2, rr = k * 42 * (j % 2 ? .82 : 1); j ? g.lineTo(Math.cos(a) * rr, Math.sin(a) * rr) : g.moveTo(Math.cos(a) * rr, Math.sin(a) * rr); }
      g.closePath(); g.fill();
    }
    g.restore(); g.globalAlpha = 1;
    g.strokeStyle = dark; g.lineWidth = 10; g.strokeRect(36, 36, size - 72, size - 72);
  }
  // fibre noise
  const img = g.getImageData(0, 0, size, size);
  for (let i = 0; i < img.data.length; i += 4) { const v = (r() - .5) * 22; img.data[i] += v; img.data[i + 1] += v; img.data[i + 2] += v; }
  g.putImageData(img, 0, 0);
  return toTex(c);
}

export function texArt(kind = 0, size = 768) {
  const c = canvas(size, Math.round(size * 1.25)), g = c.getContext('2d'), H = c.height, r = rng(kind + 20);
  const palettes = [['#efe8dc', '#0b8a7a', '#e8913a', '#1d2a2e'], ['#f3efe8', '#c9b79c', '#5f7c77', '#2b2b2b'], ['#e9e3d8', '#b75d3c', '#26453f', '#d9b46a'], ['#f4f1ea', '#1f3b57', '#9db7b3', '#e2c28f']];
  const [bg, a, b, d] = palettes[kind % palettes.length];
  g.fillStyle = bg; g.fillRect(0, 0, size, H);
  if (kind % 3 === 0) { // arches
    for (let i = 0; i < 3; i++) { g.fillStyle = [a, b, d][i]; const w = size * (.5 - i * .12), x = size / 2 - w / 2, y = H * (.3 + i * .12); g.beginPath(); g.moveTo(x, H * .85); g.lineTo(x, y + w / 2); g.arc(size / 2, y + w / 2, w / 2, Math.PI, 0); g.lineTo(x + w, H * .85); g.fill(); }
  } else if (kind % 3 === 1) { // sun and hills
    g.fillStyle = b; g.beginPath(); g.arc(size * .62, H * .35, size * .18, 0, Math.PI * 2); g.fill();
    g.fillStyle = a; g.beginPath(); g.moveTo(0, H * .7); g.bezierCurveTo(size * .3, H * .45, size * .6, H * .8, size, H * .55); g.lineTo(size, H); g.lineTo(0, H); g.fill();
    g.fillStyle = d; g.beginPath(); g.moveTo(0, H * .85); g.bezierCurveTo(size * .4, H * .7, size * .7, H * .95, size, H * .8); g.lineTo(size, H); g.lineTo(0, H); g.fill();
  } else { // lines
    g.strokeStyle = d; g.lineWidth = 3;
    for (let i = 0; i < 18; i++) { g.beginPath(); g.arc(size * .5, H * .5, 30 + i * 18, r() * 6, r() * 6 + 2); g.stroke(); }
    g.fillStyle = a; g.fillRect(size * .15, H * .7, size * .7, 8);
  }
  return toTex(c);
}

export function texCityView({ w = 2048, h = 1024, mode = 'day', mountain = true, seed = 11 } = {}) {
  const c = canvas(w, h), g = c.getContext('2d'), r = rng(seed);
  const sky = g.createLinearGradient(0, 0, 0, h);
  if (mode === 'dusk') { sky.addColorStop(0, '#2c3e63'); sky.addColorStop(.45, '#c98a76'); sky.addColorStop(.7, '#f2c79a'); sky.addColorStop(1, '#f6d9b5'); }
  else { sky.addColorStop(0, '#8fb6dd'); sky.addColorStop(.6, '#d6e6f2'); sky.addColorStop(1, '#eef3f6'); }
  g.fillStyle = sky; g.fillRect(0, 0, w, h);
  if (mountain) { // Mount Eynali / Sahand silhouette
    g.fillStyle = mode === 'dusk' ? '#6e5d6a' : '#a7b3bd';
    g.beginPath(); g.moveTo(0, h * .55);
    for (let x = 0; x <= w; x += 20) g.lineTo(x, h * (.42 + .1 * Math.sin(x / 260) + .04 * Math.sin(x / 70 + 1) + .02 * r()));
    g.lineTo(w, h); g.lineTo(0, h); g.fill();
  }
  // city blocks
  for (let layer = 0; layer < 3; layer++) {
    const base = h * (.62 + layer * .1);
    for (let x = -20; x < w; x += 40 + r() * 90) {
      const bw = 50 + r() * 120, bh = 40 + r() * (160 - layer * 30);
      const col = mode === 'dusk' ? ['#4b4250', '#3a3442', '#2b2833'][layer] : ['#c9c4bd', '#b5afa7', '#9e978e'][layer];
      g.fillStyle = col; g.fillRect(x, base - bh, bw, bh + h);
      if (mode === 'dusk') for (let wy = base - bh + 10; wy < base; wy += 16) for (let wx = x + 6; wx < x + bw - 8; wx += 14) if (r() > .6) { g.fillStyle = `rgba(255,${190 + r() * 40},120,${.5 + r() * .5})`; g.fillRect(wx, wy, 6, 8); }
    }
  }
  return toTex(c);
}

export function texText(lines, { w = 1024, h = 256, bg = null, color = '#ffffff', font = 'Vazirmatn', weight = 800, size = 140, align = 'center', dir = 'rtl', glow = 0 } = {}) {
  const c = canvas(w, h), g = c.getContext('2d');
  if (bg) { g.fillStyle = bg; g.fillRect(0, 0, w, h); }
  g.fillStyle = color; g.textAlign = align; g.textBaseline = 'middle'; g.direction = dir;
  if (glow) { g.shadowColor = color; g.shadowBlur = glow; }
  const list = Array.isArray(lines) ? lines : [lines];
  list.forEach((ln, i) => {
    const s = typeof ln === 'object' ? ln : { text: ln };
    g.font = `${s.weight || weight} ${s.size || size}px ${s.font || font}`;
    g.fillText(s.text, align === 'center' ? w / 2 : (align === 'right' ? w - 40 : 40), h * (list.length === 1 ? .5 : (i + .5) / list.length));
  });
  return toTex(c);
}

// ---------------------------------------------------------------- materials
const cache = {};
export function mat(color, o = {}) {
  return new THREE.MeshStandardMaterial({ color, roughness: o.r ?? .75, metalness: o.m ?? 0, map: o.map || null, bumpMap: o.bump || null, bumpScale: o.bumpScale ?? 1, emissive: o.e || 0x000000, emissiveIntensity: o.ei ?? 1, side: o.side ?? THREE.FrontSide, transparent: !!o.opacity && o.opacity < 1, opacity: o.opacity ?? 1, envMapIntensity: o.env ?? 1 });
}
export function fabric(color, o = {}) {
  const key = 'f' + color + (o.scale || 3);
  cache[key] = cache[key] || texNoise('#ffffff', { amount: 26, scale: o.scale || 6, seed: 31 });
  return mat(color, { r: o.r ?? .95, map: cache[key], bump: cache[key], bumpScale: .6, ...o });
}
export const M = {};
export function initMaterials() {
  M.white = mat('#f4f2ee', { r: .9 });
  M.linen = fabric('#f7f5f0');
  M.black = mat('#1b1c1e', { r: .5, m: .3 });
  M.blackMetal = mat('#202224', { r: .35, m: .8 });
  M.brass = mat('#c29b5c', { r: .3, m: 1 });
  M.chrome = mat('#d9dcdf', { r: .12, m: 1 });
  M.oak = mat('#c9a27e', { r: .6, map: texWood({ base: '#c9a27e', planks: 4, seed: 5 }) });
  M.walnut = mat('#7a563a', { r: .55, map: texWood({ base: '#7a563a', planks: 4, seed: 12 }) });
  M.glass = mat('#c9dde0', { r: .05, m: .1, opacity: .25 });
  M.mirror = mat('#dfe4e6', { r: .02, m: 1, env: 2 });
  M.ceramic = mat('#efebe3', { r: .35 });
  M.screen = mat('#0c0d0f', { r: .15, m: .2 });
  M.leaf = mat('#4f6b3a', { r: .7, side: THREE.DoubleSide });
  M.leaf2 = mat('#6b8a4a', { r: .7, side: THREE.DoubleSide });
  M.soil = mat('#3d2f25', { r: 1 });
}

// ---------------------------------------------------------------- geometry helpers
export function box(w, h, d, m, x = 0, y = 0, z = 0, parent, opts = {}) {
  const geo = opts.round ? new RoundedBoxGeometry(w, h, d, opts.seg || 3, Math.min(opts.round, w / 2, h / 2, d / 2)) : new THREE.BoxGeometry(w, h, d);
  const mesh = new THREE.Mesh(geo, m);
  mesh.position.set(x, y, z);
  mesh.castShadow = opts.cast ?? true; mesh.receiveShadow = true;
  if (parent) parent.add(mesh);
  return mesh;
}
export function cyl(rt, rb, h, m, x = 0, y = 0, z = 0, parent, seg = 32, open = false) {
  const mesh = new THREE.Mesh(new THREE.CylinderGeometry(rt, rb, h, seg, 1, open), m);
  mesh.position.set(x, y, z); mesh.castShadow = true; mesh.receiveShadow = true;
  if (parent) parent.add(mesh);
  return mesh;
}
export function sphere(r, m, x, y, z, parent, sx = 1, sy = 1, sz = 1) {
  const mesh = new THREE.Mesh(new THREE.SphereGeometry(r, 24, 16), m);
  mesh.position.set(x, y, z); mesh.scale.set(sx, sy, sz); mesh.castShadow = true; mesh.receiveShadow = true;
  if (parent) parent.add(mesh);
  return mesh;
}
export function plane(w, h, m, parent) {
  const mesh = new THREE.Mesh(new THREE.PlaneGeometry(w, h), m);
  mesh.receiveShadow = true;
  if (parent) parent.add(mesh);
  return mesh;
}
export function place(obj, x, y, z, rotY = 0) { obj.position.set(x, y, z); obj.rotation.y = rotY; return obj; }

// ---------------------------------------------------------------- room shell
// windows: [{wall, center, width, height, sill}], doors: [{wall, center, width, height}]
export function roomShell(scene, o) {
  const { w, d, h = 2.9 } = o;
  const g = new THREE.Group(); scene.add(g);
  const wallMat = o.wallMat || mat(o.wall || '#ebe7e0', { r: .92, map: texNoise('#ffffff', { amount: 6, scale: 2, seed: 41 }) });
  // floor
  const floorMat = o.floorMat || mat('#bfa588', { r: .55, map: texWood({ base: '#b9a084', planks: 8, seed: 3, repeat: [w / 2.2, d / 2.2] }) });
  if (floorMat.map) floorMat.map.repeat.set(w / 2.4, d / 2.4);
  const floor = plane(w, d, floorMat, g); floor.rotation.x = -Math.PI / 2;
  const ceil = plane(w, d, mat(o.ceiling || '#f6f5f2', { r: 1 }), g); ceil.rotation.x = Math.PI / 2; ceil.position.y = h;
  const t = 0.12;
  const walls = {
    n: { len: w, pos: (u, y) => [u, y, -d / 2 - t / 2], rot: 0 },
    s: { len: w, pos: (u, y) => [-u, y, d / 2 + t / 2], rot: 0 },
    e: { len: d, pos: (u, y) => [w / 2 + t / 2, y, u], rot: Math.PI / 2 },
    w: { len: d, pos: (u, y) => [-w / 2 - t / 2, y, -u], rot: Math.PI / 2 },
  };
  const openings = [...(o.windows || []).map(x => Object.assign(x, { kind: 'win' })), ...(o.doors || []).map(x => Object.assign(x, { kind: 'door', sill: 0 }))];
  for (const k of Object.keys(walls)) {
    const W = walls[k];
    const ops = openings.filter(op => op.wall === k).sort((a, b) => a.center - b.center);
    const wm = (o.accent && o.accent.wall === k) ? o.accent.mat : wallMat;
    let cursor = -W.len / 2;
    const seg = (u0, u1, y0, y1) => {
      if (u1 - u0 < 0.001 || y1 - y0 < 0.001) return;
      const m = box(W.rot ? t : (u1 - u0), y1 - y0, W.rot ? (u1 - u0) : t, wm, 0, 0, 0, g, { cast: true });
      const [x, y, z] = W.pos((u0 + u1) / 2, (y0 + y1) / 2);
      m.position.set(x, y, z);
    };
    for (const op of ops) {
      const u0 = op.center - op.width / 2, u1 = op.center + op.width / 2, y0 = op.sill ?? .6, y1 = y0 + op.height;
      seg(cursor, u0, 0, h); seg(u0, u1, 0, y0); seg(u0, u1, y1, h);
      cursor = u1;
      if (op.kind === 'win') addWindow(g, W, op, o, t);
      if (op.kind === 'door' && !op.open) addDoor(g, W, op, t);
      if (op.kind === 'door' && op.open) addVestibule(g, W, op, wm, floorMat, h);
    }
    seg(cursor, W.len / 2, 0, h);
    // skirting
    const sk = box(W.rot ? .015 : W.len, .08, W.rot ? W.len : .015, M.white, 0, .04, 0, g, { cast: false });
    const [sx, , sz] = W.pos(0, 0); const inward = k === 'n' ? .07 : k === 's' ? -.07 : k === 'e' ? -.07 : .07;
    if (W.rot) sk.position.set(sx + inward, .04, sz); else sk.position.set(sx, .04, sz + inward);
  }
  return g;
}
function addWindow(g, W, op, o, t) {
  const y0 = op.sill ?? .6, cy = y0 + op.height / 2;
  const [x, , z] = W.pos(op.center, cy);
  const frame = new THREE.Group(); g.add(frame);
  frame.position.set(x, cy, z); frame.rotation.y = W.rot;
  const fm = M.blackMetal, fw = .05;
  box(op.width, fw, .08, fm, 0, op.height / 2 - fw / 2, 0, frame); box(op.width, fw, .08, fm, 0, -op.height / 2 + fw / 2, 0, frame);
  box(fw, op.height, .08, fm, -op.width / 2 + fw / 2, 0, 0, frame); box(fw, op.height, .08, fm, op.width / 2 - fw / 2, 0, 0, frame);
  const mullions = op.mullions ?? Math.max(1, Math.round(op.width / 1.2)) - 1;
  for (let i = 1; i <= mullions; i++) box(.035, op.height, .06, fm, -op.width / 2 + op.width * i / (mullions + 1), 0, 0, frame);
  if (op.sillBoard !== false && y0 > .1) box(op.width + .1, .04, .22, M.white, 0, -op.height / 2 - .02, 0, frame).position.z = ({ n: .08, s: -.08, e: -.08, w: .08 })[op.wall] * (W.rot ? -1 : 1);
  // outside view: a bright backdrop a few metres outside, facing the room
  const view = op.view || o.view || texCityView({ mode: o.viewMode || 'day' });
  const vm = new THREE.MeshBasicMaterial({ map: view, toneMapped: false });
  vm.color.setScalar(o.viewBright ?? 1.1);
  const vp = new THREE.Mesh(new THREE.PlaneGeometry(op.width * 3.6 + 4, op.height * 3 + 2), vm);
  const out = { n: [0, 0, -1], s: [0, 0, 1], e: [1, 0, 0], w: [-1, 0, 0] }[op.wall];
  const dist = op.viewDist ?? 3.2;
  vp.position.set(x + out[0] * dist, cy + op.height * .25, z + out[2] * dist);
  vp.rotation.y = { n: 0, s: Math.PI, e: -Math.PI / 2, w: Math.PI / 2 }[op.wall];
  g.add(vp);
  op._center = new THREE.Vector3(x, cy, z); op._out = new THREE.Vector3(...out);
}
function addVestibule(g, W, op, wm, floorMat, h) {
  const out = { n: [0, 0, -1], s: [0, 0, 1], e: [1, 0, 0], w: [-1, 0, 0] }[op.wall];
  const [x, , z] = W.pos(op.center, 0);
  const grp = new THREE.Group(); g.add(grp); grp.position.set(x, 0, z); grp.rotation.y = { n: 0, s: Math.PI, e: -Math.PI / 2, w: Math.PI / 2 }[op.wall];
  const depth = 2.4, wid = op.width + .2, hh = op.height + .3;
  const fl = plane(wid, depth, floorMat, grp); fl.rotation.x = -Math.PI / 2; fl.position.set(0, .001, -depth / 2);
  box(wid, hh, .05, wm, 0, hh / 2, -depth, grp, { cast: false });
  box(.05, hh, depth, wm, -wid / 2, hh / 2, -depth / 2, grp, { cast: false }); box(.05, hh, depth, wm, wid / 2, hh / 2, -depth / 2, grp, { cast: false });
  box(wid, .05, depth, wm, 0, hh, -depth / 2, grp, { cast: false });
  const L = new THREE.PointLight(0xffe2c0, 4, 5, 2); L.position.set(0, hh - .4, -depth * .6); grp.add(L);
  void out;
}
function addDoor(g, W, op, t) {
  const [x, , z] = W.pos(op.center, op.height / 2);
  const grp = new THREE.Group(); g.add(grp); grp.position.set(x, op.height / 2, z); grp.rotation.y = W.rot;
  box(op.width, op.height, .05, op.mat || M.walnut, 0, 0, 0, grp);
  const side = (op.wall === 'n' || op.wall === 'w') ? 1 : -1;
  cyl(.012, .012, .14, M.brass, op.width / 2 - .12, 0, side * .05, grp).rotation.z = Math.PI / 2;
  box(op.width + .08, .04, .07, M.white, 0, op.height / 2 + .02, 0, grp); box(.04, op.height, .07, M.white, -op.width / 2 - .02, 0, 0, grp); box(.04, op.height, .07, M.white, op.width / 2 + .02, 0, 0, grp);
}

// ---------------------------------------------------------------- furniture
export function curtain(width, height, color, { sheer = false, seed = 1 } = {}) {
  const geo = new THREE.PlaneGeometry(width, height, Math.max(24, Math.round(width * 30)), 4);
  const pos = geo.attributes.position, r = rng(seed);
  const ph = r() * 6;
  for (let i = 0; i < pos.count; i++) { const x = pos.getX(i); pos.setZ(i, Math.sin(x * 22 + ph) * .035 + Math.sin(x * 7) * .015); }
  geo.computeVertexNormals();
  const m = sheer ? mat('#ffffff', { r: .9, opacity: .55, side: THREE.DoubleSide, e: '#fff8ef', ei: .25 }) : fabric(color, { side: THREE.DoubleSide });
  const mesh = new THREE.Mesh(geo, m); mesh.castShadow = !sheer; mesh.receiveShadow = true;
  return mesh;
}

export function bed(type = 'single', o = {}) {
  const g = new THREE.Group();
  const W = { single: .95, double: 1.6, queen: 1.8, king: 2.0 }[type] || .95, L = 2.05;
  const frame = o.frame || fabric('#cfc8bc');
  box(W + .04, .2, L + .02, frame, 0, .14, 0, g, { round: .04 });
  box(W - .04, .18, L - .06, M.linen, 0, .33, 0, g, { round: .05, seg: 4 });
  // duvet drapes over the mattress sides, folded back at the head
  box(W + .1, .24, L * .78, M.linen, 0, .4, L * .1, g, { round: .09, seg: 5 });
  box(W + .11, .09, .24, M.linen, 0, .5, -L * .29, g, { round: .045, seg: 4 });
  // throw across the foot
  if (o.throw !== false) box(W + .14, .035, .5, fabric(o.throwColor || '#0b8a7a'), 0, .53, L * .31, g, { round: .016 });
  const pillows = type === 'single' ? 1 : 2;
  for (let i = 0; i < pillows; i++) {
    const px = pillows === 1 ? 0 : (i - .5) * W * .48;
    const pw = W * (pillows === 1 ? .7 : .42);
    const pl = box(pw, .15, .42, M.linen, px, .55, -L / 2 + .3, g, { round: .075, seg: 5 }); pl.rotation.x = -.42; pl.scale.y = .9;
    if (o.cushion) { const cu = box(pw * .62, .3, .1, fabric(o.cushionColor || '#e8913a'), px, .6, -L / 2 + .55, g, { round: .05, seg: 4 }); cu.rotation.x = -.35; }
  }
  return g;
}
export function headboard(width, o = {}) {
  const g = new THREE.Group();
  const m = o.mat || fabric(o.color || '#cfc6b8');
  const panels = Math.max(1, Math.round(width / .6));
  for (let i = 0; i < panels; i++) box(width / panels - .02, 1.15, .08, m, -width / 2 + width / panels * (i + .5), .95, 0, g, { round: .035 });
  if (o.glow) { // LED glow above
    const strip = box(width, .02, .02, mat('#ffffff', { e: '#ffd7a1', ei: 3 }), 0, 1.6, .02, g, { cast: false });
    strip.castShadow = false;
  }
  return g;
}
export function nightstand(o = {}) {
  const g = new THREE.Group();
  box(.48, .42, .4, o.mat || M.oak, 0, .33, 0, g, { round: .015 });
  box(.44, .005, .005, M.black, 0, .36, .201, g, { cast: false });
  for (const x of [-.2, .2]) for (const z of [-.16, .16]) cyl(.012, .012, .12, M.blackMetal, x, .06, z, g, 8);
  return g;
}
export function tableLamp(scene, x, y, z, o = {}) {
  const g = new THREE.Group(); scene.add(g); g.position.set(x, y, z);
  cyl(.07, .09, .26, o.baseMat || M.ceramic, 0, .13, 0, g);
  const shade = cyl(.13, .17, .22, mat('#fbf6ee', { r: .9, e: '#ffe3bd', ei: .9, side: THREE.DoubleSide }), 0, .4, 0, g, 32, true);
  shade.castShadow = false;
  const L = new THREE.PointLight(o.color || 0xffc98f, o.intensity ?? 6, 6, 2);
  L.position.set(0, .38, 0); g.add(L);
  if (o.shadow) { L.castShadow = true; L.shadow.mapSize.set(1024, 1024); L.shadow.bias = -.002; L.shadow.radius = 6; }
  return g;
}
export function pendant(scene, x, y, z, o = {}) {
  const g = new THREE.Group(); scene.add(g); g.position.set(x, y, z);
  const len = o.len ?? .7;
  cyl(.004, .004, len, M.black, 0, -len / 2, 0, g, 6);
  const kind = o.kind || 'dome';
  if (kind === 'dome') {
    const dm = new THREE.Mesh(new THREE.SphereGeometry(o.r || .22, 32, 12, 0, Math.PI * 2, 0, Math.PI / 2), o.mat || M.blackMetal); dm.position.y = -len; dm.castShadow = true; g.add(dm);
    const bulb = sphere(.05, mat('#fff', { e: '#ffd9a8', ei: 6 }), 0, -len - .02, 0, g); bulb.castShadow = false;
  } else if (kind === 'globe') {
    const gl = sphere(o.r || .16, mat('#fff8ee', { r: .4, e: '#ffe1b8', ei: 2.2, opacity: .95 }), 0, -len - (o.r || .16), 0, g); gl.castShadow = false;
  } else if (kind === 'ring') {
    const ring = new THREE.Mesh(new THREE.TorusGeometry(o.r || .6, .02, 12, 64), mat('#fff', { e: '#ffdcae', ei: 4 })); ring.rotation.x = Math.PI / 2; ring.position.y = -len; g.add(ring);
  }
  const L = new THREE.PointLight(o.color || 0xffcf96, o.intensity ?? 8, o.distance ?? 8, 2);
  L.position.y = -len - .1; g.add(L);
  return g;
}
export function downlight(scene, x, h, z, o = {}) {
  const disc = new THREE.Mesh(new THREE.CircleGeometry(.05, 24), new THREE.MeshBasicMaterial({ color: 0xfff1dc }));
  disc.rotation.x = Math.PI / 2; disc.position.set(x, h - .002, z); scene.add(disc);
  const s = new THREE.SpotLight(o.color || 0xffe2be, o.intensity ?? 12, 6, o.angle ?? .75, .9, 2);
  s.position.set(x, h - .02, z); s.target.position.set(x, 0, z); scene.add(s); scene.add(s.target);
  return s;
}
export function wardrobe(w = 1.2, h = 2.3, o = {}) {
  const g = new THREE.Group();
  box(w, h, .6, o.mat || M.oak, 0, h / 2, 0, g);
  const doors = Math.max(2, Math.round(w / .6));
  for (let i = 1; i < doors; i++) box(.006, h - .04, .01, M.black, -w / 2 + w * i / doors, h / 2, .3, g, { cast: false });
  for (let i = 0; i < doors; i++) box(.012, .5, .02, M.blackMetal, -w / 2 + w * (i + .5) / doors + (i % 2 ? -1 : 1) * (w / doors / 2 - .06), h * .5, .31, g);
  return g;
}
export function desk(o = {}) {
  const g = new THREE.Group();
  box(1.2, .035, .55, o.mat || M.oak, 0, .75, 0, g, { round: .01 });
  for (const x of [-.56, .56]) box(.025, .75, .5, M.blackMetal, x, .375, 0, g);
  return g;
}
export function chair(o = {}) {
  const g = new THREE.Group();
  const m = o.mat || fabric(o.color || '#2f4a46');
  box(.46, .08, .46, m, 0, .46, 0, g, { round: .03 });
  box(.46, .42, .07, m, 0, .72, -.2, g, { round: .03 }).rotation.x = -.12;
  for (const x of [-.2, .2]) for (const z of [-.2, .2]) cyl(.012, .01, .44, M.blackMetal, x, .22, z, g, 8);
  return g;
}
export function armchair(o = {}) {
  const g = new THREE.Group();
  const m = o.mat || fabric(o.color || '#0b6f63');
  box(.8, .22, .8, m, 0, .32, 0, g, { round: .08, seg: 4 });
  box(.8, .52, .2, m, 0, .62, -.32, g, { round: .09, seg: 4 }).rotation.x = -.15;
  box(.16, .3, .74, m, -.36, .52, 0, g, { round: .07, seg: 4 }); box(.16, .3, .74, m, .36, .52, 0, g, { round: .07, seg: 4 });
  for (const x of [-.32, .32]) for (const z of [-.3, .3]) cyl(.015, .012, .2, M.blackMetal, x, .1, z, g, 8);
  return g;
}
export function sofa(len = 2.1, o = {}) {
  const g = new THREE.Group();
  const m = o.mat || fabric(o.color || '#d9d2c6');
  box(len, .24, .9, m, 0, .32, 0, g, { round: .08, seg: 4 });
  const seats = Math.max(2, Math.round(len / .7));
  for (let i = 0; i < seats; i++) box(len / seats - .04, .14, .78, m, -len / 2 + len / seats * (i + .5), .5, .04, g, { round: .06, seg: 4 });
  box(len, .5, .22, m, 0, .66, -.36, g, { round: .09, seg: 4 });
  box(.2, .28, .9, m, -len / 2 + .1, .52, 0, g, { round: .08, seg: 4 }); box(.2, .28, .9, m, len / 2 - .1, .52, 0, g, { round: .08, seg: 4 });
  if (o.cushions !== false) for (let i = 0; i < 2; i++) box(.42, .4, .12, fabric((o.cushionColors || ['#0b8a7a', '#e8913a'])[i]), (i ? 1 : -1) * (len / 2 - .45), .72, -.18, g, { round: .06, seg: 4 }).rotation.x = -.2;
  for (const x of [-len / 2 + .12, len / 2 - .12]) for (const z of [-.35, .35]) cyl(.015, .012, .2, M.blackMetal, x, .1, z, g, 8);
  return g;
}
export function coffeeTable(o = {}) {
  const g = new THREE.Group();
  const r = o.r || .45;
  cyl(r, r, .035, o.mat || M.walnut, 0, .42, 0, g, 48);
  cyl(.04, .1, .4, M.blackMetal, 0, .2, 0, g, 16);
  if (o.books !== false) { box(.24, .04, .17, mat('#2b4f4a'), -.05, .46, .03, g); box(.2, .03, .15, mat('#d9cbb8'), -.04, .495, .02, g); cyl(.05, .04, .12, M.ceramic, .18, .5, -.08, g); }
  return g;
}
export function sideTable(o = {}) { const g = new THREE.Group(); cyl(.24, .24, .03, o.mat || M.walnut, 0, .55, 0, g); cyl(.015, .015, .55, M.blackMetal, 0, .275, 0, g, 8); cyl(.16, .16, .015, M.blackMetal, 0, .008, 0, g); return g; }
export function plant(scale = 1, o = {}) {
  const g = new THREE.Group(), r = rng(o.seed || 5);
  cyl(.17 * scale, .13 * scale, .36 * scale, o.pot || M.ceramic, 0, .18 * scale, 0, g);
  cyl(.155 * scale, .155 * scale, .01, M.soil, 0, .355 * scale, 0, g);
  const n = o.leaves || 26;
  for (let i = 0; i < n; i++) {
    const a = r() * Math.PI * 2, tilt = .25 + r() * .7, len = (.45 + r() * .55) * scale;
    const stem = new THREE.Group(); stem.position.y = .36 * scale; stem.rotation.set(0, a, 0); g.add(stem);
    const leafG = new THREE.Mesh(new THREE.SphereGeometry(.5, 10, 6), r() > .5 ? M.leaf : M.leaf2);
    leafG.scale.set(.09 * scale, .012 * scale, len * .45); leafG.position.set(0, Math.cos(tilt) * len * .55, Math.sin(tilt) * len * .5); leafG.rotation.x = tilt - .3;
    leafG.castShadow = true; stem.add(leafG);
  }
  return g;
}
export function wallArt(scene, w, h, kind, x, y, z, rotY = 0) {
  const g = new THREE.Group(); scene.add(g); g.position.set(x, y, z); g.rotation.y = rotY;
  box(w + .06, h + .06, .03, M.black, 0, 0, 0, g);
  const p = plane(w, h, mat('#ffffff', { map: texArt(kind), r: .85 }), g); p.position.z = .017;
  return g;
}
export function tv(scene, x, y, z, rotY = 0, w = 1.1) {
  const g = new THREE.Group(); scene.add(g); g.position.set(x, y, z); g.rotation.y = rotY;
  box(w, w * .57, .03, M.screen, 0, 0, 0, g);
  return g;
}
export function rug(scene, w, d, tex, x = 0, z = 0, rot = 0) {
  const m = mat('#ffffff', { map: tex, bump: tex, bumpScale: 1.5, r: 1 });
  const p = new THREE.Mesh(new RoundedBoxGeometry(w, .012, d, 2, .006), m);
  p.position.set(x, .006, z); p.rotation.y = rot; p.receiveShadow = true; scene.add(p);
  return p;
}
export function fluted(w, h, d, m, count, parent) {
  const g = new THREE.Group(); if (parent) parent.add(g);
  const r = w / count / 2;
  for (let i = 0; i < count; i++) { const c = cyl(r, r, h, m, -w / 2 + r + i * r * 2, h / 2, 0, g, 16); c.scale.z = d / (2 * r); }
  return g;
}
