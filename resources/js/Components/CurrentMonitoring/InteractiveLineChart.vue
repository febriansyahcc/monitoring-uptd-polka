<template>
  <div
    :class="[
      'border rounded-2xl transition-colors duration-300 shadow-sm overflow-hidden',
      'bg-white border-slate-200 dark:bg-slate-900 dark:border-slate-800'
    ]"
  >
    <!-- Header Controls -->
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
            'bg-cyan-50 border-cyan-200 text-cyan-600 dark:bg-cyan-500/10 dark:border-cyan-500/30 dark:text-cyan-400'
          ]"
        >
          <TrendingUp class="w-5 h-5" />
        </div>
        <div>
          <h3 :class="['text-sm sm:text-base font-bold tracking-wide flex items-center gap-2', 'text-slate-900 dark:text-white']">
            <span>Grafik Tren Beban Arus Listrik (Ampere)</span>
          </h3>
          <p :class="['text-xs', 'text-slate-500 dark:text-slate-400']">
            {{ isSingleMode ? `Menampilkan tren feeder: ${activeFeederName}` : 'Menampilkan tren gabungan seluruh feeder' }}
          </p>
        </div>
      </div>

      <!-- Collapse / Expand Action Button -->
      <button
        type="button"
        @click.stop="isCollapsed = !isCollapsed"
        :class="[
          'p-1.5 rounded-lg border transition-all',
          'bg-white border-slate-200 text-slate-500 hover:text-slate-900 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400 dark:hover:text-white'
        ]"
        :title="isCollapsed ? 'Perluas Grafik' : 'Lipat Grafik'"
      >
        <ChevronDown v-if="isCollapsed" class="w-5 h-5" />
        <ChevronUp v-else class="w-5 h-5" />
      </button>
    </div>

    <!-- Chart Content Body -->
    <div v-show="!isCollapsed" class="p-4 sm:p-6 space-y-4">
      <!-- Simplified Toolbar Filter -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-100/80 dark:bg-slate-950 p-3 rounded-xl border border-slate-200 dark:border-slate-800">
        <!-- View Mode Selector -->
        <div class="flex items-center gap-2">
          <span :class="['text-xs font-semibold', 'text-slate-600 dark:text-slate-400']">Tampilan:</span>
          <div class="flex items-center gap-1">
            <button
              type="button"
              @click="viewMode = 'single'"
              :class="[
                'px-3 py-1.5 rounded-lg text-xs font-bold transition-all',
                viewMode === 'single'
                  ? 'bg-cyan-500 text-slate-950 shadow-sm'
                  : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:hover:text-slate-200'
              ]"
            >
              Fokus 1 Feeder
            </button>
            <button
              type="button"
              @click="viewMode = 'all'"
              :class="[
                'px-3 py-1.5 rounded-lg text-xs font-bold transition-all',
                viewMode === 'all'
                  ? 'bg-cyan-500 text-slate-950 shadow-sm'
                  : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:hover:text-slate-200'
              ]"
            >
              Semua Feeder ({{ feeders.length }})
            </button>
          </div>
        </div>

        <!-- Feeder Dropdown Select (Visible in Single Mode) -->
        <div v-if="viewMode === 'single'" class="flex items-center gap-2">
          <label :class="['text-xs font-semibold', 'text-slate-600 dark:text-slate-400']">Feeder:</label>
          <select
            v-model="selectedFeederId"
            :class="[
              'px-3 py-1.5 rounded-lg text-xs font-bold border focus:outline-none focus:ring-1 focus:ring-cyan-500 transition-all cursor-pointer',
              'bg-white border-slate-300 text-cyan-700 shadow-sm dark:bg-slate-900 dark:border-slate-700 dark:text-cyan-400'
            ]"
          >
            <option v-for="feeder in feeders" :key="feeder.id" :value="feeder.id">
              {{ feeder.name }} ({{ feeder.code }})
            </option>
          </select>
        </div>
      </div>

      <!-- ApexChart Component -->
      <div class="w-full h-[300px] sm:h-[340px]">
        <apexchart
          type="area"
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
import { TrendingUp, ChevronUp, ChevronDown } from 'lucide-vue-next';

const props = defineProps({
  feeders: {
    type: Array,
    required: true,
  },
  matrix: {
    type: Array,
    required: true,
  },
  intervals: {
    type: Array,
    required: true,
  },
  isDarkMode: {
    type: Boolean,
    default: false,
  },
});

