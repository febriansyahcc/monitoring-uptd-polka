<template>
  <div
    :class="[
      'border rounded-2xl transition-colors duration-300 shadow-sm overflow-hidden',
      'bg-white border-slate-200 dark:bg-slate-900 dark:border-slate-800'
    ]"
  >
    <!-- Header -->
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
            'bg-emerald-50 border-emerald-200 text-emerald-600 dark:bg-emerald-500/10 dark:border-emerald-500/30 dark:text-emerald-400'
          ]"
        >
          <BarChart3 class="w-5 h-5" />
        </div>
        <div>
          <h3 :class="['text-sm sm:text-base font-bold tracking-wide flex items-center gap-2', 'text-slate-900 dark:text-white']">
            <span>Grafik Produksi kWh Engine Harian</span>
          </h3>
          <p :class="['text-xs', 'text-slate-500 dark:text-slate-400']">
            Produksi per engine (selisih stand akhir) dan garis total produksi harian
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
      <div v-if="dates.length > 0" class="w-full h-[320px] sm:h-[360px]">
        <apexchart
          type="line"
          height="100%"
          width="100%"
          :options="chartOptions"
          :series="chartSeries"
        />
      </div>
      <div v-else class="h-[240px] flex flex-col items-center justify-center text-center p-6 text-slate-400 dark:text-slate-500">
        <BarChart3 class="w-8 h-8 mb-2 opacity-40 text-emerald-500" />
        <p class="text-xs font-semibold text-slate-600 dark:text-slate-400">Belum ada data produksi kWh untuk bulan yang dipilih.</p>
        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Grafik per engine dan total produksi harian akan muncul setelah data stand diinput.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, inject } from 'vue';
import { BarChart3, ChevronUp, ChevronDown } from 'lucide-vue-next';

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
const isCollapsed = ref(false);

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
      name: 'Total Produksi',
      type: 'line',
      data: dates.value.map(date => productionOf(date)),
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
      toolbar: {
        show: false,
      },
      animations: {
        enabled: true,
        easing: 'easeinout',
        speed: 400,
      },
      background: 'transparent',
    },
    colors: ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981'],
    stroke: {
      width: [...props.engines.map(() => 0), 3],
      curve: 'smooth',
    },
    plotOptions: {
      bar: {
        columnWidth: '50%',
        borderRadius: 4,
      },
    },
    fill: {
      opacity: [...props.engines.map(() => 0.85), 1],
    },
    markers: {
      size: [...props.engines.map(() => 0), 5],
      hover: {
        size: 7,
      },
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
      axisBorder: {
        color: isDark ? '#334155' : '#cbd5e1',
      },
    },
    yaxis: {
      labels: {
        style: {
          colors: isDark ? '#94a3b8' : '#475569',
          fontSize: '11px',
        },
        formatter: (val) => (val !== null ? `${val.toLocaleString('id-ID')} kWh` : '-'),
      },
      title: {
        text: 'kWh Produksi',
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
      shared: true,
      intersect: false,
      y: {
        formatter: (val) => (val !== null ? `${val.toLocaleString('id-ID')} kWh` : '-'),
      },
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
