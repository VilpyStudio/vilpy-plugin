import * as THREE from 'three';

/**
 * Modern open day-tender / sloop, rebuilt in code from three studio reference views
 * (3/4 aerial, top-down plan, port side profile).
 *
 * Rebuild v2 — tuned for the real boat's identity: a long, low, sleek grey gelcoat hull
 * with a fine plumb bow, a smooth lofted V-bottom with a soft chine, a slim gunwale cap and
 * a bright rubrail; a two-tone topside (light sheer strake -> mid grey -> near-black bottom);
 * fitted taupe diamond-quilted upholstery that fills the cockpit (bow lounge, side lounges,
 * a full-beam aft sunpad) around a central teak-herringbone walkway; a low centre console
 * with a stainless destroyer wheel; a teak swim platform, a whip antenna and a green nav light.
 *
 * Frame: +X starboard, +Y up, +Z forward (bow at +Z). Keel sits just above y=0.
 * Runtime: root.userData.tick (float bob/roll + idle helm) and root.userData.sculptRuntime.
 */

// ------------------------------------------------------------------ small math helpers
function smootherstep(e0: number, e1: number, x: number): number {
  const t = Math.min(1, Math.max(0, (x - e0) / (e1 - e0)));
  return t * t * t * (t * (t * 6 - 15) + 10);
}
function lerp(a: number, b: number, t: number): number {
  return a + (b - a) * t;
}
function catmull(p0: number, p1: number, p2: number, p3: number, t: number): number {
  const t2 = t * t;
  const t3 = t2 * t;
  return (
    0.5 *
    (2 * p1 + (-p0 + p2) * t + (2 * p0 - 5 * p1 + 4 * p2 - p3) * t2 + (-p0 + 3 * p1 - 3 * p2 + p3) * t3)
  );
}
function mulberry32(seed: number): () => number {
  let a = seed >>> 0;
  return () => {
    a |= 0;
    a = (a + 0x6d2b79f5) | 0;
    let t = Math.imul(a ^ (a >>> 15), 1 | a);
    t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
    return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
  };
}

// ------------------------------------------------------------------ hull cross-section
// Normalized half-section from sheer (top) down to the keel centreline, in fractions:
//   xFrac = fraction of the station half-beam, yFrac = fraction of section depth below sheer.
// Straight topside, a soft chine knuckle, then a V-bottom to the keel. Sampled smooth.
const SECTION_CTRL: [number, number][] = [
  [1.0, 0.0], // sheer edge
  [1.0, 0.28], // straight topside
  [0.99, 0.5],
  [0.95, 0.66], // approaching chine
  [0.9, 0.72], // chine knuckle
  [0.62, 0.9], // turn of the bilge
  [0.24, 0.98],
  [0.0, 1.0], // keel centreline
];
function buildHalfProfile(samplesPerSeg = 3): [number, number][] {
  const pts: [number, number][] = [];
  const c = SECTION_CTRL;
  for (let i = 0; i < c.length - 1; i++) {
    const p0 = c[Math.max(0, i - 1)];
    const p1 = c[i];
    const p2 = c[i + 1];
    const p3 = c[Math.min(c.length - 1, i + 2)];
    const steps = i === c.length - 2 ? samplesPerSeg + 1 : samplesPerSeg;
    for (let s = 0; s < steps; s++) {
      const t = s / samplesPerSeg;
      pts.push([catmull(p0[0], p1[0], p2[0], p3[0], t), catmull(p0[1], p1[1], p2[1], p3[1], t)]);
    }
  }
  return pts;
}
const HALF_PROFILE = buildHalfProfile(3);

/** Two-tone hull colour by depth-fraction below the sheer. */
function hullColorAt(yFrac: number): [number, number, number] {
  const c = new THREE.Color();
  if (yFrac < 0.12) c.setHex(0xc4c8cd); // bright sheer strake
  else if (yFrac < 0.66) c.setHex(0x767c86); // mid grey topside
  else if (yFrac < 0.9) c.setHex(0x565b64); // darker lower topside
  else c.setHex(0x14161a); // near-black bottom
  // soften the band edges
  if (yFrac >= 0.09 && yFrac < 0.16) c.lerp(new THREE.Color(0x767c86), (yFrac - 0.09) / 0.07);
  if (yFrac >= 0.62 && yFrac < 0.7) c.lerp(new THREE.Color(0x565b64), (yFrac - 0.62) / 0.08);
  return [c.r, c.g, c.b];
}

