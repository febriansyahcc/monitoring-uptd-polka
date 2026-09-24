<template>
  <div>
    <!-- Bottom Nav Bar (Mobile < 768px only) -->
    <nav
      class="fixed bottom-0 inset-x-0 md:hidden z-40 backdrop-blur-lg border-t px-1.5 py-1 flex items-center justify-around shadow-2xl transition-colors duration-300 bg-white/95 border-slate-200 text-slate-600 dark:bg-slate-900/95 dark:border-slate-800 dark:text-slate-400"
    >
      <!-- Main Nav Items (Max 4 slots) -->
      <Link
        v-for="item in primaryNavItems"
        :key="item.href"
        :href="item.href"
        :class="[
          'flex flex-col items-center justify-center gap-0.5 px-2 py-1.5 rounded-xl transition-all min-h-[44px] min-w-[52px]',
          isActive(item)
            ? 'font-bold scale-105 ' + item.activeClass
            : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'
        ]"
      >
        <component :is="item.icon" class="w-5 h-5 shrink-0" />
        <span class="text-[10px] leading-tight tracking-tight">{{ item.label }}</span>
      </Link>

      <!-- Button "Lainnya" (Opens bottom sheet for remaining items + actions) -->
      <button
        type="button"
        @click="showMore = !showMore"
        :class="[
          'flex flex-col items-center justify-center gap-0.5 px-2 py-1.5 rounded-xl transition-all min-h-[44px] min-w-[52px] cursor-pointer',
          isMoreActive || showMore
            ? 'text-cyan-600 dark:text-cyan-400 font-bold scale-105'
            : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'
        ]"
        aria-label="Buka menu navigasi lainnya"
        :aria-expanded="showMore"
      >
        <MoreHorizontal class="w-5 h-5 shrink-0" />
        <span class="text-[10px] leading-tight tracking-tight">Lainnya</span>
      </button>
    </nav>

    <!-- Bottom Sheet Modal / Drawer for "Lainnya" -->
    <Teleport to="body">
      <div v-if="showMore" class="fixed inset-0 z-50 md:hidden flex flex-col justify-end">
        <!-- Backdrop -->
        <div
          class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
          @click="showMore = false"
        />

        <!-- Sheet Content -->
        <div
          class="relative w-full max-h-[85vh] overflow-y-auto rounded-t-3xl border-t p-5 shadow-2xl space-y-4 bg-white border-slate-200 text-slate-900 dark:bg-slate-900 dark:border-slate-800 dark:text-slate-100"
          role="dialog"
          aria-modal="true"
          aria-label="Menu Navigasi Lainnya"
        >
          <!-- Header Handle & Title -->
          <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2">
              <div class="w-1.5 h-4 rounded-full bg-cyan-500" />
              <h3 class="text-sm font-bold text-slate-900 dark:text-white">Menu Lainnya</h3>
            </div>
            <button
              type="button"
              @click="showMore = false"
              class="p-2 rounded-xl border transition-colors border-slate-200 text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:border-slate-800 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white"
              aria-label="Tutup menu lainnya"
            >
              <X class="w-4 h-4" />
            </button>
          </div>

          <!-- Secondary Permitted Nav Items (Gangguan, BBM, Pengguna, etc.) -->
          <div v-if="secondaryNavItems.length" class="space-y-1">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1 px-1">
              Modul Monitoring
            </p>
            <div class="grid grid-cols-1 gap-1.5">
              <Link
                v-for="item in secondaryNavItems"
                :key="item.href"
                :href="item.href"
                @click="showMore = false"
                :class="[
                  'flex items-center justify-between p-3 rounded-2xl border transition-all text-xs font-semibold min-h-[44px]',
                  isActive(item)
                    ? 'border-cyan-500/40 bg-cyan-50 text-cyan-700 dark:bg-cyan-500/10 dark:text-cyan-400 dark:border-cyan-500/30'
                    : 'border-slate-200 bg-slate-50/50 text-slate-700 hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-800/40 dark:text-slate-300 dark:hover:bg-slate-800'
                ]"
              >
                <div class="flex items-center gap-3">
                  <div
                    :class="[
                      'w-8 h-8 rounded-xl border flex items-center justify-center shrink-0 shadow-sm',
                      item.iconBg
                    ]"
                  >
                    <component :is="item.icon" class="w-4 h-4" />
                  </div>
                  <div>
                    <span class="block font-bold text-slate-900 dark:text-white">{{ item.fullLabel || item.label }}</span>
                    <span class="text-[10px] font-normal text-slate-500 dark:text-slate-400">{{ item.desc }}</span>
                  </div>
                </div>
                <div v-if="isActive(item)" class="w-2 h-2 rounded-full bg-cyan-500" />
              </Link>
            </div>
          </div>

          <!-- Quick Actions & User Info -->
          <div class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-3">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-1">
              Akun & Preferensi
            </p>

            <!-- User Info Card -->
            <div
              v-if="$page.props.auth.user"
              class="p-3 rounded-2xl border flex items-center justify-between bg-slate-50 border-slate-200 dark:bg-slate-800/60 dark:border-slate-800 text-xs"
            >
              <div>
                <p class="font-bold text-slate-900 dark:text-white truncate max-w-[200px]">
                  {{ $page.props.auth.user.name }}
                </p>
                <p class="text-[10px] text-slate-500 dark:text-slate-400">
                  {{ $page.props.auth.user.email }}
                </p>
              </div>
              <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-lg border uppercase bg-cyan-100 text-cyan-800 border-cyan-300 dark:bg-cyan-950 dark:text-cyan-300 dark:border-cyan-700">
                {{ $page.props.auth.user.role }}
              </span>
            </div>

            <!-- Logout Link -->
            <Link
              href="/logout"
              method="post"
              as="button"
              @click="showMore = false"
              class="w-full py-3 px-4 rounded-2xl border transition-all text-xs font-bold flex items-center justify-center gap-2 border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-400 dark:hover:bg-rose-900/40 min-h-[44px] cursor-pointer"
            >
              <LogOut class="w-4 h-4" />
              <span>Keluar dari Akun</span>
            </Link>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { usePermission } from '@/composables/usePermission';
