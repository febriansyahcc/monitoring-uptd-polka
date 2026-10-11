<template>
  <!-- Jumlah per status sudah ada di strip ringkasan, jadi grafik hanya menampilkan jenis gangguan -->
  <ChartCard
    title="Jenis gangguan"
    description="Jumlah kejadian per jenis bulan ini"
    :empty="series[0].data.length === 0"
    empty-text="Tidak ada gangguan bulan ini."
    :height-class="heightClass"
  >
    <apexchart type="bar" height="100%" width="100%" :options="options" :series="series" />
  </ChartCard>
</template>

<script setup>
import { ref, computed, inject } from 'vue';
import ChartCard from '@/Components/Shared/ChartCard.vue';
import { baseChartOptions, chartTokens } from '@/utils/chartTheme';

const props = defineProps({
  typeDistribution: { type: Object, required: true },
  summary: { type: Object, required: true },
  isDarkMode: { type: Boolean, default: false },
});

const isDarkModeRef = inject('isDarkMode', ref(props.isDarkMode));

// Diurutkan dari yang paling sering; jenis dengan 0 kejadian tidak ditampilkan
const entries = computed(() =>
  Object.entries(props.typeDistribution)
    .filter(([, count]) => count > 0)
    .sort((a, b) => b[1] - a[1])
);

const series = computed(() => [{ name: 'Kejadian', data: entries.value.map(([, count]) => count) }]);

// Tinggi mengikuti jumlah baris agar batang tidak terlalu tebal/tipis
const heightClass = computed(() => (entries.value.length <= 3 ? 'h-40' : entries.value.length <= 6 ? 'h-56' : 'h-72'));

const options = computed(() => {
  const isDark = isDarkModeRef.value;
  const base = baseChartOptions(isDark);
  return {
    ...base,
    chart: { ...base.chart, type: 'bar' },
    colors: [chartTokens(isDark).brand],
    plotOptions: { bar: { horizontal: true, barHeight: '60%', borderRadius: 2 } },
    xaxis: {
      ...base.xaxis,
      categories: entries.value.map(([type]) => type),
      labels: { ...base.xaxis.labels, formatter: (val) => Math.round(val) },
      tickAmount: Math.min(5, Math.max(1, ...series.value[0].data)),
    },
    yaxis: { labels: { ...base.yaxis.labels, maxWidth: 200 } },
    tooltip: { ...base.tooltip, y: { formatter: (val) => `${val} kejadian` } },
    legend: { show: false },
  };
});
</script>
