<template>
  <AppLayout v-slot="{ isDarkMode }">
    <!-- Header Controls & Summary Stats -->
    <div class="space-y-6">
      <!-- Title & Month Selector (No Card Wrapper) -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2">
        <div class="flex items-center gap-3">
          <div
            :class="[
              'w-12 h-12 rounded-2xl border flex items-center justify-center shadow-inner shrink-0',
              isDarkMode
                ? 'bg-gradient-to-tr from-emerald-500/20 to-teal-500/20 border-emerald-500/30 text-emerald-400'
                : 'bg-emerald-50 border-emerald-200 text-emerald-600'
            ]"
          >
            <Zap class="w-6 h-6" />
          </div>
          <div>
            <h1 :class="['text-xl font-extrabold tracking-tight flex items-center gap-2', isDarkMode ? 'text-white' : 'text-slate-900']">
              Monitoring kWh Produksi
            </h1>
            <p :class="['text-xs', isDarkMode ? 'text-slate-400' : 'text-slate-500']">
              Pencatatan harian kWh PS, kWh Digital (1 & 2), serta akumulasi produksi total
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
          <Calendar class="w-4 h-4 text-emerald-500" />
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

      <!-- Flash Message Alert -->
      <Transition name="fade">
        <div
          v-if="$page.props.flash && $page.props.flash.success"
          :class="[
            'p-4 rounded-xl flex items-center justify-between shadow-lg border',
            isDarkMode
              ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400'
              : 'bg-emerald-50 border-emerald-200 text-emerald-800 font-semibold'
          ]"
        >
          <div class="flex items-center gap-2 text-xs font-medium">
            <CheckCircle2 class="w-4 h-4 shrink-0 text-emerald-500" />
            <span>{{ $page.props.flash.success }}</span>
          </div>
        </div>
      </Transition>

      <!-- Summary KPI Stat Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total kWh Produksi -->
        <div
          :class="[
            'p-4 rounded-2xl border shadow-sm space-y-2 transition-colors',
            isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'
          ]"
        >
          <div class="flex items-center justify-between text-xs font-bold text-slate-400">
            <span>Total Produksi Bulan Ini</span>
            <Zap class="w-4 h-4 text-emerald-500" />
          </div>
          <p class="text-xl sm:text-2xl font-black font-mono text-emerald-500">
            {{ summary.totalKwh.toLocaleString('id-ID', { minimumFractionDigits: 2 }) }}
            <span class="text-xs font-normal text-slate-400">kWh</span>
          </p>
        </div>

        <!-- Total kWh PS -->
        <div
          :class="[
            'p-4 rounded-2xl border shadow-sm space-y-2 transition-colors',
            isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'
          ]"
        >
          <div class="flex items-center justify-between text-xs font-bold text-slate-400">
            <span>Total kWh PS</span>
            <Activity class="w-4 h-4 text-blue-500" />
          </div>
          <p class="text-xl sm:text-2xl font-black font-mono text-blue-500">
            {{ summary.totalKwhPs.toLocaleString('id-ID', { minimumFractionDigits: 2 }) }}
            <span class="text-xs font-normal text-slate-400">kWh</span>
          </p>
        </div>

        <!-- Total kWh Digital (1 & 2) -->
        <div
          :class="[
            'p-4 rounded-2xl border shadow-sm space-y-2 transition-colors',
            isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'
          ]"
        >
          <div class="flex items-center justify-between text-xs font-bold text-slate-400">
            <span>Total kWh Digital (1+2)</span>
            <Sliders class="w-4 h-4 text-purple-500" />
          </div>
          <p class="text-xl sm:text-2xl font-black font-mono text-purple-500">
            {{ (summary.totalKwhDigital1 + summary.totalKwhDigital2).toLocaleString('id-ID', { minimumFractionDigits: 2 }) }}
            <span class="text-xs font-normal text-slate-400">kWh</span>
          </p>
        </div>

        <!-- Data Entry Count -->
        <div
          :class="[
            'p-4 rounded-2xl border shadow-sm space-y-2 transition-colors',
            isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'
          ]"
        >
          <div class="flex items-center justify-between text-xs font-bold text-slate-400">
            <span>Hari Tercatat</span>
            <Calendar class="w-4 h-4 text-cyan-500" />
          </div>
          <p class="text-xl sm:text-2xl font-black font-mono text-cyan-500">
            {{ summary.count }}
            <span class="text-xs font-normal text-slate-400">Hari</span>
          </p>
        </div>
      </div>

      <!-- Combo Bar & Line Chart -->
      <KwhBarLineChart :logs="logs" :isDarkMode="isDarkMode" />

      <!-- Data Table & Input Modal -->
      <KwhDataTable :logs="logs" :isDarkMode="isDarkMode" />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import KwhBarLineChart from '@/Components/KwhProduction/KwhBarLineChart.vue';
import KwhDataTable from '@/Components/KwhProduction/KwhDataTable.vue';
import { Zap, Calendar, Activity, Sliders, CheckCircle2 } from 'lucide-vue-next';

const props = defineProps({
  logs: Array,
  selectedMonth: String,
  summary: Object,
});

const monthFilter = ref(props.selectedMonth);

const applyMonthFilter = () => {
  router.get(
    '/monitoring-kwh',
    { month: monthFilter.value },
    { preserveState: true, preserveScroll: true }
  );
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
