<template>
  <Head title="kWh Produksi" />
  <div class="space-y-4">
    <!-- Filter bulan + jenis data (judul halaman ada di Header) -->
    <div class="flex flex-wrap items-center gap-2">
      <label class="sr-only" for="kwh-month">Bulan</label>
      <input
        id="kwh-month"
        type="month"
        v-model="monthFilter"
        @change="applyMonthFilter"
        class="h-9 px-3 rounded-lg border border-line bg-surface text-body tabular-nums text-fg focus:outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
      />
      <Segmented v-model="activeTab" :options="tabs" label="Jenis data" tabs class="sm:ml-auto" />
    </div>

    <!-- Ringkasan bulan: satu strip, tanpa ikon -->
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-px bg-line border border-line rounded-lg overflow-hidden" aria-label="Ringkasan bulan">
      <KpiStat label="Produksi engine" :value="formatQty(summary.totalProduksi, 0)" unit="kWh" hint="Bulan ini" />
      <KpiStat label="PS total penyulang" :value="formatQty(summary.totalPsTotal, 0)" unit="kWh" hint="Bulan ini" />
      <KpiStat label="Hari tercatat · engine" :value="String(summary.engineDays)" unit="hari" />
      <KpiStat label="Hari tercatat · penyulang" :value="String(summary.feederDays)" unit="hari" />
    </section>

    <template v-if="activeTab === 'engine'">
      <KwhBarLineChart :logs="engineLogs" :engines="engines" :isDarkMode="isDarkMode" />
      <KwhEngineTable :logs="engineLogs" :engines="engines" :selectedMonth="selectedMonth" />
    </template>

    <KwhFeederTable
      v-else
      :logs="feederLogs"
      :feeders="feeders"
      :standChoices="standChoices"
      :selectedMonth="selectedMonth"
    />
  </div>
</template>

<script setup>
import { ref, inject } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { queryParam } from '@/utils/url';
import KwhBarLineChart from '@/Components/KwhProduction/KwhBarLineChart.vue';
import KwhEngineTable from '@/Components/KwhProduction/KwhEngineTable.vue';
import KwhFeederTable from '@/Components/KwhProduction/KwhFeederTable.vue';
import Segmented from '@/Components/Shared/Segmented.vue';
import KpiStat from '@/Components/Dashboard/KpiStat.vue';
import { formatQty } from '@/utils/format';

const isDarkMode = inject('isDarkMode');

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
  { key: 'engine', label: 'Engine' },
  { key: 'penyulang', label: 'Penyulang' },
];
// Tab dibaca dari ?tab= agar tetap sama setelah pindah bulan (mis. setelah simpan data bulan lain)
const activeTab = ref(queryParam(usePage().url, 'tab', tabs.map((tab) => tab.key), 'engine'));

const monthFilter = ref(props.selectedMonth);

const applyMonthFilter = () => {
  router.get(
    '/monitoring-kwh',
    { month: monthFilter.value, tab: activeTab.value },
    { preserveState: true, preserveScroll: true }
  );
};
</script>
