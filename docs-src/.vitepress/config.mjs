import { defineConfig } from 'vitepress';
import { MANUAL_META, MANUAL_SECTIONS } from '../../resources/js/utils/manual.js';

const GROUP_MAP = new Map([
  ['pendahuluan', 'Pengenalan'],
  ['akses', 'Pengenalan'],
  ['peran', 'Pengenalan'],
  ['dashboard', 'Modul Operasional'],
  ['monitoring-arus', 'Modul Operasional'],
  ['monitoring-kwh', 'Modul Operasional'],
  ['monitoring-operasi-engine', 'Modul Operasional'],
  ['monitoring-gangguan', 'Modul Operasional'],
  ['monitoring-bbm', 'Modul Operasional'],
  ['users', 'Administrasi & Bantuan'],
  ['faq', 'Administrasi & Bantuan'],
]);

const grouped = new Map();
MANUAL_SECTIONS.forEach((section, index) => {
  const groupName = GROUP_MAP.get(section.id) ?? 'Panduan';
  if (!grouped.has(groupName)) grouped.set(groupName, []);
  grouped.get(groupName).push({
    text: `${index + 1}. ${section.title}`,
    link: `/${section.id}`,
  });
});

const sidebar = Array.from(grouped.entries()).map(([text, items]) => ({
  text,
  items,
}));

export default defineConfig({
  lang: 'id-ID',
  title: MANUAL_META.title,
  description: MANUAL_META.subtitle,
  base: '/docs/',
  outDir: '../public/docs',
  cleanUrls: false,
  lastUpdated: false,

  head: [
    ['meta', { name: 'theme-color', content: '#0a66b2' }],
  ],

  themeConfig: {
    siteTitle: 'PLN Monitor Docs',
    nav: [
      { text: 'Beranda', link: '/' },
      { text: 'Mulai', link: '/pendahuluan' },
      { text: 'Tanya Jawab', link: '/faq' },
      { text: 'Buka Aplikasi', link: '/../', target: '_self' },
    ],
    sidebar,
    outline: {
      level: [2, 3],
      label: 'Di halaman ini',
    },
    docFooter: {
      prev: 'Sebelumnya',
      next: 'Berikutnya',
    },
    darkModeSwitchLabel: 'Tampilan',
    lightModeSwitchTitle: 'Beralih ke mode terang',
    darkModeSwitchTitle: 'Beralih ke mode gelap',
    sidebarMenuLabel: 'Daftar Isi',
    returnToTopLabel: 'Kembali ke atas',
    search: {
      provider: 'local',
      options: {
        translations: {
          button: {
            buttonText: 'Cari panduan...',
            buttonAriaLabel: 'Cari di panduan',
          },
          modal: {
            noResultsText: 'Tidak ada hasil untuk',
            resetButtonTitle: 'Bersihkan pencarian',
            footer: {
              selectText: 'untuk memilih',
              navigateText: 'untuk navigasi',
              closeText: 'untuk tutup',
            },
          },
        },
      },
    },
    footer: {
      message: `Versi ${MANUAL_META.version} · Diperbarui ${MANUAL_META.updated}`,
      copyright: 'PLN Monitor — ULPLTD Poka',
    },
  },
});
