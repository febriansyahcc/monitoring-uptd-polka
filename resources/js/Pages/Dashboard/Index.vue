<template>
  <div class="space-y-6">
    <div class="space-y-6">
      <!-- Judul & tanggal hari ini -->
      <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-1">
        <div>
          <h1 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">Dashboard Operasional</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400">Ringkasan kondisi ULPLTD Poka hari ini</p>
        </div>
        <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
          <CalendarDays class="w-4 h-4 text-cyan-600 dark:text-cyan-400" aria-hidden="true" />
          <span>{{ todayDateFormatted }}</span>
        </p>
      </div>

      <!-- 4 KPI Summary Cards Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <!-- KPI 1: Beban Arus Sistem -->
        <component
          :is="can('monitoring_arus.view') ? Link : 'div'"
          :href="can('monitoring_arus.view') ? '/monitoring-arus' : undefined"
          :class="[
            'p-5 rounded-2xl border transition-all duration-200 shadow-sm hover:shadow-md flex flex-col justify-between group',
            'bg-white border-slate-200 hover:border-cyan-400 dark:bg-slate-900 dark:border-slate-800 dark:hover:border-cyan-500/50'
          ]"
        >
          <div class="flex items-center justify-between">
            <span :class="['text-xs font-bold uppercase tracking-wider', 'text-slate-500 dark:text-slate-400']">
              Beban Arus Sistem
            </span>
            <div class="p-2 rounded-xl bg-cyan-500/10 text-cyan-500 border border-cyan-500/20 group-hover:bg-cyan-500 group-hover:text-slate-950 transition-colors">
              <Activity class="w-5 h-5" />
            </div>
          </div>
          <div class="mt-3">
            <div class="text-3xl font-extrabold tracking-tight flex items-baseline gap-1.5">
              <span>{{ kpi.current.total_load.toLocaleString('id-ID') }}</span>
              <span class="text-sm font-bold text-cyan-500">A</span>
            </div>
            <p :class="['text-xs mt-1 font-medium', 'text-slate-500 dark:text-slate-400']">
              {{ kpi.current.latest_interval !== '-' ? `Update terakhir ${kpi.current.latest_interval}` : 'Belum ada input hari ini' }}
              • {{ kpi.current.active_feeders }} Feeder
            </p>
          </div>
        </component>

        <!-- KPI 2: Produksi kWh Hari Ini -->
        <component
          :is="can('monitoring_kwh.view') ? Link : 'div'"
          :href="can('monitoring_kwh.view') ? '/monitoring-kwh' : undefined"
          :class="[
            'p-5 rounded-2xl border transition-all duration-200 shadow-sm hover:shadow-md flex flex-col justify-between group',
            'bg-white border-slate-200 hover:border-emerald-400 dark:bg-slate-900 dark:border-slate-800 dark:hover:border-emerald-500/50'
          ]"
        >
          <div class="flex items-center justify-between">
            <span :class="['text-xs font-bold uppercase tracking-wider', 'text-slate-500 dark:text-slate-400']">
              kWh Produksi Hari Ini
            </span>
            <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 group-hover:bg-emerald-500 group-hover:text-slate-950 transition-colors">
              <Zap class="w-5 h-5" />
            </div>
          </div>
          <div class="mt-3">
            <div class="text-3xl font-extrabold tracking-tight flex items-baseline gap-1.5">
              <span>{{ kpi.kwh.today_total.toLocaleString('id-ID') }}</span>
              <span class="text-sm font-bold text-emerald-500">kWh</span>
            </div>
            <p :class="['text-xs mt-1 font-medium', 'text-slate-500 dark:text-slate-400']">
              Bulan ini: {{ kpi.kwh.month_total.toLocaleString('id-ID') }} kWh
            </p>
          </div>
        </component>

        <!-- KPI 3: Status Gangguan -->
        <component
          :is="can('monitoring_gangguan.view') ? Link : 'div'"
          :href="can('monitoring_gangguan.view') ? '/monitoring-gangguan' : undefined"
          :class="[
            'p-5 rounded-2xl border transition-all duration-200 shadow-sm hover:shadow-md flex flex-col justify-between group',
            'bg-white border-slate-200 hover:border-rose-400 dark:bg-slate-900 dark:border-slate-800 dark:hover:border-rose-500/50'
          ]"
        >
          <div class="flex items-center justify-between">
            <span :class="['text-xs font-bold uppercase tracking-wider', 'text-slate-500 dark:text-slate-400']">
              Gangguan Bulan Ini
            </span>
            <div class="p-2 rounded-xl bg-rose-500/10 text-rose-500 border border-rose-500/20 group-hover:bg-rose-500 group-hover:text-white transition-colors">
              <AlertTriangle class="w-5 h-5" />
            </div>
          </div>
          <div class="mt-3">
            <div class="text-3xl font-extrabold tracking-tight flex items-baseline gap-1.5">
              <span>{{ kpi.disturbance.total_month }}</span>
              <span class="text-sm font-bold text-rose-500">Kejadian</span>
            </div>
            <p :class="['text-xs mt-1 font-medium', 'text-slate-500 dark:text-slate-400']">
              {{ kpi.disturbance.in_progress }} Penanganan • {{ kpi.disturbance.investigating }} Investigasi • {{ kpi.disturbance.resolved }} Selesai
            </p>
          </div>
        </component>

        <!-- KPI 4: Stok BBM & HOP -->
        <component
          :is="can('monitoring_bbm.view') ? Link : 'div'"
          :href="can('monitoring_bbm.view') ? '/monitoring-bbm' : undefined"
          :class="[
            'p-5 rounded-2xl border transition-all duration-200 shadow-sm hover:shadow-md flex flex-col justify-between group',
            'bg-white border-slate-200 hover:border-amber-400 dark:bg-slate-900 dark:border-slate-800 dark:hover:border-amber-500/50'
          ]"
        >
          <div class="flex items-center justify-between">
            <span :class="['text-xs font-bold uppercase tracking-wider', 'text-slate-500 dark:text-slate-400']">
              Ketahanan Stok BBM
            </span>
            <div class="p-2 rounded-xl bg-amber-500/10 text-amber-500 border border-amber-500/20 group-hover:bg-amber-500 group-hover:text-slate-950 transition-colors">
              <Fuel class="w-5 h-5" />
            </div>
          </div>
          <div class="mt-3">
            <div class="flex items-baseline justify-between">
              <div class="text-3xl font-extrabold tracking-tight flex items-baseline gap-1.5">
                <span>{{ kpi.fuel.days_of_supply }}</span>
                <span class="text-sm font-bold text-amber-500">Hari (HOP)</span>
              </div>
              <span
                :class="[
                  'px-2 py-0.5 rounded text-[10px] font-bold border uppercase',
                  fuelBadgeClass(kpi.fuel.status)
                ]"
              >
                {{ kpi.fuel.status }}
              </span>
            </div>
            <p :class="['text-xs mt-1 font-medium', 'text-slate-500 dark:text-slate-400']">
              Stok Netto: {{ kpi.fuel.netto_stock.toLocaleString('id-ID') }} Liter
            </p>
          </div>
        </component>
      </div>

      <!-- Charts Section (2 Columns Grid) -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Chart 1: Tren Beban Arus Hari Ini -->
        <div
          :class="[
            'p-5 rounded-2xl border transition-colors duration-300 shadow-sm overflow-hidden',
            'bg-white border-slate-200 dark:bg-slate-900 dark:border-slate-800'
          ]"
        >
          <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-200 dark:border-slate-700/40">
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20">
                <BarChart2 class="w-5 h-5" />
              </div>
              <div>
                <h3 :class="['text-sm font-bold', 'text-slate-900 dark:text-white']">
                  Grafik Tren Total Arus Sistem (Hari Ini)
                </h3>
                <p :class="['text-xs', 'text-slate-500 dark:text-slate-400']">
                  Total beban Amper per interval waktu shift
                </p>
              </div>
            </div>
          </div>

          <div class="w-full h-[280px]">
            <apexchart
              v-if="currentChartData.length > 0"
              type="area"
              height="100%"
              width="100%"
              :options="currentChartOptions"
              :series="currentChartSeries"
            />
            <div v-else class="h-full flex items-center justify-center text-slate-500 text-xs italic">
              Belum ada data log arus yang diinput hari ini.
            </div>
          </div>
        </div>

        <!-- Chart 2: Tren Produksi kWh (7 Hari Terakhir) -->
        <div
          :class="[
            'p-5 rounded-2xl border transition-colors duration-300 shadow-sm overflow-hidden',
            'bg-white border-slate-200 dark:bg-slate-900 dark:border-slate-800'
          ]"
        >
          <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-200 dark:border-slate-700/40">
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                <TrendingUp class="w-5 h-5" />
              </div>
              <div>
                <h3 :class="['text-sm font-bold', 'text-slate-900 dark:text-white']">
                  Tren Produksi kWh (7 Hari Terakhir)
                </h3>
                <p :class="['text-xs', 'text-slate-500 dark:text-slate-400']">
                  Perbandingan Produksi Engine & PS Total Penyulang
                </p>
              </div>
            </div>
          </div>

          <div class="w-full h-[280px]">
            <apexchart
              v-if="kwhTrend.length > 0"
              type="bar"
              height="100%"
              width="100%"
              :options="kwhChartOptions"
              :series="kwhChartSeries"
            />
            <div v-else class="h-full flex items-center justify-center text-slate-500 text-xs italic">
              Belum ada data produksi kWh dalam 7 hari terakhir.
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Feeder Status & Recent Disturbance Log -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Feeder Load Overview (2 Cols) -->
        <div
          :class="[
            'lg:col-span-2 p-5 rounded-2xl border transition-colors duration-300 shadow-sm',
            'bg-white border-slate-200 dark:bg-slate-900 dark:border-slate-800'
          ]"
        >
          <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-200 dark:border-slate-700/40">
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                <Layers class="w-5 h-5" />
              </div>
              <div>
                <h3 :class="['text-sm font-bold', 'text-slate-900 dark:text-white']">
                  Status Beban Feeder Hari Ini
                </h3>
                <p :class="['text-xs', 'text-slate-500 dark:text-slate-400']">
                  Ringkasan beban arus terakhir per Feeder
                </p>
              </div>
            </div>
            <Link v-if="can('monitoring_arus.view')" href="/monitoring-arus" class="text-xs font-bold text-cyan-700 hover:text-cyan-600 dark:text-cyan-500 dark:hover:text-cyan-400 flex items-center gap-1">
              <span>Buka Matriks</span>
              <ArrowRight class="w-3.5 h-3.5" />
            </Link>
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            <div
              v-for="feeder in feederStatusList"
              :key="feeder.id"
              :class="[
                'p-3 rounded-xl border transition-all flex flex-col justify-between',
                'bg-slate-50 border-slate-200 dark:bg-slate-950/60 dark:border-slate-800'
              ]"
            >
              <div class="flex items-center justify-between">
                <span :class="['text-[11px] font-bold truncate', 'text-slate-700 dark:text-slate-300']">
                  {{ feeder.code }}
                </span>
                <span
                  :class="['w-2 h-2 rounded-full', feeder.has_data ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-600']"
                  :title="feeder.has_data ? `Update ${feeder.last_updated}` : 'Belum ada input hari ini'"
                ></span>
                <span class="sr-only">{{ feeder.has_data ? 'ada data' : 'belum ada data' }}</span>
              </div>
              <div class="mt-2">
                <div :class="['text-lg font-black', feeder.has_data ? 'text-cyan-700 dark:text-cyan-400' : 'text-slate-400 dark:text-slate-600']">
                  {{ feeder.current_value.toLocaleString('id-ID') }} <span class="text-[10px] text-slate-400">A</span>
                </div>
                <div class="text-[10px] text-slate-500 truncate">
                  {{ feeder.name }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Disturbances (1 Col) -->
        <div
          :class="[
            'p-5 rounded-2xl border transition-colors duration-300 shadow-sm flex flex-col justify-between',
            'bg-white border-slate-200 dark:bg-slate-900 dark:border-slate-800'
          ]"
        >
          <div>
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-200 dark:border-slate-700/40">
              <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                  <Clock class="w-5 h-5" />
                </div>
                <div>
                  <h3 :class="['text-sm font-bold', 'text-slate-900 dark:text-white']">
                    Gangguan Terbaru
                  </h3>
                  <p :class="['text-xs', 'text-slate-500 dark:text-slate-400']">
                    Log 5 kejadian gangguan terakhir
                  </p>
                </div>
              </div>
            </div>

            <div v-if="recentDisturbances.length > 0" class="space-y-3">
              <div
                v-for="item in recentDisturbances"
                :key="item.id"
                :class="[
                  'p-3 rounded-xl border text-xs space-y-1',
                  'bg-slate-50 border-slate-200 dark:bg-slate-950/60 dark:border-slate-800'
                ]"
              >
                <div class="flex items-center justify-between font-bold">
                  <span class="text-rose-600 dark:text-rose-400 truncate">{{ item.disturbance_type }}</span>
                  <span
                    :class="[
                      'px-1.5 py-0.5 rounded text-[10px]',
                      item.status === 'Selesai'
                        ? 'bg-emerald-500/20 text-emerald-700 dark:text-emerald-400'
                        : 'bg-amber-500/20 text-amber-700 dark:text-amber-400'
                    ]"
                  >
                    {{ item.status }}
                  </span>
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                  <span>{{ item.event_date }} - {{ item.event_time }}</span>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-300 line-clamp-1 italic">
                  "{{ item.description }}"
                </p>
              </div>
            </div>
            <div v-else class="py-8 text-center text-slate-500 text-xs italic">
              Tidak ada catatan gangguan terbaru.
            </div>
          </div>

          <div v-if="can('monitoring_gangguan.view')" class="mt-4 pt-3 border-t border-dashed border-slate-200 dark:border-slate-700/50">
            <Link
              href="/monitoring-gangguan"
              class="w-full py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-cyan-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-cyan-400 font-bold text-xs flex items-center justify-center gap-2 transition-colors"
            >
              <span>Lihat Semua Log Gangguan</span>
              <ArrowRight class="w-4 h-4" />
            </Link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, inject, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { usePermission } from '@/composables/usePermission';
