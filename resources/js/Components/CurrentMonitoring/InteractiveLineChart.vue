<template>
  <section class="bg-surface border border-line rounded-lg">
    <!-- Header: klik untuk melipat grafik -->
    <div class="flex items-center justify-between gap-3 px-4 sm:px-5 py-3">
      <button
        type="button"
        class="flex-1 min-w-0 text-left rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-brand"
        :aria-expanded="!isCollapsed"
        @click="isCollapsed = !isCollapsed"
      >
        <h2 class="text-section font-semibold text-fg">Tren beban arus</h2>
        <p class="text-caption text-fg-muted truncate">
          {{ isSingleMode ? activeFeederName : `Semua penyulang (${feeders.length})` }}
        </p>
      </button>
      <button
        type="button"
        class="p-1.5 rounded-md text-fg-muted hover:text-fg hover:bg-surface-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-brand"
        :title="isCollapsed ? 'Tampilkan grafik' : 'Lipat grafik'"
        :aria-label="isCollapsed ? 'Tampilkan grafik' : 'Lipat grafik'"
        @click="isCollapsed = !isCollapsed"
      >
        <ChevronDown v-if="isCollapsed" class="w-5 h-5" />
        <ChevronUp v-else class="w-5 h-5" />
      </button>
    </div>

    <div v-show="!isCollapsed" class="px-4 sm:px-5 pb-4 sm:pb-5 space-y-3">
      <div class="flex flex-wrap items-center gap-2">
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
      </div>

      <div class="w-full h-[300px] sm:h-[340px]">
        <apexchart type="line" height="100%" width="100%" :options="chartOptions" :series="chartSeries" />
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, inject } from 'vue';
import { ChevronUp, ChevronDown } from 'lucide-vue-next';
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

const isCollapsed = ref(false);
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
