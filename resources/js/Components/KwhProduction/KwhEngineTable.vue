<template>
  <div class="space-y-6">
    <div class="border rounded-2xl overflow-hidden shadow-sm transition-colors duration-300 bg-white border-slate-200 dark:bg-slate-900 dark:border-slate-800">
      <!-- Card Header -->
      <div class="p-4 sm:p-5 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-slate-100 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-900/60">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg border flex items-center justify-center bg-emerald-50 border-emerald-200 text-emerald-600 dark:bg-emerald-500/10 dark:border-emerald-500/30 dark:text-emerald-400">
            <Cog class="w-4 h-4" />
          </div>
          <div>
            <h3 class="text-sm font-bold tracking-wide text-slate-900 dark:text-white">
              Tabel kWh Produksi — ENGINE
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Stand akhir kWh produksi, kWh PS, dan flowmeter per engine
            </p>
          </div>
        </div>

        <Button v-if="canInput" accent="emerald" class="self-start sm:self-auto" @click="openAddModal">
          <Plus class="w-4 h-4" />
          <span>Tambah Data Engine</span>
        </Button>
      </div>

      <!-- Desktop Data Table (>= 768px) -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="border-b font-semibold uppercase tracking-wider text-center bg-slate-100/90 text-slate-700 border-slate-200 dark:bg-slate-950/80 dark:text-slate-300 dark:border-slate-800">
              <th rowspan="2" class="py-2 px-4 text-left">Tanggal</th>
              <th rowspan="2" class="py-2 px-3 text-left">Engine</th>
              <th colspan="2" class="py-2 px-3">Stand Akhir kWh Produksi</th>
              <th rowspan="2" class="py-2 px-3">Stand Akhir kWh PS</th>
              <th colspan="2" class="py-2 px-3">Flowmeter</th>
              <th rowspan="2" class="py-2 px-3 text-emerald-600 dark:text-emerald-400">Produksi</th>
              <th rowspan="2" class="py-2 px-4 min-w-[190px]">Last Modified</th>
              <th v-if="canInput" rowspan="2" class="py-2 px-3 w-24">Aksi</th>
            </tr>
            <tr class="border-b font-semibold uppercase tracking-wider text-center text-[10px] bg-slate-100/90 text-slate-600 border-slate-200 dark:bg-slate-950/80 dark:text-slate-400 dark:border-slate-800">
              <th class="py-1.5 px-3">Akhir</th>
              <th class="py-1.5 px-3">EDMI MK10</th>
              <th class="py-1.5 px-3">In</th>
              <th class="py-1.5 px-3">Out</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800/60">
            <tr v-for="log in logs" :key="log.id" class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/40">
              <td class="py-3 px-4 font-bold font-mono">
                <span class="px-2.5 py-1 rounded border bg-slate-100 text-slate-800 border-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700">
                  {{ formatDate(log.recorded_date) }}
                </span>
              </td>
              <td class="py-3 px-3 font-bold">{{ log.engine_label }}</td>
              <td class="py-3 px-3 text-right font-mono">{{ formatNumber(log.stand_akhir) }}</td>
              <td class="py-3 px-3 text-right font-mono">{{ formatNumber(log.stand_edmi_mk10) }}</td>
              <td class="py-3 px-3 text-right font-mono">{{ formatNumber(log.stand_kwh_ps) }}</td>
              <td class="py-3 px-3 text-right font-mono">{{ formatNumber(log.flowmeter_in) }}</td>
              <td class="py-3 px-3 text-right font-mono">{{ formatNumber(log.flowmeter_out) }}</td>
              <td class="py-3 px-3 text-right font-mono font-extrabold text-emerald-600 dark:text-emerald-400">
                {{ formatNumber(log.produksi) }}
              </td>
              <td class="py-3 px-4 text-center font-mono text-[10px]">
                <span
                  v-if="log.last_modified !== '-'"
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded border font-medium bg-slate-100 text-slate-700 border-slate-200 dark:font-normal dark:bg-slate-800/80 dark:text-slate-300 dark:border-slate-700/50"
                >
                  <Clock class="w-3 h-3 text-emerald-500 shrink-0" />
                  <span>{{ log.last_modified }}</span>
                </span>
                <span v-else class="text-slate-400 italic text-[11px]">-</span>
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
              <td :colspan="canInput ? 10 : 9" class="py-8 text-center text-slate-400 text-xs italic">
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
          class="border rounded-xl p-4 space-y-3 shadow-sm bg-slate-50 border-slate-200 dark:bg-slate-950 dark:border-slate-800"
        >
          <div class="flex items-center justify-between gap-3 border-b pb-2 border-slate-200 dark:border-slate-800">
            <div>
              <span class="font-bold font-mono text-sm text-emerald-600 dark:text-emerald-500">{{ formatDate(log.recorded_date) }}</span>
              <span class="ml-2 text-xs font-bold text-slate-800 dark:text-slate-200">{{ log.engine_label }}</span>
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

          <div class="grid grid-cols-2 gap-2 text-xs">
            <div v-for="field in fields" :key="field.key">
              <span class="text-[10px] text-slate-500 dark:text-slate-400 block">{{ field.label }}</span>
              <span class="font-mono font-semibold">{{ formatNumber(log[field.key]) }}</span>
            </div>
            <div>
              <span class="text-[10px] text-emerald-600 dark:text-emerald-500 font-bold block">Produksi</span>
              <span class="font-mono font-extrabold text-emerald-600 dark:text-emerald-500">{{ formatNumber(log.produksi) }}</span>
            </div>
          </div>

          <div class="text-[10px] font-mono p-2 rounded border text-slate-500 bg-white border-slate-200 dark:text-slate-400 dark:bg-slate-900/40 dark:border-slate-800">
            Last Modified: {{ log.last_modified }}
          </div>
        </div>

        <p v-if="logs.length === 0" class="py-6 text-center text-slate-400 text-xs italic">
          Belum ada data kWh Engine untuk bulan ini.
        </p>
      </div>
    </div>

    <!-- Modal Form Add/Edit -->
    <Modal :show="showModal" max-width="md" :closeable="!form.processing" @close="closeModal">
      <template #title>
        <Cog class="w-5 h-5 text-emerald-500" />
        <span>{{ isEditing ? 'Edit Data kWh Engine' : 'Tambah Data kWh Engine' }}</span>
      </template>

      <form id="kwh-engine-form" @submit.prevent="submitForm" class="space-y-4 text-xs">
        <div class="grid grid-cols-2 gap-3">
          <FormField label="Tanggal" type="date" accent="emerald" v-model="form.recorded_date" :error="form.errors.recorded_date" :disabled="isEditing" required />
          <FormField label="Engine" accent="emerald" :error="form.errors.engine" required v-slot="{ id, inputClass, describedBy }">
            <select :id="id" v-model="form.engine" required :disabled="isEditing" :class="inputClass" :aria-describedby="describedBy">
              <option value="" disabled>— Pilih —</option>
              <option v-for="engine in engines" :key="engine.key" :value="engine.key">{{ engine.label }}</option>
            </select>
          </FormField>
        </div>

        <div v-for="group in fieldGroups" :key="group.title" class="space-y-2">
          <p class="font-bold text-[10px] uppercase tracking-wider text-emerald-600 dark:text-emerald-500">{{ group.title }}</p>
          <div class="grid grid-cols-2 gap-3">
            <FormField
              v-for="field in group.fields"
              :key="field.key"
              :label="field.label"
              type="number"
              step="0.01"
              min="0"
              placeholder="0.00"
              accent="emerald"
              v-model="form[field.key]"
              :error="form.errors[field.key]"
            />
          </div>
        </div>

        <FormField label="Nama Operator" accent="emerald" v-model="form.operator_name" :error="form.errors.operator_name" placeholder="Kosongkan untuk memakai nama akun" />
      </form>

      <template #footer>
        <Button variant="secondary" class="flex-1" :disabled="form.processing" @click="closeModal">Batal</Button>
        <Button type="submit" form="kwh-engine-form" accent="emerald" class="flex-1" :loading="form.processing">Simpan</Button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { Cog, Plus, Clock } from 'lucide-vue-next';
import Modal from '@/Components/Shared/Modal.vue';
import FormField from '@/Components/Shared/FormField.vue';
import Button from '@/Components/Shared/Button.vue';
import RowActions from '@/Components/Shared/RowActions.vue';
import { usePermission } from '@/composables/usePermission';
import { todayLocal } from '@/utils/date';
import { formatDate, formatNumber } from '@/utils/format';

defineProps({
  logs: { type: Array, required: true },
  engines: { type: Array, required: true },
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
  form.post('/monitoring-kwh/engine', {
    preserveScroll: true,
    preserveState: true,
    onSuccess: closeModal,
  });
};

const deleteEntry = (log) => {
  if (confirm(`Hapus data ${log.engine_label} tanggal ${formatDate(log.recorded_date)}?`)) {
    router.delete(`/monitoring-kwh/engine/${log.id}`, { preserveScroll: true, preserveState: true });
  }
};
</script>
