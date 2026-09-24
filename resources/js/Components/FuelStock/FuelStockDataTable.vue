<template>
  <div class="space-y-6">
    <!-- Desktop & Mobile Main Card Container -->
    <div class="border rounded-2xl overflow-hidden shadow-sm transition-colors duration-300 bg-white border-slate-200 dark:bg-slate-900 dark:border-slate-800">
      <!-- Card Header -->
      <div class="p-4 sm:p-5 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-slate-100 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-900/60">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg border flex items-center justify-center bg-amber-50 border-amber-200 text-amber-600 dark:bg-amber-500/10 dark:border-amber-500/30 dark:text-amber-400">
            <Table class="w-4 h-4" />
          </div>
          <div>
            <h3 class="text-sm font-bold tracking-wide text-slate-900 dark:text-white">
              Tabel Pencatatan Stok & Pemakaian BBM
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Monitoring harian main tank, death stock, unloading, netto stock, dan sisa hari operasi
            </p>
          </div>
        </div>

        <Button v-if="canInput" accent="amber" class="self-start sm:self-auto" @click="openAddModal">
          <Plus class="w-4 h-4" />
          <span>Tambah Data Stok BBM</span>
        </Button>
      </div>

      <!-- Desktop Data Table (>= 768px) -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="border-b font-semibold uppercase tracking-wider bg-slate-100/90 text-slate-700 border-slate-200 dark:bg-slate-950/80 dark:text-slate-300 dark:border-slate-800">
              <th class="py-3 px-3 w-28">Tanggal</th>
              <th class="py-3 px-3 text-right">Pemakaian Hari Ini</th>
              <th class="py-3 px-3 text-right">Main Tank</th>
              <th class="py-3 px-3 text-right">Total Gross</th>
              <th class="py-3 px-3 text-right">Death Stock</th>
              <th class="py-3 px-3 text-right font-bold text-emerald-600 dark:text-emerald-500">Unloading</th>
              <th class="py-3 px-3 text-right font-bold text-amber-600 dark:text-amber-500">Netto Stock</th>
              <th class="py-3 px-3 text-right">Estimasi Harian</th>
              <th class="py-3 px-3 text-center font-bold text-cyan-700 dark:text-cyan-400">Sisa Hari Operasi</th>
              <th class="py-3 px-3 text-center min-w-[170px]">Last Modified</th>
              <th v-if="canInput" class="py-3 px-3 w-24 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800/60">
            <tr v-for="log in logs" :key="log.id" class="transition-colors group hover:bg-slate-50 dark:hover:bg-slate-800/50">
              <td class="py-3 px-3 font-bold font-mono text-xs">
                <span class="px-2 py-0.5 rounded border bg-slate-100 text-slate-800 border-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700">
                  {{ formatDate(log.recorded_date) }}
                </span>
              </td>
              <td class="py-3 px-3 text-right font-mono">{{ formatNumber(log.daily_consumption) }} L</td>
              <td class="py-3 px-3 text-right font-mono">{{ formatNumber(log.main_tank) }} L</td>
              <td class="py-3 px-3 text-right font-mono font-medium">{{ formatNumber(log.total_gross) }} L</td>
              <td class="py-3 px-3 text-right font-mono text-rose-600 dark:text-rose-400">{{ formatNumber(log.death_stock) }} L</td>
              <td class="py-3 px-3 text-right font-mono font-semibold text-emerald-600 dark:text-emerald-500">+{{ formatNumber(log.unloading) }} L</td>
              <td class="py-3 px-3 text-right font-mono font-extrabold text-amber-600 dark:text-amber-500">{{ formatNumber(log.netto_stock) }} L</td>
              <td class="py-3 px-3 text-right font-mono">{{ formatNumber(log.estimated_daily_consumption) }} L</td>
              <td class="py-3 px-3 text-center font-mono">
                <span :class="['px-2.5 py-1 rounded-full text-xs font-black border inline-flex items-center gap-1', daysBadgeClass(log.days_of_supply)]">
                  {{ formatNumber(log.days_of_supply, 1) }} Hari
                </span>
              </td>
              <td class="py-3 px-3 text-center font-mono text-[10px]">
                <span
                  v-if="log.last_modified !== '-'"
                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded border font-medium bg-slate-100 text-slate-700 border-slate-200 dark:font-normal dark:bg-slate-800/80 dark:text-slate-300 dark:border-slate-700/50"
                >
                  <Clock class="w-3 h-3 text-amber-500 shrink-0" />
                  <span>{{ log.last_modified }}</span>
                </span>
                <span v-else class="text-slate-400 italic text-[11px]">-</span>
              </td>
              <td v-if="canInput" class="py-3 px-3 text-center">
                <RowActions
                  :editLabel="`Edit stok BBM ${formatDate(log.recorded_date)}`"
                  :deleteLabel="`Hapus stok BBM ${formatDate(log.recorded_date)}`"
                  @edit="openEditModal(log)"
                  @delete="deleteEntry(log)"
                />
              </td>
            </tr>

            <tr v-if="logs.length === 0">
              <td :colspan="canInput ? 11 : 10" class="py-8 text-center text-slate-400 text-xs italic">
                Belum ada data pencatatan stok BBM untuk bulan ini.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Card List (< 768px) -->
      <div class="block md:hidden p-4 space-y-3">
        <div
          v-for="log in logs"
          :key="'m-fuel-' + log.id"
          class="border rounded-xl p-4 space-y-3 shadow-sm bg-slate-50 border-slate-200 dark:bg-slate-950 dark:border-slate-800"
        >
          <div class="flex items-center justify-between gap-3 border-b pb-2 border-slate-200 dark:border-slate-800">
            <span class="font-bold font-mono text-xs text-amber-600 dark:text-amber-500">
              {{ formatDate(log.recorded_date) }}
            </span>
            <RowActions
              v-if="canInput"
              size="lg"
              :editLabel="`Edit stok BBM ${formatDate(log.recorded_date)}`"
              :deleteLabel="`Hapus stok BBM ${formatDate(log.recorded_date)}`"
              @edit="openEditModal(log)"
              @delete="deleteEntry(log)"
            />
          </div>

          <div class="grid grid-cols-2 gap-2 text-xs">
            <div>
              <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Netto Stock</span>
              <span class="font-mono font-bold text-amber-600 dark:text-amber-500">{{ formatNumber(log.netto_stock, 0) }} L</span>
            </div>
            <div>
              <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Sisa Hari Operasi</span>
              <span class="font-mono font-extrabold text-cyan-700 dark:text-cyan-400">{{ formatNumber(log.days_of_supply, 0) }} Hari</span>
            </div>
            <div>
              <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Pemakaian Hari Ini</span>
              <span class="font-mono">{{ formatNumber(log.daily_consumption, 0) }} L</span>
            </div>
            <div>
              <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Unloading</span>
              <span class="font-mono text-emerald-600 dark:text-emerald-500">+{{ formatNumber(log.unloading, 0) }} L</span>
            </div>
          </div>

          <div class="text-[10px] font-mono p-2 rounded border text-slate-500 bg-white border-slate-200 dark:text-slate-400 dark:bg-slate-900/40 dark:border-slate-800">
            Last Modified: {{ log.last_modified }}
          </div>
        </div>

        <p v-if="logs.length === 0" class="py-6 text-center text-slate-400 text-xs italic">
          Belum ada data pencatatan stok BBM untuk bulan ini.
        </p>
      </div>
    </div>

    <!-- Modal Form Add/Edit -->
    <Modal :show="showModal" max-width="lg" :closeable="!form.processing" @close="closeModal">
      <template #title>
        <Fuel class="w-5 h-5 text-amber-500" />
        <span>{{ isEditing ? 'Edit Data Stok BBM' : 'Tambah Data Stok BBM' }}</span>
      </template>

      <form id="fuel-stock-form" @submit.prevent="submitForm" class="space-y-4 text-xs">
        <FormField
          label="Tanggal Pencatatan"
          type="date"
          accent="amber"
          v-model="form.recorded_date"
          :error="form.errors.recorded_date"
          :disabled="isEditing"
          :hint="isEditing ? 'Tanggal tidak dapat diubah saat edit.' : null"
          required
        />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <FormField
            v-for="field in amountFields"
            :key="field.key"
            :label="field.label"
            type="number"
            step="0.01"
            min="0"
            placeholder="0.00"
            accent="amber"
            v-model="form[field.key]"
            :error="form.errors[field.key]"
          />
        </div>

        <FormField
          label="Estimasi Pemakaian Harian (Liter/Hari)"
          type="number"
          step="0.01"
          min="0"
          placeholder="0.00"
          accent="amber"
          v-model="form.estimated_daily_consumption"
          :error="form.errors.estimated_daily_consumption"
        />

        <!-- Live Formulations Auto Preview Card -->
        <div class="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/30 space-y-2 text-xs font-mono text-slate-700 dark:text-slate-300">
          <div class="flex items-center justify-between gap-2">
            <span>Total Gross BBM (= Main Tank):</span>
            <span class="font-bold text-amber-700 dark:text-amber-400">{{ formatNumber(calculatedTotalGross, 0) }} Liter</span>
          </div>
          <div class="flex items-center justify-between gap-2">
            <span>Netto Stock (= Gross - Death + Unloading):</span>
            <span class="font-bold text-amber-700 dark:text-amber-400">{{ formatNumber(calculatedNettoStock, 0) }} Liter</span>
          </div>
          <div class="flex items-center justify-between gap-2 pt-1 border-t border-amber-500/20">
            <span>Sisa Hari Operasi (= Netto / Estimasi):</span>
            <span class="font-black text-cyan-700 dark:text-cyan-400 text-sm">{{ formatNumber(calculatedDaysOfSupply, 1) }} Hari</span>
          </div>
        </div>

        <FormField label="Nama Operator" accent="amber" v-model="form.operator_name" :error="form.errors.operator_name" placeholder="Kosongkan untuk memakai nama akun" />
      </form>

      <template #footer>
        <Button variant="secondary" class="flex-1" :disabled="form.processing" @click="closeModal">Batal</Button>
        <Button type="submit" form="fuel-stock-form" accent="amber" class="flex-1" :loading="form.processing">Simpan</Button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { Table, Plus, Clock, Fuel } from 'lucide-vue-next';
