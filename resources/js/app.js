import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import VueApexCharts from 'vue3-apexcharts';
import AppLayout from './Layouts/AppLayout.vue';

createInertiaApp({
  title: (title) => (title ? `${title} — PLN Monitor ULPLTD POKA` : 'PLN Monitor ULPLTD POKA'),
  resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
    return pages[`./Pages/${name}.vue`];
  },
  // Persistent layout: AppLayout tidak di-mount ulang saat pindah halaman.
  // Halaman Auth (Login, Lupa Password) tampil tanpa layout.
  layout: (name) => (name.startsWith('Auth/') ? null : AppLayout),
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(VueApexCharts)
      .mount(el);
  },
  progress: {
    color: '#06b6d4',
  },
});
