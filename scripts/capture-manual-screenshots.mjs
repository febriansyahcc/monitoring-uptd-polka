// Mengambil screenshot setiap halaman untuk Manual Book.
//
//   npm run manual:screenshots            (isi ulang database demo lalu ambil screenshot)
//   npm run manual:screenshots -- --no-seed
//
// Langkah:
//   1. Database terpisah (MANUAL_DB, default `manual_demo`) dibuat & diisi ManualDemoSeeder.
//      Database kerja di .env TIDAK disentuh.
//   2. Server Laravel sementara dijalankan di port MANUAL_PORT (default 8010) memakai database itu.
//   3. Chrome headless (puppeteer-core) login dengan akun demo Admin lalu memotret tiap halaman
//      ke public/images/manual/*.png. Server dihentikan setelah selesai.
// Setelah ini jalankan `npm run manual:pdf` agar PDF memakai gambar terbaru.
import { spawn, execFileSync } from 'node:child_process';
import { mkdirSync, existsSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import puppeteer from 'puppeteer-core';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const outDir = resolve(root, 'public/images/manual');
const DB = process.env.MANUAL_DB || 'manual_demo';
const PORT = process.env.MANUAL_PORT || '8010';
const BASE = `http://127.0.0.1:${PORT}`;
const seed = !process.argv.includes('--no-seed');

if (!/^[a-z0-9_]+$/i.test(DB) || DB === readEnv('DB_DATABASE')) {
  console.error(`MANUAL_DB "${DB}" tidak valid atau sama dengan database kerja di .env.`);
  process.exit(1);
}

function readEnv(key) {
  try {
    const line = execFileSync('php', ['-r', `$e=parse_ini_file('.env', false, INI_SCANNER_RAW); echo $e['${key}'] ?? '';`], { cwd: root });
    return String(line).trim();
  } catch {
    return '';
  }
}

const demoEnv = { ...process.env, DB_DATABASE: DB, APP_ENV: 'local' };
const php = (args, env = process.env) => execFileSync('php', args, { cwd: root, env, stdio: 'inherit' });

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

// ---------- 1. Database demo ----------
if (seed) {
  console.log(`Menyiapkan database demo "${DB}"...`);
  php(['artisan', 'tinker', `--execute=DB::statement('CREATE DATABASE IF NOT EXISTS \`${DB}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');`]);
  php(['artisan', 'migrate:fresh', '--seed', '--seeder=ManualDemoSeeder', '--force'], demoEnv);
}

// ---------- 2. Server sementara ----------
const server = spawn('php', ['artisan', 'serve', `--port=${PORT}`, '--host=127.0.0.1'], { cwd: root, env: demoEnv, stdio: 'ignore' });
const stopServer = () => {
  if (server.exitCode !== null) return;
  if (process.platform === 'win32') {
    try {
      execFileSync('taskkill', ['/pid', String(server.pid), '/T', '/F'], { stdio: 'ignore' });
    } catch {}
  } else {
    server.kill();
  }
};
process.on('exit', stopServer);

for (let i = 0; ; i++) {
  try {
    const res = await fetch(`${BASE}/login`);
    if (res.ok) break;
  } catch {}
  if (i > 60) throw new Error('Server Laravel sementara tidak merespons.');
  await sleep(1000);
}

// ---------- 3. Screenshot ----------
const chrome = [
  process.env.CHROME_PATH,
  'C:/Program Files/Google/Chrome/Application/chrome.exe',
  'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
  '/usr/bin/google-chrome',
  '/usr/bin/chromium',
  '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
].find((p) => p && existsSync(p));
if (!chrome) throw new Error('Chrome/Edge tidak ditemukan. Atur CHROME_PATH.');

mkdirSync(outDir, { recursive: true });

const DESKTOP = { width: 1280, height: 800, deviceScaleFactor: 1 };
const MOBILE = { width: 390, height: 844, deviceScaleFactor: 2, isMobile: true, hasTouch: true };

// name, path, viewport, aksi opsional sebelum dipotret
const SHOTS = [
  { name: 'dashboard', path: '/' },
  { name: 'arus', path: '/monitoring-arus' },
  { name: 'arus-fasa', path: '/monitoring-arus', click: 'Arus per fasa' },
  { name: 'kwh', path: '/monitoring-kwh' },
  { name: 'kwh-form', path: '/monitoring-kwh', click: 'Tambah Data Engine', modal: true },
  { name: 'engine', path: '/monitoring-operasi-engine' },
  { name: 'engine-form', path: '/monitoring-operasi-engine', click: 'Tambah Data', modal: true },
  { name: 'gangguan', path: '/monitoring-gangguan' },
  { name: 'gangguan-form', path: '/monitoring-gangguan', click: 'Catat Gangguan Baru', modal: true },
  { name: 'bbm', path: '/monitoring-bbm' },
  { name: 'bbm-form', path: '/monitoring-bbm', click: 'Tambah Data Stok BBM', modal: true },
  { name: 'users', path: '/users' },
  { name: 'users-form', path: '/users', click: 'Tambah pengguna', modal: true },
  { name: 'profil', path: '/profil' },
  { name: 'mobile-dashboard', path: '/', viewport: MOBILE },
  { name: 'mobile-arus', path: '/monitoring-arus', viewport: MOBILE },
];

const browser = await puppeteer.launch({ executablePath: chrome, headless: true, args: ['--hide-scrollbars'] });
try {
  const page = await browser.newPage();
  // Mode terang & sidebar terbuka untuk semua screenshot
  await page.evaluateOnNewDocument(() => {
    try {
      localStorage.setItem('pln_theme', 'light');
      localStorage.setItem('pln_sidebar_collapsed', '0');
    } catch {}
  });
  await page.emulateMediaFeatures([{ name: 'prefers-color-scheme', value: 'light' }]);
  await page.setViewport(DESKTOP);

  const settle = async () => {
    await page.waitForNetworkIdle({ idleTime: 500, timeout: 30000 }).catch(() => {});
    await sleep(1200); // animasi grafik ApexCharts
  };
  const save = async (name) => {
    await page.screenshot({ path: resolve(outDir, `${name}.png`) });
    console.log(`  ✓ ${name}.png`);
  };

  // Halaman login (panel akun uji coba lokal disembunyikan)
  await page.goto(`${BASE}/login`, { waitUntil: 'networkidle0' });
  await page.evaluate(() => {
    const label = [...document.querySelectorAll('p, span, h2, h3')].find((el) => el.textContent.includes('Uji Coba Cepat'));
    if (label) label.parentElement.style.display = 'none';
  });
  await save('login');

  // Login sebagai Admin (akun dari UserSeeder) agar semua menu terlihat
  await page.type('input[type="text"]', 'admin@pln.co.id');
  await page.type('input[type="password"]', 'password');
  await page.keyboard.press('Enter');
  await page.waitForFunction(() => !location.pathname.startsWith('/login'), { timeout: 60000 });

  for (const shot of SHOTS) {
    await page.setViewport(shot.viewport ?? DESKTOP);
    await page.goto(`${BASE}${shot.path}`, { waitUntil: 'networkidle0', timeout: 60000 });
    await settle();
    if (shot.click) {
      await page.locator(`::-p-text(${shot.click})`).click();
      await sleep(shot.modal ? 700 : 1200);
    }
    await save(shot.name);
  }
} finally {
  await browser.close();
  stopServer();
}

console.log(`Selesai: ${outDir}`);
