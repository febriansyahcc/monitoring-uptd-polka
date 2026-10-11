<template>
  <Head title="Monitoring Gangguan" />
  <div class="space-y-4">
    <!-- Filter bulan (judul halaman ada di Header) -->
    <div class="flex flex-wrap items-center gap-2">
      <label class="sr-only" for="gangguan-month">Bulan</label>
      <input
        id="gangguan-month"
        type="month"
        v-model="monthFilter"
        @change="applyMonthFilter"
        class="h-9 px-3 rounded-lg border border-line bg-surface text-body tabular-nums text-fg focus:outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
      />
    </div>

    <!-- Ringkasan bulan: merah tidak dipakai di sini; yang belum selesai = waspada -->
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-px bg-line border border-line rounded-lg overflow-hidden" aria-label="Ringkasan bulan">
      <KpiStat label="Total gangguan" :value="String(summary.total)" unit="kejadian" :hint="summary.total === 0 ? 'Tidak ada gangguan' : 'Bulan ini'" />
      <KpiStat
        label="Dalam penanganan"
        :value="String(summary.inProgress)"
        :status="summary.inProgress > 0 ? 'warning' : null"
        :status-label="summary.inProgress > 0 ? 'Belum selesai' : null"
      />
      <KpiStat
        label="Investigasi"
        :value="String(summary.investigating)"
        :status="summary.investigating > 0 ? 'warning' : null"
        :status-label="summary.investigating > 0 ? 'Belum selesai' : null"
      />
      <KpiStat label="Selesai" :value="String(summary.resolved)" />
    </section>

    <DisturbanceCharts :typeDistribution="typeDistribution" :summary="summary" :isDarkMode="isDarkMode" />

    <DisturbanceDataTable
      :disturbances="disturbances"
      :disturbanceTypes="disturbanceTypes"
      :statusOptions="statusOptions"
      :selectedMonth="selectedMonth"
    />
  </div>
</template>

<script setup>
import { ref, inject } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DisturbanceCharts from '@/Components/DisturbanceMonitoring/DisturbanceCharts.vue';
import DisturbanceDataTable from '@/Components/DisturbanceMonitoring/DisturbanceDataTable.vue';
import KpiStat from '@/Components/Dashboard/KpiStat.vue';

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
