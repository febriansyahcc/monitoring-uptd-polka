<template>
  <nav
    :class="[
      'fixed bottom-0 inset-x-0 md:hidden z-50 backdrop-blur-lg border-t px-2 py-1.5 flex items-center justify-around shadow-2xl transition-colors duration-300',
      isDarkMode
        ? 'bg-slate-900/95 border-slate-800'
        : 'bg-white/95 border-slate-200'
    ]"
  >
    <!-- Dashboard -->
    <Link
      href="/"
      :class="[
        'flex flex-col items-center gap-0.5 px-2 py-1 rounded-xl transition-all',
        $page.url === '/' || $page.url.startsWith('/dashboard')
          ? isDarkMode ? 'text-cyan-400 font-bold scale-105' : 'text-cyan-600 font-bold scale-105'
          : isDarkMode ? 'text-slate-400' : 'text-slate-500'
      ]"
    >
      <LayoutDashboard class="w-5 h-5" />
      <span class="text-[9px]">Dashboard</span>
    </Link>

    <!-- Arus -->
    <Link
      href="/monitoring-arus"
      :class="[
        'flex flex-col items-center gap-0.5 px-2 py-1 rounded-xl transition-all',
        $page.url.startsWith('/monitoring-arus')
          ? isDarkMode ? 'text-cyan-400 font-bold scale-105' : 'text-cyan-600 font-bold scale-105'
          : isDarkMode ? 'text-slate-400' : 'text-slate-500'
      ]"
    >
      <Activity class="w-5 h-5" />
      <span class="text-[9px]">Arus</span>
    </Link>

    <!-- kWh -->
    <Link
      href="/monitoring-kwh"
      :class="[
        'flex flex-col items-center gap-0.5 px-2 py-1 rounded-xl transition-all',
        $page.url.startsWith('/monitoring-kwh')
          ? isDarkMode ? 'text-emerald-400 font-bold scale-105' : 'text-emerald-600 font-bold scale-105'
          : isDarkMode ? 'text-slate-400' : 'text-slate-500'
      ]"
    >
      <Zap class="w-5 h-5" />
      <span class="text-[9px]">kWh</span>
    </Link>

    <!-- Operasi Engine -->
    <Link
      href="/monitoring-operasi-engine"
      :class="[
        'flex flex-col items-center gap-0.5 px-2 py-1 rounded-xl transition-all',
        $page.url.startsWith('/monitoring-operasi-engine')
          ? isDarkMode ? 'text-violet-400 font-bold scale-105' : 'text-violet-600 font-bold scale-105'
          : isDarkMode ? 'text-slate-400' : 'text-slate-500'
      ]"
    >
      <Cpu class="w-5 h-5" />
      <span class="text-[9px]">Engine</span>
    </Link>

    <!-- Gangguan -->
    <Link
      href="/monitoring-gangguan"
      :class="[
        'flex flex-col items-center gap-0.5 px-2 py-1 rounded-xl transition-all',
        $page.url.startsWith('/monitoring-gangguan')
          ? isDarkMode ? 'text-rose-400 font-bold scale-105' : 'text-rose-600 font-bold scale-105'
          : isDarkMode ? 'text-slate-400' : 'text-slate-500'
      ]"
    >
      <AlertTriangle class="w-5 h-5" />
      <span class="text-[9px]">Gangguan</span>
    </Link>

    <!-- BBM -->
    <Link
      href="/monitoring-bbm"
      :class="[
        'flex flex-col items-center gap-0.5 px-2 py-1 rounded-xl transition-all',
        $page.url.startsWith('/monitoring-bbm')
          ? isDarkMode ? 'text-amber-400 font-bold scale-105' : 'text-amber-600 font-bold scale-105'
          : isDarkMode ? 'text-slate-400' : 'text-slate-500'
      ]"
    >
      <Fuel class="w-5 h-5" />
      <span class="text-[9px]">BBM</span>
    </Link>
  </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { LayoutDashboard, Activity, Zap, Cpu, AlertTriangle, Fuel } from 'lucide-vue-next';

defineProps({
  isDarkMode: {
    type: Boolean,
    default: false,
  },
});
</script>