// ------------------------------------------------------------------ hull stations
interface Station {
  z: number;
  hb: number; // half beam at sheer
  sheerY: number;
  keelY: number;
}
function buildStations(N: number): Station[] {
  const zStern = -3.35;
  const zBow = 3.35;
  const out: Station[] = [];
  for (let i = 0; i <= N; i++) {
    const t = i / N; // 0 stern .. 1 bow
    const z = lerp(zStern, zBow, t);
    // half beam: fuller, held long amidships, fine entry to a sharp bow
    let hb = 1.02 + 0.16 * smootherstep(0.0, 0.32, t);
    hb -= 0.03 * smootherstep(0.58, 0.85, t);
    hb *= 1 - Math.pow(smootherstep(0.85, 1.0, t), 1.8); // sharpen the bow
    hb = Math.max(0.015, hb);
    // low sheer, gentle sweep up to a raised bow, a touch of stern lift
    const sheerY =
      0.56 + 0.26 * smootherstep(0.62, 1.0, t) + 0.03 * (1 - smootherstep(0.0, 0.2, t));
    // V-bottom: lowest amidships, forefoot rises toward the bow, slight stern rocker
    const keelY = 0.03 + 0.09 * (1 - smootherstep(0.0, 0.16, t)) + 0.5 * smootherstep(0.5, 1.0, t);
    out.push({ z, hb, sheerY, keelY });
  }
  return out;
}

function beamAtZ(st: Station[], z: number): number {
  return sampleField(st, z, (s) => s.hb);
}
function sheerAtZ(st: Station[], z: number): number {
  return sampleField(st, z, (s) => s.sheerY);
}
function sampleField(st: Station[], z: number, get: (s: Station) => number): number {
  if (z <= st[0].z) return get(st[0]);
  if (z >= st[st.length - 1].z) return get(st[st.length - 1]);
  for (let i = 0; i < st.length - 1; i++) {
    if (z >= st[i].z && z <= st[i + 1].z) {
      const t = (z - st[i].z) / (st[i + 1].z - st[i].z);
      return lerp(get(st[i]), get(st[i + 1]), t);
    }
  }
  return get(st[st.length - 1]);
}

// ------------------------------------------------------------------ hull mesh
function ringPoints(s: Station): { p: [number, number, number]; yf: number }[] {
  const depth = s.sheerY - s.keelY;
  const stbd: { p: [number, number, number]; yf: number }[] = [];
  for (const [xf, yf] of HALF_PROFILE) {
    stbd.push({ p: [xf * s.hb, s.sheerY - yf * depth, s.z], yf });
  }
  // full ring: starboard sheer->keel, then port keel->sheer (mirror), open across the top
  const port = stbd
    .slice(0, -1)
    .reverse()
    .map((v) => ({ p: [-v.p[0], v.p[1], v.p[2]] as [number, number, number], yf: v.yf }));
  return [...stbd, ...port];
}

function buildHull(stations: Station[], mat: THREE.Material): THREE.Mesh {
  const pos: number[] = [];
  const col: number[] = [];
  const rings = stations.map(ringPoints);
  const P = rings[0].length;
  const pushV = (v: { p: [number, number, number]; yf: number }): void => {
    pos.push(v.p[0], v.p[1], v.p[2]);
    const c = hullColorAt(v.yf);
    col.push(c[0], c[1], c[2]);
  };
  for (let i = 0; i < rings.length - 1; i++) {
    const A = rings[i];
    const B = rings[i + 1];
    for (let p = 0; p < P - 1; p++) {
      pushV(A[p]);
      pushV(B[p]);
      pushV(B[p + 1]);
      pushV(A[p]);
      pushV(B[p + 1]);
      pushV(A[p + 1]);
    }
  }
  // transom cap (stern ring), fanned from centroid, near-black
  const stern = rings[0];
  let cx = 0;
  let cy = 0;
  for (const v of stern) {
    cx += v.p[0];
    cy += v.p[1];
  }
  cx /= stern.length;
  cy /= stern.length;
  const cz = stern[0].p[2];
  const centroid = { p: [cx, cy, cz] as [number, number, number], yf: 0.8 };
  for (let p = 0; p < stern.length; p++) {
    const a = stern[p];
    const b = stern[(p + 1) % stern.length];
    pushV(centroid);
    pushV(b);
    pushV(a);
  }
  const geo = new THREE.BufferGeometry();
  geo.setAttribute('position', new THREE.Float32BufferAttribute(pos, 3));
  geo.setAttribute('color', new THREE.Float32BufferAttribute(col, 3));
  geo.computeVertexNormals();
  const mesh = new THREE.Mesh(geo, mat);
  mesh.castShadow = true;
  mesh.receiveShadow = true;
  mesh.name = 'hull';
  return mesh;
}

