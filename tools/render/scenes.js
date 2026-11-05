// Demo scenes for Venus Hotel. Each builder returns { scene, shots: {name: {pos, target, fov, shift}}, pano: {pos, yaw}, exposure }.
import * as THREE from 'three';
import * as K from './kit.js';
const { M, mat, fabric, box, cyl, sphere, plane, place } = K;

function baseLights(scene, o = {}) {
  scene.add(new THREE.HemisphereLight(o.sky || 0xe8eef5, o.ground || 0x8c7a66, o.hemi ?? .5));
}
function sun(scene, from, to, o = {}) {
  const s = new THREE.DirectionalLight(o.color || 0xffe4c0, o.intensity ?? 3);
  s.position.set(...from); s.target.position.set(...to);
  s.castShadow = true; s.shadow.mapSize.set(2048, 2048); s.shadow.bias = -.0004; s.shadow.normalBias = .02; s.shadow.radius = 4;
  const c = s.shadow.camera; c.left = -6; c.right = 6; c.top = 6; c.bottom = -6; c.near = .5; c.far = 30;
  scene.add(s); scene.add(s.target);
  return s;
}
function windowGlow(scene, win, room, o = {}) {
  // soft daylight from the window into the room
  const L = new THREE.RectAreaLight(o.color || 0xeef4ff, o.intensity ?? 5, win.width, win.height);
  const c = win._center.clone().addScaledVector(win._out, -.05);
  L.position.copy(c);
  L.lookAt(c.clone().addScaledVector(win._out, -1));
  scene.add(L);
  return L;
}

// ------------------------------------------------------------------ bedrooms
// cfg: { w, d, beds: [{type, z}], headColor, throwColor, wall, accent, lounge, wardrobe, desk, sofa, rugStyle, view, art }
export function hotelRoom(cfg) {
  const scene = new THREE.Scene();
  const w = cfg.w, d = cfg.d, h = cfg.h || 2.85;
  const winW = Math.min(w - 1.2, cfg.winW || 2.6);
  const win = { wall: 'n', center: cfg.winX || 0, width: winW, height: 2.05, sill: .5, mullions: 1 };
  const doorX = w / 2 - .7;
  K.roomShell(scene, {
    w, d, h, wall: cfg.wall || '#ebe7e1', windows: [win],
    doors: [{ wall: 's', center: -doorX, width: .92, height: 2.15 }, ...(cfg.bathDoor !== false ? [{ wall: 'e', center: d / 2 - .75, width: .82, height: 2.15, mat: M.white }] : [])],
    view: K.texCityView({ mode: 'day', mountain: cfg.mountain ?? true, seed: cfg.seed || 11 }),
    floorMat: cfg.floorMat,
  });
  baseLights(scene, { hemi: .22 });
  windowGlow(scene, win, null, { intensity: 3.5 });
  sun(scene, [win.center - 2.5, 5.5, -d / 2 - 5], [win.center + .5, 0, .2], { intensity: 2.4 });

  // headboard wall (west): full-width panel with soft LED
  const hbLen = d - .3;
  const panel = new THREE.Group(); scene.add(panel);
  const slat = mat(cfg.panelColor || '#b89474', { r: .6, map: K.texWood({ base: cfg.panelColor || '#b89474', planks: 3, seed: 21 }) });
  for (let i = 0; i < Math.floor(hbLen / .11); i++) box(.07, 2.4, .035, slat, 0, 1.2, -hbLen / 2 + .055 + i * .11, panel, { cast: false });
  panel.position.set(-w / 2 + .02, 0, 0);
  const strip = box(.02, .02, hbLen, mat('#fff', { e: '#ffd7a1', ei: 4 }), -w / 2 + .06, 2.42, 0, scene, { cast: false });
  const glow = new THREE.RectAreaLight(0xffcf9a, 2.2, hbLen, .1); glow.position.set(-w / 2 + .1, 2.4, 0); glow.lookAt(-w / 2 + .1, 0, 0); scene.add(glow);
  void strip;

  // beds along the west wall
  const L = 2.05;
  for (const b of cfg.beds) {
    const bw = { single: .95, double: 1.6, queen: 1.8 }[b.type];
    const hb = K.headboard(bw + .3, { color: cfg.headColor || '#d9d1c5' }); place(hb, -w / 2 + .1, 0, b.z, Math.PI / 2); scene.add(hb);
    const bedG = K.bed(b.type, { throwColor: cfg.throwColor, cushion: b.type !== 'single', cushionColor: cfg.cushionColor });
    place(bedG, -w / 2 + .14 + L / 2, 0, b.z, Math.PI / 2); scene.add(bedG);
  }
  for (const ns of cfg.nightstands || []) {
    const n = K.nightstand(); place(n, -w / 2 + .32, 0, ns, Math.PI / 2); scene.add(n);
    K.tableLamp(scene, -w / 2 + .32, .54, ns, { intensity: 2.5, shadow: !!cfg.lampShadow });
  }
  // ceiling downlights
  for (const [x, z] of cfg.downlights || [[0, -d / 4], [0, d / 4], [w / 4, d / 2 - 1]]) K.downlight(scene, x, h, z, { intensity: 5 });

  // east wall: desk / TV / wardrobe / bench
  const ex = w / 2;
  if (cfg.desk !== false) {
    const dk = K.desk(); place(dk, ex - .3, 0, cfg.deskZ ?? (d / 2 - 3.15), -Math.PI / 2); scene.add(dk);
    const ch = K.chair({ color: cfg.chairColor || '#2d4b46' }); place(ch, ex - .78, 0, cfg.deskZ ?? (d / 2 - 3.15), Math.PI / 2 + .25); scene.add(ch);
    K.tv(scene, ex - .03, 1.45, (cfg.deskZ ?? (d / 2 - 3.15)), -Math.PI / 2, 1.05);
    K.tableLamp(scene, ex - .3, .77, (cfg.deskZ ?? (d / 2 - 3.15)) - .45, { intensity: 3, baseMat: M.blackMetal });
  }
  if (cfg.wardrobe !== false) { const wd = K.wardrobe(1.1); place(wd, ex - .31, 0, d / 2 - 1.85, -Math.PI / 2); scene.add(wd); }
  // window corner: armchair + side table + plant (or a lounge with sofa)
  if (cfg.sofa) {
    const sf = K.sofa(1.9, { color: cfg.sofaColor || '#cfc7bb' }); place(sf, w / 2 - .5, 0, -d / 2 + 1.5, -Math.PI / 2); scene.add(sf);
    const ct = K.coffeeTable({ r: .4 }); place(ct, w / 2 - 1.45, 0, -d / 2 + 1.5); scene.add(ct);
  } else if (cfg.armchair !== false) {
    const ac = K.armchair({ color: cfg.armchairColor || '#0b6f63' }); place(ac, w / 2 - .75, 0, -d / 2 + .9, -Math.PI * .8); scene.add(ac);
    const st = K.sideTable(); place(st, w / 2 - .35, 0, -d / 2 + .45); scene.add(st);
  }
  const pl = K.plant(1.1, { seed: 3 }); place(pl, -w / 2 + .45, 0, -d / 2 + .45); scene.add(pl);
  // curtains
  for (const side of [-1, 1]) {
    const c = K.curtain(.7, h - .1, cfg.curtain || '#cdc4b6', { seed: side + 3 }); c.position.set(win.center + side * (winW / 2 + .2), (h - .1) / 2, -d / 2 + .12); scene.add(c);
  }
  const sheer = K.curtain(winW * .55, h - .15, '#fff', { sheer: true, seed: 9 }); sheer.position.set(win.center - winW * .22, (h - .15) / 2, -d / 2 + .08); scene.add(sheer);
  // art + rug
  if (cfg.art !== false) K.wallArt(scene, .7, .9, cfg.art ?? 0, ex - .02, 1.55, (cfg.deskZ ?? (d / 2 - 3.15)) - 1.05, -Math.PI / 2);
  K.rug(scene, 2.2, 1.6, K.texRug({ style: cfg.rugStyle || 'lines', colors: cfg.rugColors }), cfg.rugX ?? .35, cfg.rugZ ?? .2);

  return {
    scene,
    shots: {
      main: { pos: [w / 2 - 1.25, 1.3, d / 2 - .3], target: [-w / 2 + .5, .95, -d / 5], fov: 58, shift: -.07 },
      alt: { pos: [-w / 2 + 2.55, 1.32, d / 2 - .55], target: [w / 2 - .5, 1.0, -d / 2 + .5], fov: 60, shift: -.07 },
    },
    pano: { pos: [.35, 1.45, .25] },
    exposure: cfg.exposure ?? 1.05,
  };
}

