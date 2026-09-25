<template>
  <div class="space-y-6">
    <div class="border rounded-2xl overflow-hidden shadow-sm transition-colors duration-300 bg-white border-slate-200 dark:bg-slate-900 dark:border-slate-800">
      <!-- Card Header -->
      <div class="p-4 sm:p-5 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-slate-100 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-900/60">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg border flex items-center justify-center bg-violet-50 border-violet-200 text-violet-600 dark:bg-violet-500/10 dark:border-violet-500/30 dark:text-violet-400">
            <Thermometer class="w-4 h-4" />
          </div>
          <div>
            <h3 class="text-sm font-bold tracking-wide text-slate-900 dark:text-white">
              Engine Area
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Turbo speed, temperatur, tekanan, cylinder head, radiator dan flowmeter per jam per engine
            </p>
          </div>
        </div>

        <Button v-if="canInput" accent="violet" class="self-start sm:self-auto" @click="openAddModal">
          <Plus class="w-4 h-4" />
          <span>Tambah Data Engine Area</span>
        </Button>
      </div>

      <!-- Desktop Table (>= 768px) -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-xs border-collapse">
          <thead>
            <tr class="border-b font-semibold uppercase tracking-wider text-center text-[10px] bg-slate-100/90 text-slate-700 border-slate-200 dark:bg-slate-950/80 dark:text-slate-300 dark:border-slate-800">
              <th class="py-2 px-3 sticky left-0 z-10 bg-slate-100 dark:bg-slate-950">Jam</th>
              <th class="py-2 px-3">Engine</th>
              <th v-for="field in fixedFields" :key="field.key" class="py-2 px-2 border-l border-slate-200 dark:border-slate-800">
                {{ field.label }}
                <span v-if="field.unit" class="block font-normal normal-case text-slate-500 dark:text-slate-400">({{ field.unit }})</span>
              </th>
              <th v-for="group in choiceGroups" :key="group.key" class="py-2 px-2 min-w-[150px] border-l border-slate-200 dark:border-slate-800">
                {{ group.label }}
                <span v-if="group.unit" class="block font-normal normal-case text-slate-500 dark:text-slate-400">({{ group.unit }})</span>
              </th>
              <th class="py-2 px-3 min-w-[180px] border-l border-slate-200 dark:border-slate-800">Last Modified</th>
              <th v-if="canInput" class="py-2 px-3 sticky right-0 z-10 bg-slate-100 dark:bg-slate-950">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800/60">
            <tr v-for="log in logs" :key="log.id" class="align-top transition-colors group hover:bg-slate-50 dark:hover:bg-slate-800/40">
              <td class="py-2.5 px-3 text-center sticky left-0 z-10 bg-white dark:bg-slate-900">
                <span class="px-2 py-1 rounded font-mono font-bold border bg-violet-50 text-violet-700 border-violet-200 dark:bg-slate-800/80 dark:text-violet-400 dark:border-slate-700/60">
                  {{ log.recorded_time }}
                </span>
              </td>
              <td class="py-2.5 px-3 font-bold whitespace-nowrap">{{ log.engine_label }}</td>
              <td
                v-for="field in fixedFields"
                :key="field.key"
                :class="['py-2.5 px-2 text-center font-mono', log[field.key] === null ? 'text-slate-400' : '']"
              >
                {{ formatNumber(log[field.key], 0) }}
              </td>
              <td v-for="group in choiceGroups" :key="group.key" class="py-2.5 px-2">
                <div class="flex flex-col gap-0.5 font-mono text-[10px]">
                  <span v-for="option in filledOptions(log, group)" :key="option.key" class="whitespace-nowrap">
                    <span class="text-slate-500 dark:text-slate-400">{{ option.label }}:</span> <b>{{ formatNumber(log[option.key], 0) }}</b>
                  </span>
                  <span v-if="filledOptions(log, group).length === 0" class="text-slate-400 text-center">-</span>
                </div>
              </td>
              <td class="py-2.5 px-3 text-center font-mono text-[10px] text-slate-500 dark:text-slate-400">{{ log.last_modified }}</td>
              <td v-if="canInput" class="py-2.5 px-3 text-center sticky right-0 z-10 bg-white dark:bg-slate-900">
                <RowActions
                  :editLabel="`Edit Engine Area ${log.engine_label} jam ${log.recorded_time}`"
                  :deleteLabel="`Hapus Engine Area ${log.engine_label} jam ${log.recorded_time}`"
                  @edit="openEditModal(log)"
                  @delete="deleteEntry(log)"
                />
              </td>
            </tr>

            <tr v-if="logs.length === 0">
              <td :colspan="fixedFields.length + choiceGroups.length + (canInput ? 4 : 3)" class="py-8 text-center text-slate-400 text-xs italic">
                Belum ada data Engine Area pada tanggal ini.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Cards (< 768px) -->
      <div class="block md:hidden p-4 space-y-3">
        <div
          v-for="log in logs"
          :key="'m-ea-' + log.id"
          class="border rounded-xl p-4 space-y-3 shadow-sm bg-slate-50 border-slate-200 dark:bg-slate-950 dark:border-slate-800"
        >
          <div class="flex items-center justify-between gap-3 border-b pb-2 border-slate-200 dark:border-slate-800">
            <div>
              <span class="font-bold font-mono text-sm text-violet-600 dark:text-violet-500">{{ log.recorded_time }}</span>
              <span class="ml-2 text-xs font-bold text-slate-800 dark:text-slate-200">{{ log.engine_label }}</span>
            </div>
            <RowActions
              v-if="canInput"
              size="lg"
              :editLabel="`Edit Engine Area ${log.engine_label} jam ${log.recorded_time}`"
              :deleteLabel="`Hapus Engine Area ${log.engine_label} jam ${log.recorded_time}`"
              @edit="openEditModal(log)"
              @delete="deleteEntry(log)"
            />
          </div>

          <div class="grid grid-cols-3 gap-2 text-xs">
            <div v-for="field in fixedFields" :key="field.key">
              <span class="text-[10px] text-slate-500 dark:text-slate-400 block">{{ field.label }}</span>
              <span class="font-mono font-semibold">{{ formatNumber(log[field.key], 0) }}</span>
            </div>
          </div>

          <template v-for="group in choiceGroups" :key="'m-' + group.key">
            <div v-if="filledOptions(log, group).length" class="text-xs">
              <span class="text-[10px] text-slate-500 dark:text-slate-400 block">{{ group.label }}<span v-if="group.unit"> ({{ group.unit }})</span></span>
              <div class="flex flex-wrap gap-x-3 font-mono">
                <span v-for="option in filledOptions(log, group)" :key="option.key">
                  <span class="text-slate-500 dark:text-slate-400">{{ option.label }}</span> <b>{{ formatNumber(log[option.key], 0) }}</b>
                </span>
              </div>
            </div>
          </template>

          <div class="text-[10px] font-mono p-2 rounded border text-slate-500 bg-white border-slate-200 dark:text-slate-400 dark:bg-slate-900/40 dark:border-slate-800">
            Last Modified: {{ log.last_modified }}
          </div>
        </div>

        <p v-if="logs.length === 0" class="py-6 text-center text-slate-400 text-xs italic">
          Belum ada data Engine Area pada tanggal ini.
        </p>
      </div>
    </div>

    <!-- Modal Form Add/Edit -->
    <Modal :show="showModal" max-width="2xl" :closeable="!form.processing" @close="closeModal">
      <template #title>
        <Thermometer class="w-5 h-5 text-violet-500" />
        <span>{{ isEditing ? 'Edit Data Engine Area' : 'Tambah Data Engine Area' }}</span>
      </template>

      <form id="engine-area-form" @submit.prevent="submitForm" class="space-y-4 text-xs">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <FormField label="Tanggal" type="date" accent="violet" v-model="form.recorded_date" :error="form.errors.recorded_date" :disabled="isEditing" required />
          <FormField label="Jam" type="time" accent="violet" v-model="form.recorded_time" :error="form.errors.recorded_time" :disabled="isEditing" required />
          <FormField label="Engine" accent="violet" :error="form.errors.engine" required v-slot="{ id, inputClass, describedBy }">
            <select :id="id" v-model="form.engine" required :disabled="isEditing" :class="inputClass" :aria-describedby="describedBy">
              <option value="" disabled>— Pilih —</option>
              <option v-for="engine in engines" :key="engine.key" :value="engine.key">{{ engine.label }}</option>
            </select>
          </FormField>
        </div>

        <div class="grid grid-cols-3 gap-3">
          <FormField
            v-for="field in fixedFields"
            :key="'f-' + field.key"
            :label="field.unit ? `${field.label} (${field.unit})` : field.label"
            type="number"
            step="0.01"
            min="0"
            placeholder="-"
            accent="violet"
            v-model="form[field.key]"
            :error="form.errors[field.key]"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div v-for="group in choiceGroups" :key="'c-' + group.key" class="p-3 rounded-xl border space-y-1 border-slate-200 dark:border-slate-800">
            <ChoiceValueInput :label="group.label" :unit="group.unit" :options="group.options" :values="form" />
            <p v-for="message in groupErrors(group)" :key="message" class="text-[11px] font-semibold text-rose-500">{{ message }}</p>
          </div>
        </div>

        <FormField label="Nama Operator" accent="violet" v-model="form.operator_name" :error="form.errors.operator_name" placeholder="Kosongkan untuk memakai nama akun" />
      </form>

      <template #footer>
        <Button variant="secondary" class="flex-1" :disabled="form.processing" @click="closeModal">Batal</Button>
        <Button type="submit" form="engine-area-form" accent="violet" class="flex-1" :loading="form.processing">Simpan</Button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { Thermometer, Plus } from 'lucide-vue-next';
