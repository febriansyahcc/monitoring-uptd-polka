<template>
  <Head title="Monitoring Arus" />
  <div class="space-y-6">
    <!-- Top Action Bar & Filter Header (No Card Wrapper) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2">
      <div class="flex items-center gap-3">
        <div
          :class="[
            'w-12 h-12 rounded-2xl border flex items-center justify-center shadow-inner shrink-0',
            'bg-cyan-50 border-cyan-200 text-cyan-600 dark:bg-gradient-to-tr dark:from-cyan-500/20 dark:to-blue-500/20 dark:border-cyan-500/30 dark:text-cyan-400 dark:bg-transparent'
          ]"
        >
          <Activity class="w-6 h-6" />
        </div>
        <div>
          <h1 :class="['text-xl font-extrabold tracking-tight flex items-center gap-2', 'text-slate-900 dark:text-white']">
            Monitoring Arus (Current Monitoring)
          </h1>
          <p :class="['text-xs', 'text-slate-500 dark:text-slate-400']">
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
            'bg-slate-50 border-slate-200 dark:bg-slate-950 dark:border-slate-800'
          ]"
        >
          <Calendar class="w-4 h-4 text-cyan-500" />
          <input
            type="date"
            v-model="filterDate"
            @change="applyFilter"
            :class="[
              'bg-transparent text-xs font-mono font-medium focus:outline-none cursor-pointer',
              'text-slate-800 dark:text-slate-100'
            ]"
          />
        </div>

        <!-- Shift Tabs -->
        <div
          :class="[
            'flex items-center p-1 rounded-xl border',
            'bg-slate-100 border-slate-200 dark:bg-slate-950 dark:border-slate-800'
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
                : 'text-slate-600 hover:text-slate-900 hover:bg-white dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-900'
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
        'bg-slate-100 border-slate-200 dark:bg-slate-950 dark:border-slate-800'
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
            : 'text-slate-600 hover:text-slate-900 hover:bg-white dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-900'
        ]"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Interactive Line Chart -->
    <InteractiveLineChart
      v-if="activeTab === 'penyulang'"
      :feeders="feeders"
      :matrix="matrix"
      :intervals="intervals"
      :isDarkMode="isDarkMode"
    />

    <!-- Adaptive Matrix Data Table: v-show agar isian yang belum disimpan tidak hilang saat pindah tab -->
    <AdaptiveDataTable
      v-show="activeTab === 'penyulang'"
      :feeders="feeders"
      :matrix="matrix"
      :selectedDate="selectedDate"
      :selectedShift="selectedShift"
      @dirty-change="dirtyCount = $event"
    />

    <!-- Arus Tiap Fasa (otomatis dari data penyulang) -->
    <PhaseCurrentTable
      v-if="activeTab === 'fasa'"
      :phaseFeeders="phaseFeeders"
      :phaseMatrix="phaseMatrix"
    />
  </div>
</template>

<script setup>
import { ref, inject, onMounted, onBeforeUnmount } from 'vue';
import { Head, router } from '@inertiajs/vue3';
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

// ---------- Lindungi isian yang belum disimpan (BUG-01) ----------
const dirtyCount = ref(0);
let skipGuard = false;

const confirmDiscard = () =>
  dirtyCount.value === 0 ||
  window.confirm(
    `Ada ${dirtyCount.value} baris jam yang belum disimpan. Isian tersebut akan hilang jika Anda melanjutkan. Lanjutkan?`
  );

const visitFilter = (params) => {
  skipGuard = true; // sudah dikonfirmasi di sini, jangan tanya dua kali di penjaga navigasi
  router.get('/monitoring-arus', params, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => {
      skipGuard = false;
    },
  });
};

const applyFilter = () => {
  if (!confirmDiscard()) {
    filterDate.value = props.selectedDate; // batalkan perubahan tanggal di input
    return;
  }
  visitFilter({ date: filterDate.value, shift: props.selectedShift });
};

const selectShift = (shiftKey) => {
  if (shiftKey === props.selectedShift || !confirmDiscard()) return;
  visitFilter({ date: filterDate.value, shift: shiftKey });
};

// Navigasi lain (menu sidebar, tombol back) saat masih ada isian yang belum disimpan
const removeNavigationGuard = router.on('before', (event) => {
  if (skipGuard || event.detail.visit.method !== 'get') return;
  if (!confirmDiscard()) event.preventDefault();
});

// Reload / tutup tab browser
const onBeforeUnload = (event) => {
  if (dirtyCount.value === 0) return;
  event.preventDefault();
  event.returnValue = '';
};

onMounted(() => window.addEventListener('beforeunload', onBeforeUnload));
onBeforeUnmount(() => {
  removeNavigationGuard();
  window.removeEventListener('beforeunload', onBeforeUnload);
});
</script>