export const ROOMS = {
  classic: { w: 4.3, d: 5.2, beds: [{ type: 'single', z: -1.45 }, { type: 'single', z: 0 }, { type: 'single', z: 1.45 }], nightstands: [-.72, .72], throwColor: '#0b8a7a', armchair: true, sofa: false, rugStyle: 'lines', art: 1, seed: 13, mountain: false, wardrobeZ: 1.65 },
  comfort: { w: 4.6, d: 5.4, beds: [{ type: 'double', z: -.9 }, { type: 'single', z: 1.35 }], nightstands: [-1.95, .3], throwColor: '#e8913a', cushionColor: '#0b8a7a', armchairColor: '#c98b4a', rugStyle: 'lines', art: 2, seed: 17, headColor: '#cfc3b2', wardrobeZ: 1.85 },
  deluxe: { w: 5.0, d: 5.9, beds: [{ type: 'queen', z: -1.0 }, { type: 'single', z: 1.5 }], nightstands: [-2.18, .32], throwColor: '#1f3b57', cushionColor: '#e8913a', sofa: true, sofaColor: '#2d4b46', rugStyle: 'medallion', rugColors: ['#d9cfc0', '#b9ab97', '#1f3b57', '#efe8dc'], art: 3, seed: 19, panelColor: '#8a6a4f', headColor: '#e3dbcf', wardrobeZ: 2.15, exposure: 1.08 },
  quad: { w: 5.0, d: 6.0, beds: [{ type: 'double', z: -1.75 }, { type: 'single', z: .55 }, { type: 'single', z: 1.95 }], nightstands: [-.6, 1.25], throwColor: '#b75d3c', cushionColor: '#e8913a', armchair: false, rugStyle: 'lines', art: 0, seed: 23, headColor: '#d6cdbf', wardrobeZ: 1.9, winW: 2.6 },
  suiteBed: { w: 4.6, d: 5.0, beds: [{ type: 'double', z: 0 }], nightstands: [-1.15, 1.15], throwColor: '#7a3b5e', cushionColor: '#e8913a', sofa: false, armchairColor: '#7a3b5e', art: 2, seed: 29, panelColor: '#6f5644', wardrobeZ: 1.4 },
  suiteKids: { w: 4.4, d: 5.0, beds: [{ type: 'single', z: -1.45 }, { type: 'single', z: 0 }, { type: 'single', z: 1.45 }], nightstands: [-.72, .72], throwColor: '#e8913a', armchair: false, art: 1, seed: 31, desk: false, wardrobeZ: 1.4, rugStyle: 'lines' },
};