/** Sheer polyline (bow-ward on starboard, back on port), closed around the bow/stern. */
function sheerLoop(stations: Station[], yOffset = 0, xScale = 1): THREE.Vector3[] {
  const pts: THREE.Vector3[] = [];
  for (const s of stations) pts.push(new THREE.Vector3(s.hb * xScale, s.sheerY + yOffset, s.z));
  for (let i = stations.length - 1; i >= 0; i--) {
    const s = stations[i];
    pts.push(new THREE.Vector3(-s.hb * xScale, s.sheerY + yOffset, s.z));
  }
  return pts;
}

// ------------------------------------------------------------------ textures
function makeQuiltTexture(): THREE.CanvasTexture {
  const cv = document.createElement('canvas');
  cv.width = cv.height = 256;
  const ctx = cv.getContext('2d')!;
  ctx.fillStyle = '#808080';
  ctx.fillRect(0, 0, 256, 256);
  const cells = 6;
  const step = 256 / cells;
  for (let gy = -1; gy < cells + 1; gy++) {
    for (let gx = -1; gx < cells + 1; gx++) {
      const cxp = gx * step + (gy % 2 ? step * 0.5 : 0) + step * 0.5;
      const cyp = gy * step + step * 0.5;
      const g = ctx.createRadialGradient(cxp, cyp, 1, cxp, cyp, step * 0.6);
      g.addColorStop(0, '#d0d0d0');
      g.addColorStop(0.72, '#9a9a9a');
      g.addColorStop(1, '#585858');
      ctx.fillStyle = g;
      ctx.beginPath();
      ctx.moveTo(cxp, cyp - step * 0.5);
      ctx.lineTo(cxp + step * 0.5, cyp);
      ctx.lineTo(cxp, cyp + step * 0.5);
      ctx.lineTo(cxp - step * 0.5, cyp);
      ctx.closePath();
      ctx.fill();
    }
  }
  ctx.strokeStyle = '#3a3a3a';
  ctx.lineWidth = 2;
  for (let gy = -1; gy < cells + 1; gy++) {
    for (let gx = -1; gx < cells + 1; gx++) {
      const cxp = gx * step + (gy % 2 ? step * 0.5 : 0) + step * 0.5;
      const cyp = gy * step + step * 0.5;
      ctx.beginPath();
      ctx.moveTo(cxp, cyp - step * 0.5);
      ctx.lineTo(cxp + step * 0.5, cyp);
      ctx.lineTo(cxp, cyp + step * 0.5);
      ctx.lineTo(cxp - step * 0.5, cyp);
      ctx.closePath();
      ctx.stroke();
    }
  }
  const tex = new THREE.CanvasTexture(cv);
  tex.wrapS = tex.wrapT = THREE.RepeatWrapping;
  return tex;
}

function makeTeakHerringboneTexture(): THREE.CanvasTexture {
  const cv = document.createElement('canvas');
  cv.width = cv.height = 512;
  const ctx = cv.getContext('2d')!;
  const rnd = mulberry32(1337);
  ctx.fillStyle = '#d3c096';
  ctx.fillRect(0, 0, 512, 512);
  const plank = (x: number, y: number, w: number, h: number, rot: number): void => {
    ctx.save();
    ctx.translate(x, y);
    ctx.rotate(rot);
    const shade = 0.9 + rnd() * 0.16;
    ctx.fillStyle = `rgb(${Math.round(214 * shade)},${Math.round(194 * shade)},${Math.round(152 * shade)})`;
    ctx.fillRect(-w / 2, -h / 2, w, h);
    ctx.strokeStyle = 'rgba(70,52,30,0.85)';
    ctx.lineWidth = 3;
    ctx.strokeRect(-w / 2, -h / 2, w, h);
    ctx.restore();
  };
  const pw = 92;
  const ph = 32;
  for (let row = -1; row < 8; row++) {
    for (let col = -1; col < 8; col++) {
      const x = col * pw + (row % 2) * (pw / 2);
      const y = row * ph;
      plank(x, y, pw, ph, (row + col) % 2 ? Math.PI / 4 : -Math.PI / 4);
    }
  }
  const tex = new THREE.CanvasTexture(cv);
  tex.wrapS = tex.wrapT = THREE.RepeatWrapping;
  return tex;
}

