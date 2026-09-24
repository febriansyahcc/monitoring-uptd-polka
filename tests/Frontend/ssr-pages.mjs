// Render SSR setiap halaman (light & dark) dengan data Inertia asli dari Laravel, tanpa browser.
// Mengecek: render tanpa error runtime, layout persisten terpasang, isDarkMode dari AppLayout,
// Toast global, halaman Auth tanpa layout, dan tidak ada warning Vue.
//
//   node tests/Frontend/ssr-pages.mjs
//
// Data diambil read-only dari database tes (default `laravel_testing`, ganti via SSR_DB). Isi dulu:
//   DB_DATABASE=laravel_testing php artisan migrate:fresh --force
//   DB_DATABASE=laravel_testing php artisan db:seed --class=FeederSeeder --force
//   DB_DATABASE=laravel_testing php artisan db:seed --class=UserSeeder --force
// (`php artisan test` mengosongkan database ini, jadi seed ulang setelahnya.)
import { createServer } from 'vite';
import { execFileSync } from 'node:child_process';

const ROUTES = ['/', '/monitoring-arus', '/monitoring-kwh', '/monitoring-operasi-engine', '/monitoring-gangguan', '/monitoring-bbm', '/users'];
const GUEST_ROUTES = ['/login', '/forgot-password'];

const env = { ...process.env, DB_CONNECTION: 'mysql', DB_DATABASE: process.env.SSR_DB ?? 'laravel_testing', SESSION_DRIVER: 'array', CACHE_STORE: 'array' };
const dump = (email, urls) =>
  JSON.parse(execFileSync('php', ['tests/Frontend/dump-pages.php', email, ...urls], { env, encoding: 'utf8', maxBuffer: 64 * 1024 * 1024 }));
const data = { ...dump('admin@pln.co.id', ROUTES), ...dump('-', GUEST_ROUTES) };
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