import {
  Activity,
  Zap,
  AlertTriangle,
  Fuel,
  ArrowRight,
  BarChart2,
  TrendingUp,
  Layers,
  Clock,
  CalendarDays
} from 'lucide-vue-next';

const props = defineProps({
  todayDateFormatted: { type: String, required: true },
  kpi: { type: Object, required: true },
  currentChartData: { type: Array, required: true },
  kwhTrend: { type: Array, required: true },
  feederStatusList: { type: Array, required: true },
  recentDisturbances: { type: Array, required: true },
});

const isDarkModeRef = inject('isDarkMode', ref(false));
const isDarkMode = computed(() => isDarkModeRef.value);

const { can } = usePermission();

const fuelBadgeClass = (status) => {
  switch (status) {
    case 'Aman': return 'bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 border-emerald-500/30';
    case 'Waspada': return 'bg-amber-500/20 text-amber-700 dark:text-amber-400 border-amber-500/30';
    case 'Kritis': return 'bg-rose-500/20 text-rose-700 dark:text-rose-400 border-rose-500/30';
    default: return 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/30'; // Belum Ada Data
  }
};

const formatAxis = (val) => Number(val).toLocaleString('id-ID', { maximumFractionDigits: 1 });

// Current Area Chart Options
const currentChartSeries = computed(() => [
  {
    name: 'Total Arus (A)',
    data: props.currentChartData.map(i => i.total_current),
  }
]);