import {
  LayoutDashboard,
  Activity,
  Zap,
  Cpu,
  AlertTriangle,
  Fuel,
  ShieldCheck,
  MoreHorizontal,
  X,
  LogOut,
} from 'lucide-vue-next';

defineProps({
  isDarkMode: {
    type: Boolean,
    default: false,
  },
});

const page = usePage();
const { can } = usePermission();
const showMore = ref(false);

const onKeydown = (e) => {
  if (e.key === 'Escape' && showMore.value) {
    showMore.value = false;
  }
};

onMounted(() => {
  if (typeof window !== 'undefined') {
    window.addEventListener('keydown', onKeydown);
  }
});

onUnmounted(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('keydown', onKeydown);
  }
});

// Definisi seluruh menu navigasi aplikasi beserta hak aksesnya
const allNavItems = [
  {
    href: '/',
    label: 'Dashboard',
    fullLabel: 'Dashboard Utama',
    desc: 'Ringkasan KPI operasional pembangkit',
    icon: LayoutDashboard,
    permission: null,
    activeClass: 'text-cyan-600 dark:text-cyan-400',
    iconBg: 'bg-cyan-50 border-cyan-200 text-cyan-600 dark:bg-cyan-500/10 dark:border-cyan-500/30 dark:text-cyan-400',
    match: (url) => url === '/' || url.startsWith('/dashboard'),
  },
  {
    href: '/monitoring-arus',
    label: 'Arus',
    fullLabel: 'Monitoring Arus',
    desc: 'Pencatatan beban arus listrik per feeder',
    icon: Activity,
    permission: 'monitoring_arus.view',
    activeClass: 'text-cyan-600 dark:text-cyan-400',
    iconBg: 'bg-cyan-50 border-cyan-200 text-cyan-600 dark:bg-cyan-500/10 dark:border-cyan-500/30 dark:text-cyan-400',
    match: (url) => url.startsWith('/monitoring-arus'),
  },
  {
    href: '/monitoring-kwh',
    label: 'kWh',
    fullLabel: 'Monitoring kWh Produksi',
    desc: 'Produksi stand harian engine & penyulang',
    icon: Zap,
    permission: 'monitoring_kwh.view',
    activeClass: 'text-emerald-600 dark:text-emerald-400',
    iconBg: 'bg-emerald-50 border-emerald-200 text-emerald-600 dark:bg-emerald-500/10 dark:border-emerald-500/30 dark:text-emerald-400',
    match: (url) => url.startsWith('/monitoring-kwh'),
  },
  {
    href: '/monitoring-operasi-engine',
    label: 'Engine',
    fullLabel: 'Monitoring Operasi Engine',
    desc: 'Logsheet control panel & engine area',
    icon: Cpu,
    permission: 'monitoring_engine.view',
    activeClass: 'text-violet-600 dark:text-violet-400',
    iconBg: 'bg-violet-50 border-violet-200 text-violet-600 dark:bg-violet-500/10 dark:border-violet-500/30 dark:text-violet-400',
    match: (url) => url.startsWith('/monitoring-operasi-engine'),
  },
  {
    href: '/monitoring-gangguan',
    label: 'Gangguan',
    fullLabel: 'Monitoring Gangguan',
    desc: 'Log pencatatan kendala dan trip operasi',
    icon: AlertTriangle,
    permission: 'monitoring_gangguan.view',
    activeClass: 'text-rose-600 dark:text-rose-400',
    iconBg: 'bg-rose-50 border-rose-200 text-rose-600 dark:bg-rose-500/10 dark:border-rose-500/30 dark:text-rose-400',
    match: (url) => url.startsWith('/monitoring-gangguan'),
  },
  {
    href: '/monitoring-bbm',
    label: 'BBM',
    fullLabel: 'Monitoring Stok BBM',
    desc: 'Stok tangki, konsumsi & estimasi HOP',
    icon: Fuel,
    permission: 'monitoring_bbm.view',
    activeClass: 'text-amber-600 dark:text-amber-400',
    iconBg: 'bg-amber-50 border-amber-200 text-amber-600 dark:bg-amber-500/10 dark:border-amber-500/30 dark:text-amber-400',
    match: (url) => url.startsWith('/monitoring-bbm'),
  },
  {
    href: '/users',
    label: 'Pengguna',
    fullLabel: 'Pengelolaan Pengguna',
    desc: 'Manajemen akun pegawai & hak akses PBAC',
    icon: ShieldCheck,
    permission: 'users.manage',
    activeClass: 'text-purple-600 dark:text-purple-400',
    iconBg: 'bg-purple-50 border-purple-200 text-purple-600 dark:bg-purple-500/10 dark:border-purple-500/30 dark:text-purple-400',
    match: (url) => url.startsWith('/users'),
  },
];

// Filter item berdasarkan izin user
const permittedItems = computed(() => {
  return allNavItems.filter((item) => !item.permission || can(item.permission));
});

// Maksimal 4 item utama tampil langsung di bottom bar
const primaryNavItems = computed(() => permittedItems.value.slice(0, 4));

// Sisanya masuk ke dalam bottom sheet "Lainnya"
const secondaryNavItems = computed(() => permittedItems.value.slice(4));

const isActive = (item) => item.match(page.url);

const isMoreActive = computed(() => {
  return secondaryNavItems.value.some((item) => isActive(item));
});
</script>
