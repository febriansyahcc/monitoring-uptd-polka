<template>
  <Head title="Dashboard" />

  <!-- Judul, tanggal & shift ada di Header (satu-satunya judul halaman) -->

  <!-- 1. Peringatan: hanya muncul bila ada kondisi waspada/kritis -->
  <StatusBanner
    v-if="fuelAlert"
    :status="kpi.fuel.status"
    :title="fuelAlert.title"
    :detail="fuelAlert.detail"
    :href="can('monitoring_bbm.input') ? '/monitoring-bbm' : null"
    action-label="Input BBM"
  />

  <!-- 2. KPI: satu strip, tanpa ikon; warna hanya lewat badge status -->
  <section class="grid grid-cols-2 lg:grid-cols-4 gap-px bg-line border border-line rounded-lg overflow-hidden" aria-label="Ringkasan hari ini">
    <KpiStat
      label="Beban arus"
      :value="kpi.current.has_data ? formatQty(kpi.current.total_load) : null"
      unit="A"
      :hint="kpi.current.has_data ? `Update terakhir ${kpi.current.latest_interval}` : 'Belum diinput hari ini'"
      :href="can('monitoring_arus.view') ? '/monitoring-arus' : null"
    />
    <KpiStat
      label="kWh produksi hari ini"
      :value="kpi.kwh.has_data ? formatQty(kpi.kwh.today_total, 0) : null"
      unit="kWh"
      :hint="kpi.kwh.has_data ? `Bulan ini ${formatQty(kpi.kwh.month_total, 0)} kWh` : 'Belum diinput hari ini'"
      :href="can('monitoring_kwh.view') ? '/monitoring-kwh' : null"
    />
    <KpiStat
      label="Gangguan bulan ini"
      :value="String(kpi.disturbance.total_month)"
      :status="openDisturbances > 0 ? 'warning' : null"
      :status-label="openDisturbances > 0 ? `${openDisturbances} belum selesai` : null"
      :hint="disturbanceHint"
      :href="can('monitoring_gangguan.view') ? '/monitoring-gangguan' : null"
    />
    <KpiStat
      label="Ketahanan stok BBM"
      :value="kpi.fuel.status === 'empty' ? null : fuelDays.value"
      unit="hari"
      :status="kpi.fuel.status"
      :status-label="kpi.fuel.status === 'empty' ? 'Belum ada data' : null"
      :hint="kpi.fuel.status === 'empty' ? null : `Netto ${formatQty(kpi.fuel.netto_stock, 0)} L`"
      :href="can('monitoring_bbm.view') ? '/monitoring-bbm' : null"
    />
  </section>

  <!-- 3. Yang perlu dikerjakan + gangguan terbaru -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-5">
    <InputProgress :progress="inputProgress" class="lg:col-span-2" />

    <Card title="Gangguan terbaru" flush>
      <template #actions>
        <Link v-if="can('monitoring_gangguan.view')" href="/monitoring-gangguan" class="text-label font-medium text-brand-text hover:underline">
          Lihat semua
        </Link>
      </template>
      <ul v-if="recentDisturbances.length" class="divide-y divide-line border-t border-line">
        <li v-for="item in recentDisturbances" :key="item.id" class="px-4 sm:px-5 py-3">
          <div class="flex items-start justify-between gap-3">
            <p class="text-body font-medium text-fg min-w-0 truncate">{{ item.disturbance_type }}</p>
            <StatusBadge :status="item.status_code" :label="item.status" />
          </div>
          <p class="text-caption text-fg-muted tabular-nums mt-0.5">{{ item.event_date }}, {{ item.event_time }}</p>
          <p v-if="item.description && item.description !== '-'" class="text-caption text-fg-muted mt-1 line-clamp-2">
            {{ item.description }}
          </p>
        </li>
      </ul>
      <p v-else class="px-4 sm:px-5 pb-4 text-body text-fg-muted">Belum ada catatan gangguan.</p>
    </Card>
  </div>

  <!-- 4. Grafik: tinggi penuh hanya bila ada data -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-5">
    <Card title="Beban arus hari ini" description="Total semua penyulang per interval">
      <div v-if="currentChartData.length" class="h-64">
        <apexchart type="area" height="100%" width="100%" :options="currentChartOptions" :series="currentChartSeries" />
      </div>
      <p v-else class="text-body text-fg-muted">Belum ada data arus hari ini.</p>
    </Card>

    <Card title="Produksi kWh 7 hari terakhir" description="Produksi engine dan PS total penyulang">
      <div v-if="kwhTrend.length" class="h-64">
        <apexchart type="bar" height="100%" width="100%" :options="kwhChartOptions" :series="kwhChartSeries" />
      </div>
      <p v-else class="text-body text-fg-muted">Belum ada data produksi dalam 7 hari terakhir.</p>
    </Card>
  </div>

  <!-- 5. Beban per penyulang -->
  <Card title="Beban per penyulang" description="Nilai terakhir hari ini">
    <template #actions>
      <Link v-if="can('monitoring_arus.view')" href="/monitoring-arus" class="text-label font-medium text-brand-text hover:underline">
        Buka tabel arus
      </Link>
    </template>
    <ul class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-px bg-line border border-line rounded-md overflow-hidden">
      <li v-for="feeder in feederStatusList" :key="feeder.id" class="bg-surface px-3 py-2.5 min-w-0">
        <p class="flex items-center gap-1.5 text-caption text-fg-muted">
          <span :class="['w-1.5 h-1.5 rounded-full shrink-0', feeder.has_data ? 'bg-ok' : 'bg-empty']" aria-hidden="true"></span>
          <span class="truncate" :title="feeder.name">{{ feeder.name }}</span>
        </p>
        <p class="mt-0.5 text-body font-medium tabular-nums text-fg">
          <template v-if="feeder.has_data">{{ formatQty(feeder.current_value) }} <span class="text-fg-muted font-normal">A</span></template>
          <span v-else class="text-fg-muted font-normal">—<span class="sr-only"> belum ada data</span></span>
        </p>
      </li>
    </ul>
  </Card>