const currentChartOptions = computed(() => {
  const isDark = isDarkMode.value;
  return {
    chart: {
      type: 'area',
      fontFamily: 'Inter, sans-serif',
      foreColor: isDark ? '#94a3b8' : '#475569',
      toolbar: { show: false },
      background: 'transparent',
    },
    colors: ['#06b6d4'],
    stroke: { curve: 'smooth', width: 3 },
    fill: {
      type: 'gradient',
      gradient: {
        shadeIntensity: 1,
        opacityFrom: 0.45,
        opacityTo: 0.05,
        stops: [0, 90, 100]
      }
    },
    xaxis: {
      categories: props.currentChartData.map(i => i.interval),
      labels: { style: { colors: isDark ? '#94a3b8' : '#475569', fontSize: '10px' } }
    },
    yaxis: {
      labels: {
        style: { colors: isDark ? '#94a3b8' : '#475569', fontSize: '10px' },
        formatter: (val) => `${formatAxis(val)} A`
      }
    },
    grid: { borderColor: isDark ? '#1e293b' : '#f1f5f9', strokeDashArray: 4 },
    tooltip: {
      theme: isDark ? 'dark' : 'light',
      y: { formatter: (val) => `${formatAxis(val)} Amper` }
    }
  };
});

// kWh Bar Chart Options
const kwhChartSeries = computed(() => [
  { name: 'Produksi Engine', data: props.kwhTrend.map(i => i.kwh_total) },
  { name: 'PS Total', data: props.kwhTrend.map(i => i.kwh_ps) },
]);

const kwhChartOptions = computed(() => {
  const isDark = isDarkMode.value;
  return {
    chart: {
      type: 'bar',
      fontFamily: 'Inter, sans-serif',
      foreColor: isDark ? '#94a3b8' : '#475569',
      toolbar: { show: false },
      background: 'transparent',
    },
    colors: ['#10b981', '#3b82f6'],
    plotOptions: {
      bar: { columnWidth: '45%', borderRadius: 4 }
    },
    xaxis: {
      categories: props.kwhTrend.map(i => i.date),
      labels: { style: { colors: isDark ? '#94a3b8' : '#475569', fontSize: '10px' } }
    },
    yaxis: {
      labels: {
        style: { colors: isDark ? '#94a3b8' : '#475569', fontSize: '10px' },
        formatter: (val) => formatAxis(val)
      }
    },
    grid: { borderColor: isDark ? '#1e293b' : '#f1f5f9', strokeDashArray: 4 },
    tooltip: {
      theme: isDark ? 'dark' : 'light',
      y: { formatter: (val) => `${formatAxis(val)} kWh` }
    }
  };
});
</script>
