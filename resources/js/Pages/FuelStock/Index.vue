<template>
  <div class="space-y-6">
    <!-- Header Controls & Summary Stats -->
    <div class="space-y-6">
      <!-- Title Bar & Month Selector (No Card Wrapper) -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2">
        <div class="flex items-center gap-3">
          <div
            :class="[
              'w-12 h-12 rounded-2xl border flex items-center justify-center shadow-inner shrink-0',
              isDarkMode
                ? 'bg-gradient-to-tr from-amber-500/20 to-orange-500/20 border-amber-500/30 text-amber-400'
                : 'bg-amber-50 border-amber-200 text-amber-600'
            ]"
          >
            <Fuel class="w-6 h-6" />
          </div>
          <div>
            <h1 :class="['text-xl font-extrabold tracking-tight flex items-center gap-2', isDarkMode ? 'text-white' : 'text-slate-900']">
              Monitoring Stok & Pemakaian BBM
            </h1>
            <p :class="['text-xs', isDarkMode ? 'text-slate-400' : 'text-slate-500']">
              Pencatatan harian stok BBM, kalkulasi Netto Stock, dan Sisa Hari Operasi (Days of Supply)
            </p>
          </div>
        </div>

        <!-- Month Filter Selector -->
        <div
          :class="[
            'flex items-center gap-2 px-3 py-1.5 rounded-xl border',
            isDarkMode ? 'bg-slate-950 border-slate-800' : 'bg-slate-50 border-slate-200'
          ]"
        >
          <Calendar class="w-4 h-4 text-amber-500" />
          <input
            type="month"
            v-model="monthFilter"
            @change="applyMonthFilter"
            :class="[
              'bg-transparent text-xs font-mono font-bold focus:outline-none cursor-pointer',
              isDarkMode ? 'text-slate-100' : 'text-slate-800'
            ]"
          />
        </div>
      </div>

      <!-- Critical Days of Supply Alert (If < 7 Days) -->
      <div
        v-if="summary.latestDaysOfSupply > 0 && summary.latestDaysOfSupply < 7"
        class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/40 text-rose-400 flex items-center gap-3 shadow-lg animate-pulse"
      >
        <AlertTriangle class="w-6 h-6 shrink-0 text-rose-500" />
        <div class="text-xs">
          <p class="font-extrabold text-sm">Peringatan Kritis Ketersediaan Stok BBM!</p>
          <p>Sisa hari operasi terkini hanya {{ summary.latestDaysOfSupply }} hari. Segera lakukan pengajuan penerimaan (unloading) BBM tambahan!</p>
        </div>
      </div>

      <!-- Summary KPI Stat Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Stok Netto Terakhir -->
        <div
          :class="[
            'p-4 rounded-2xl border shadow-sm space-y-2 transition-colors',
            isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'
          ]"
        >
          <div class="flex items-center justify-between text-xs font-bold text-slate-400">
            <span>Stok Netto Terakhir</span>
            <Fuel class="w-4 h-4 text-amber-500" />
          </div>
          <p class="text-xl sm:text-2xl font-black font-mono text-amber-500">
            {{ summary.latestNettoStock.toLocaleString('id-ID', { minimumFractionDigits: 2 }) }}
            <span class="text-xs font-normal text-slate-400">Liter</span>
          </p>
        </div>

        <!-- Sisa Hari Operasi -->
        <div
          :class="[
            'p-4 rounded-2xl border shadow-sm space-y-2 transition-colors',
            isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'
          ]"
        >
          <div class="flex items-center justify-between text-xs font-bold text-slate-400">
            <span>Sisa Hari Operasi</span>
            <Clock class="w-4 h-4 text-cyan-400" />
          </div>
          <p
            :class="[
              'text-xl sm:text-2xl font-black font-mono',
              summary.latestDaysOfSupply < 7 ? 'text-rose-500' : 'text-cyan-400'
            ]"
          >
            {{ summary.latestDaysOfSupply.toLocaleString('id-ID', { minimumFractionDigits: 1 }) }}
            <span class="text-xs font-normal text-slate-400">Hari</span>
          </p>
        </div>

        <!-- Total Pemakaian Bulan Ini -->
        <div
          :class="[
            'p-4 rounded-2xl border shadow-sm space-y-2 transition-colors',
            isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'
          ]"
        >
          <div class="flex items-center justify-between text-xs font-bold text-slate-400">
            <span>Total Pemakaian Bulan Ini</span>
            <Activity class="w-4 h-4 text-rose-400" />
          </div>
          <p class="text-xl sm:text-2xl font-black font-mono text-rose-400">
            {{ summary.totalConsumption.toLocaleString('id-ID', { minimumFractionDigits: 2 }) }}
            <span class="text-xs font-normal text-slate-400">Liter</span>
          </p>
        </div>

        <!-- Total Unloading Bulan Ini -->
        <div
          :class="[
            'p-4 rounded-2xl border shadow-sm space-y-2 transition-colors',
            isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'
          ]"
        >
          <div class="flex items-center justify-between text-xs font-bold text-slate-400">
            <span>Total Unloading Bulan Ini</span>
            <TrendingUp class="w-4 h-4 text-emerald-500" />
          </div>
          <p class="text-xl sm:text-2xl font-black font-mono text-emerald-500">
            {{ summary.totalUnloading.toLocaleString('id-ID', { minimumFractionDigits: 2 }) }}
            <span class="text-xs font-normal text-slate-400">Liter</span>
          </p>
        </div>
      </div>

      <!-- Combo Line & Area Chart -->
      <FuelStockChart :logs="logs" :isDarkMode="isDarkMode" />

      <!-- Data Table & Modal Input -->
      <FuelStockDataTable :logs="logs" :isDarkMode="isDarkMode" />
    </div>
  </div>
</template>

<script setup>
import { ref, inject } from 'vue';
import { router } from '@inertiajs/vue3';
import FuelStockChart from '@/Components/FuelStock/FuelStockChart.vue';
import FuelStockDataTable from '@/Components/FuelStock/FuelStockDataTable.vue';
import { Fuel, Calendar, Clock, Activity, TrendingUp, AlertTriangle } from 'lucide-vue-next';

const isDarkMode = inject('isDarkMode');

const props = defineProps({
  logs: Array,
  selectedMonth: String,
  summary: Object,
});

const monthFilter = ref(props.selectedMonth);

const applyMonthFilter = () => {
  router.get(
    '/monitoring-bbm',
    { month: monthFilter.value },
    { preserveState: true, preserveScroll: true }
  );
};
</script>