// ------------------------------------------------------------------ suite living room
export function suiteLiving() {
  const scene = new THREE.Scene();
  const w = 5.2, d = 5.0, h = 2.85;
  const win = { wall: 'n', center: -.3, width: 3.4, height: 2.1, sill: .45, mullions: 2 };
  K.roomShell(scene, { w, d, h, windows: [win], doors: [{ wall: 'e', center: 1.4, width: .9, height: 2.15, open: true }], view: K.texCityView({ mode: 'day', seed: 7 }) });
  baseLights(scene); windowGlow(scene, win, null, { intensity: 6 });
  sun(scene, [-3.5, 5.5, -d / 2 - 5], [0, 0, .5], { intensity: 2.6 });
  const sf = K.sofa(2.4, { color: '#d8d0c3', cushionColors: ['#7a3b5e', '#e8913a'] }); place(sf, -.3, 0, 1.2, Math.PI); scene.add(sf);
  const a1 = K.armchair({ color: '#7a3b5e' }); place(a1, -2, 0, -.2, Math.PI / 2 + .3); scene.add(a1);
  const ct = K.coffeeTable({ r: .5 }); place(ct, -.3, 0, .15); scene.add(ct);
  K.rug(scene, 3, 2.2, K.texRug({ style: 'medallion', colors: ['#ddd3c5', '#bcae99', '#7a3b5e', '#f1ebe1'] }), -.3, .3);
  // dining corner
  cyl(.55, .55, .04, M.oak, 1.6, .75, -1.2, scene, 48); cyl(.05, .25, .73, M.blackMetal, 1.6, .37, -1.2, scene, 16);
  for (let i = 0; i < 3; i++) { const c = K.chair({ color: '#2d4b46' }); const a = i / 3 * Math.PI * 2 + .4; place(c, 1.6 + Math.cos(a) * .75, 0, -1.2 + Math.sin(a) * .75, -a - Math.PI / 2); scene.add(c); }
  K.pendant(scene, 1.6, h, -1.2, { kind: 'globe', len: .9, r: .2, intensity: 6 });
  K.tv(scene, -.3, 1.35, -d / 2 + .05, 0, 1.3);
  box(2.2, .45, .42, M.walnut, -.3, .23, -d / 2 + .25, scene);
  K.wallArt(scene, 1.1, .8, 0, -w / 2 + .02, 1.6, .4, Math.PI / 2);
  K.wallArt(scene, .6, .8, 2, w / 2 - .02, 1.6, -.6, -Math.PI / 2);
  for (const [x, z] of [[-1, -1], [-1, 1.4], [1.6, -1.2], [1.5, 1.3]]) K.downlight(scene, x, h, z, { intensity: 8 });
  const pl = K.plant(1.3, { seed: 8 }); place(pl, 2.2, 0, -2.05); scene.add(pl);
  const pl2 = K.plant(.9, { seed: 11 }); place(pl2, -2.2, 0, 1.9); scene.add(pl2);
  for (const side of [-1, 1]) { const c = K.curtain(.7, h - .1, '#cdc4b6', { seed: side + 7 }); c.position.set(win.center + side * (win.width / 2 + .2), (h - .1) / 2, -d / 2 + .12); scene.add(c); }
  return { scene, shots: { main: { pos: [2.2, 1.35, 2.1], target: [-1.4, .9, -.9], fov: 64, shift: -.05 } }, exposure: 1.05 };
}

// ------------------------------------------------------------------ bathroom
export function bathroom(o = {}) {
  const scene = new THREE.Scene();
  const w = 2.6, d = 3.0, h = 2.6;
  const tile = mat('#ffffff', { r: .35, map: K.texTiles({ color: '#e9e6e1', grout: '#cfcac3', tw: 4, th: 8, repeat: [2, 1.3] }) });
  const floor = mat('#ffffff', { r: .5, map: K.texTerrazzo({ base: '#d9d4cc', repeat: [2, 2] }) });
  K.roomShell(scene, { w, d, h, wallMat: tile, floorMat: floor, doors: [{ wall: 's', center: .6, width: .8, height: 2.1, open: true }] });
  const accent = mat('#ffffff', { r: .3, map: K.texTiles({ color: '#2f6f66', grout: '#24524c', tw: 10, th: 20, repeat: [1, 1] }) });
  box(w - .02, h - .02, .02, accent, 0, h / 2, -d / 2 + .02, scene, { cast: false });
  baseLights(scene, { hemi: .6 });
  // vanity
  box(1.2, .5, .5, M.oak, -.5, .65, -d / 2 + .3, scene, { round: .02 });
  box(1.24, .03, .52, mat('#f2efea', { r: .2, map: K.texMarble() }), -.5, .915, -d / 2 + .3, scene);
  const basin = cyl(.2, .17, .12, M.ceramic, -.5, .99, -d / 2 + .3, scene, 40); void basin;
  cyl(.012, .012, .25, M.blackMetal, -.5, 1.1, -d / 2 + .1, scene, 8);
  box(1.0, .9, .02, M.mirror, -.5, 1.6, -d / 2 + .04, scene);
  const strip = box(1.0, .02, .03, mat('#fff', { e: '#ffe2c0', ei: 5 }), -.5, 2.08, -d / 2 + .05, scene, { cast: false }); void strip;
  const vl = new THREE.RectAreaLight(0xffe6c8, 5, 1, .1); vl.position.set(-.5, 2.06, -d / 2 + .1); vl.lookAt(-.5, 1, 0); scene.add(vl);
  if (o.tub) {
    box(1.65, .55, .78, M.ceramic, .45, .28, .9, scene, { round: .12, seg: 4 });
    box(1.45, .02, .58, mat('#cfe0e3', { r: .05, m: .1, opacity: .85 }), .45, .52, .9, scene, { cast: false });
  } else {
    box(.012, 2, .9, M.glass, .2, 1, .85, scene, { cast: false });
    cyl(.12, .12, .02, M.blackMetal, .75, 2.1, .9, scene, 24);
  }
  box(.5, .02, .3, fabric('#f4f1ea'), .9, .9, -d / 2 + .2, scene);
  const pl = K.plant(.6, { seed: 4 }); place(pl, -1.05, .93, -d / 2 + .3); scene.add(pl);
  for (const [x, z] of [[-.5, -.6], [.4, .6]]) K.downlight(scene, x, h, z, { intensity: 9 });
  return { scene, shots: { main: { pos: [.85, 1.45, 1.3], target: [-.6, 1, -1.2], fov: 68, shift: -.04 } }, exposure: 1.05 };
}

