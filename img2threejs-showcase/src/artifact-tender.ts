import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';
import { RoomEnvironment } from 'three/examples/jsm/environments/RoomEnvironment.js';
import {
  createModernTenderBoatModel,
  createModernTenderBoatLookDevLights,
  makeTenderStudioBackground,
} from './demos/modern-tender/createModernTenderBoatModel';

const app = document.getElementById('app')!;

const renderer = new THREE.WebGLRenderer({ antialias: true });
renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
renderer.toneMapping = THREE.ACESFilmicToneMapping;
renderer.toneMappingExposure = 1.0;
renderer.shadowMap.enabled = true;
renderer.shadowMap.type = THREE.PCFSoftShadowMap;
renderer.outputColorSpace = THREE.SRGBColorSpace;
app.appendChild(renderer.domElement);

const scene = new THREE.Scene();
scene.background = makeTenderStudioBackground();

const pmrem = new THREE.PMREMGenerator(renderer);
scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
pmrem.dispose();

const camera = new THREE.PerspectiveCamera(30, 1, 0.1, 100);
camera.position.set(-8.8, 3.4, 7.6);

const controls = new OrbitControls(camera, renderer.domElement);
controls.enableDamping = true;
controls.target.set(0, 0.25, -0.2);
controls.minDistance = 4;
controls.maxDistance = 20;
controls.maxPolarAngle = Math.PI * 0.5;
controls.update();

scene.add(createModernTenderBoatLookDevLights());
scene.add(createModernTenderBoatModel({ shadows: true }));

const ground = new THREE.Mesh(
  new THREE.PlaneGeometry(40, 40),
  new THREE.ShadowMaterial({ opacity: 0.28 }),
);
ground.rotation.x = -Math.PI / 2;
ground.receiveShadow = true;
scene.add(ground);

function resize(): void {
  const w = window.innerWidth;
  const h = window.innerHeight;
  camera.aspect = w / Math.max(1, h);
  camera.updateProjectionMatrix();
  renderer.setSize(w, h);
}
window.addEventListener('resize', resize);
resize();

const tickers: Array<(dt: number, elapsed: number) => void> = [];
scene.traverse((o) => {
  const t = (o.userData as { tick?: unknown }).tick;
  if (typeof t === 'function') tickers.push(t as (dt: number, elapsed: number) => void);
});

const clock = new THREE.Clock();
function loop(): void {
  requestAnimationFrame(loop);
  const dt = clock.getDelta();
  const elapsed = clock.getElapsedTime();
  for (const t of tickers) t(dt, elapsed);
  controls.update();
  renderer.render(scene, camera);
}
loop();