import Modal from '@/Components/Shared/Modal.vue';
import FormField from '@/Components/Shared/FormField.vue';
import Button from '@/Components/Shared/Button.vue';
import RowActions from '@/Components/Shared/RowActions.vue';
import { usePermission } from '@/composables/usePermission';
import { todayLocal } from '@/utils/date';
import { formatDate, formatNumber } from '@/utils/format';

const props = defineProps({
  logs: {
    type: Array,
    required: true,
  },
  selectedMonth: {
    type: String,
    default: '',
  },
});

const { can } = usePermission();
const canInput = computed(() => can('monitoring_bbm.input'));

const amountFields = [
  { key: 'daily_consumption', label: 'Pemakaian Hari Ini (Liter)' },
  { key: 'main_tank', label: 'Main Tank (Liter)' },
  { key: 'death_stock', label: 'Death Stock (Liter)' },
  { key: 'unloading', label: 'Unloading (Liter)' },
];

const showModal = ref(false);
const isEditing = ref(false);

// Nilai awal kosong (null) + placeholder, bukan angka 0 yang harus dihapus dulu (BUG-17)
const emptyForm = () => ({
  id: null,
  recorded_date: todayLocal(),
  daily_consumption: null,
  main_tank: null,
  death_stock: null,
  unloading: null,
  estimated_daily_consumption: null,
  operator_name: '',
});