// ------------------------------------------------------------------ corridor
export function corridor() {
  const scene = new THREE.Scene();
  const w = 2.0, d = 16, h = 2.7;
  const carpet = mat('#ffffff', { r: 1, map: K.texRug({ style: 'lines', colors: ['#6b6f72', '#5b5f62', '#3c4245', '#7a7e80'], seed: 3 }) });
  carpet.map.repeat.set(1, 6);
  const doors = [];
  for (let i = 0; i < 5; i++) { doors.push({ wall: 'e', center: -6 + i * 3, width: .9, height: 2.15 }); doors.push({ wall: 'w', center: -6 + i * 3, width: .9, height: 2.15 }); }
  K.roomShell(scene, { w, d, h, floorMat: carpet, doors, wall: '#e6e1d9' });
  baseLights(scene, { hemi: .35 });
  for (let i = 0; i < 6; i++) {
    const z = -7 + i * 3;
    K.downlight(scene, 0, h, z, { intensity: 6, angle: .9 });
    for (const sx of [-1, 1]) { const sc = box(.06, .3, .1, mat('#fff', { e: '#ffd8a6', ei: 3 }), sx * (w / 2 - .05), 1.75, z + 1.5, scene, { cast: false }); void sc; const p = new THREE.PointLight(0xffcf96, 1.5, 3, 2); p.position.set(sx * (w / 2 - .2), 1.8, z + 1.5); scene.add(p); }
  }
  return { scene, shots: { main: { pos: [.25, 1.45, d / 2 - .6], target: [-.05, 1.3, -d / 2], fov: 58, shift: 0 } }, exposure: 1.1 };
}

