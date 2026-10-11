<template>
  <ChartCard
    title="Produksi harian per engine"
    description="Batang = produksi tiap engine (selisih stand akhir), garis = total harian"
    :empty="dates.length === 0"
    empty-text="Belum ada data produksi untuk bulan ini."
  >
    <apexchart type="line" height="100%" width="100%" :options="chartOptions" :series="chartSeries" />
  </ChartCard>
</template>

<script setup>
import { ref, computed, inject } from 'vue';
import ChartCard from '@/Components/Shared/ChartCard.vue';
import { baseChartOptions, chartTokens, seriesColors } from '@/utils/chartTheme';

const props = defineProps({
  logs: {
    type: Array,
    required: true,
  },
  engines: {
    type: Array,
    required: true,
  },
  isDarkMode: {
    type: Boolean,
    default: false,
  },
});

const isDarkModeRef = inject('isDarkMode', ref(props.isDarkMode));

// Produksi harian dijumlahkan per tanggal per engine
const dates = computed(() => [...new Set(props.logs.map(item => item.recorded_date))].sort());

const categories = computed(() => {
  return dates.value.map(date => {
    const parts = date.split('-');
    return `${parts[2]}/${parts[1]}`;
  });
});

const productionOf = (date, engineKey = null) => {
  const rows = props.logs.filter(item =>
    item.recorded_date === date && item.produksi !== null && (engineKey === null || item.engine === engineKey)
  );
  return rows.length ? rows.reduce((sum, item) => sum + item.produksi, 0) : null;
};

const chartSeries = computed(() => {
  const engineSeries = props.engines.map(engine => ({
    name: engine.label,
    type: 'column',
    data: dates.value.map(date => productionOf(date, engine.key)),
  }));

  return [
    ...engineSeries,
    {
      name: 'Total produksi',
      type: 'line',
      data: dates.value.map(date => productionOf(date)),
    },
  ];
});

const formatKwh = (val) => (val !== null && val !== undefined ? `${Number(val).toLocaleString('id-ID', { maximumFractionDigits: 0 })} kWh` : '-');

const chartOptions = computed(() => {
  const isDark = isDarkModeRef.value;
  const base = baseChartOptions(isDark);
  const engineCount = props.engines.length;

  return {
    ...base,
    chart: { ...base.chart, type: 'line', stacked: false },
    // Engine = palet kategorikal grafik; garis total = warna teks utama agar menonjol tanpa warna baru
    colors: [...seriesColors(isDark, engineCount), chartTokens(isDark).text],
    stroke: { width: [...props.engines.map(() => 0), 2], curve: 'smooth' },
    plotOptions: { bar: { columnWidth: '55%', borderRadius: 2 } },
    fill: { opacity: [...props.engines.map(() => 0.9), 1] },
    markers: { size: [...props.engines.map(() => 0), 3], hover: { size: 5 } },
    xaxis: { ...base.xaxis, categories: categories.value },
    yaxis: { labels: { ...base.yaxis.labels, formatter: formatKwh } },
    tooltip: { ...base.tooltip, shared: true, intersect: false, y: { formatter: formatKwh } },
    legend: { ...base.legend, position: 'top', horizontalAlign: 'left' },
  };
});
</script>
