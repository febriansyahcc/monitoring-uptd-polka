<template>
  <Head title="Monitoring Gangguan" />
  <div class="space-y-6">
    <!-- Header Controls & Summary Stats -->
    <div class="space-y-6">
      <!-- Title Bar & Month Selector (No Card Wrapper) -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2">
        <div class="flex items-center gap-3">
          <div
            :class="[
              'w-12 h-12 rounded-2xl border flex items-center justify-center shadow-inner shrink-0',
              'bg-rose-50 border-rose-200 text-rose-600 dark:bg-gradient-to-tr dark:from-rose-500/20 dark:to-amber-500/20 dark:border-rose-500/30 dark:text-rose-400 dark:bg-transparent'
            ]"
          >
            <AlertTriangle class="w-6 h-6" />
          </div>
          <div>
            <h1 :class="['text-xl font-extrabold tracking-tight flex items-center gap-2', 'text-slate-900 dark:text-white']">
              Monitoring Gangguan Operasional
            </h1>
            <p :class="['text-xs', 'text-slate-500 dark:text-slate-400']">
              Pencatatan riwayat kejadian gangguan, analisis frekuensi, dan status penanganan operasional
            </p>
          </div>
        </div>

        <!-- Month Filter Selector -->
        <div
          :class="[
            'flex items-center gap-2 px-3 py-1.5 rounded-xl border',
            'bg-slate-50 border-slate-200 dark:bg-slate-950 dark:border-slate-800'
          ]"
        >
          <Calendar class="w-4 h-4 text-rose-500" />
          <input
            type="month"
            v-model="monthFilter"
            @change="applyMonthFilter"
            :class="[
              'bg-transparent text-xs font-mono font-bold focus:outline-none cursor-pointer',
              'text-slate-800 dark:text-slate-100'
            ]"
          />
        </div>
      </div>

      <!-- KPI Summary Stat Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Gangguan -->
        <div
          :class="[
            'p-4 rounded-2xl border shadow-sm space-y-2 transition-colors',
            'bg-white border-slate-200 dark:bg-slate-900 dark:border-slate-800'
          ]"
        >
          <div class="flex items-center justify-between text-xs font-bold text-slate-400">
            <span>Total Gangguan Bulan Ini</span>
            <AlertTriangle class="w-4 h-4 text-rose-500" />
          </div>
          <p class="text-xl sm:text-2xl font-black font-mono text-rose-500">
            {{ summary.total }}
            <span class="text-xs font-normal text-slate-400">Kasus</span>
          </p>
        </div>

        <!-- Dalam Penanganan -->
        <div
          :class="[
            'p-4 rounded-2xl border shadow-sm space-y-2 transition-colors',
            'bg-white border-slate-200 dark:bg-slate-900 dark:border-slate-800'
          ]"
        >
          <div class="flex items-center justify-between text-xs font-bold text-slate-400">
            <span>Dalam Penanganan</span>
            <Clock class="w-4 h-4 text-amber-500" />
          </div>
          <p class="text-xl sm:text-2xl font-black font-mono text-amber-500">
            {{ summary.inProgress }}
            <span class="text-xs font-normal text-slate-400">Kasus</span>
          </p>
        </div>

        <!-- Selesai (Normal) -->
        <div
          :class="[
            'p-4 rounded-2xl border shadow-sm space-y-2 transition-colors',
            'bg-white border-slate-200 dark:bg-slate-900 dark:border-slate-800'
          ]"
        >
          <div class="flex items-center justify-between text-xs font-bold text-slate-400">
            <span>Selesai (Normal)</span>
            <CheckCircle2 class="w-4 h-4 text-emerald-500" />
          </div>
          <p class="text-xl sm:text-2xl font-black font-mono text-emerald-500">
            {{ summary.resolved }}
            <span class="text-xs font-normal text-slate-400">Kasus</span>
          </p>
        </div>

        <!-- Dalam Investigasi -->
        <div
          :class="[
            'p-4 rounded-2xl border shadow-sm space-y-2 transition-colors',
            'bg-white border-slate-200 dark:bg-slate-900 dark:border-slate-800'
          ]"
        >
          <div class="flex items-center justify-between text-xs font-bold text-slate-400">
            <span>Dalam Investigasi</span>
            <Search class="w-4 h-4 text-blue-500" />
          </div>
          <p class="text-xl sm:text-2xl font-black font-mono text-blue-500">
            {{ summary.investigating }}
            <span class="text-xs font-normal text-slate-400">Kasus</span>
          </p>
        </div>
      </div>

      <!-- Donut & Bar Charts Visualization -->
      <DisturbanceCharts
        :typeDistribution="typeDistribution"
        :summary="summary"
        :isDarkMode="isDarkMode"
      />

      <!-- Data Table & Modal Input -->
      <DisturbanceDataTable
        :disturbances="disturbances"
        :disturbanceTypes="disturbanceTypes"
        :statusOptions="statusOptions"
        :selectedMonth="selectedMonth"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, inject } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DisturbanceCharts from '@/Components/DisturbanceMonitoring/DisturbanceCharts.vue';
import DisturbanceDataTable from '@/Components/DisturbanceMonitoring/DisturbanceDataTable.vue';
import { AlertTriangle, Calendar, Clock, CheckCircle2, Search } from 'lucide-vue-next';

const isDarkMode = inject('isDarkMode');

const props = defineProps({
  disturbances: Array,
  selectedMonth: String,
  disturbanceTypes: Array,
  statusOptions: Array,
  typeDistribution: Object,
  summary: Object,
});

const monthFilter = ref(props.selectedMonth);

const applyMonthFilter = () => {
  router.get(
    '/monitoring-gangguan',
    { month: monthFilter.value },
    { preserveState: true, preserveScroll: true }
  );
};
</script>
