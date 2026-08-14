<template>
  <div
    :class="[
      'border rounded-2xl transition-colors duration-300 shadow-sm overflow-hidden',
      isDarkMode
        ? 'bg-slate-900 border-slate-800'
        : 'bg-white border-slate-200'
    ]"
  >
    <!-- Card Header -->
    <div
      :class="[
        'p-4 sm:p-5 flex items-center justify-between gap-3 border-b cursor-pointer select-none',
        isDarkMode ? 'border-slate-800 bg-slate-900/60' : 'border-slate-100 bg-slate-50/50'
      ]"
      @click="isCollapsed = !isCollapsed"
    >
      <div class="flex items-center gap-3">
        <div
          :class="[
            'w-9 h-9 rounded-xl border flex items-center justify-center shrink-0 shadow-sm',
            isDarkMode
              ? 'bg-rose-500/10 border-rose-500/30 text-rose-400'
              : 'bg-rose-50 border-rose-200 text-rose-600'
          ]"
        >
          <PieChart class="w-5 h-5" />
        </div>
        <div>
          <h3 :class="['text-sm sm:text-base font-bold tracking-wide flex items-center gap-2', isDarkMode ? 'text-white' : 'text-slate-900']">
            <span>Visualisasi Distribusi & Status Penanganan Gangguan</span>
          </h3>
          <p :class="['text-xs', isDarkMode ? 'text-slate-400' : 'text-slate-500']">
            Analisis frekuensi jenis gangguan operasional dan persentase status penanganan
          </p>
        </div>
      </div>

      <button
        type="button"
        @click.stop="isCollapsed = !isCollapsed"
        :class="[
          'p-1.5 rounded-lg border transition-all',
          isDarkMode
            ? 'bg-slate-800 border-slate-700 text-slate-400 hover:text-white'
            : 'bg-white border-slate-200 text-slate-500 hover:text-slate-900'
        ]"
      >
        <ChevronDown v-if="isCollapsed" class="w-5 h-5" />
        <ChevronUp v-else class="w-5 h-5" />
      </button>
    </div>

    <!-- Charts Container (2 Charts Side-by-Side) -->
    <div v-show="!isCollapsed" class="p-4 sm:p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- 1. Pie / Donut Chart: Distribusi Frekuensi Jenis Gangguan -->
      <div class="p-4 rounded-xl border dark:border-slate-800 border-slate-100 bg-slate-50/50 dark:bg-slate-950/40 space-y-3">
        <h4 :class="['text-xs font-bold uppercase tracking-wider', isDarkMode ? 'text-slate-400' : 'text-slate-600']">
          Distribusi Frekuensi Jenis Gangguan
        </h4>
        <div class="w-full h-[280px]">
          <apexchart
            type="donut"
            height="100%"
            width="100%"
            :options="pieChartOptions"
            :series="pieChartSeries"
          />
        </div>
      </div>

      <!-- 2. Bar Chart: Status Penanganan Operasional -->
      <div class="p-4 rounded-xl border dark:border-slate-800 border-slate-100 bg-slate-50/50 dark:bg-slate-950/40 space-y-3">
        <h4 :class="['text-xs font-bold uppercase tracking-wider', isDarkMode ? 'text-slate-400' : 'text-slate-600']">
          Status Penanganan Operasional
        </h4>
        <div class="w-full h-[280px]">
          <apexchart
            type="bar"
            height="100%"
            width="100%"
            :options="barChartOptions"
            :series="barChartSeries"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, inject } from 'vue';
import { PieChart, ChevronUp, ChevronDown } from 'lucide-vue-next';

const props = defineProps({
  typeDistribution: {
    type: Object,
    required: true,
  },
  summary: {
    type: Object,
    required: true,
  },
  isDarkMode: {
    type: Boolean,
    default: false,
  },
});

const isDarkModeRef = inject('isDarkMode', ref(props.isDarkMode));
const isCollapsed = ref(false);

// Donut Chart Series & Options
const pieChartSeries = computed(() => {
  return Object.values(props.typeDistribution);
});

const pieChartOptions = computed(() => {
  const isDark = isDarkModeRef.value;
  const labels = Object.keys(props.typeDistribution);

  return {
    chart: {
      type: 'donut',
      fontFamily: 'Inter, sans-serif',
      foreColor: isDark ? '#94a3b8' : '#475569',
      background: 'transparent',
    },
    labels: labels,
    colors: ['#ef4444', '#f97316', '#eab308', '#3b82f6', '#8b5cf6', '#64748b'],
    legend: {
      position: 'bottom',
      horizontalAlign: 'center',
      labels: {
        colors: isDark ? '#cbd5e1' : '#334155',
      },
    },
    tooltip: {
      theme: isDark ? 'dark' : 'light',
      y: {
        formatter: (val) => `${val} Kejadian`,
      },
    },
    stroke: {
      show: true,
      colors: [isDark ? '#0f172a' : '#ffffff'],
    },
  };
});

// Bar Chart Series & Options (Status Penanganan)
const barChartSeries = computed(() => {
  return [
    {
      name: 'Jumlah Kejadian',
      data: [
        props.summary.inProgress,
        props.summary.resolved,
        props.summary.investigating,
      ],
    },
  ];
});

const barChartOptions = computed(() => {
  const isDark = isDarkModeRef.value;

  return {
    chart: {
      type: 'bar',
      fontFamily: 'Inter, sans-serif',
      foreColor: isDark ? '#94a3b8' : '#475569',
      toolbar: { show: false },
      background: 'transparent',
    },
    plotOptions: {
      bar: {
        distributed: true,
        borderRadius: 8,
        columnWidth: '45%',
      },
    },
    colors: ['#eab308', '#10b981', '#3b82f6'],
    xaxis: {
      categories: ['Dalam Penanganan', 'Selesai (Normal)', 'Investigasi'],
      labels: {
        style: {
          colors: isDark ? '#94a3b8' : '#475569',
          fontSize: '11px',
          fontWeight: 600,
        },
      },
      axisBorder: { color: isDark ? '#334155' : '#cbd5e1' },
    },
    yaxis: {
      labels: {
        style: { colors: isDark ? '#94a3b8' : '#475569' },
        formatter: (val) => Math.round(val),
      },
    },
    grid: {
      borderColor: isDark ? '#1e293b' : '#f1f5f9',
      strokeDashArray: 4,
    },
    tooltip: {
      theme: isDark ? 'dark' : 'light',
      y: {
        formatter: (val) => `${val} Kasus`,
      },
    },
    legend: { show: false },
  };
});
</script>
