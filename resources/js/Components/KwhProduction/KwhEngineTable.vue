<template>
  <div class="space-y-6">
    <div class="border rounded-lg overflow-hidden transition-colors bg-surface border-line">
      <!-- Card Header -->
      <div class="p-4 sm:p-5 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-line">
        <div class="flex items-center gap-3">
          <div>
            <h2 class="text-section font-semibold text-fg">
              kWh per engine
            </h2>
            <p class="text-caption text-fg-muted">
              Stand akhir kWh produksi, kWh PS, dan flowmeter per engine
            </p>
          </div>
        </div>

        <Button v-if="canInput" class="self-start sm:self-auto" @click="openAddModal">
          <Plus class="w-4 h-4" />
          <span>Tambah Data Engine</span>
        </Button>
      </div>

      <!-- Desktop Data Table (>= 768px) -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-body border-collapse">
          <thead>
            <tr class="border-b text-label font-medium text-center bg-surface-muted text-fg-muted border-line">
              <th rowspan="2" class="py-2 px-4 text-left">Tanggal</th>
              <th rowspan="2" class="py-2 px-3 text-left">Engine</th>
              <th colspan="2" class="py-2 px-3">Stand Akhir kWh Produksi</th>
              <th rowspan="2" class="py-2 px-3">Stand Akhir kWh PS</th>
              <th colspan="2" class="py-2 px-3">Flowmeter</th>
              <th rowspan="2" class="py-2 px-3 text-fg-muted">Produksi</th>
              <th rowspan="2" class="py-2 px-4 min-w-[190px]">Terakhir diubah</th>
              <th v-if="canInput" rowspan="2" class="py-2 px-3 w-24">Aksi</th>
            </tr>
            <tr class="border-b text-label font-medium text-center text-caption bg-surface-muted text-fg-muted border-line">
              <th class="py-1.5 px-3">Akhir</th>
              <th class="py-1.5 px-3">EDMI MK10</th>
              <th class="py-1.5 px-3">In</th>
              <th class="py-1.5 px-3">Out</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-line">
            <tr v-for="log in logs" :key="log.id" class="transition-colors hover:bg-surface-muted">
              <td class="py-3 px-4 tabular-nums">
                {{ formatDate(log.recorded_date) }}
              </td>
              <td class="py-3 px-3 font-medium">{{ log.engine_label }}</td>
              <td class="py-3 px-3 text-right tabular-nums">{{ formatNumber(log.stand_akhir) }}</td>
              <td class="py-3 px-3 text-right tabular-nums">{{ formatNumber(log.stand_edmi_mk10) }}</td>
              <td class="py-3 px-3 text-right tabular-nums">{{ formatNumber(log.stand_kwh_ps) }}</td>
              <td class="py-3 px-3 text-right tabular-nums">{{ formatNumber(log.flowmeter_in) }}</td>
              <td class="py-3 px-3 text-right tabular-nums">{{ formatNumber(log.flowmeter_out) }}</td>
              <td class="py-3 px-3 text-right tabular-nums font-semibold text-fg">
                {{ formatNumber(log.produksi) }}
              </td>
              <td class="py-3 px-4 text-center tabular-nums text-caption">
                {{ log.last_modified !== '-' ? log.last_modified : '—' }}
              </td>
              <td v-if="canInput" class="py-3 px-3 text-center">
                <RowActions
                  :editLabel="`Edit ${log.engine_label} ${formatDate(log.recorded_date)}`"
                  :deleteLabel="`Hapus ${log.engine_label} ${formatDate(log.recorded_date)}`"
                  @edit="openEditModal(log)"
                  @delete="deleteEntry(log)"
                />
              </td>
            </tr>

            <tr v-if="logs.length === 0">
              <td :colspan="canInput ? 10 : 9" class="py-8 text-center text-fg-muted text-caption">
                Belum ada data kWh Engine untuk bulan ini.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Card List (< 768px) -->
      <div class="block md:hidden p-4 space-y-3">
        <div
          v-for="log in logs"
          :key="'m-engine-' + log.id"
          class="border rounded-lg p-4 space-y-3 bg-surface border-line"
        >
          <div class="flex items-center justify-between gap-3 border-b pb-2 border-line">
            <div>
              <span class="font-medium tabular-nums text-body text-fg">{{ formatDate(log.recorded_date) }}</span>
              <span class="ml-2 text-body text-fg-muted">{{ log.engine_label }}</span>
            </div>
            <RowActions
              v-if="canInput"
              size="lg"
              :editLabel="`Edit ${log.engine_label} ${formatDate(log.recorded_date)}`"
              :deleteLabel="`Hapus ${log.engine_label} ${formatDate(log.recorded_date)}`"
              @edit="openEditModal(log)"
              @delete="deleteEntry(log)"
            />
          </div>

          <div class="grid grid-cols-2 gap-2 text-caption">
            <div v-for="field in fields" :key="field.key">
              <span class="text-caption text-fg-muted block">{{ field.label }}</span>
              <span class="tabular-nums text-fg">{{ formatNumber(log[field.key]) }}</span>
            </div>
            <div>
              <span class="text-caption text-fg-muted block">Produksi</span>
              <span class="tabular-nums font-semibold text-fg">{{ formatNumber(log.produksi) }}</span>
            </div>
          </div>

          <div class="text-caption tabular-nums text-fg-muted">
            Terakhir diubah: {{ log.last_modified }}
          </div>
        </div>

        <p v-if="logs.length === 0" class="py-6 text-center text-fg-muted text-caption">
          Belum ada data kWh Engine untuk bulan ini.
        </p>
      </div>
    </div>

    <!-- Modal Form Add/Edit -->
    <Modal :show="showModal" max-width="md" :closeable="!form.processing" @close="closeModal">
      <template #title>
        <span>{{ isEditing ? 'Edit Data kWh Engine' : 'Tambah Data kWh Engine' }}</span>
      </template>

      <form id="kwh-engine-form" @submit.prevent="submitForm" class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
          <FormField label="Tanggal" type="date" v-model="form.recorded_date" :error="form.errors.recorded_date" :disabled="isEditing" required />
          <FormField label="Engine" :error="form.errors.engine" required v-slot="{ id, inputClass, describedBy }">
            <select :id="id" v-model="form.engine" required :disabled="isEditing" :class="inputClass" :aria-describedby="describedBy">
              <option value="" disabled>— Pilih —</option>
              <option v-for="engine in engines" :key="engine.key" :value="engine.key">{{ engine.label }}</option>
            </select>
          </FormField>
        </div>

        <div v-for="group in fieldGroups" :key="group.title" class="space-y-2">
          <p class="text-label font-medium text-fg">{{ group.title }}</p>
          <div class="grid grid-cols-2 gap-3">
            <FormField
              v-for="field in group.fields"
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
        </div>

        <FormField label="Nama Operator" v-model="form.operator_name" :error="form.errors.operator_name" placeholder="Kosongkan untuk memakai nama akun" />
      </form>

      <template #footer>
        <Button variant="secondary" class="flex-1" :disabled="form.processing" @click="closeModal">Batal</Button>
        <Button type="submit" form="kwh-engine-form" class="flex-1" :loading="form.processing">Simpan</Button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import Modal from '@/Components/Shared/Modal.vue';
