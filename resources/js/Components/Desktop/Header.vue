<template>
  <header
    class="h-16 sticky top-0 z-20 flex items-center justify-between px-4 sm:px-6 transition-colors duration-300 shadow-sm border-b backdrop-blur-md bg-white/90 border-slate-200 dark:bg-slate-900/90 dark:border-slate-800"
  >
    <div class="flex items-center gap-3 min-w-0">
      <!-- Mobile App Identity & Active Page Title (< 768px) -->
      <div class="flex items-center gap-2.5 md:hidden min-w-0">
        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center shadow-md shadow-cyan-500/20 shrink-0">
          <Zap class="w-4 h-4 text-white" />
        </div>
        <div class="min-w-0">
          <h1 class="font-extrabold text-xs tracking-wider leading-tight truncate text-slate-900 dark:text-white">
            {{ pageTitle }}
          </h1>
          <p class="text-[9px] text-cyan-600 dark:text-cyan-400 font-semibold tracking-wide uppercase">
            ULPLTD POKA
          </p>
        </div>
      </div>

      <!-- Toggle Desktop Sidebar Button (muncul jika sidebar di-ciutkan / collapsed) -->
      <button
        v-if="sidebarCollapsed"
        @click="$emit('toggleDesktopSidebar')"
        class="hidden md:flex p-2 rounded-xl transition-all border bg-slate-100 border-slate-200 text-slate-600 hover:text-slate-900 dark:bg-slate-800/80 dark:border-slate-700/80 dark:text-slate-400 dark:hover:text-white cursor-pointer"
        title="Perluas Sidebar"
        aria-label="Perluas Sidebar"
      >
        <PanelLeft class="w-5 h-5 text-cyan-500" />
      </button>

      <!-- Desktop Breadcrumb / Active Page Title (>= 768px) -->
      <div class="hidden md:flex items-center gap-2 pl-1">
        <div class="w-1.5 h-4 rounded-full bg-cyan-500" />
        <span class="text-sm font-bold tracking-tight text-slate-800 dark:text-slate-100">
          {{ pageTitle }}
        </span>
      </div>
    </div>

    <!-- Right Controls -->
    <div class="flex items-center gap-2.5 sm:gap-3 shrink-0">
      <!-- Realtime Clock (WIT) -->
      <div
        v-if="currentTime"
        class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl border font-mono text-xs font-semibold bg-slate-100/80 border-slate-200 text-slate-700 dark:bg-slate-800/80 dark:border-slate-700 dark:text-slate-300 shadow-xs"
        title="Waktu Operasional ULPLTD Poka (WIT)"
      >
        <Clock class="w-3.5 h-3.5 text-cyan-600 dark:text-cyan-400 shrink-0" />
        <span>{{ currentTime }} WIT</span>
      </div>

      <!-- Dark / Light Mode Toggle Button -->
      <button
        @click="$emit('toggleTheme')"
        class="p-2 rounded-xl border transition-all duration-200 flex items-center justify-center bg-slate-100 border-slate-200 text-indigo-600 hover:bg-slate-200 dark:bg-slate-800 dark:border-slate-700 dark:text-amber-400 dark:hover:bg-slate-700 shadow-xs cursor-pointer"
        :title="isDarkMode ? 'Beralih ke Mode Terang (Light Mode)' : 'Beralih ke Mode Gelap (Dark Mode)'"
        :aria-label="isDarkMode ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap'"
      >
        <Sun v-if="isDarkMode" class="w-4 h-4 text-amber-400" />
        <Moon v-else class="w-4 h-4 text-indigo-600" />
      </button>

      <!-- User Info & Logout Button -->
      <div
        v-if="$page.props.auth && $page.props.auth.user"
        class="flex items-center gap-2 sm:gap-3 pl-2 sm:pl-3 border-l border-slate-200 dark:border-slate-800"
      >
        <div class="hidden sm:block text-right text-xs">
          <p class="font-bold truncate max-w-[180px] text-slate-900 dark:text-white">
            {{ $page.props.auth.user.name }}
          </p>
          <span :class="['text-[10px] font-extrabold px-1.5 py-0.5 rounded border uppercase inline-block mt-0.5', getRoleBadgeClass($page.props.auth.user.role)]">
            {{ getRoleLabel($page.props.auth.user.role) }}
          </span>
        </div>

        <Link
          href="/logout"
          method="post"
          as="button"
          class="p-2 rounded-xl border transition-all text-rose-500 hover:bg-rose-500/10 border-rose-500/30 bg-slate-100 dark:bg-slate-800 cursor-pointer"
          title="Keluar (Logout)"
          aria-label="Keluar dari akun"
        >
          <LogOut class="w-4 h-4" />
        </Link>
      </div>

      <div v-else>
        <Link
          href="/login"
          class="px-3 py-1.5 rounded-xl bg-cyan-500 text-slate-950 font-bold text-xs shadow hover:bg-cyan-400"
        >
          Masuk
        </Link>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Zap, PanelLeft, Clock, Sun, Moon, LogOut } from 'lucide-vue-next';

defineProps({
  sidebarCollapsed: {
    type: Boolean,
    default: false,
  },
  isDarkMode: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['toggleDesktopSidebar', 'toggleTheme']);

const page = usePage();

const pageTitle = computed(() => {
  const url = page.url || '';
  if (url === '/' || url.startsWith('/dashboard')) return 'Dashboard Utama';
  if (url.startsWith('/monitoring-arus')) return 'Monitoring Arus';
  if (url.startsWith('/monitoring-kwh')) return 'Monitoring kWh Produksi';
  if (url.startsWith('/monitoring-operasi-engine')) return 'Monitoring Operasi Engine';
  if (url.startsWith('/monitoring-gangguan')) return 'Monitoring Gangguan';
  if (url.startsWith('/monitoring-bbm')) return 'Monitoring Stok BBM';
  if (url.startsWith('/users')) return 'Pengelolaan Pengguna';
  return 'PLN Monitor ULPLTD Poka';
});

const currentTime = ref('');

const getRoleLabel = (role) => {
  switch (role) {
    case 'admin': return 'Admin';
    case 'manager': return 'Manager';
    case 'tl_operasi': return 'TL Operasi';
    case 'tl_pemeliharaan': return 'TL Pemeliharaan';
    case 'operator': return 'Operator';
    default: return role;
  }
};

const getRoleBadgeClass = (role) => {
  switch (role) {
    case 'admin': return 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border-rose-300 dark:border-rose-700';
    case 'manager': return 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300 border-purple-300 dark:border-purple-700';
    case 'tl_operasi': return 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 border-blue-300 dark:border-blue-700';
    case 'tl_pemeliharaan': return 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border-amber-300 dark:border-amber-700';
    default: return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-300 dark:border-emerald-700';
  }
};

const updateTime = () => {
  const now = new Date();
  // Label jam adalah WIT, jadi zona waktunya dipaku ke Asia/Jayapura (bukan zona waktu perangkat)
  currentTime.value = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', timeZone: 'Asia/Jayapura' });
};

let timer = null;
onMounted(() => {
  updateTime();
  timer = setInterval(updateTime, 1000);
});

onUnmounted(() => {
  if (timer) clearInterval(timer);
});
</script>
