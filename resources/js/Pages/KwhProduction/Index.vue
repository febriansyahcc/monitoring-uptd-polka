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
              Pencatatan harian stand akhir kWh per ENGINE dan per PENYULANG
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
        <div
          :class="[
            'p-4 rounded-2xl border shadow-sm space-y-2 transition-colors',
            isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'
          ]"
        >
          <div class="flex items-center justify-between text-xs font-bold text-slate-400">
            <span>Produksi Engine Bulan Ini</span>
            <Zap class="w-4 h-4 text-emerald-500" />
          </div>
          <p class="text-xl sm:text-2xl font-black font-mono text-emerald-500">
            {{ summary.totalProduksi.toLocaleString('id-ID', { maximumFractionDigits: 2 }) }}
            <span class="text-xs font-normal text-slate-400">kWh</span>
          </p>
        </div>
        <div
          :class="[
            'p-4 rounded-2xl border shadow-sm space-y-2 transition-colors',
            isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'
          ]"
        >
          <div class="flex items-center justify-between text-xs font-bold text-slate-400">
            <span>PS Total Penyulang</span>
            <Activity class="w-4 h-4 text-blue-500" />
          </div>
          <p class="text-xl sm:text-2xl font-black font-mono text-blue-500">
            {{ summary.totalPsTotal.toLocaleString('id-ID', { maximumFractionDigits: 2 }) }}
            <span class="text-xs font-normal text-slate-400">kWh</span>
          </p>
        </div>
        <div
          :class="[
            'p-4 rounded-2xl border shadow-sm space-y-2 transition-colors',
            isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'
          ]"
        >
          <div class="flex items-center justify-between text-xs font-bold text-slate-400">
            <span>Hari Tercatat (Engine)</span>
            <Cog class="w-4 h-4 text-purple-500" />
          </div>
          <p class="text-xl sm:text-2xl font-black font-mono text-purple-500">
            {{ summary.engineDays.toLocaleString('id-ID', { maximumFractionDigits: 2 }) }}
            <span class="text-xs font-normal text-slate-400">Hari</span>
          </p>
        </div>
        <div
          :class="[
            'p-4 rounded-2xl border shadow-sm space-y-2 transition-colors',
            isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'
          ]"
        >
          <div class="flex items-center justify-between text-xs font-bold text-slate-400">
            <span>Hari Tercatat (Penyulang)</span>
            <Calendar class="w-4 h-4 text-cyan-500" />
          </div>
          <p class="text-xl sm:text-2xl font-black font-mono text-cyan-500">
            {{ summary.feederDays.toLocaleString('id-ID', { maximumFractionDigits: 2 }) }}
            <span class="text-xs font-normal text-slate-400">Hari</span>
          </p>
        </div>
      </div>

      <!-- Tabs: ENGINE / PENYULANG -->
      <div
        :class="[
          'inline-flex items-center p-1 rounded-xl border',
          isDarkMode ? 'bg-slate-950 border-slate-800' : 'bg-slate-100 border-slate-200'
        ]"
      >
        <button
          v-for="tab in tabs"
          :key="tab.key"
          @click="activeTab = tab.key"
          :class="[
            'px-4 py-1.5 rounded-lg text-xs font-bold transition-all duration-200 flex items-center gap-1.5',
            activeTab === tab.key
              ? 'bg-emerald-500 text-slate-950 shadow-md font-extrabold'
              : isDarkMode
                ? 'text-slate-400 hover:text-slate-200 hover:bg-slate-900'
                : 'text-slate-600 hover:text-slate-900 hover:bg-white'
          ]"
        >
          <component :is="tab.icon" class="w-3.5 h-3.5" />
          <span>{{ tab.label }}</span>
        </button>
      </div>

      <template v-if="activeTab === 'engine'">
        <KwhBarLineChart :logs="engineLogs" :engines="engines" :isDarkMode="isDarkMode" />
        <KwhEngineTable :logs="engineLogs" :engines="engines" :isDarkMode="isDarkMode" />
      </template>

      <KwhFeederTable
        v-else
        :logs="feederLogs"
        :feeders="feeders"
        :standChoices="standChoices"
        :isDarkMode="isDarkMode"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import KwhBarLineChart from '@/Components/KwhProduction/KwhBarLineChart.vue';
import KwhEngineTable from '@/Components/KwhProduction/KwhEngineTable.vue';
import KwhFeederTable from '@/Components/KwhProduction/KwhFeederTable.vue';
import { Zap, Calendar, Activity, Cog, Cable, CheckCircle2 } from 'lucide-vue-next';

const props = defineProps({
  engineLogs: Array,
  feederLogs: Array,
  engines: Array,
  feeders: Array,
  standChoices: Array,
  selectedMonth: String,
  summary: Object,
});

const tabs = [
  { key: 'engine', label: 'ENGINE', icon: Cog },
  { key: 'penyulang', label: 'PENYULANG', icon: Cable },
];
const activeTab = ref('engine');

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
