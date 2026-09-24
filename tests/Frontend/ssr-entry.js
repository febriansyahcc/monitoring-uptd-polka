// Entry SSR untuk verifikasi frontend tanpa browser (dimuat oleh ssr-pages.mjs & ssr-components.mjs
// lewat Vite ssrLoadModule). Konfigurasi resolve/layout harus sama dengan resources/js/app.js.
import { createSSRApp, h } from 'vue';
import { renderToString } from 'vue/server-renderer';
import { createInertiaApp } from '@inertiajs/vue3';
import AppLayout from '/resources/js/Layouts/AppLayout.vue';

const pages = import.meta.glob('/resources/js/Pages/**/*.vue', { eager: true });

export async function render(page) {
  const result = await createInertiaApp({
    page,
    render: renderToString,
    resolve: (name) => pages[`/resources/js/Pages/${name}.vue`],
    layout: (name) => (name.startsWith('Auth/') ? null : AppLayout),
    setup: ({ App, props, plugin }) => createSSRApp({ render: () => h(App, props) }).use(plugin),
  });
  return result.body;
}

// Render komponen tunggal (untuk cek Shared components)
import FormField from '/resources/js/Components/Shared/FormField.vue';
import Button from '/resources/js/Components/Shared/Button.vue';
import Modal from '/resources/js/Components/Shared/Modal.vue';
export const components = { FormField, Button, Modal };
export { h };
export async function renderApp(render) {
  const ctx = {};
  const html = await renderToString(createSSRApp({ render }), ctx);
  return { html, teleports: ctx.teleports ?? {} };
}