// ------------------------------------------------------------------ lobby (360 centre + photos)
export function lobby() {
  const scene = new THREE.Scene();
  const w = 14, d = 12, h = 4.2;
  const floor = mat('#ffffff', { r: .3, map: K.texTerrazzo({ base: '#e6e1d8', repeat: [5, 5] }) });
  const wins = [{ wall: 'n', center: -4, width: 3, height: 3.2, sill: .2, mullions: 2 }];
  K.roomShell(scene, {
    w, d, h, floorMat: floor, wall: '#ece8e1', windows: wins,
    doors: [{ wall: 'e', center: 0, width: 2.6, height: 3, open: true }, { wall: 'w', center: 0, width: 2.6, height: 3, open: true }, { wall: 'n', center: 2.5, width: 2.2, height: 2.6, open: true }],
    view: K.texCityView({ mode: 'day', seed: 31 }),
  });
  baseLights(scene, { hemi: .55 });
  windowGlow(scene, wins[0], null, { intensity: 5 });
  sun(scene, [-8, 9, -12], [-3, 0, 0], { intensity: 2.2 });
  // reception desk on the south wall: fluted oak front + stone top
  const desk = new THREE.Group(); scene.add(desk); desk.position.set(0, 0, d / 2 - 1.6);
  K.fluted(4.2, 1.05, .14, M.oak, 60, desk).position.z = -.38;
  box(4.4, .05, .9, mat('#f0ece6', { r: .25, map: K.texMarble({ seed: 3 }) }), 0, 1.1, 0, desk);
  box(4.2, 1.05, .7, M.oak, 0, .52, .1, desk);
  // back wall feature: big backlit panel with the logo star
  const back = box(6, 2.8, .06, mat('#1d2427', { r: .7 }), 0, 2.2, d / 2 - .05, scene, { cast: false }); void back;
  const star = new THREE.Group(); scene.add(star); star.position.set(0, 2.55, d / 2 - .09); star.rotation.y = Math.PI;
  const lm = mat('#fff', { e: '#ffd9a6', ei: 3 });
  for (const rot of [0, Math.PI / 4]) {
    const sq = new THREE.Mesh(new THREE.TorusGeometry(.5, .018, 6, 4), lm); sq.rotation.z = rot + Math.PI / 4; star.add(sq);
  }
  const ring = new THREE.Mesh(new THREE.TorusGeometry(.17, .016, 8, 48), lm); star.add(ring);
  const sign = plane(2.4, .5, new THREE.MeshBasicMaterial({ map: K.texText([{ text: 'VENUS', font: 'Jakarta', size: 150, weight: 800 }], { w: 1024, h: 220, dir: 'ltr' }), transparent: true, toneMapped: false }), scene);
  sign.position.set(0, 1.55, d / 2 - .1); sign.rotation.y = Math.PI; sign.material.color.setScalar(.95);
  const bl = new THREE.RectAreaLight(0xffd9a6, 3, 5, .2); bl.position.set(0, 3.55, d / 2 - .2); bl.lookAt(0, 0, d / 2 - .2); scene.add(bl);
  // lounge: two sofas + armchairs + table on a rug
  K.rug(scene, 5, 3.6, K.texRug({ style: 'medallion', colors: ['#d8d2c8', '#b8ae9f', '#0b6f63', '#eee8de'], seed: 5 }), -3.2, -1.6);
  const s1 = K.sofa(2.6, { color: '#cfc7ba' }); place(s1, -3.2, 0, -3.1); scene.add(s1);
  const a1 = K.armchair({ color: '#0b6f63' }); place(a1, -5, 0, -1.4, Math.PI / 2); scene.add(a1);
  const a2 = K.armchair({ color: '#0b6f63' }); place(a2, -1.4, 0, -1.4, -Math.PI / 2); scene.add(a2);
  const ct = K.coffeeTable({ r: .6 }); place(ct, -3.2, 0, -1.6); scene.add(ct);
  // second lounge
  const s2 = K.sofa(2.2, { color: '#2d4b46', cushionColors: ['#e8913a', '#d9cfc0'] }); place(s2, 3.6, 0, -2.6); scene.add(s2);
  const ct2 = K.coffeeTable({ r: .45 }); place(ct2, 3.6, 0, -1.4); scene.add(ct2);
  const a3 = K.armchair({ color: '#c98b4a' }); place(a3, 3.6, 0, -.2, Math.PI); scene.add(a3);
  // columns + plants
  for (const x of [-5.4, 5.4]) cyl(.22, .22, h, mat('#e9e4dc', { r: .6 }), x, h / 2, 2.6, scene, 40);
  for (const [x, z, s] of [[-6.2, -5.2, 1.6], [6.2, -5.2, 1.4], [-6.2, 4.8, 1.5], [6.2, 4.8, 1.6], [1.6, 4.3, 1.1]]) { const p = K.plant(s, { seed: Math.round(x * 7 + z) }); place(p, x, 0, z); scene.add(p); }
  // ring chandeliers + downlights
  K.pendant(scene, -3.2, h, -1.6, { kind: 'ring', r: .9, len: 1.4, intensity: 18, distance: 10 });
  K.pendant(scene, 3.6, h, -1.4, { kind: 'ring', r: .7, len: 1.4, intensity: 14, distance: 10 });
  for (const x of [-4, 0, 4]) for (const z of [-4, 0, 3.5]) K.downlight(scene, x, h, z, { intensity: 14, angle: .8 });
  // elevator doors on the north wall (right) and art
  box(1.1, 2.3, .04, M.chrome, 5, 1.15, -d / 2 + .03, scene); box(1.1, 2.3, .04, M.chrome, 6.3, 1.15, -d / 2 + .03, scene);
  K.wallArt(scene, 1.6, 1.2, 0, -w / 2 + .02, 2, -3.5, Math.PI / 2);
  K.wallArt(scene, 1.6, 1.2, 3, w / 2 - .02, 2, 3.5, -Math.PI / 2);
  // bar stools / bench by the window not needed; a luggage trolley near the desk
  const tr = new THREE.Group(); scene.add(tr); tr.position.set(3.4, 0, d / 2 - 2.6);
  box(.9, .04, .5, M.brass, 0, .25, 0, tr); for (const x of [-.42, .42]) cyl(.015, .015, 1.6, M.brass, x, 1.05, 0, tr, 8); box(.9, .03, .03, M.brass, 0, 1.85, 0, tr);
  box(.45, .6, .25, mat('#1f3b57', { r: .5 }), -.15, .58, 0, tr, { round: .04 });
  return {
    scene,
    shots: {
      main: { pos: [5.6, 1.5, -4.6], target: [-2, 1.4, 2.5], fov: 66, shift: -.05 },
      lounge: { pos: [1.2, 1.35, 1.4], target: [-4.2, .9, -3], fov: 60, shift: -.05 },
      reception: { pos: [-1.6, 1.5, 1], target: [.4, 1.5, d / 2], fov: 62, shift: -.04 },
    },
    pano: { pos: [0, 1.5, 0] },
    exposure: 1.0,
  };
}