const form = useForm(emptyForm());

// Live Formulations
const calculatedTotalGross = computed(() => parseFloat(form.main_tank) || 0);

const calculatedNettoStock = computed(
  () => calculatedTotalGross.value - (parseFloat(form.death_stock) || 0) + (parseFloat(form.unloading) || 0)
);

const calculatedDaysOfSupply = computed(() => {
  const est = parseFloat(form.estimated_daily_consumption) || 0;
  return est > 0 ? Math.round((calculatedNettoStock.value / est) * 100) / 100 : 0;
});

const daysBadgeClass = (days) => {
  if (days < 7) return 'bg-rose-500/20 text-rose-700 border-rose-500/40 animate-pulse dark:text-rose-400';
  if (days < 15) return 'bg-amber-500/20 text-amber-700 border-amber-500/40 dark:text-amber-300';
  return 'bg-cyan-500/20 text-cyan-700 border-cyan-500/40 dark:text-cyan-300';
};

const openModal = (data, editing) => {
  isEditing.value = editing;
  Object.assign(form, emptyForm(), data);
  form.clearErrors();
  showModal.value = true;
};

const openAddModal = () => openModal({}, false);

const openEditModal = (log) =>
  openModal(
    {
      id: log.id,
      recorded_date: log.recorded_date,
      daily_consumption: log.daily_consumption,
      main_tank: log.main_tank,
      death_stock: log.death_stock,
      unloading: log.unloading,
      estimated_daily_consumption: log.estimated_daily_consumption,
      operator_name: log.operator_name,
    },
    true
  );

const closeModal = () => {
  showModal.value = false;
};

const submitForm = () => {
  const targetMonth = form.recorded_date?.slice(0, 7);
  form.post('/monitoring-bbm', {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      closeModal();
      if (targetMonth && props.selectedMonth && targetMonth !== props.selectedMonth) {
        router.visit(`/monitoring-bbm?month=${targetMonth}`);
      }
    },
  });
};

const deleteEntry = (log) => {
  if (confirm(`Apakah Anda yakin ingin menghapus pencatatan stok BBM tanggal ${formatDate(log.recorded_date)}?`)) {
    router.delete(`/monitoring-bbm/${log.id}`, { preserveScroll: true, preserveState: true });
  }
};
</script>