function makeSlatTexture(): THREE.CanvasTexture {
  const cv = document.createElement('canvas');
  cv.width = cv.height = 256;
  const ctx = cv.getContext('2d')!;
  const rnd = mulberry32(77);
  const slats = 10;
  const h = 256 / slats;
  for (let i = 0; i < slats; i++) {
    const shade = 0.9 + rnd() * 0.14;
    ctx.fillStyle = `rgb(${Math.round(216 * shade)},${Math.round(198 * shade)},${Math.round(158 * shade)})`;
    ctx.fillRect(0, i * h, 256, h - 2);
    ctx.fillStyle = 'rgba(66,50,30,0.65)';
    ctx.fillRect(0, i * h + h - 2, 256, 2);
  }
  const tex = new THREE.CanvasTexture(cv);
  tex.wrapS = tex.wrapT = THREE.RepeatWrapping;
  return tex;
}

export function makeTenderStudioBackground(): THREE.CanvasTexture {
  const cv = document.createElement('canvas');
  cv.width = cv.height = 512;
  const ctx = cv.getContext('2d')!;
  const g = ctx.createRadialGradient(256, 190, 40, 256, 340, 540);
  g.addColorStop(0, '#ffffff');
  g.addColorStop(0.6, '#eef0f2');
  g.addColorStop(1, '#d5d9de');
  ctx.fillStyle = g;
  ctx.fillRect(0, 0, 512, 512);
  const tex = new THREE.CanvasTexture(cv);
  tex.colorSpace = THREE.SRGBColorSpace;
  return tex;
}

// ------------------------------------------------------------------ fitted cushions/decks
interface Span {
  z: number;
  xL: number;
  xR: number;
}

/** Flat textured surface (cockpit sole / foredeck) following the plan taper. */
function buildSurface(spans: Span[], y: number, mat: THREE.Material, uvScale = 0.5): THREE.Mesh {
  const pos: number[] = [];
  const uv: number[] = [];
  const push = (x: number, z: number): void => {
    pos.push(x, y, z);
    uv.push(x * uvScale, z * uvScale);
  };
  for (let i = 0; i < spans.length - 1; i++) {
    const a = spans[i];
    const b = spans[i + 1];
    push(a.xL, a.z);
    push(a.xR, a.z);
    push(b.xR, b.z);
    push(a.xL, a.z);
    push(b.xR, b.z);
    push(b.xL, b.z);
  }
  const geo = new THREE.BufferGeometry();
  geo.setAttribute('position', new THREE.Float32BufferAttribute(pos, 3));
  geo.setAttribute('uv', new THREE.Float32BufferAttribute(uv, 2));
  geo.computeVertexNormals();
  const mesh = new THREE.Mesh(geo, mat);
  mesh.receiveShadow = true;
  return mesh;
}

/**
 * A fitted upholstered pad = quilted top surface (slightly crowned) + grey skirt/underside,
 * built as a prism from a plan boundary so it fills the cockpit instead of floating.
 */