// ------------------------------------------------------------------ breakfast hall
export function breakfast() {
  const scene = new THREE.Scene();
  const w = 12, d = 10, h = 3.4;
  const wins = [-3.5, 0, 3.5].map(c => ({ wall: 'n', center: c, width: 2.6, height: 2.5, sill: .4, mullions: 1 }));
  K.roomShell(scene, { w, d, h, wall: '#efebe4', windows: wins, doors: [{ wall: 's', center: 0, width: 2.2, height: 2.6, open: true }], view: K.texCityView({ mode: 'day', seed: 41 }) });
  baseLights(scene, { hemi: .6 });
  for (const wn of wins) windowGlow(scene, wn, null, { intensity: 4 });
  sun(scene, [-6, 8, -11], [0, 0, 0], { intensity: 2.4 });
  // buffet island
  const buf = new THREE.Group(); scene.add(buf); buf.position.set(0, 0, 1.8);
  K.fluted(5, .9, .1, M.oak, 70, buf).position.z = -.42; K.fluted(5, .9, .1, M.oak, 70, buf).position.z = .42;
  box(5, .9, .8, M.oak, 0, .45, 0, buf);
  box(5.1, .05, .95, mat('#efebe5', { r: .25, map: K.texMarble({ seed: 9 }) }), 0, .925, 0, buf);
  const food = [['#e8b04a', '#d98c2b'], ['#c8402f', '#a8322a'], ['#7da34a', '#5c8a3a'], ['#f0e6d2', '#e2d2b2'], ['#f3c94e', '#e6a72f']];
  for (let i = 0; i < 9; i++) {
    const x = -2.2 + i * .55, kind = i % 3;
    if (kind === 0) { // bowl of fruit
      cyl(.2, .13, .1, M.ceramic, x, 1.0, -.1, buf, 32);
      for (let k = 0; k < 7; k++) sphere(.045, mat(food[(i + k) % food.length][k % 2], { r: .5 }), x + Math.cos(k) * .1, 1.07 + (k % 2) * .03, -.1 + Math.sin(k) * .1, buf);
    } else if (kind === 1) { // bread basket
      box(.38, .08, .26, mat('#9c7a52', { r: .9 }), x, .99, .1, buf, { round: .03 });
      for (let k = 0; k < 4; k++) box(.24, .05, .08, mat('#d9a866', { r: .8 }), x, 1.05, .02 + k * .055, buf, { round: .025 });
    } else { // chafing dish
      box(.42, .09, .3, M.chrome, x, 1.0, -.05, buf, { round: .03 });
      const lid = new THREE.Mesh(new THREE.SphereGeometry(.22, 24, 8, 0, Math.PI * 2, 0, Math.PI / 2), M.chrome); lid.scale.set(1, .4, .7); lid.position.set(x, 1.04, -.05); buf.add(lid);
    }
  }
  for (const x of [-2.8, 2.8]) { cyl(.11, .11, .35, mat('#f2a33a', { r: .1, opacity: .85 }), x, 1.13, .15, buf, 24); cyl(.115, .115, .04, M.chrome, x, 1.32, .15, buf); }
  // tables
  for (const [x, z] of [[-4, -2.5], [0, -2.5], [4, -2.5], [-4.2, 3.5], [4.2, 3.5]]) {
    box(1.4, .04, .8, M.walnut, x, .74, z, scene, { round: .01 });
    for (const sx of [-.6, .6]) box(.05, .72, .7, M.blackMetal, x + sx, .36, z, scene);
    for (const [cx, cz, r] of [[-.35, -.65, 0], [.35, -.65, 0], [-.35, .65, Math.PI], [.35, .65, Math.PI]]) { const c = K.chair({ color: '#d7cfc2' }); place(c, x + cx, 0, z + cz, r); scene.add(c); }
    cyl(.04, .03, .12, M.ceramic, x, .82, z, scene); sphere(.035, mat('#e8913a'), x, .9, z, scene);
  }
  for (const x of [-4, 0, 4]) K.pendant(scene, x, h, -2.5, { kind: 'dome', len: .9, r: .28, intensity: 7, mat: mat('#0b6f63', { r: .4, m: .2 }) });
  for (const x of [-1.6, 0, 1.6]) K.pendant(scene, x, h, 1.8, { kind: 'globe', len: 1.1, r: .16, intensity: 6 });
  for (const [x, z] of [[-5.3, -4.4], [5.3, -4.4], [-5.3, 4.3], [5.3, 4.3]]) { const p = K.plant(1.4, { seed: x + z }); place(p, x, 0, z); scene.add(p); }
  K.wallArt(scene, 1.8, 1.3, 1, -w / 2 + .02, 1.8, 1.5, Math.PI / 2);
  const sign = plane(2.4, .4, new THREE.MeshBasicMaterial({ map: K.texText([{ text: 'صبحانه · Breakfast', size: 96, weight: 700 }], { w: 1400, h: 220, color: '#1d2427' }), transparent: true }), scene);
  sign.position.set(w / 2 - .02, 2.4, 1.8); sign.rotation.y = -Math.PI / 2;
  return {
    scene,
    shots: {
      main: { pos: [5.2, 1.5, 4.4], target: [-2.5, 1.1, -2], fov: 66, shift: -.05 },
      buffet: { pos: [1.6, 1.45, 3.9], target: [-.6, .95, 1.6], fov: 55, shift: -.08 },
    },
    pano: { pos: [0, 1.5, -.4] },
    exposure: 1.0,
  };
}

