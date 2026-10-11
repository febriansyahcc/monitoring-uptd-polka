<template>
  <div class="space-y-6">
    <!-- Desktop & Mobile Main Card Container -->
    <div class="border rounded-lg overflow-hidden transition-colors bg-surface border-line">
      <!-- Card Header -->
      <div class="p-4 sm:p-5 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-line">
        <div class="flex items-center gap-3">
          <div>
            <h2 class="text-section font-semibold text-fg">
              Stok & pemakaian harian
            </h2>
            <p class="text-caption text-fg-muted">
              Monitoring harian main tank, death stock, unloading, netto stock, dan sisa hari operasi
            </p>
          </div>
        </div>

        <Button v-if="canInput" class="self-start sm:self-auto" @click="openAddModal">
          <Plus class="w-4 h-4" />
          <span>Tambah Data Stok BBM</span>
        </Button>
      </div>

      <!-- Desktop Data Table (>= 768px) -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-body border-collapse">
          <thead>
            <tr class="border-b text-label font-medium bg-surface-muted text-fg-muted border-line">
              <th class="py-3 px-3 w-28">Tanggal</th>
              <th class="py-3 px-3 text-right">Pemakaian Hari Ini</th>
              <th class="py-3 px-3 text-right">Main Tank</th>
              <th class="py-3 px-3 text-right">Total Gross</th>
              <th class="py-3 px-3 text-right">Death Stock</th>
              <th class="py-3 px-3 text-right">Unloading</th>
              <th class="py-3 px-3 text-right">Netto</th>
              <th class="py-3 px-3 text-right">Estimasi Harian</th>
              <th class="py-3 px-3 text-center">HOP</th>
              <th class="py-3 px-3 text-center min-w-[170px]">Terakhir diubah</th>
              <th v-if="canInput" class="py-3 px-3 w-24 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-line">
            <tr v-for="log in logs" :key="log.id" class="transition-colors group hover:bg-surface-muted">
              <td class="py-3 px-3 tabular-nums font-medium">{{ formatDate(log.recorded_date) }}</td>
              <td class="py-3 px-3 text-right tabular-nums">{{ formatNumber(log.daily_consumption) }} L</td>
              <td class="py-3 px-3 text-right tabular-nums">{{ formatNumber(log.main_tank) }} L</td>
              <td class="py-3 px-3 text-right tabular-nums font-medium">{{ formatNumber(log.total_gross) }} L</td>
              <td class="py-3 px-3 text-right tabular-nums text-fg-muted">{{ formatNumber(log.death_stock) }} L</td>
              <td class="py-3 px-3 text-right tabular-nums">+{{ formatNumber(log.unloading) }} L</td>
              <td class="py-3 px-3 text-right tabular-nums font-semibold text-fg">{{ formatNumber(log.netto_stock) }} L</td>
              <td class="py-3 px-3 text-right tabular-nums">{{ formatNumber(log.estimated_daily_consumption) }} L</td>
              <td class="py-3 px-3 text-center tabular-nums">
                <StatusBadge :status="fuelStatus(log.days_of_supply)" :label="`${formatNumber(log.days_of_supply, 1)} hari`" />
              </td>
              <td class="py-3 px-3 text-center tabular-nums text-caption">
                {{ log.last_modified !== '-' ? log.last_modified : '—' }}
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
              <td :colspan="canInput ? 11 : 10" class="py-8 text-center text-fg-muted text-caption">
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
          class="border rounded-lg p-4 space-y-3 bg-surface border-line"
        >
          <div class="flex items-center justify-between gap-3 border-b pb-2 border-line">
            <span class="font-medium tabular-nums text-body text-fg">{{ formatDate(log.recorded_date) }}</span>
            <RowActions
              v-if="canInput"
              size="lg"
              :editLabel="`Edit stok BBM ${formatDate(log.recorded_date)}`"
              :deleteLabel="`Hapus stok BBM ${formatDate(log.recorded_date)}`"
              @edit="openEditModal(log)"
              @delete="deleteEntry(log)"
            />
          </div>

          <div class="grid grid-cols-2 gap-2 text-caption">
            <div>
              <span class="text-caption text-fg-muted block">Netto</span>
              <span class="tabular-nums font-semibold text-fg">{{ formatNumber(log.netto_stock, 0) }} L</span>
            </div>
            <div>
              <span class="text-caption text-fg-muted block">HOP</span>
              <StatusBadge :status="fuelStatus(log.days_of_supply)" :label="`${formatNumber(log.days_of_supply, 1)} hari`" />
            </div>
            <div>
              <span class="text-caption text-fg-muted block">Pemakaian Hari Ini</span>
              <span class="tabular-nums">{{ formatNumber(log.daily_consumption, 0) }} L</span>
            </div>
            <div>
              <span class="text-caption text-fg-muted block">Unloading</span>
              <span class="tabular-nums text-fg-muted">+{{ formatNumber(log.unloading, 0) }} L</span>
            </div>
          </div>

          <div class="text-caption tabular-nums text-fg-muted">
            Terakhir diubah: {{ log.last_modified }}
          </div>
        </div>

        <p v-if="logs.length === 0" class="py-6 text-center text-fg-muted text-caption">
          Belum ada data pencatatan stok BBM untuk bulan ini.
        </p>
      </div>
    </div>

    <!-- Modal Form Add/Edit -->
    <Modal :show="showModal" max-width="lg" :closeable="!form.processing" @close="closeModal">
      <template #title>
        <span>{{ isEditing ? 'Edit Data Stok BBM' : 'Tambah Data Stok BBM' }}</span>
      </template>

      <form id="fuel-stock-form" @submit.prevent="submitForm" class="space-y-4">
        <FormField
          label="Tanggal Pencatatan"
          type="date"
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
          v-model="form.estimated_daily_consumption"
          :error="form.errors.estimated_daily_consumption"
        />

        <!-- Live Formulations Auto Preview Card -->
        <div class="p-3 rounded-lg bg-surface-muted border border-line space-y-1.5 text-label tabular-nums text-fg-muted">
          <div class="flex items-center justify-between gap-2">
            <span>Total Gross BBM (= Main Tank):</span>
            <span class="text-fg">{{ formatNumber(calculatedTotalGross, 0) }} L</span>
          </div>
          <div class="flex items-center justify-between gap-2">
            <span>Netto Stock (= Gross - Death + Unloading):</span>
            <span class="text-fg">{{ formatNumber(calculatedNettoStock, 0) }} L</span>
          </div>
          <div class="flex items-center justify-between gap-2 pt-1 border-t border-line">
            <span>Sisa Hari Operasi (= Netto / Estimasi):</span>
            <span class="font-semibold text-fg">{{ formatNumber(calculatedDaysOfSupply, 1) }} hari</span>
          </div>
        </div>

        <FormField label="Nama Operator" v-model="form.operator_name" :error="form.errors.operator_name" placeholder="Kosongkan untuk memakai nama akun" />
      </form>

      <template #footer>
        <Button variant="secondary" class="flex-1" :disabled="form.processing" @click="closeModal">Batal</Button>
        <Button type="submit" form="fuel-stock-form" class="flex-1" :loading="form.processing">Simpan</Button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { Table, Plus } from 'lucide-vue-next';
import Modal from '@/Components/Shared/Modal.vue';
import FormField from '@/Components/Shared/FormField.vue';
import Button from '@/Components/Shared/Button.vue';
import RowActions from '@/Components/Shared/RowActions.vue';
import StatusBadge from '@/Components/Shared/StatusBadge.vue';
import { fuelStatus } from '@/utils/status';
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
