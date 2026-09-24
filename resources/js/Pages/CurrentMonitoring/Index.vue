<template>
  <div class="space-y-6">
    <!-- Top Action Bar & Filter Header (No Card Wrapper) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2">
      <div class="flex items-center gap-3">
        <div
          :class="[
            'w-12 h-12 rounded-2xl border flex items-center justify-center shadow-inner shrink-0',
            isDarkMode
              ? 'bg-gradient-to-tr from-cyan-500/20 to-blue-500/20 border-cyan-500/30 text-cyan-400'
              : 'bg-cyan-50 border-cyan-200 text-cyan-600'
          ]"
        >
          <Activity class="w-6 h-6" />
        </div>
        <div>
          <h1 :class="['text-xl font-extrabold tracking-tight flex items-center gap-2', isDarkMode ? 'text-white' : 'text-slate-900']">
            Monitoring Arus (Current Monitoring)
          </h1>
          <p :class="['text-xs', isDarkMode ? 'text-slate-400' : 'text-slate-500']">
            Pencatatan & pemantauan tren beban arus listrik (Ampere) per shift
          </p>
        </div>
      </div>

      <!-- Filters: Date & Shift Selector -->
      <div class="flex items-center gap-3 flex-wrap">
        <!-- Date Selector -->
        <div
          :class="[
            'flex items-center gap-2 px-3 py-1.5 rounded-xl border',
            isDarkMode ? 'bg-slate-950 border-slate-800' : 'bg-slate-50 border-slate-200'
          ]"
        >
          <Calendar class="w-4 h-4 text-cyan-500" />
          <input
            type="date"
            v-model="filterDate"
            @change="applyFilter"
            :class="[
              'bg-transparent text-xs font-mono font-medium focus:outline-none cursor-pointer',
              isDarkMode ? 'text-slate-100' : 'text-slate-800'
            ]"
          />
        </div>

        <!-- Shift Tabs -->
        <div
          :class="[
            'flex items-center p-1 rounded-xl border',
            isDarkMode ? 'bg-slate-950 border-slate-800' : 'bg-slate-100 border-slate-200'
          ]"
        >
          <button
            v-for="s in shifts"
            :key="s.key"
            @click="selectShift(s.key)"
            :class="[
              'px-3 py-1.5 rounded-lg text-xs font-bold transition-all duration-200 capitalize flex items-center gap-1.5',
              selectedShift === s.key
                ? 'bg-cyan-500 text-slate-950 shadow-md font-extrabold scale-[1.02]'
                : isDarkMode
                  ? 'text-slate-400 hover:text-slate-200 hover:bg-slate-900'
                  : 'text-slate-600 hover:text-slate-900 hover:bg-white'
            ]"
          >
            <Sun class="w-3.5 h-3.5" v-if="s.key === 'pagi'" />
            <Sunset class="w-3.5 h-3.5" v-else-if="s.key === 'sore'" />
            <Moon class="w-3.5 h-3.5" v-else />
            <span>Shift {{ s.key }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Tabs: Beban & Arus Penyulang / Arus Tiap Fasa -->
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
          'px-4 py-1.5 rounded-lg text-xs font-bold transition-all duration-200',
          activeTab === tab.key
            ? 'bg-cyan-500 text-slate-950 shadow-md font-extrabold'
            : isDarkMode
              ? 'text-slate-400 hover:text-slate-200 hover:bg-slate-900'
              : 'text-slate-600 hover:text-slate-900 hover:bg-white'
        ]"
      >
        {{ tab.label }}
      </button>
    </div>

    <template v-if="activeTab === 'penyulang'">
      <!-- Interactive Line Chart -->
      <InteractiveLineChart
        :feeders="feeders"
        :matrix="matrix"
        :intervals="intervals"
        :isDarkMode="isDarkMode"
      />

      <!-- Adaptive Matrix Data Table -->
      <AdaptiveDataTable
        :feeders="feeders"
        :matrix="matrix"
        :selectedDate="selectedDate"
        :selectedShift="selectedShift"
        :isDarkMode="isDarkMode"
      />
    </template>

    <!-- Arus Tiap Fasa (otomatis dari data penyulang) -->
    <PhaseCurrentTable
      v-else
      :phaseFeeders="phaseFeeders"
      :phaseMatrix="phaseMatrix"
      :isDarkMode="isDarkMode"
    />
  </div>
</template>

<script setup>
import { ref, inject } from 'vue';
import { router } from '@inertiajs/vue3';
import InteractiveLineChart from '@/Components/CurrentMonitoring/InteractiveLineChart.vue';
import AdaptiveDataTable from '@/Components/CurrentMonitoring/AdaptiveDataTable.vue';
import PhaseCurrentTable from '@/Components/CurrentMonitoring/PhaseCurrentTable.vue';
import { Activity, Calendar, Sun, Sunset, Moon } from 'lucide-vue-next';

const isDarkMode = inject('isDarkMode');

const props = defineProps({
  feeders: Array,
  matrix: Array,
  selectedDate: String,
  selectedShift: String,
  intervals: Array,
  shifts: Array,
  phaseFeeders: Array,
  phaseMatrix: Array,
});

const tabs = [
  { key: 'penyulang', label: 'Beban & Arus Penyulang' },
  { key: 'fasa', label: 'Arus Tiap Fasa' },
];
const activeTab = ref('penyulang');

const filterDate = ref(props.selectedDate);

const applyFilter = () => {
  router.get(
    '/monitoring-arus',
    {
      date: filterDate.value,
      shift: props.selectedShift,
    },
    { preserveState: true, preserveScroll: true }
  );
};

const selectShift = (shiftKey) => {
  router.get(
    '/monitoring-arus',
    {
      date: filterDate.value,
      shift: shiftKey,
    },
    { preserveState: true, preserveScroll: true }
  );
};
</script>