function buildCushion(
  spans: Span[],
  topY: number,
  thickness: number,
  quiltMat: THREE.Material,
  baseMat: THREE.Material,
  crown = 0.02,
): THREE.Group {
  const g = new THREE.Group();
  const topPos: number[] = [];
  const topUv: number[] = [];
  const mid = spans.length - 1;
  const crownAt = (z: number, x: number, s: Span): number => {
    const cz = (spans[0].z + spans[mid].z) / 2;
    const halfLen = Math.max(0.001, (spans[mid].z - spans[0].z) / 2);
    const spanW = Math.max(0.001, s.xR - s.xL);
    const cxMid = (s.xL + s.xR) / 2;
    const fz = 1 - Math.pow(Math.abs(z - cz) / halfLen, 2);
    const fx = 1 - Math.pow(Math.abs(x - cxMid) / (spanW / 2), 2);
    return topY + crown * Math.max(0, fz) * Math.max(0, fx);
  };
  const pushTop = (x: number, z: number, s: Span): void => {
    topPos.push(x, crownAt(z, x, s), z);
    topUv.push(x * 3.0, z * 3.0);
  };
  for (let i = 0; i < spans.length - 1; i++) {
    const a = spans[i];
    const b = spans[i + 1];
    pushTop(a.xL, a.z, a);
    pushTop(a.xR, a.z, a);
    pushTop(b.xR, b.z, b);
    pushTop(a.xL, a.z, a);
    pushTop(b.xR, b.z, b);
    pushTop(b.xL, b.z, b);
  }
  const topGeo = new THREE.BufferGeometry();
  topGeo.setAttribute('position', new THREE.Float32BufferAttribute(topPos, 3));
  topGeo.setAttribute('uv', new THREE.Float32BufferAttribute(topUv, 2));
  topGeo.computeVertexNormals();
  const top = new THREE.Mesh(topGeo, quiltMat);
  top.castShadow = true;
  top.receiveShadow = true;
  g.add(top);

  // skirt: vertical band around the perimeter, down by thickness
  const boundary: [number, number][] = [];
  for (const s of spans) boundary.push([s.xR, s.z]);
  for (let i = spans.length - 1; i >= 0; i--) boundary.push([spans[i].xL, spans[i].z]);
  const sPos: number[] = [];
  for (let i = 0; i < boundary.length; i++) {
    const a = boundary[i];
    const b = boundary[(i + 1) % boundary.length];
    const yTop = topY;
    const yBot = topY - thickness;
    sPos.push(a[0], yTop, a[1], b[0], yBot, b[1], b[0], yTop, b[1]);
    sPos.push(a[0], yTop, a[1], a[0], yBot, a[1], b[0], yBot, b[1]);
  }
  const skirtGeo = new THREE.BufferGeometry();
  skirtGeo.setAttribute('position', new THREE.Float32BufferAttribute(sPos, 3));
  skirtGeo.computeVertexNormals();
  const skirt = new THREE.Mesh(skirtGeo, baseMat);
  skirt.castShadow = true;
  g.add(skirt);
  return g;
}

/** Left/right inner-hull x at a given z, inset from the sheer beam. */
function innerX(stations: Station[], z: number, inset: number): number {
  return Math.max(0.02, beamAtZ(stations, z) * 0.9 - inset);
}
function spanFull(stations: Station[], z0: number, z1: number, steps: number, inset: number): Span[] {
  const out: Span[] = [];
  for (let i = 0; i <= steps; i++) {
    const z = lerp(z0, z1, i / steps);
    const x = innerX(stations, z, inset);
    out.push({ z, xL: -x, xR: x });
  }
  return out;
}

// ------------------------------------------------------------------ console
function buildConsole(
  greyMat: THREE.Material,
  blackMat: THREE.Material,
  steelMat: THREE.Material,
  soleY: number,
): { group: THREE.Group; wheelSpin: THREE.Group } {
  const g = new THREE.Group();
  g.name = 'console';
  const redMat = new THREE.MeshStandardMaterial({ color: 0xc0392b, roughness: 0.4 });

  // grey pedestal (tall, slightly narrower, gently tapered toward the top)
  const h = 0.62;
  const base = new THREE.Mesh(new THREE.BoxGeometry(0.5, h, 0.56), greyMat);
  base.position.y = soleY + h / 2;
  base.castShadow = true;
  base.receiveShadow = true;
  g.add(base);
  const topCap = new THREE.Mesh(new THREE.BoxGeometry(0.46, 0.06, 0.5), greyMat);
  topCap.position.y = soleY + h + 0.02;
  topCap.castShadow = true;
  g.add(topCap);

  // black raked dash facing aft, with small round gauges + a red kill switch
  const dashPivot = new THREE.Group();
  dashPivot.position.set(0, soleY + h + 0.03, -0.22);
  dashPivot.rotation.x = THREE.MathUtils.degToRad(32);
  g.add(dashPivot);
  const dash = new THREE.Mesh(new THREE.BoxGeometry(0.44, 0.3, 0.05), blackMat);
  dash.castShadow = true;
  dashPivot.add(dash);
  for (const dx of [-0.11, 0.02]) {
    const gauge = new THREE.Mesh(new THREE.CylinderGeometry(0.045, 0.045, 0.02, 20), steelMat);
    gauge.rotation.x = Math.PI / 2;
    gauge.position.set(dx, 0.03, 0.035);
    dashPivot.add(gauge);
  }
  const kill = new THREE.Mesh(new THREE.CylinderGeometry(0.012, 0.012, 0.02, 12), redMat);
  kill.rotation.x = Math.PI / 2;
  kill.position.set(0.15, -0.06, 0.035);
  dashPivot.add(kill);
  // black throttle lever to starboard on the top cap
  const throttle = new THREE.Mesh(new THREE.CylinderGeometry(0.009, 0.012, 0.13, 8), blackMat);
  throttle.position.set(0.2, soleY + h + 0.1, -0.02);
  throttle.rotation.x = THREE.MathUtils.degToRad(-28);
  g.add(throttle);

  // black 3-spoke destroyer wheel on a short raked column, facing aft
  const wheelMount = new THREE.Group();
  wheelMount.position.set(0, soleY + h + 0.14, -0.26);
  wheelMount.rotation.x = THREE.MathUtils.degToRad(30);
  g.add(wheelMount);
  const wheelSpin = new THREE.Group();
  wheelSpin.name = 'wheelSpin';
  wheelMount.add(wheelSpin);
  const rim = new THREE.Mesh(new THREE.TorusGeometry(0.135, 0.015, 12, 44), blackMat);
  wheelSpin.add(rim);
  const hub = new THREE.Mesh(new THREE.CylinderGeometry(0.028, 0.028, 0.05, 16), steelMat);
  hub.rotation.x = Math.PI / 2;
  wheelSpin.add(hub);
  for (let i = 0; i < 3; i++) {
    const ang = (i * 2 * Math.PI) / 3 + Math.PI / 2; // one spoke up, two lower
    const spoke = new THREE.Mesh(new THREE.BoxGeometry(0.012, 0.12, 0.012), blackMat);
    spoke.position.set(Math.cos(ang) * 0.065, Math.sin(ang) * 0.065, 0);
    spoke.rotation.z = ang - Math.PI / 2;
    wheelSpin.add(spoke);
  }
  const column = new THREE.Mesh(new THREE.CylinderGeometry(0.024, 0.028, 0.16, 12), blackMat);
  column.position.set(0, -0.12, -0.02);
  wheelMount.add(column);
  return { group: g, wheelSpin };
}

