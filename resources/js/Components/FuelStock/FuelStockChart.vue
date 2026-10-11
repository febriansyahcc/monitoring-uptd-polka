<template>
  <ChartCard
    title="Ketahanan stok per hari"
    description="Hari operasi (HOP) dengan batas waspada 10 hari dan kritis 5 hari"
    :empty="logs.length === 0"
    empty-text="Belum ada data stok untuk bulan ini."
  >
    <apexchart type="area" height="100%" width="100%" :options="chartOptions" :series="chartSeries" />
  </ChartCard>
</template>

<script setup>
import { ref, computed, inject } from 'vue';
import ChartCard from '@/Components/Shared/ChartCard.vue';
import { baseChartOptions, chartTokens } from '@/utils/chartTheme';

// Sama dengan App\Support\Status::fuel()
const WARNING_DAYS = 10;
const CRITICAL_DAYS = 5;

const props = defineProps({
  logs: { type: Array, required: true },
  isDarkMode: { type: Boolean, default: false },
});

const isDarkModeRef = inject('isDarkMode', ref(props.isDarkMode));

const formatDaysAxis = (val) => (val !== null && val !== undefined ? `${Number(val).toLocaleString('id-ID', { maximumFractionDigits: 1 })} hari` : '-');
const formatLiter = (val) => `${Number(val).toLocaleString('id-ID', { maximumFractionDigits: 0 })} L`;

const categories = computed(() => props.logs.map((item) => {
  const [, month, day] = item.recorded_date.split('-');
  return `${day}/${month}`;
}));

const chartSeries = computed(() => [{ name: 'Ketahanan stok', data: props.logs.map((item) => item.days_of_supply) }]);

const cssVar = (name, fallback) =>
  typeof getComputedStyle === 'function' && typeof document !== 'undefined'
    ? getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback
    : fallback;

const chartOptions = computed(() => {
  const isDark = isDarkModeRef.value;
  const base = baseChartOptions(isDark);
  const t = chartTokens(isDark);
  // Garis batas memakai warna status (makna), bukan warna dekoratif
  const warn = cssVar('--warn', isDark ? '#e0a84a' : '#9a5b00');
  const crit = cssVar('--crit', isDark ? '#f07a6e' : '#b42318');
  const threshold = (y, color, text) => ({
    y,
    borderColor: color,
    strokeDashArray: 4,
    label: { text, borderWidth: 0, style: { color, background: 'transparent', fontSize: '12px' }, position: 'left', textAnchor: 'start', offsetX: 4 },
  });

  return {
    ...base,
    chart: { ...base.chart, type: 'area' },
    colors: [t.brand],
    stroke: { width: 2, curve: 'straight' },
    fill: { type: 'solid', opacity: 0.08 },
    markers: { size: 3, hover: { size: 5 } },
    xaxis: { ...base.xaxis, categories: categories.value },
    yaxis: { min: 0, labels: { ...base.yaxis.labels, formatter: formatDaysAxis } },
    annotations: {
      yaxis: [threshold(WARNING_DAYS, warn, 'Waspada'), threshold(CRITICAL_DAYS, crit, 'Kritis')],
    },
    tooltip: {
      ...base.tooltip,
      y: {
        formatter: (val, { dataPointIndex }) => {
          const log = props.logs[dataPointIndex];
          return log ? `${formatDaysAxis(val)} · netto ${formatLiter(log.netto_stock)}` : formatDaysAxis(val);
        },
      },
    },
    legend: { show: false },
  };
});
</script>