import FormField from '@/Components/Shared/FormField.vue';
import Button from '@/Components/Shared/Button.vue';
import RowActions from '@/Components/Shared/RowActions.vue';
import { usePermission } from '@/composables/usePermission';
import { todayLocal } from '@/utils/date';
import { formatDate, formatNumber } from '@/utils/format';

const props = defineProps({
  logs: { type: Array, required: true },
  engines: { type: Array, required: true },
  selectedMonth: { type: String, default: '' },
});

const { can } = usePermission();
const canInput = computed(() => can('monitoring_kwh.input'));

const fieldGroups = [
  {
    title: 'Stand Akhir kWh Produksi',
    fields: [
      { key: 'stand_akhir', label: 'Akhir' },
      { key: 'stand_edmi_mk10', label: 'EDMI MK10' },
    ],
  },
  {
    title: 'Stand Akhir kWh PS',
    fields: [{ key: 'stand_kwh_ps', label: 'kWh PS' }],
  },
  {
    title: 'Flowmeter',
    fields: [
      { key: 'flowmeter_in', label: 'In' },
      { key: 'flowmeter_out', label: 'Out' },
    ],
  },
];

const fields = fieldGroups.flatMap((group) =>
  group.fields.map((field) => ({
    key: field.key,
    label: group.title === 'Flowmeter' ? `Flowmeter ${field.label}` : field.label,
  }))
);

const showModal = ref(false);
const isEditing = ref(false);

const emptyForm = () => ({
  recorded_date: todayLocal(),
  engine: '',
  stand_akhir: null,
  stand_edmi_mk10: null,
  stand_kwh_ps: null,
  flowmeter_in: null,
  flowmeter_out: null,
  operator_name: '',
});

const form = useForm(emptyForm());

const openModal = (data, editing) => {
  isEditing.value = editing;
  Object.assign(form, emptyForm(), data);
  form.clearErrors();
  showModal.value = true;
};

const openAddModal = () => openModal({}, false);

const openEditModal = (log) =>
  openModal(Object.fromEntries(Object.keys(emptyForm()).map((key) => [key, log[key] ?? emptyForm()[key]])), true);

const closeModal = () => {
  showModal.value = false;
};

const submitForm = () => {
  const targetMonth = form.recorded_date?.slice(0, 7);
  form.post('/monitoring-kwh/engine', {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      closeModal();
      if (targetMonth && props.selectedMonth && targetMonth !== props.selectedMonth) {
        router.visit(`/monitoring-kwh?month=${targetMonth}&tab=engine`);
      }
    },
  });
};

const deleteEntry = (log) => {
  if (confirm(`Hapus data ${log.engine_label} tanggal ${formatDate(log.recorded_date)}?`)) {
    router.delete(`/monitoring-kwh/engine/${log.id}`, { preserveScroll: true, preserveState: true });
  }
};
</script>
