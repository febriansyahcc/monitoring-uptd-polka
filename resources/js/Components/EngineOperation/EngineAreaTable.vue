<template>
  <div class="space-y-6">
    <div class="border rounded-lg overflow-hidden transition-colors bg-surface border-line">
      <!-- Card Header -->
      <div class="p-4 sm:p-5 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-line">
        <div class="flex items-center gap-3">
          <div>
            <h2 class="text-section font-semibold text-fg">
              Engine Area
            </h2>
            <p class="text-caption text-fg-muted">
              Turbo speed, temperatur, tekanan, cylinder head, radiator dan flowmeter per jam per engine
            </p>
          </div>
        </div>

        <Button v-if="canInput" class="self-start sm:self-auto" @click="openAddModal">
          <Plus class="w-4 h-4" />
          <span>Tambah Data Engine Area</span>
        </Button>
      </div>

      <!-- Desktop Table (>= 768px) -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-body border-collapse">
          <thead>
            <tr class="border-b text-label font-medium text-center text-caption bg-surface-muted text-fg-muted border-line">
              <th class="py-2 px-3 sticky left-0 z-10 bg-surface-muted">Jam</th>
              <th class="py-2 px-3">Engine</th>
              <th v-for="field in fixedFields" :key="field.key" class="py-2 px-2 border-l border-line">
                {{ field.label }}
                <span v-if="field.unit" class="block font-normal normal-case text-fg-muted">({{ field.unit }})</span>
              </th>
              <th v-for="group in choiceGroups" :key="group.key" class="py-2 px-2 min-w-[150px] border-l border-line">
                {{ group.label }}
                <span v-if="group.unit" class="block font-normal normal-case text-fg-muted">({{ group.unit }})</span>
              </th>
              <th class="py-2 px-3 min-w-[180px] border-l border-line">Terakhir diubah</th>
              <th v-if="canInput" class="py-2 px-3 sticky right-0 z-10 bg-surface-muted">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-line">
            <tr v-for="log in logs" :key="log.id" class="align-top transition-colors group hover:bg-surface-muted">
              <td class="py-2.5 px-3 text-center sticky left-0 z-10 bg-surface">
                {{ log.recorded_time }}
              </td>
              <td class="py-2.5 px-3 font-medium whitespace-nowrap">{{ log.engine_label }}</td>
              <td
                v-for="field in fixedFields"
                :key="field.key"
                :class="['py-2.5 px-2 text-center tabular-nums', log[field.key] === null ? 'text-fg-muted' : '']"
              >
                {{ formatNumber(log[field.key], 0) }}
              </td>
              <td v-for="group in choiceGroups" :key="group.key" class="py-2.5 px-2">
                <div class="flex flex-col gap-0.5 tabular-nums text-caption">
                  <span v-for="option in filledOptions(log, group)" :key="option.key" class="whitespace-nowrap">
                    <span class="text-fg-muted">{{ option.label }}:</span> <span class="text-fg">{{ formatNumber(log[option.key], 0) }}</span>
                  </span>
                  <span v-if="filledOptions(log, group).length === 0" class="text-fg-muted text-center">-</span>
                </div>
              </td>
              <td class="py-2.5 px-3 text-center tabular-nums text-caption text-fg-muted">{{ log.last_modified }}</td>
              <td v-if="canInput" class="py-2.5 px-3 text-center sticky right-0 z-10 bg-surface">
                <RowActions
                  :editLabel="`Edit Engine Area ${log.engine_label} jam ${log.recorded_time}`"
                  :deleteLabel="`Hapus Engine Area ${log.engine_label} jam ${log.recorded_time}`"
                  @edit="openEditModal(log)"
                  @delete="deleteEntry(log)"
                />
              </td>
            </tr>

            <tr v-if="logs.length === 0">
              <td :colspan="fixedFields.length + choiceGroups.length + (canInput ? 4 : 3)" class="py-8 text-center text-fg-muted text-caption">
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
          class="border rounded-lg p-4 space-y-3 bg-surface border-line"
        >
          <div class="flex items-center justify-between gap-3 border-b pb-2 border-line">
            <div>
              <span class="font-medium tabular-nums text-body text-fg">{{ log.recorded_time }}</span>
              <span class="ml-2 text-body text-fg-muted">{{ log.engine_label }}</span>
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

          <div class="grid grid-cols-3 gap-2 text-caption">
            <div v-for="field in fixedFields" :key="field.key">
              <span class="text-caption text-fg-muted block">{{ field.label }}</span>
              <span class="tabular-nums text-fg">{{ formatNumber(log[field.key], 0) }}</span>
            </div>
          </div>

          <template v-for="group in choiceGroups" :key="'m-' + group.key">
            <div v-if="filledOptions(log, group).length" class="text-caption">
              <span class="text-caption text-fg-muted block">{{ group.label }}<span v-if="group.unit"> ({{ group.unit }})</span></span>
              <div class="flex flex-wrap gap-x-3 tabular-nums">
                <span v-for="option in filledOptions(log, group)" :key="option.key">
                  <span class="text-fg-muted">{{ option.label }}</span> <span class="text-fg">{{ formatNumber(log[option.key], 0) }}</span>
                </span>
              </div>
            </div>
          </template>

          <div class="text-caption tabular-nums text-fg-muted">
            Terakhir diubah: {{ log.last_modified }}
          </div>
        </div>

        <p v-if="logs.length === 0" class="py-6 text-center text-fg-muted text-caption">
          Belum ada data Engine Area pada tanggal ini.
        </p>
      </div>
    </div>

    <!-- Modal Form Add/Edit -->
    <Modal :show="showModal" max-width="2xl" :closeable="!form.processing" @close="closeModal">
      <template #title>
        <span>{{ isEditing ? 'Edit Data Engine Area' : 'Tambah Data Engine Area' }}</span>
      </template>

      <form id="engine-area-form" @submit.prevent="submitForm" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <FormField label="Tanggal" type="date" v-model="form.recorded_date" :error="form.errors.recorded_date" :disabled="isEditing" required />
          <FormField label="Jam" type="time" v-model="form.recorded_time" :error="form.errors.recorded_time" :disabled="isEditing" required />
          <FormField label="Engine" :error="form.errors.engine" required v-slot="{ id, inputClass, describedBy }">
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
            v-model="form[field.key]"
            :error="form.errors[field.key]"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div v-for="group in choiceGroups" :key="'c-' + group.key" class="p-3 rounded-lg border space-y-1 border-line">
            <ChoiceValueInput :label="group.label" :unit="group.unit" :options="group.options" :values="form" />
            <p v-for="message in groupErrors(group)" :key="message" class="text-caption text-crit">{{ message }}</p>
          </div>
        </div>

        <FormField label="Nama Operator" v-model="form.operator_name" :error="form.errors.operator_name" placeholder="Kosongkan untuk memakai nama akun" />
      </form>

      <template #footer>
        <Button variant="secondary" class="flex-1" :disabled="form.processing" @click="closeModal">Batal</Button>
        <Button type="submit" form="engine-area-form" class="flex-1" :loading="form.processing">Simpan</Button>
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