// ------------------------------------------------------------------ factory
export interface ModernTenderBoatOptions {
  shadows?: boolean;
}

export function createModernTenderBoatModel(options: ModernTenderBoatOptions = {}): THREE.Group {
  const shadows = options.shadows ?? true;
  const root = new THREE.Group();
  root.name = 'modern-tender-boat';

  // ---- materials
  const gelcoat = new THREE.MeshPhysicalMaterial({
    vertexColors: true,
    roughness: 0.32,
    metalness: 0.0,
    clearcoat: 0.7,
    clearcoatRoughness: 0.28,
    side: THREE.DoubleSide,
  });
  const capMat = new THREE.MeshPhysicalMaterial({
    color: 0xd6dade,
    roughness: 0.28,
    clearcoat: 0.8,
    clearcoatRoughness: 0.22,
  });
  const darkTrim = new THREE.MeshStandardMaterial({ color: 0x111318, roughness: 0.5 });
  const chrome = new THREE.MeshStandardMaterial({ color: 0xd7dbe0, metalness: 0.95, roughness: 0.16 });
  const liner = new THREE.MeshStandardMaterial({ color: 0x828994, roughness: 0.55, side: THREE.DoubleSide });

  const quiltTex = makeQuiltTexture();
  const upholstery = new THREE.MeshStandardMaterial({
    color: 0xc7ba9e,
    roughness: 0.92,
    bumpMap: quiltTex,
    bumpScale: 2.2,
  });
  quiltTex.repeat.set(9, 9);
  const seatBase = new THREE.MeshStandardMaterial({ color: 0x8a919b, roughness: 0.6 });
  const teakTex = makeTeakHerringboneTexture();
  teakTex.repeat.set(2, 2);
  const teak = new THREE.MeshStandardMaterial({ color: 0xffffff, map: teakTex, roughness: 0.72 });
  const slatTex = makeSlatTexture();
  const slat = new THREE.MeshStandardMaterial({ color: 0xffffff, map: slatTex, roughness: 0.7 });
  const blackMat = new THREE.MeshStandardMaterial({ color: 0x15171b, roughness: 0.5 });
  const steel = new THREE.MeshStandardMaterial({ color: 0xccd0d6, metalness: 0.9, roughness: 0.25 });
  const navGreen = new THREE.MeshStandardMaterial({
    color: 0x1f9d55,
    emissive: 0x18c060,
    emissiveIntensity: 0.9,
    roughness: 0.3,
  });

  const stations = buildStations(52);
  const soleY = 0.3;

  // ---- hull + inner liner
  const hull = buildHull(stations, gelcoat);
  root.add(hull);
  // thin inner liner shell (slightly inset) so the cockpit reads as a moulded interior
  const linerStations = stations.map((s) => ({
    ...s,
    hb: s.hb * 0.94,
    sheerY: s.sheerY - 0.01,
    keelY: s.keelY + 0.16,
  }));
  const linerMesh = buildHull(linerStations, liner);
  (linerMesh.material as THREE.Material).vertexColors = false;
  linerMesh.name = 'liner';
  root.add(linerMesh);

  // ---- slim gunwale cap
  const capCurve = new THREE.CatmullRomCurve3(sheerLoop(stations, 0.005), true, 'catmullrom', 0.5);
  const cap = new THREE.Mesh(new THREE.TubeGeometry(capCurve, 300, 0.028, 10, true), capMat);
  cap.castShadow = true;
  cap.name = 'gunwale';
  root.add(cap);

  // ---- bright rubrail just below the sheer + dark accent under it
  const railPts = stations
    .map((s) => new THREE.Vector3(s.hb * 1.004, s.sheerY - 0.07, s.z))
    .concat(
      stations
        .slice()
        .reverse()
        .map((s) => new THREE.Vector3(-s.hb * 1.004, s.sheerY - 0.07, s.z)),
    );
  const railCurve = new THREE.CatmullRomCurve3(railPts, true, 'catmullrom', 0.5);
  root.add(new THREE.Mesh(new THREE.TubeGeometry(railCurve, 280, 0.014, 8, true), chrome));

  // swept character line on each topside: high at the bow, dipping toward mid-aft
  const charDrop = (z: number): number => {
    const t = (z + 3.35) / 6.7; // 0 stern .. 1 bow
    return 0.1 + 0.12 * smootherstep(0.15, 0.6, t) - 0.1 * smootherstep(0.62, 1.0, t);
  };
  for (const side of [-1, 1]) {
    const pts: THREE.Vector3[] = [];
    for (const s of stations) {
      if (s.z < -2.9 || s.z > 3.0) continue;
      pts.push(new THREE.Vector3(side * s.hb * 0.995, s.sheerY - charDrop(s.z), s.z));
    }
    const cCurve = new THREE.CatmullRomCurve3(pts, false, 'catmullrom', 0.5);
    root.add(new THREE.Mesh(new THREE.TubeGeometry(cCurve, 160, 0.009, 6, false), darkTrim));
  }

  // ---- cockpit sole + foredeck (teak)
  root.add(buildSurface(spanFull(stations, -3.0, 1.55, 26, 0.16), soleY, teak, 0.55));
  const foredeckSpans = spanFull(stations, 2.35, 3.2, 8, 0.12);
  root.add(buildSurface(foredeckSpans, 0.74, teak, 0.6));

  // ---- swim platform (teak slats, aft, low at the water)
  const platform = new THREE.Mesh(new THREE.BoxGeometry(1.78, 0.08, 0.66), slat);
  platform.position.set(0, 0.3, -3.62);
  platform.castShadow = true;
  platform.receiveShadow = true;
  root.add(platform);

  // ---- fitted upholstery -------------------------------------------------------------
  const cushions = new THREE.Group();
  cushions.name = 'cushions';
  root.add(cushions);

  // aft sunpad: full-beam pad over the back third
  cushions.add(
    buildCushion(spanFull(stations, -3.15, -1.2, 16, 0.05), 0.46, 0.15, upholstery, seatBase, 0.03),
  );
  // side lounges: fill each side up to the coaming, leaving a slim central teak walkway
  const gap = 0.22;
  const sideSteps = 16;
  const leftSpans: Span[] = [];
  const rightSpans: Span[] = [];
  for (let i = 0; i <= sideSteps; i++) {
    const z = lerp(-1.35, 1.55, i / sideSteps);
    const xi = innerX(stations, z, 0.05);
    rightSpans.push({ z, xL: gap, xR: xi });
    leftSpans.push({ z, xL: -xi, xR: -gap });
  }
  cushions.add(buildCushion(rightSpans, 0.44, 0.13, upholstery, seatBase, 0.025));
  cushions.add(buildCushion(leftSpans, 0.44, 0.13, upholstery, seatBase, 0.025));
  // bow lounge: full-beam infill wrapping the forward cockpit
  cushions.add(
    buildCushion(spanFull(stations, 1.55, 2.42, 10, 0.05), 0.44, 0.13, upholstery, seatBase, 0.03),
  );

  // angled backrest bolsters along both coamings (cushions up to the rim)
  for (const side of [-1, 1]) {
    const zc = 0.15;
    const bx = innerX(stations, zc, 0.06);
    const bolster = new THREE.Mesh(new THREE.BoxGeometry(0.16, 0.34, 2.5), upholstery);
    bolster.position.set(side * (bx - 0.02), 0.6, zc);
    bolster.rotation.z = side * THREE.MathUtils.degToRad(24);
    bolster.castShadow = true;
    cushions.add(bolster);
  }

  // ---- console (slightly forward + offset to port, like the reference)
  const helmGrey = new THREE.MeshPhysicalMaterial({ color: 0x767c86, roughness: 0.4, clearcoat: 0.4 });
  const { group: console3d, wheelSpin } = buildConsole(helmGrey, blackMat, steel, soleY);
  console3d.position.set(-0.12, 0, 0.05);
  root.add(console3d);
  // dark recessed locker / step just aft of the console
  const locker = new THREE.Mesh(new THREE.BoxGeometry(0.5, 0.14, 0.5), darkTrim);
  locker.position.set(-0.12, soleY + 0.06, -0.55);
  root.add(locker);

  // ---- fittings
  const antenna = new THREE.Mesh(new THREE.CylinderGeometry(0.006, 0.009, 0.8, 8), steel);
  antenna.position.set(0.62, 0.62 + 0.4, -0.9);
  root.add(antenna);
  const antennaTip = new THREE.Mesh(new THREE.SphereGeometry(0.018, 12, 10), steel);
  antennaTip.position.set(0.62, 0.62 + 0.8, -0.9);
  root.add(antennaTip);

  const navLight = new THREE.Mesh(new THREE.SphereGeometry(0.026, 12, 10), navGreen);
  navLight.position.set(beamAtZ(stations, -0.6) * 1.0, sheerAtZ(stations, -0.6) - 0.16, -0.6);
  root.add(navLight);

  // cleats (small dark blocks on the sheer, fore + aft, both sides)
  for (const side of [-1, 1]) {
    for (const z of [1.9, -2.6]) {
      const cleat = new THREE.Mesh(new THREE.BoxGeometry(0.05, 0.03, 0.12), darkTrim);
      cleat.position.set(side * beamAtZ(stations, z) * 0.99, sheerAtZ(stations, z) + 0.02, z);
      root.add(cleat);
    }
  }

  if (!shadows) {
    root.traverse((o) => {
      const m = o as THREE.Mesh;
      m.castShadow = false;
      m.receiveShadow = false;
    });
  }

  // ---- runtime
  const baseY = 0.0;
  root.userData.tick = (_dt: number, elapsed: number): void => {
    root.position.y = baseY + Math.sin(elapsed * 0.8) * 0.018;
    root.rotation.z = Math.sin(elapsed * 0.55) * 0.008;
    root.rotation.x = Math.sin(elapsed * 0.7 + 1.3) * 0.005;
    wheelSpin.rotation.z = Math.sin(elapsed * 0.5) * 0.18;
  };
  root.userData.sculptRuntime = {
    nodes: { hull, liner: linerMesh, console: console3d, wheelSpin, cushions, platform },
    materials: { gelcoat, upholstery, teak, steel },
    sockets: {
      helm: new THREE.Vector3(0, soleY + 0.5, -0.75),
      bowLight: new THREE.Vector3(0, 0.8, 3.1),
      antenna: new THREE.Vector3(0.62, 0.62, -0.9),
    },
  };
  return root;
}

/** Bright high-key studio rig matching the white product-shot references. */
export function createModernTenderBoatLookDevLights(): THREE.Group {
  const lights = new THREE.Group();
  lights.name = 'lookdev-lights';

  const hemi = new THREE.HemisphereLight(0xffffff, 0xcbcfd4, 0.7);
  lights.add(hemi);

  const key = new THREE.DirectionalLight(0xfff4e6, 2.3);
  key.position.set(-5, 7.5, 5.5);
  key.castShadow = true;
  key.shadow.mapSize.set(2048, 2048);
  key.shadow.camera.near = 0.5;
  key.shadow.camera.far = 30;
  const kc = key.shadow.camera as THREE.OrthographicCamera;
  kc.left = -5.5;
  kc.right = 5.5;
  kc.top = 5.5;
  kc.bottom = -5.5;
  key.shadow.bias = -0.0004;
  lights.add(key);

  const fill = new THREE.DirectionalLight(0xe3ecff, 0.7);
  fill.position.set(6, 3.5, 3.5);
  lights.add(fill);

  const rim = new THREE.DirectionalLight(0xffffff, 0.85);
  rim.position.set(2, 4, -7);
  lights.add(rim);

  return lights;
}
