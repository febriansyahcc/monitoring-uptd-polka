// Render SSR komponen Shared (Modal, FormField, Button) untuk cek markup aksesibilitas.
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
  const { FormField, Button, Modal } = components;

  // FormField: label terhubung ke input, error per field
  let { html } = await renderApp(() => h(FormField, { label: 'Tanggal', type: 'date', modelValue: '2026-09-24', error: 'Tanggal wajib diisi.', required: true }));
  const forId = html.match(/<label[^>]*for="([^"]+)"/)?.[1];
  check('FormField: <label for> ada', !!forId);
  check('FormField: input id sama dengan label for', !!forId && html.includes(`<input id="${forId}"`));
  check('FormField: pesan error tampil & aria-describedby menunjuk ke error', html.includes('Tanggal wajib diisi.') && html.includes(`aria-describedby="${forId}-error"`) && html.includes(`id="${forId}-error"`));
  check('FormField: aria-invalid saat error', html.includes('aria-invalid="true"'));
  check('FormField: kelas error (border-rose-500)', html.includes('border-rose-500'));

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
} finally {
  await vite.close();
}

console.log(failed ? `\n${failed} FAIL` : '\nSEMUA PASS');
process.exit(failed ? 1 : 0);
