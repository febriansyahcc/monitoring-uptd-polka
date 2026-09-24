<template>
  <!-- Desktop Sidebar Only (strictly hidden md:flex) -->
  <aside
    :class="[
      'hidden md:flex flex-col sticky top-0 h-screen border-r transition-all duration-300 ease-in-out shadow-sm shrink-0',
      'bg-white border-slate-200 text-slate-900 dark:bg-slate-900 dark:border-slate-800 dark:text-slate-100',
      collapsed ? 'w-20' : 'w-64',
    ]"
  >
    <!-- Desktop Header / Logo Section -->
    <div
      :class="[
        'h-16 flex items-center justify-between px-4 shrink-0 border-b border-slate-100 dark:border-slate-800',
        collapsed ? 'justify-center px-2' : '',
      ]"
    >
      <div class="flex items-center gap-3 overflow-hidden">
        <div
          class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center shadow-md shadow-cyan-500/20 shrink-0"
        >
          <Zap class="w-6 h-6 text-white" />
        </div>
        <div
          v-if="!collapsed"
          class="truncate transition-opacity duration-200"
        >
          <h1 class="font-extrabold text-sm tracking-wider leading-tight text-slate-900 dark:text-white">
            PLN MONITOR
          </h1>
          <p class="text-[10px] text-cyan-600 dark:text-cyan-400 font-semibold tracking-wide uppercase">
            ULPLTD POKA
          </p>
        </div>
      </div>

      <!-- Desktop Collapse Toggle Button -->
      <button
        v-if="!collapsed"
        @click="$emit('toggleCollapse')"
        class="hidden md:flex items-center justify-center w-8 h-8 rounded-lg transition-colors text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800 cursor-pointer"
        :title="collapsed ? 'Perluas Sidebar' : 'Ciutkan Sidebar'"
        aria-label="Toggle sidebar collapse"
      >
        <ChevronLeft class="w-5 h-5" />
      </button>
    </div>

    <!-- Desktop Navigation Items Section -->
    <div class="flex-1 px-3 py-5 space-y-6 overflow-y-auto custom-scrollbar">
      <!-- Group 1: Monitoring Utama (PBAC Filtered) -->
      <div class="space-y-1">
        <div
          v-if="!collapsed"
          class="px-3 text-[10px] font-bold uppercase tracking-wider mb-2 text-slate-400 dark:text-slate-500"
        >
          Monitoring Utama
        </div>

        <!-- Item: Dashboard Utama -->
        <Link
          href="/"
          :class="[
            'flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold text-xs transition-all group relative',
            collapsed ? 'justify-center px-0' : '',
            $page.url === '/' || $page.url.startsWith('/dashboard')
              ? 'bg-blue-50 text-blue-700 border border-blue-200 shadow-sm dark:bg-gradient-to-r dark:from-blue-500/20 dark:to-cyan-500/10 dark:text-cyan-400 dark:border-cyan-500/30'
              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60',
          ]"
          :title="collapsed ? 'Dashboard Utama' : ''"
        >
          <LayoutDashboard class="w-5 h-5 text-blue-500 dark:text-blue-400 shrink-0" />
          <span v-if="!collapsed" class="truncate">Dashboard Utama</span>
        </Link>

        <!-- Item: Monitoring Arus -->
        <Link
          v-if="can('monitoring_arus.view')"
          href="/monitoring-arus"
          :class="[
            'flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold text-xs transition-all group relative',
            collapsed ? 'justify-center px-0' : '',
            $page.url.startsWith('/monitoring-arus')
              ? 'bg-cyan-50 text-cyan-700 border border-cyan-200 shadow-sm dark:bg-gradient-to-r dark:from-cyan-500/20 dark:to-blue-500/10 dark:text-cyan-400 dark:border-cyan-500/30'
              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60',
          ]"
          :title="collapsed ? 'Monitoring Arus' : ''"
        >
          <Activity class="w-5 h-5 text-cyan-500 dark:text-cyan-400 shrink-0" />
          <span v-if="!collapsed" class="truncate">Monitoring Arus</span>
          <span
            v-if="!collapsed && $page.props.feederCount"
            class="ml-auto px-1.5 py-0.5 rounded text-[9px] font-extrabold border bg-cyan-100 text-cyan-800 border-cyan-300 dark:bg-cyan-500/20 dark:text-cyan-300 dark:border-cyan-500/40"
          >
            {{ $page.props.feederCount }} Feeder
          </span>
        </Link>

        <!-- Item: Monitoring kWh Produksi -->
        <Link
          v-if="can('monitoring_kwh.view')"
          href="/monitoring-kwh"
          :class="[
            'flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold text-xs transition-all group relative',
            collapsed ? 'justify-center px-0' : '',
            $page.url.startsWith('/monitoring-kwh')
              ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm dark:bg-gradient-to-r dark:from-emerald-500/20 dark:to-teal-500/10 dark:text-emerald-400 dark:border-emerald-500/30'
              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60',
          ]"
          :title="collapsed ? 'Monitoring kWh Produksi' : ''"
        >
          <Zap class="w-5 h-5 text-emerald-500 dark:text-emerald-400 shrink-0" />
          <span v-if="!collapsed" class="truncate">Monitoring kWh Produksi</span>
        </Link>

        <!-- Item: Monitoring Operasi Engine -->
        <Link
          v-if="can('monitoring_engine.view')"
          href="/monitoring-operasi-engine"
          :class="[
            'flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold text-xs transition-all group relative',
            collapsed ? 'justify-center px-0' : '',
            $page.url.startsWith('/monitoring-operasi-engine')
              ? 'bg-violet-50 text-violet-700 border border-violet-200 shadow-sm dark:bg-gradient-to-r dark:from-violet-500/20 dark:to-indigo-500/10 dark:text-violet-400 dark:border-violet-500/30'
              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60',
          ]"
          :title="collapsed ? 'Monitoring Operasi Engine' : ''"
        >
          <Cpu class="w-5 h-5 text-violet-500 dark:text-violet-400 shrink-0" />
          <span v-if="!collapsed" class="truncate">Monitoring Operasi Engine</span>
        </Link>

        <!-- Item: Monitoring Gangguan -->
        <Link
          v-if="can('monitoring_gangguan.view')"
          href="/monitoring-gangguan"
          :class="[
            'flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold text-xs transition-all group relative',
            collapsed ? 'justify-center px-0' : '',
            $page.url.startsWith('/monitoring-gangguan')
              ? 'bg-rose-50 text-rose-700 border border-rose-200 shadow-sm dark:bg-gradient-to-r dark:from-rose-500/20 dark:to-amber-500/10 dark:text-rose-400 dark:border-rose-500/30'
              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60',
          ]"
          :title="collapsed ? 'Monitoring Gangguan' : ''"
        >
          <AlertTriangle class="w-5 h-5 text-rose-500 dark:text-rose-400 shrink-0" />
          <span v-if="!collapsed" class="truncate">Monitoring Gangguan</span>
        </Link>

        <!-- Item: Monitoring Stok BBM -->
        <Link
          v-if="can('monitoring_bbm.view')"
          href="/monitoring-bbm"
          :class="[
            'flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold text-xs transition-all group relative',
            collapsed ? 'justify-center px-0' : '',
            $page.url.startsWith('/monitoring-bbm')
              ? 'bg-amber-50 text-amber-700 border border-amber-200 shadow-sm dark:bg-gradient-to-r dark:from-amber-500/20 dark:to-orange-500/10 dark:text-amber-400 dark:border-amber-500/30'
              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60',
          ]"
          :title="collapsed ? 'Monitoring Stok BBM' : ''"
        >
          <Fuel class="w-5 h-5 text-amber-500 dark:text-amber-400 shrink-0" />
          <span v-if="!collapsed" class="truncate">Monitoring Stok BBM</span>
        </Link>
      </div>

      <!-- Group 2: Sistem Admin & Pengaturan PBAC -->
      <div v-if="can('users.manage')" class="space-y-1">
        <div
          v-if="!collapsed"
          class="px-3 text-[10px] font-bold uppercase tracking-wider mb-2 text-slate-400 dark:text-slate-500"
        >
          Sistem Admin
        </div>

        <!-- Item: Pengelolaan Pengguna (User Management) -->
        <Link
          href="/users"
          :class="[
            'flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold text-xs transition-all group relative',
            collapsed ? 'justify-center px-0' : '',
            $page.url.startsWith('/users')
              ? 'bg-purple-50 text-purple-700 border border-purple-200 shadow-sm dark:bg-gradient-to-r dark:from-purple-500/20 dark:to-rose-500/10 dark:text-purple-400 dark:border-purple-500/30'
              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/60',
          ]"
          :title="collapsed ? 'Pengelolaan Pengguna' : ''"
        >
          <ShieldCheck class="w-5 h-5 text-purple-500 dark:text-purple-400 shrink-0" />
          <span v-if="!collapsed" class="truncate">Pengelolaan Pengguna</span>
          <span
            v-if="!collapsed"
            class="ml-auto px-1.5 py-0.5 rounded text-[9px] font-extrabold border bg-purple-100 text-purple-800 border-purple-300 dark:bg-purple-500/20 dark:text-purple-300 dark:border-purple-500/40"
          >
            PBAC
          </span>
        </Link>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { usePermission } from '@/composables/usePermission';
import {
  LayoutDashboard,
  Zap,
  Activity,
  AlertTriangle,
  Fuel,
  Cpu,
  ShieldCheck,
  ChevronLeft,
} from 'lucide-vue-next';

defineProps({
  collapsed: {
    type: Boolean,
    default: false,
  },
  isDarkMode: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['toggleCollapse']);

const { can } = usePermission();
</script>