// ------------------------------------------------------------------ café "Truth"
export function cafe() {
  const scene = new THREE.Scene();
  const w = 10, d = 8.5, h = 3.4;
  const wins = [{ wall: 'n', center: -2, width: 4.2, height: 2.6, sill: .35, mullions: 3 }];
  const brick = mat('#ffffff', { r: .9, map: K.texBrick({ color: '#9a5a3f', mortar: '#cbbfb0', rows: 22, cols: 7, repeat: [3, 1.2] }) });
  K.roomShell(scene, {
    w, d, h, wall: '#e9e3da', windows: wins, accent: { wall: 'w', mat: brick },
    floorMat: mat('#ffffff', { r: .55, map: K.texWood({ base: '#6f5641', planks: 10, seed: 44 }) }),
    doors: [{ wall: 's', center: 0, width: 2, height: 2.6, open: true }], view: K.texCityView({ mode: 'dusk', seed: 51 }), viewBright: 1.05,
  });
  baseLights(scene, { hemi: .35, sky: 0xffe9d2 });
  windowGlow(scene, wins[0], null, { intensity: 3.5, color: 0xffd9b8 });
  // bar along the east wall
  const bar = new THREE.Group(); scene.add(bar); bar.position.set(w / 2 - 1.3, 0, -.2); bar.rotation.y = Math.PI / 2;
  K.fluted(4.4, 1.05, .12, mat('#1f2a2c', { r: .5 }), 64, bar).position.z = .42;
  box(4.4, 1.05, .8, mat('#1f2a2c', { r: .5 }), 0, .52, 0, bar);
  box(4.6, .05, .95, mat('#f0ece6', { r: .25, map: K.texMarble({ seed: 12 }) }), 0, 1.08, 0, bar);
  // espresso machine
  box(.75, .45, .5, M.chrome, -.8, 1.33, -.1, bar, { round: .04 }); box(.75, .08, .5, mat('#0b8a7a', { r: .3, m: .4 }), -.8, 1.6, -.1, bar);
  for (const x of [-1.0, -.6]) cyl(.03, .03, .12, M.blackMetal, x, 1.08 + .13, .1, bar, 12);
  cyl(.08, .06, .25, M.chrome, .3, 1.23, -.1, bar, 24); cyl(.12, .1, .3, mat('#1d1d1d', { r: .4 }), .8, 1.25, -.1, bar, 24);
  for (let i = 0; i < 5; i++) { cyl(.04, .035, .09, M.ceramic, 1.4 + i * .12, 1.15, .15, bar, 16); }
  // back shelves with jars
  for (let s = 0; s < 2; s++) {
    box(3.2, .03, .3, M.oak, 0, 1.6 + s * .45, -.9, bar);
    for (let i = 0; i < 9; i++) { const c = cyl(.06, .06, .18 + (i % 3) * .05, mat(['#c9b79c', '#5f7c77', '#efe8dc', '#b75d3c'][(i + s) % 4], { r: .3 }), -1.4 + i * .35, 1.71 + s * .45, -.9, bar, 16); void c; }
  }
  // menu board + neon sign
  const board = plane(1.6, 1.0, new THREE.MeshBasicMaterial({ map: K.texText([{ text: 'TRUTH', font: 'Jakarta', size: 120, weight: 800 }, { text: 'کافه تروث', size: 96, weight: 800 }], { w: 1024, h: 640, bg: '#151a1b', color: '#f4efe6' }) }), scene);
  board.position.set(w / 2 - .02, 3.0, -.2); board.rotation.y = -Math.PI / 2; board.material.color.setScalar(.9); board.scale.set(1.2, .8, 1);
  // stools
  for (let i = 0; i < 5; i++) { const z = -1.9 + i * .85; cyl(.2, .2, .06, fabric('#c98b4a'), w / 2 - 2.05, .78, z, scene, 24); cyl(.02, .02, .76, M.blackMetal, w / 2 - 2.05, .39, z, scene, 8); cyl(.18, .18, .02, M.blackMetal, w / 2 - 2.05, .01, z, scene, 24); }
  // tables by the window and a bench along the brick wall
  box(.55, .45, d - 2, fabric('#2d4b46'), -w / 2 + .3, .23, .3, scene, { round: .05 });
  box(.2, .5, d - 2, fabric('#2d4b46'), -w / 2 + .12, .7, .3, scene, { round: .06 });
  for (const z of [-2.4, -.8, .8, 2.4]) {
    cyl(.4, .4, .035, M.oak, -w / 2 + 1.25, .74, z, scene, 40); cyl(.03, .2, .72, M.blackMetal, -w / 2 + 1.25, .36, z, scene, 16);
    const c = K.chair({ color: '#b75d3c' }); place(c, -w / 2 + 1.95, 0, z, -Math.PI / 2); scene.add(c);
    cyl(.045, .035, .08, M.ceramic, -w / 2 + 1.2, .8, z + .1, scene, 16);
    K.pendant(scene, -w / 2 + 1.25, h, z, { kind: 'dome', len: 1.25, r: .18, intensity: 5, mat: M.brass });
  }
  for (const x of [-1.2, .6]) {
    cyl(.4, .4, .035, M.oak, x, .74, -2.6, scene, 40); cyl(.03, .2, .72, M.blackMetal, x, .36, -2.6, scene, 16);
    for (const s of [-1, 1]) { const c = K.chair({ color: '#2d4b46' }); place(c, x + s * .65, 0, -2.6, s * Math.PI / 2 * -1); scene.add(c); }
    K.pendant(scene, x, h, -2.6, { kind: 'globe', len: 1.2, r: .14, intensity: 5 });
  }
  for (const [x, z, s] of [[-4.4, -3.6, 1.3], [1.8, -3.7, 1.1], [-4.4, 3.6, 1.2]]) { const p = K.plant(s, { seed: Math.round(x * 3 + z) }); place(p, x, 0, z); scene.add(p); }
  K.downlight(scene, 3.5, h, -.2, { intensity: 10 }); K.downlight(scene, 3.5, h, 1.6, { intensity: 10 }); K.downlight(scene, 3.5, h, -2, { intensity: 10 });
  K.downlight(scene, 0, h, 2, { intensity: 6 });
  return {
    scene,
    shots: {
      main: { pos: [1.2, 1.45, 3.6], target: [-2.4, 1.1, -1.8], fov: 66, shift: -.05 },
      counter: { pos: [-.4, 1.5, 2.2], target: [4, 1.4, -.6], fov: 58, shift: -.05 },
      window: { pos: [-1.4, 1.3, 2.6], target: [-4.2, .9, -2.6], fov: 56, shift: -.06 },
    },
    pano: { pos: [0, 1.5, .2] },
    exposure: 1.12,
  };
}