</template>

<script setup>
import { computed, inject, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { usePermission } from '@/composables/usePermission';
import Card from '@/Components/Shared/Card.vue';
import StatusBadge from '@/Components/Shared/StatusBadge.vue';
import StatusBanner from '@/Components/Dashboard/StatusBanner.vue';
import KpiStat from '@/Components/Dashboard/KpiStat.vue';
import InputProgress from '@/Components/Dashboard/InputProgress.vue';
import { formatQty, formatDays } from '@/utils/format';
import { baseChartOptions, chartTokens, seriesColors } from '@/utils/chartTheme';

const props = defineProps({
  kpi: { type: Object, required: true },
  currentChartData: { type: Array, required: true },
  kwhTrend: { type: Array, required: true },
  feederStatusList: { type: Array, required: true },
  recentDisturbances: { type: Array, required: true },
  inputProgress: { type: Object, required: true },
});

const isDarkMode = inject('isDarkMode', ref(false));
const { can } = usePermission();

const fuelDays = computed(() => formatDays(props.kpi.fuel.days_of_supply));

const fuelAlert = computed(() => {
  const { status, netto_stock } = props.kpi.fuel;
  if (status !== 'critical' && status !== 'warning') return null;
  const approx = fuelDays.value.approx ? ` (${fuelDays.value.approx})` : '';
  return {
    title: `Stok BBM ${status === 'critical' ? 'kritis' : 'menipis'}: ${fuelDays.value.value} hari${approx}`,
    detail: `netto ${formatQty(netto_stock, 0)} L`,
  };
});

const openDisturbances = computed(() => props.kpi.disturbance.in_progress + props.kpi.disturbance.investigating);

const disturbanceHint = computed(() => {
  const d = props.kpi.disturbance;
  if (d.total_month === 0) return 'Tidak ada gangguan';
  return `${d.resolved} selesai`;
});

// ---------- Grafik ----------
const axis = (val) => Number(val).toLocaleString('id-ID', { maximumFractionDigits: 1 });

const currentChartSeries = computed(() => [{ name: 'Total arus', data: props.currentChartData.map((i) => i.total_current) }]);

const currentChartOptions = computed(() => {
  const base = baseChartOptions(isDarkMode.value);
  return {
    ...base,
    chart: { ...base.chart, type: 'area' },
    colors: [chartTokens(isDarkMode.value).brand],
    stroke: { curve: 'smooth', width: 2 },
    fill: { type: 'solid', opacity: 0.08 },
    xaxis: { ...base.xaxis, categories: props.currentChartData.map((i) => i.interval) },
    yaxis: { labels: { ...base.yaxis.labels, formatter: (v) => `${axis(v)} A` } },
    tooltip: { ...base.tooltip, y: { formatter: (v) => `${axis(v)} A` } },
  };
});

const kwhChartSeries = computed(() => [
  { name: 'Produksi engine', data: props.kwhTrend.map((i) => i.kwh_total) },
  { name: 'PS total', data: props.kwhTrend.map((i) => i.kwh_ps) },
]);

const kwhChartOptions = computed(() => {
  const base = baseChartOptions(isDarkMode.value);
  return {
    ...base,
    chart: { ...base.chart, type: 'bar' },
    colors: seriesColors(isDarkMode.value, 2),
    plotOptions: { bar: { columnWidth: '50%', borderRadius: 2 } },
    xaxis: { ...base.xaxis, categories: props.kwhTrend.map((i) => i.date) },
    yaxis: { labels: { ...base.yaxis.labels, formatter: axis } },
    legend: { ...base.legend, position: 'top', horizontalAlign: 'left' },
    tooltip: { ...base.tooltip, y: { formatter: (v) => `${axis(v)} kWh` } },
  };
});
</script>
