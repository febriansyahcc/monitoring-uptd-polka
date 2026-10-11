<template>
  <Head title="Monitoring Stok BBM" />
  <div class="space-y-4">
    <!-- Filter bulan (judul halaman ada di Header) -->
    <div class="flex flex-wrap items-center gap-2">
      <label class="sr-only" for="bbm-month">Bulan</label>
      <input
        id="bbm-month"
        type="month"
        v-model="monthFilter"
        @change="applyMonthFilter"
        class="h-9 px-3 rounded-lg border border-line bg-surface text-body tabular-nums text-fg focus:outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
      />
    </div>

    <!-- Peringatan: status dari server, ambangnya sama dengan Dashboard -->
    <StatusBanner
      v-if="summary.latestStatus === 'critical' || summary.latestStatus === 'warning'"
      :status="summary.latestStatus"
      :title="`Stok BBM ${summary.latestStatus === 'critical' ? 'kritis' : 'menipis'}: ${days.value} hari${days.approx ? ` (${days.approx})` : ''}`"
      detail="ajukan penerimaan (unloading) BBM tambahan"
    />

    <section class="grid grid-cols-2 lg:grid-cols-4 gap-px bg-line border border-line rounded-lg overflow-hidden" aria-label="Ringkasan bulan">
      <KpiStat
        label="Stok netto terakhir"
        :value="summary.latestStatus === 'empty' ? null : formatQty(summary.latestNettoStock, 0)"
        unit="L"
        :hint="summary.latestStatus === 'empty' ? 'Belum ada data bulan ini' : null"
      />
      <KpiStat
        label="Ketahanan stok (HOP)"
        :value="summary.latestStatus === 'empty' ? null : days.value"
        unit="hari"
        :status="summary.latestStatus"
        :status-label="summary.latestStatus === 'empty' ? 'Belum ada data' : null"
      />
      <KpiStat label="Pemakaian" :value="formatQty(summary.totalConsumption, 0)" unit="L" hint="Bulan ini" />
      <KpiStat label="Unloading" :value="formatQty(summary.totalUnloading, 0)" unit="L" hint="Bulan ini" />
    </section>

    <FuelStockChart :logs="logs" :isDarkMode="isDarkMode" />

    <FuelStockDataTable :logs="logs" :selectedMonth="selectedMonth" />
  </div>
</template>

<script setup>
import { ref, computed, inject } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import FuelStockChart from '@/Components/FuelStock/FuelStockChart.vue';
import FuelStockDataTable from '@/Components/FuelStock/FuelStockDataTable.vue';
import KpiStat from '@/Components/Dashboard/KpiStat.vue';
import StatusBanner from '@/Components/Dashboard/StatusBanner.vue';
import { formatQty, formatDays } from '@/utils/format';

const isDarkMode = inject('isDarkMode');

const props = defineProps({
  logs: Array,
  selectedMonth: String,
  summary: Object,
});

const days = computed(() => formatDays(props.summary.latestDaysOfSupply));

const monthFilter = ref(props.selectedMonth);

const applyMonthFilter = () => {
  router.get(
    '/monitoring-bbm',
    { month: monthFilter.value },
    { preserveState: true, preserveScroll: true }
  );
};
</script>