// ------------------------------------------------------------------ building exterior
export function exterior(mode = 'dusk') {
  const scene = new THREE.Scene();
  const dusk = mode === 'dusk';
  const sky = K.texCityView({ w: 2048, h: 1024, mode: dusk ? 'dusk' : 'day', mountain: true, seed: 61 });
  scene.background = sky;
  scene.add(new THREE.HemisphereLight(dusk ? 0x9fb0d8 : 0xd9e6f5, 0x6b5f55, dusk ? .5 : 1.0));
  const s = sun(scene, dusk ? [-30, 12, 25] : [-25, 40, 30], [0, 6, 0], { intensity: dusk ? .9 : 3.2, color: dusk ? 0xffb27a : 0xfff1dc });
  s.shadow.camera.left = -25; s.shadow.camera.right = 25; s.shadow.camera.top = 25; s.shadow.camera.bottom = -25; s.shadow.camera.far = 120;
  // street + sidewalk
  const ground = plane(120, 80, mat('#4a4c4f', { r: .9 }), scene); ground.rotation.x = -Math.PI / 2;
  const walk = box(60, .15, 6, mat('#b9b4ab', { r: .85, map: K.texTiles({ color: '#c4bfb6', grout: '#9e998f', tw: 10, th: 4, repeat: [8, 1] }) }), 0, .075, 4, scene);
  void walk;
  // building: 7 floors, 22 m wide
  const W = 22, H = 25, D = 14, fl = 3.4;
  const facade = mat(dusk ? '#bdb5aa' : '#e2dbd0', { r: .85, map: K.texNoise('#ffffff', { amount: 8, scale: 2, seed: 71 }) });
  box(W, H, D, facade, 0, H / 2, -D / 2, scene);
  // vertical fins and windows
  const winLit = dusk ? mat('#3a3128', { e: '#ffc47e', ei: .9, r: .2 }) : mat('#8ea3b0', { r: .1, m: .3 });
  const winDark = mat(dusk ? '#2b3640' : '#7d93a1', { r: .12, m: dusk ? .5 : .35 });
  const r = K.rng(7);
  for (let f = 1; f < 7; f++) for (let i = 0; i < 9; i++) {
    const x = -W / 2 + 1.7 + i * 2.32, y = f * fl + 1.6;
    box(1.6, 2.2, .1, (dusk && r() > .35) || (!dusk && r() > .85) ? winLit : winDark, x, y, .02, scene, { cast: false });
    box(.1, 2.6, .5, M.blackMetal, x - .95, y, .25, scene);
  }
  for (let f = 1; f <= 7; f++) box(W + .2, .18, .4, mat('#d6cfc4'), 0, f * fl, .2, scene);
  // ground floor glass lobby
  box(W - 2, 3, .1, dusk ? mat('#2a2622', { e: '#ffcf96', ei: .9, r: .1, m: .3 }) : mat('#6d828f', { r: .1, m: .4 }), 0, 1.65, .05, scene, { cast: false });
  for (let i = 0; i < 11; i++) box(.08, 3.1, .15, M.blackMetal, -W / 2 + 1 + i * 2, 1.7, .12, scene);
  // canopy + sign
  box(8, .25, 3, mat('#1e2326', { r: .6 }), 0, 3.6, 1.5, scene);
  const sl = new THREE.SpotLight(0xffd6a0, dusk ? 60 : 10, 12, .9, .8, 2); sl.position.set(0, 3.4, 2.2); sl.target.position.set(0, 0, 2.5); scene.add(sl); scene.add(sl.target);
  const sign = plane(7, 1.3, new THREE.MeshBasicMaterial({ map: K.texText([{ text: 'VENUS HOTEL', font: 'Jakarta', size: 150, weight: 800 }], { w: 2048, h: 380, dir: 'ltr', color: '#ffffff', glow: dusk ? 26 : 0 }), transparent: true, toneMapped: false }), scene);
  sign.position.set(0, 4.5, 3.02); sign.material.color.setScalar(dusk ? 1.6 : .95);
  const fa = plane(4, 1.1, new THREE.MeshBasicMaterial({ map: K.texText([{ text: 'هتل ونوس', size: 190, weight: 800 }], { w: 1400, h: 380, color: '#ffffff', glow: dusk ? 26 : 0 }), transparent: true, toneMapped: false }), scene);
  fa.position.set(-W / 2 + 3.3, H - 2.2, .1); fa.material.color.setScalar(dusk ? 1.5 : .95);
  // stars on the roof sign
  // trees + lamps
  const tr = K.rng(5);
  for (const x of [-13, -7, 7, 13]) {
    cyl(.16, .24, 3.4, mat('#4a3a2c'), x, 1.7, 5.5, scene, 10);
    for (let k = 0; k < 14; k++) sphere(.7 + tr() * .6, mat(dusk ? ['#33402c', '#3b4a33'][k % 2] : ['#55703d', '#64824a'][k % 2], { r: .95 }), x + (tr() - .5) * 2.4, 4 + tr() * 2.2, 5.5 + (tr() - .5) * 2.2, scene);
  }
  for (const x of [-10, 0, 10]) {
    cyl(.07, .1, 5, M.blackMetal, x, 2.5, 6.8, scene, 10);
    const head = sphere(.25, mat('#fff', { e: '#ffd9a6', ei: dusk ? 5 : .2 }), x, 5, 6.8, scene); head.castShadow = false;
    if (dusk) { const pl = new THREE.PointLight(0xffc98f, 40, 14, 2); pl.position.set(x, 4.8, 6.8); scene.add(pl); }
  }
  // a car
  const car = new THREE.Group(); scene.add(car); car.position.set(5, 0, 10);
  box(4.4, .8, 1.8, mat('#c7ccd1', { r: .25, m: .7 }), 0, .7, 0, car, { round: .3, seg: 4 });
  box(2.4, .6, 1.6, mat('#1b2228', { r: .1, m: .6 }), -.2, 1.35, 0, car, { round: .25, seg: 4 });
  for (const x of [-1.4, 1.4]) for (const z of [-.85, .85]) { const wh = cyl(.36, .36, .25, mat('#111'), x, .36, z, car, 20); wh.rotation.x = Math.PI / 2; }
  return {
    scene,
    shots: { main: { pos: [-17, 1.8, 31], target: [1.5, 1.8, -3], fov: 48, shift: .42 } },
    exposure: dusk ? .95 : .9,
  };
}

export const SCENES = {
  'room-classic': () => hotelRoom(ROOMS.classic),
  'room-comfort': () => hotelRoom(ROOMS.comfort),
  'room-deluxe': () => hotelRoom(ROOMS.deluxe),
  'room-quad': () => hotelRoom(ROOMS.quad),
  'room-suite-bed': () => hotelRoom(ROOMS.suiteBed),
  'room-suite-kids': () => hotelRoom(ROOMS.suiteKids),
  'suite-living': suiteLiving,
  'bathroom': () => bathroom({ tub: false }),
  'bathroom-tub': () => bathroom({ tub: true }),
  corridor, lobby, breakfast, cafe,
  'exterior-dusk': () => exterior('dusk'),
  'exterior-day': () => exterior('day'),
};
