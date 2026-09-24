<template>
  <div
    :class="[
      'border rounded-2xl transition-colors duration-300 shadow-sm overflow-hidden',
      'bg-white border-slate-200 dark:bg-slate-900 dark:border-slate-800'
    ]"
  >
    <!-- Card Header -->
    <div
      :class="[
        'p-4 sm:p-5 flex items-center justify-between gap-3 border-b cursor-pointer select-none',
        'border-slate-100 bg-slate-50/50 dark:border-slate-800 dark:bg-slate-900/60'
      ]"
      @click="isCollapsed = !isCollapsed"
    >
      <div class="flex items-center gap-3">
        <div
          :class="[
            'w-9 h-9 rounded-xl border flex items-center justify-center shrink-0 shadow-sm',
            'bg-amber-50 border-amber-200 text-amber-600 dark:bg-amber-500/10 dark:border-amber-500/30 dark:text-amber-400'
          ]"
        >
          <Fuel class="w-5 h-5" />
        </div>
        <div>
          <h3 :class="['text-sm sm:text-base font-bold tracking-wide flex items-center gap-2', 'text-slate-900 dark:text-white']">
            <span>Grafik Tren Stok Netto & Ketahanan Operasi BBM (Days of Supply)</span>
          </h3>
          <p :class="['text-xs', 'text-slate-500 dark:text-slate-400']">
            Visualisasi perkembangan ketersediaan stok Netto BBM (Liter) dan ketahanan sisa hari operasi
          </p>
        </div>
      </div>

      <button
        type="button"
        @click.stop="isCollapsed = !isCollapsed"
        :class="[
          'p-1.5 rounded-lg border transition-all',
          'bg-white border-slate-200 text-slate-500 hover:text-slate-900 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400 dark:hover:text-white'
        ]"
      >
        <ChevronDown v-if="isCollapsed" class="w-5 h-5" />
        <ChevronUp v-else class="w-5 h-5" />
      </button>
    </div>

    <!-- Chart Body -->
    <div v-show="!isCollapsed" class="p-4 sm:p-6 space-y-4">
      <div class="w-full h-[320px] sm:h-[360px]">
        <apexchart
          type="line"
          height="100%"
          width="100%"
          :options="chartOptions"
          :series="chartSeries"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, inject } from 'vue';
import { Fuel, ChevronUp, ChevronDown } from 'lucide-vue-next';

const props = defineProps({
  logs: {
    type: Array,
    required: true,
  },
  isDarkMode: {
    type: Boolean,
    default: false,
  },
});

const isDarkModeRef = inject('isDarkMode', ref(props.isDarkMode));
const isCollapsed = ref(false);

const categories = computed(() => {
  return props.logs.map(item => {
    const parts = item.recorded_date.split('-');
    return `${parts[2]}/${parts[1]}`;
  });
});

const chartSeries = computed(() => {
  const nettoStockData = props.logs.map(item => item.netto_stock);
  const daysOfSupplyData = props.logs.map(item => item.days_of_supply);

  return [
    {
      name: 'Netto Stock (Liter)',
      type: 'area',
      data: nettoStockData,
    },
    {
      name: 'Sisa Hari Operasi (Hari)',
      type: 'line',
      data: daysOfSupplyData,
    },
  ];
});

const chartOptions = computed(() => {
  const isDark = isDarkModeRef.value;

  return {
    chart: {
      type: 'line',
      stacked: false,
      fontFamily: 'Inter, sans-serif',
      foreColor: isDark ? '#94a3b8' : '#475569',
      toolbar: { show: false },
      background: 'transparent',
    },
    colors: ['#f59e0b', '#06b6d4'],
    stroke: {
      width: [2, 3],
      curve: 'smooth',
    },
    fill: {
      type: ['gradient', 'solid'],
      gradient: {
        shadeIntensity: 1,
        opacityFrom: 0.35,
        opacityTo: 0.05,
        stops: [0, 90, 100],
      },
    },
    markers: {
      size: [3, 5],
      hover: { size: 7 },
    },
    xaxis: {
      categories: categories.value,
      labels: {
        style: {
          colors: isDark ? '#94a3b8' : '#475569',
          fontSize: '11px',
          fontWeight: 600,
        },
      },
      axisBorder: { color: isDark ? '#334155' : '#cbd5e1' },
    },
    yaxis: [
      {
        title: {
          text: 'Netto Stock (Liter)',
          style: { color: '#f59e0b', fontSize: '10px', fontWeight: 600 },
        },
        labels: {
          style: { colors: isDark ? '#94a3b8' : '#475569', fontSize: '11px' },
          formatter: (val) => (val !== null ? `${val.toLocaleString('id-ID')} L` : '-'),
        },
      },
      {
        opposite: true,
        title: {
          text: 'Sisa Hari Operasi (Hari)',
          style: { color: '#06b6d4', fontSize: '10px', fontWeight: 600 },
        },
        labels: {
          style: { colors: isDark ? '#94a3b8' : '#475569', fontSize: '11px' },
          formatter: (val) => (val !== null ? `${val} Hari` : '-'),
        },
      },
    ],
    grid: {
      borderColor: isDark ? '#1e293b' : '#f1f5f9',
      strokeDashArray: 4,
    },
    tooltip: {
      theme: isDark ? 'dark' : 'light',
      shared: true,
      intersect: false,
    },
    legend: {
      show: true,
      position: 'top',
      horizontalAlign: 'left',
      labels: {
        colors: isDark ? '#cbd5e1' : '#334155',
      },
    },
  };
});
</script>
