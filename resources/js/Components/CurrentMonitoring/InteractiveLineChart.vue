<template>
  <ChartCard
    title="Tren beban arus"
    :description="isSingleMode ? activeFeederName : `Semua penyulang (${feeders.length})`"
  >
    <template #controls>
      <Segmented v-model="viewMode" :options="viewOptions" label="Tampilan grafik" />
      <template v-if="isSingleMode">
        <label for="chart-feeder" class="sr-only">Penyulang</label>
        <select
          id="chart-feeder"
          v-model="selectedFeederId"
          class="h-9 px-3 rounded-lg border border-line bg-surface text-body text-fg focus:outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
        >
          <option v-for="feeder in feeders" :key="feeder.id" :value="feeder.id">
            {{ feeder.name }} ({{ feeder.code }})
          </option>
        </select>
      </template>
    </template>

    <apexchart type="line" height="100%" width="100%" :options="chartOptions" :series="chartSeries" />
  </ChartCard>
</template>

<script setup>
import { ref, computed, inject } from 'vue';
import ChartCard from '@/Components/Shared/ChartCard.vue';
import Segmented from '@/Components/Shared/Segmented.vue';
import { baseChartOptions, chartTokens, seriesColors } from '@/utils/chartTheme';

const props = defineProps({
  feeders: { type: Array, required: true },
  matrix: { type: Array, required: true },
  intervals: { type: Array, required: true },
  isDarkMode: { type: Boolean, default: false },
});

const isDarkModeRef = inject('isDarkMode', ref(props.isDarkMode));

// Label sumbu Y & tooltip: maksimal 1 desimal, format Indonesia (mis. 125,5)
const formatAmpere = (val) => Number(val).toLocaleString('id-ID', { maximumFractionDigits: 1 });

const viewMode = ref('single'); // 'single' | 'all'
const selectedFeederId = ref(props.feeders.length > 0 ? props.feeders[0].id : null);

const viewOptions = computed(() => [
  { key: 'single', label: 'Satu penyulang' },
  { key: 'all', label: 'Semua', count: props.feeders.length },
]);

const isSingleMode = computed(() => viewMode.value === 'single');

const activeFeederName = computed(() => props.feeders.find((item) => item.id === selectedFeederId.value)?.name ?? '');

const seriesFor = (feeder) => ({
  name: feeder.name,
  data: props.matrix.map((row) => {
    const val = row.values[feeder.id];
    return val !== null && val !== undefined ? parseFloat(val) : null;
  }),
});

const chartSeries = computed(() => {
  if (isSingleMode.value) {
    const feeder = props.feeders.find((item) => item.id === selectedFeederId.value);
    return feeder ? [seriesFor(feeder)] : [];
  }
  return props.feeders.map(seriesFor);
});

const chartOptions = computed(() => {
  const isDark = isDarkModeRef.value;
  const base = baseChartOptions(isDark);
  return {
    ...base,
    chart: { ...base.chart, type: 'line' },
    // Satu penyulang = warna brand; semua penyulang = palet kategorikal khusus grafik
    colors: isSingleMode.value ? [chartTokens(isDark).brand] : seriesColors(isDark, props.feeders.length),
    stroke: { curve: 'smooth', width: isSingleMode.value ? 2.5 : 1.5 },
    markers: { size: isSingleMode.value ? 3 : 0, hover: { size: 5 } },
    xaxis: { ...base.xaxis, categories: props.intervals },
    yaxis: {
      labels: { ...base.yaxis.labels, formatter: (val) => (val !== null ? `${formatAmpere(val)} A` : '-') },
    },
    tooltip: {
      ...base.tooltip,
      y: { formatter: (val) => (val !== null && val !== undefined ? `${formatAmpere(val)} A` : 'Belum diisi') },
    },
    legend: { ...base.legend, show: !isSingleMode.value, position: 'top', horizontalAlign: 'left' },
  };
});
</script>