const isDarkModeRef = inject('isDarkMode', ref(props.isDarkMode));

// Label sumbu Y & tooltip: maksimal 1 desimal, format Indonesia (mis. 125,5)
const formatAmpere = (val) => Number(val).toLocaleString('id-ID', { maximumFractionDigits: 1 });

const isCollapsed = ref(false);
const viewMode = ref('single'); // 'single' | 'all'
const selectedFeederId = ref(props.feeders.length > 0 ? props.feeders[0].id : null);

const isSingleMode = computed(() => viewMode.value === 'single');

const activeFeederName = computed(() => {
  const f = props.feeders.find(item => item.id === selectedFeederId.value);
  return f ? f.name : '';
});

// Color Palette
const colorPalette = [
  '#06b6d4', '#2563eb', '#7c3aed', '#db2777',
  '#e11d48', '#ea580c', '#ca8a04', '#059669',
  '#0d9488', '#4f46e5', '#9333ea', '#dc2626'
];

const getFeederColor = (feederId) => {
  const index = props.feeders.findIndex(f => f.id === feederId);
  return colorPalette[index % colorPalette.length];
};

const chartSeries = computed(() => {
  if (viewMode.value === 'single') {
    const f = props.feeders.find(item => item.id === selectedFeederId.value);
    if (!f) return [];

    const data = props.matrix.map(row => {
      const val = row.values[f.id];
      return val !== null && val !== undefined ? parseFloat(val) : null;
    });

    return [
      {
        name: f.name,
        data: data,
      }
    ];
  }

  // All mode
  return props.feeders.map(f => {
    const data = props.matrix.map(row => {
      const val = row.values[f.id];
      return val !== null && val !== undefined ? parseFloat(val) : null;
    });

    return {
      name: f.name,
      data: data,
    };
  });
});

const chartOptions = computed(() => {
  const isDark = isDarkModeRef.value;
  let colors = [];

  if (viewMode.value === 'single') {
    colors = [getFeederColor(selectedFeederId.value)];
  } else {
    colors = props.feeders.map(f => getFeederColor(f.id));
  }

  return {
    chart: {
      type: 'area',
      fontFamily: 'Inter, sans-serif',
      foreColor: isDark ? '#94a3b8' : '#475569',
      toolbar: {
        show: false, // Hide cluttered ApexCharts toolbar
      },
      animations: {
        enabled: true,
        easing: 'easeinout',
        speed: 400,
      },
      background: 'transparent',
    },
    colors: colors,
    fill: {
      type: 'gradient',
      gradient: {
        shadeIntensity: 1,
        opacityFrom: isSingleMode.value ? 0.45 : 0.15,
        opacityTo: 0.05,
        stops: [0, 90, 100],
      },
    },
    stroke: {
      curve: 'smooth',
      width: isSingleMode.value ? 3 : 2,
    },
    markers: {
      size: isSingleMode.value ? 5 : 3,
      hover: {
        size: 7,
      },
    },
    xaxis: {
      categories: props.intervals,
      labels: {
        style: {
          colors: isDark ? '#94a3b8' : '#475569',
          fontSize: '11px',
          fontWeight: 600,
        },
      },
      axisBorder: {
        color: isDark ? '#334155' : '#cbd5e1',
      },
      axisTicks: {
        color: isDark ? '#334155' : '#cbd5e1',
      },
    },
    yaxis: {
      labels: {
        style: {
          colors: isDark ? '#94a3b8' : '#475569',
          fontSize: '11px',
        },
        formatter: (val) => (val !== null ? `${formatAmpere(val)} A` : '-'),
      },
      title: {
        text: 'Ampere (A)',
        style: {
          color: isDark ? '#64748b' : '#64748b',
          fontSize: '10px',
          fontWeight: 600,
        },
      },
    },
    grid: {
      borderColor: isDark ? '#1e293b' : '#f1f5f9',
      strokeDashArray: 4,
    },
    tooltip: {
      theme: isDark ? 'dark' : 'light',
      x: {
        show: true,
      },
      y: {
        formatter: (val) => (val !== null ? `${formatAmpere(val)} Ampere` : 'Belum diisi'),
      },
    },
    legend: {
      show: !isSingleMode.value,
      position: 'top',
      horizontalAlign: 'left',
      labels: {
        colors: isDark ? '#cbd5e1' : '#334155',
      },
    },
  };
});
</script>
