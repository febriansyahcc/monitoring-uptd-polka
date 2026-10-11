// Satu-satunya definisi menu aplikasi: dipakai Sidebar (desktop), BottomNav (mobile) dan judul Header.
// Modul tidak punya warna sendiri (IMPLEMENTATION.md P2) — ikon & state aktif diwarnai oleh komponen nav.
import { LayoutDashboard, Activity, Zap, Cpu, AlertTriangle, Fuel, Users } from 'lucide-vue-next';

export const NAV_ITEMS = [
  {
    href: '/',
    label: 'Dashboard',
    short: 'Dashboard',
    title: 'Dashboard',
    desc: 'Ringkasan kondisi hari ini',
    icon: LayoutDashboard,
    permission: null,
    group: 'monitoring',
    match: (url) => url === '/' || url.startsWith('/dashboard') || url.startsWith('/?'),
  },
  {
    href: '/monitoring-arus',
    label: 'Arus',
    short: 'Arus',
    title: 'Monitoring Arus',
    desc: 'Beban arus per penyulang tiap interval',
    icon: Activity,
    permission: 'monitoring_arus.view',
    group: 'monitoring',
    match: (url) => url.startsWith('/monitoring-arus'),
  },
  {
    href: '/monitoring-kwh',
    label: 'kWh Produksi',
    short: 'kWh',
    title: 'Monitoring kWh Produksi',
    desc: 'Stand harian engine & penyulang',
    icon: Zap,
    permission: 'monitoring_kwh.view',
    group: 'monitoring',
    match: (url) => url.startsWith('/monitoring-kwh'),
  },
  {
    href: '/monitoring-operasi-engine',
    label: 'Operasi Engine',
    short: 'Engine',
    title: 'Monitoring Operasi Engine',
    desc: 'Logsheet control panel & engine area',
    icon: Cpu,
    permission: 'monitoring_engine.view',
    group: 'monitoring',
    match: (url) => url.startsWith('/monitoring-operasi-engine'),
  },
  {
    href: '/monitoring-gangguan',
    label: 'Gangguan',
    short: 'Gangguan',
    title: 'Monitoring Gangguan',
    desc: 'Catatan gangguan & penanganannya',
    icon: AlertTriangle,
    permission: 'monitoring_gangguan.view',
    group: 'monitoring',
    match: (url) => url.startsWith('/monitoring-gangguan'),
  },
  {
    href: '/monitoring-bbm',
    label: 'Stok BBM',
    short: 'BBM',
    title: 'Monitoring Stok BBM',
    desc: 'Stok tangki, pemakaian & HOP',
    icon: Fuel,
    permission: 'monitoring_bbm.view',
    group: 'monitoring',
    match: (url) => url.startsWith('/monitoring-bbm'),
  },
  {
    href: '/users',
    label: 'Pengguna',
    short: 'Pengguna',
    title: 'Pengguna',
    desc: 'Akun pegawai & hak akses',
    icon: Users,
    permission: 'users.manage',
    group: 'admin',
    match: (url) => url.startsWith('/users'),
  },
];

export const NAV_GROUPS = [
  { key: 'monitoring', label: 'Monitoring' },
  { key: 'admin', label: 'Administrasi' },
];

export const ROLE_LABELS = {
  admin: 'Admin',
  manager: 'Manager',
  tl_operasi: 'TL Operasi',
  tl_pemeliharaan: 'TL Pemeliharaan',
  operator: 'Operator',
};

export const navItemFor = (url) => NAV_ITEMS.find((item) => item.match(url ?? '')) ?? null;