import ChoiceValueInput from '@/Components/Shared/ChoiceValueInput.vue';
import Modal from '@/Components/Shared/Modal.vue';
import FormField from '@/Components/Shared/FormField.vue';
import Button from '@/Components/Shared/Button.vue';
import RowActions from '@/Components/Shared/RowActions.vue';
import { usePermission } from '@/composables/usePermission';
import { formatNumber } from '@/utils/format';
import { currentTimeRounded } from '@/utils/date';

const props = defineProps({
  logs: { type: Array, required: true },
  engines: { type: Array, required: true },
  // [{ key, label, unit }]
  fixedFields: { type: Array, required: true },
  // [{ key, label, unit, options: [{ key, label }] }]
  choiceGroups: { type: Array, required: true },
  selectedDate: { type: String, required: true },
});

const { can } = usePermission();
const canInput = computed(() => can('monitoring_engine.input'));

const valueKeys = computed(() => [
  ...props.fixedFields.map((field) => field.key),
  ...props.choiceGroups.flatMap((group) => group.options.map((option) => option.key)),
]);

const showModal = ref(false);
const isEditing = ref(false);

const currentTime = () => currentTimeRounded();

const emptyForm = () => ({
  recorded_date: props.selectedDate,
  recorded_time: currentTime(),
  engine: '',
  operator_name: '',
  ...Object.fromEntries(valueKeys.value.map((key) => [key, null])),
});

const form = useForm(emptyForm());

const filledOptions = (log, group) => group.options.filter((option) => log[option.key] !== null && log[option.key] !== undefined);

// Error untuk field yang diisi lewat ChoiceValueInput, ditampilkan per grup
const groupErrors = (group) => group.options.map((option) => form.errors[option.key]).filter(Boolean);

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
  const targetDate = form.recorded_date;
  form.post('/monitoring-operasi-engine/engine-area', {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      closeModal();
      if (targetDate && targetDate !== props.selectedDate) {
        router.visit(`/monitoring-operasi-engine?date=${targetDate}&tab=engine_area`);
      }
    },
  });
};

const deleteEntry = (log) => {
  if (confirm(`Hapus data Engine Area ${log.engine_label} jam ${log.recorded_time}?`)) {
    router.delete(`/monitoring-operasi-engine/engine-area/${log.id}`, { preserveScroll: true, preserveState: true });
  }
};
</script>
