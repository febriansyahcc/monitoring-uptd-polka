// Render SSR setiap halaman (light & dark) dengan data Inertia asli dari Laravel, tanpa browser.
// Mengecek: render tanpa error runtime, layout persisten terpasang, isDarkMode dari AppLayout,
// Toast global, halaman Auth tanpa layout, dan tidak ada warning Vue.
//
//   node tests/Frontend/ssr-pages.mjs
//
// Juga mengecek izin di UI: penanda tombol aksi (ACTION_MARKERS) harus ada untuk admin dan
// TIDAK ada untuk manager (role view-only).
//
// Data diambil read-only dari database tes (default `laravel_testing`, ganti via SSR_DB). Isi dulu:
//   php tests/Frontend/seed-ssr.php
// (`php artisan test` mengosongkan database ini, jadi seed ulang setelahnya.)
import { createServer } from 'vite';
import { execFileSync } from 'node:child_process';

const ROUTES = ['/', '/monitoring-arus', '/monitoring-kwh', '/monitoring-operasi-engine', '/monitoring-gangguan', '/monitoring-bbm', '/users'];
const GUEST_ROUTES = ['/login', '/forgot-password'];

// Teks/markup yang hanya muncul bila user punya izin input/manage di modul tersebut
const ACTION_MARKERS = {
  '/monitoring-arus': ['Simpan Data Jam', '>Aksi<'],
  '/monitoring-kwh': ['Tambah Data Engine', '>Aksi<', 'aria-label="Edit ', 'aria-label="Hapus '],
  '/monitoring-operasi-engine': ['Tambah Data Control Panel', '>Aksi<', 'aria-label="Edit Control Panel', 'aria-label="Hapus Control Panel'],
  '/monitoring-gangguan': ['Catat Gangguan Baru', '>Aksi<', 'aria-label="Edit gangguan', 'aria-label="Hapus gangguan'],
};

const env = { ...process.env, DB_CONNECTION: 'mysql', DB_DATABASE: process.env.SSR_DB ?? 'laravel_testing', SESSION_DRIVER: 'array', CACHE_STORE: 'array' };
const dump = (email, urls) =>
  JSON.parse(execFileSync('php', ['tests/Frontend/dump-pages.php', email, ...urls], { env, encoding: 'utf8', maxBuffer: 64 * 1024 * 1024 }));
const data = { ...dump('admin@pln.co.id', ROUTES), ...dump('-', GUEST_ROUTES) };
const managerData = dump('manager@pln.co.id', Object.keys(ACTION_MARKERS));
const vite = await createServer({ server: { middlewareMode: true }, appType: 'custom', logLevel: 'error' });

const warnings = [];
const origWarn = console.warn;
console.warn = (...a) => warnings.push(a.map(String).join(' '));

let failed = 0;
const check = (label, cond) => {
  console.log(`${cond ? 'PASS' : 'FAIL'}  ${label}`);
  if (!cond) failed++;
};

try {
  const { render } = await vite.ssrLoadModule('/tests/Frontend/ssr-entry.js');

  for (const [url, { status, page }] of Object.entries(data)) {
    for (const dark of [false, true]) {
      globalThis.document = { documentElement: { classList: { contains: () => dark, toggle() {} } } };
      const tag = `${url} [${dark ? 'dark' : 'light'}]`;
      if (!page) { check(`${tag} status ${status}: page JSON ada`, false); continue; }
      const before = warnings.length;
      let html = '';
      try {
        html = await render(page);
      } catch (e) {
        check(`${tag} render tanpa error (${e.message})`, false);
        continue;
      }
      const isAuth = page.component.startsWith('Auth/');
      check(`${tag} render ${page.component}`, html.length > 500);
      if (!isAuth) {
        check(`${tag} layout ter-render (<main> + sidebar)`, html.includes('<main') && html.includes('<aside'));
        check(`${tag} isDarkMode dari layout -> bg layout ${dark ? 'gelap' : 'terang'}`, html.includes(dark ? 'bg-slate-950 text-slate-100' : 'bg-slate-50 text-slate-900'));
        check(`${tag} container Toast (aria-live) ada`, html.includes('aria-live="polite"'));
      } else {
        check(`${tag} halaman Auth tanpa layout`, !html.includes('<aside'));
      }
      const newWarn = warnings.slice(before).filter((w) => !/apexchart/i.test(w) && !/missing template or render function[\s\S]*<Anonymous type="(line|bar|donut|area|pie|radialBar)"/.test(w));
      check(`${tag} tanpa warning Vue${newWarn.length ? ': ' + newWarn[0].slice(0, 200) : ''}`, newWarn.length === 0);
    }
  }

  // Izin di UI: admin melihat tombol aksi, manager (view-only) tidak
  globalThis.document = { documentElement: { classList: { contains: () => false, toggle() {} } } };
  for (const [url, markers] of Object.entries(ACTION_MARKERS)) {
    const squash = (html) => html.replace(/>\s+/g, '>').replace(/\s+</g, '<');
    const adminHtml = squash(await render(data[url].page));
    const managerPage = managerData[url].page;
    check(`${url} manager: halaman bisa dibuka (status ${managerData[url].status})`, !!managerPage);
    if (!managerPage) continue;
    const managerHtml = squash(await render(managerPage));
    for (const marker of markers) {
      check(`${url} admin: tombol aksi "${marker}" ada`, adminHtml.includes(marker));
      check(`${url} manager: tombol aksi "${marker}" tidak ada`, !managerHtml.includes(marker));
    }
  }

  // Toast menampilkan flash.success & flash.error dari props
  const dash = data['/'].page;
  globalThis.document = { documentElement: { classList: { contains: () => false, toggle() {} } } };
  const html = await render({ ...dash, props: { ...dash.props, flash: { success: 'Data tersimpan XYZ', error: 'Gagal ABC', status: null } } });
  check('Toast merender flash.success', html.includes('Data tersimpan XYZ') && html.includes('role="status"'));
  check('Toast merender flash.error', html.includes('Gagal ABC') && html.includes('role="alert"'));
} finally {
  console.warn = origWarn;
  await vite.close();
}

console.log(failed ? `\n${failed} FAIL` : '\nSEMUA PASS');
process.exit(failed ? 1 : 0);
