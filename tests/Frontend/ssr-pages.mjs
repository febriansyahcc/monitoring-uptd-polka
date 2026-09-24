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
  '/monitoring-bbm': ['Tambah Data Stok BBM', '>Aksi<', 'aria-label="Edit stok BBM', 'aria-label="Hapus stok BBM'],
};

const env = { ...process.env, DB_CONNECTION: 'mysql', DB_DATABASE: process.env.SSR_DB ?? 'laravel_testing', SESSION_DRIVER: 'array', CACHE_STORE: 'array' };
const dump = (email, urls) =>
  JSON.parse(execFileSync('php', ['tests/Frontend/dump-pages.php', email, ...urls], { env, encoding: 'utf8', maxBuffer: 64 * 1024 * 1024 }));
const data = { ...dump('admin@pln.co.id', ROUTES), ...dump('-', GUEST_ROUTES) };
const managerData = dump('manager@pln.co.id', Object.keys(ACTION_MARKERS));
// TL Pemeliharaan tidak punya izin lihat kWh & BBM -> dashboard tidak boleh menautkan ke sana
const tlPemeliharaanDashboard = dump('tl.pemeliharaan@pln.co.id', ['/'])['/'];
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

  // Tampilan mobile (kartu md:hidden) tersedia di halaman tabel; tabel desktop disembunyikan di HP
  globalThis.document = { documentElement: { classList: { contains: () => false, toggle() {} } } };
  for (const url of [...Object.keys(ACTION_MARKERS), '/users']) {
    const html = await render(data[url].page);
    check(`${url} mobile: ada tampilan kartu "block md:hidden"`, html.includes('block md:hidden'));
    check(`${url} mobile: tabel desktop "hidden md:block"`, html.includes('hidden md:block'));
  }
  const usersHtml = await render(data['/users'].page);
  check('/users mobile: tombol kartu (Atur PBAC & status) min-h-11 (>= 44px)', (usersHtml.match(/min-h-11/g) || []).length >= 2);

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

  // Dashboard (PAGE-07): link KPI mengikuti izin, tanggal hari ini tampil
  globalThis.document = { documentElement: { classList: { contains: () => false, toggle() {} } } };
  // Hanya isi halaman (<main>); BottomNav mobile belum difilter izin (BUG-06, Fase 4)
  const mainOf = (html) => html.slice(html.indexOf('<main'), html.indexOf('</main>'));
  const adminDash = mainOf(await render(data['/'].page));
  const tlDash = mainOf(await render(tlPemeliharaanDashboard.page));
  check('/ admin (isi halaman): kartu KPI menautkan ke /monitoring-kwh & /monitoring-bbm', adminDash.includes('href="/monitoring-kwh"') && adminDash.includes('href="/monitoring-bbm"'));
  check('/ TL Pemeliharaan (isi halaman): tidak ada link ke /monitoring-kwh & /monitoring-bbm', !tlDash.includes('href="/monitoring-kwh"') && !tlDash.includes('href="/monitoring-bbm"'));
  check('/ TL Pemeliharaan: link ke /monitoring-gangguan tetap ada', tlDash.includes('href="/monitoring-gangguan"'));
  check('/ tanggal hari ini (todayDateFormatted) tampil', adminDash.includes(data['/'].page.props.todayDateFormatted));
  check('/ tidak ada label "Shift <jam>" di KPI arus', !/Shift \d{2}\.\d{2}/.test(adminDash));

  // Toast menampilkan flash.success & flash.error dari props
  const dash = data['/'].page;
  globalThis.document = { documentElement: { classList: { contains: () => false, toggle() {} } } };
  const html = await render({ ...dash, props: { ...dash.props, flash: { success: 'Data tersimpan XYZ', error: 'Gagal ABC', status: null } } });
  check('Toast merender flash.success', html.includes('Data tersimpan XYZ') && html.includes('role="status"'));
  check('Toast merender flash.error', html.includes('Gagal ABC') && html.includes('role="alert"'));

  // Fase 4: BottomNav mobile (BUG-06) difilter izin, max slot + "Lainnya", link /users untuk admin
  const adminFull = await render(data['/'].page);
  const tlFull = await render(tlPemeliharaanDashboard.page);
  check('BottomNav mobile: tombol "Lainnya" ada', adminFull.includes('Lainnya') && adminFull.includes('aria-label="Buka menu navigasi lainnya"'));
  check('BottomNav mobile: admin punya link /users', adminFull.includes('href="/users"'));
  check('BottomNav mobile: TL Pemeliharaan TIDAK punya link /monitoring-kwh & /monitoring-bbm', !tlFull.includes('href="/monitoring-kwh"') && !tlFull.includes('href="/monitoring-bbm"'));
  check('BottomNav mobile: item punya target sentuh min-h-[44px]', adminFull.includes('min-h-[44px]'));

  // Fase 4: Header & Jam WIT & Judul (BUG-09)
  check('Header: judul halaman aktif ter-render', adminFull.includes('Dashboard Utama'));
  check('Header: jam operasional WIT ter-render', adminFull.includes('WIT'));

  // Fase 4: Login demo RBAC hanya tampil saat isLocal (BUG-17)
  const loginLocal = await render({ ...data['/login'].page, props: { ...data['/login'].page.props, isLocal: true } });
  const loginProd = await render({ ...data['/login'].page, props: { ...data['/login'].page.props, isLocal: false } });
  check('Login: tombol demo RBAC tampil saat isLocal=true', loginLocal.includes('Uji Coba Cepat 5 Peran'));
  check('Login: tombol demo RBAC TIDAK tampil saat isLocal=false', !loginProd.includes('Uji Coba Cepat 5 Peran'));
  check('Login: tombol submit punya gaya disabled', loginLocal.includes('disabled:opacity-60'));
} finally {
  console.warn = origWarn;
  await vite.close();
}

console.log(failed ? `\n${failed} FAIL` : '\nSEMUA PASS');
process.exit(failed ? 1 : 0);
