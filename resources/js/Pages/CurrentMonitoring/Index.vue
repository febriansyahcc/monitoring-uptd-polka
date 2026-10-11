<template>
  <Head title="Monitoring Arus" />
  <div class="space-y-4">
    <!-- Filter: tanggal, shift, jenis tabel (judul halaman ada di Header) -->
    <div class="flex flex-wrap items-center gap-2">
      <label class="sr-only" for="arus-date">Tanggal</label>
      <input
        id="arus-date"
        type="date"
        v-model="filterDate"
        @change="applyFilter"
        class="h-9 px-3 rounded-lg border border-line bg-surface text-body tabular-nums text-fg focus:outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
      />
      <Segmented
        :modelValue="selectedShift"
        :options="shiftOptions"
        label="Shift"
        @update:modelValue="selectShift"
      />
      <Segmented v-model="activeTab" :options="tabs" label="Jenis tabel" tabs class="sm:ml-auto" />
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
import { ref, computed, inject, onMounted, onBeforeUnmount } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import InteractiveLineChart from '@/Components/CurrentMonitoring/InteractiveLineChart.vue';
import AdaptiveDataTable from '@/Components/CurrentMonitoring/AdaptiveDataTable.vue';
import PhaseCurrentTable from '@/Components/CurrentMonitoring/PhaseCurrentTable.vue';
import Segmented from '@/Components/Shared/Segmented.vue';

const SHIFT_LABELS = { pagi: 'Pagi', sore: 'Sore', malam: 'Malam' };

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
  { key: 'penyulang', label: 'Beban penyulang' },
  { key: 'fasa', label: 'Arus per fasa' },
];

// Label pendek untuk pilihan shift (jam shift: pagi 08–16, sore 16–24, malam 00–08)
const shiftOptions = computed(() => (props.shifts ?? []).map((s) => ({ key: s.key, label: SHIFT_LABELS[s.key] ?? s.key })));
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
