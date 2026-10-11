// Render SSR komponen Shared (Modal, FormField, Button, RowActions) untuk cek markup aksesibilitas.
//   node tests/Frontend/ssr-components.mjs
import { createServer } from 'vite';

const vite = await createServer({ server: { middlewareMode: true }, appType: 'custom', logLevel: 'error' });
let failed = 0;
const check = (label, cond) => {
  console.log(`${cond ? 'PASS' : 'FAIL'}  ${label}`);
  if (!cond) failed++;
};

try {
  const { components, h, renderApp } = await vite.ssrLoadModule('/tests/Frontend/ssr-entry.js');
  const { FormField, Button, Modal, RowActions, StatusBadge, StatusBanner, KpiStat } = components;

  // FormField: label terhubung ke input, error per field
  let { html } = await renderApp(() => h(FormField, { label: 'Tanggal', type: 'date', modelValue: '2026-09-24', error: 'Tanggal wajib diisi.', required: true }));
  const forId = html.match(/<label[^>]*for="([^"]+)"/)?.[1];
  check('FormField: <label for> ada', !!forId);
  check('FormField: input id sama dengan label for', !!forId && html.includes(`<input id="${forId}"`));
  check('FormField: pesan error tampil & aria-describedby menunjuk ke error', html.includes('Tanggal wajib diisi.') && html.includes(`aria-describedby="${forId}-error"`) && html.includes(`id="${forId}-error"`));
  check('FormField: aria-invalid saat error', html.includes('aria-invalid="true"'));
  check('FormField: kelas error memakai token status (border-crit)', html.includes('border-crit'));

  ({ html } = await renderApp(() => h(FormField, { label: 'A' })));
  const id2 = html.match(/for="([^"]+)"/)?.[1];
  ({ html } = await renderApp(() => [h(FormField, { label: 'A' }), h(FormField, { label: 'B' })]));
  const ids = [...html.matchAll(/for="([^"]+)"/g)].map((m) => m[1]);
  check('FormField: id unik per field', ids.length === 2 && ids[0] !== ids[1] && !!id2);

  // Button: loading -> disabled + aria-busy + gaya disabled
  ({ html } = await renderApp(() => h(Button, { type: 'submit', loading: true }, () => 'Simpan')));
  check('Button: loading memasang atribut disabled', /<button[^>]*\sdisabled[\s>]/.test(html));
  check('Button: aria-busy saat loading', html.includes('aria-busy="true"'));
  check('Button: ada gaya disabled: (opacity & cursor)', html.includes('disabled:opacity-50') && html.includes('disabled:cursor-not-allowed'));
  check('Button: spinner tampil saat loading', html.includes('animate-spin'));
  ({ html } = await renderApp(() => h(Button, {}, () => 'Batal')));
  check('Button: tidak loading -> tidak disabled', !/<button[^>]*\sdisabled[\s>]/.test(html));

  // RowActions (BUG-15): varian mobile >= 44px dan berjarak
  ({ html } = await renderApp(() => h(RowActions, { size: 'lg', editLabel: 'Edit A', deleteLabel: 'Hapus A' })));
  check('RowActions lg: tombol p-2.5 + min-w-11 min-h-11 (>= 44px)', (html.match(/p-2\.5 min-w-11 min-h-11/g) || []).length === 2);
  check('RowActions lg: jarak antar tombol gap-3', html.includes('gap-3'));
  check('RowActions: aria-label Edit & Hapus', html.includes('aria-label="Edit A"') && html.includes('aria-label="Hapus A"'));
  ({ html } = await renderApp(() => h(RowActions, {})));
  check('RowActions sm (desktop): ringkas p-1.5', (html.match(/(?<![\w-])p-1\.5/g) || []).length === 2 && !html.includes('min-w-11'));

  // Modal (Teleport ke body)
  globalThis.document = { activeElement: null, addEventListener() {}, removeEventListener() {}, body: { style: {} } };
  let { teleports } = await renderApp(() => h(Modal, { show: true, title: 'Tambah Data' }, { default: () => h('input', { name: 'x' }), footer: () => 'F' }));
  const modalHtml = teleports.body ?? '';
  check('Modal: di-teleport ke body', modalHtml.length > 0);
  check('Modal: role="dialog" & aria-modal="true"', modalHtml.includes('role="dialog"') && modalHtml.includes('aria-modal="true"'));
  const labelledBy = modalHtml.match(/aria-labelledby="([^"]+)"/)?.[1];
  check('Modal: aria-labelledby menunjuk ke judul', !!labelledBy && modalHtml.includes(`id="${labelledBy}"`) && modalHtml.includes('Tambah Data'));
  check('Modal: panel max-h-[90vh] overflow-y-auto', modalHtml.includes('max-h-[90vh]') && modalHtml.includes('overflow-y-auto'));
  check('Modal: tombol tutup punya aria-label', modalHtml.includes('aria-label="Tutup"'));
  ({ teleports } = await renderApp(() => h(Modal, { show: false, title: 'X' })));
  check('Modal: show=false tidak merender dialog', !(teleports.body ?? '').includes('role="dialog"'));

  // IMPLEMENTATION.md Fase C/D/F: status & tombol memakai token
  let out;
  ({ html: out } = await renderApp(() => h(Button, {}, () => 'Simpan')));
  check('Button primary: bg-brand + text-on-solid (bukan text-white)', out.includes('bg-brand') && out.includes('text-on-solid') && !out.includes('text-white'));
  for (const [code, cls] of [['ok', 'text-ok'], ['warning', 'text-warn'], ['critical', 'text-crit'], ['empty', 'text-empty']]) {
    ({ html: out } = await renderApp(() => h(StatusBadge, { status: code })));
    check(`StatusBadge ${code}: kelas ${cls}`, out.includes(cls));
  }
  ({ html: out } = await renderApp(() => h(StatusBadge, { status: 'tidak-dikenal' })));
  check('StatusBadge kode tidak dikenal -> empty', out.includes('text-empty') && out.includes('Belum ada data'));
  ({ html: out } = await renderApp(() => h(StatusBanner, { status: 'critical', title: 'Stok BBM kritis: 0,17 hari (±4 jam)', detail: 'netto 17 L' })));
  check('StatusBanner critical: role=alert, garis kiri crit, teks tampil', out.includes('role="alert"') && out.includes('border-l-crit') && out.includes('0,17 hari'));
  ({ html: out } = await renderApp(() => h(StatusBanner, { status: 'warning', title: 'Stok BBM menipis' })));
  check('StatusBanner warning: role=status, garis kiri warn', out.includes('role="status"') && out.includes('border-l-warn'));
  ({ html: out } = await renderApp(() => h(KpiStat, { label: 'Beban arus', value: null, unit: 'A', hint: 'Belum diinput hari ini' })));
  check('KpiStat value null -> "—" tanpa satuan', out.includes('>—<') && !out.includes('>A<'));
  ({ html: out } = await renderApp(() => h(KpiStat, { label: 'Gangguan', value: '0', hint: 'Tidak ada gangguan' })));
  check('KpiStat value "0" tetap tampil 0 (bukan —)', /tabular-nums text-fg">0</.test(out) && !/tabular-nums text-fg">—</.test(out));
} finally {
  await vite.close();
}

console.log(failed ? `\n${failed} FAIL` : '\nSEMUA PASS');
process.exit(failed ? 1 : 0);
