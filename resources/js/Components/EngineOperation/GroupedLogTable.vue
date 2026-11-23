<template>
  <div class="space-y-6">
    <div class="border rounded-lg overflow-hidden transition-colors bg-surface border-line">
      <!-- Card Header -->
      <div class="p-4 sm:p-5 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-line">
        <div class="flex items-center gap-3">
          <div>
            <h2 class="text-section font-semibold text-fg">
              {{ title }}
            </h2>
            <p v-if="description" class="text-caption text-fg-muted">
              {{ description }}
            </p>
          </div>
        </div>

        <Button v-if="canInput" class="self-start sm:self-auto" @click="openAddModal">
          <Plus class="w-4 h-4" />
          <span>Tambah Data {{ title }}</span>
        </Button>
      </div>

      <!-- Desktop Table (>= 768px) -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-body border-collapse">
          <thead>
            <tr
              v-for="(cells, rowIndex) in headerRows"
              :key="rowIndex"
              class="border-b text-label font-medium text-center text-caption bg-surface-muted text-fg-muted border-line"
            >
              <template v-if="rowIndex === 0">
                <th :rowspan="levels" class="py-2 px-3 sticky left-0 z-10 bg-surface-muted">Jam</th>
                <th v-if="showEngineColumn" :rowspan="levels" class="py-2 px-3">Engine</th>
              </template>

              <th
                v-for="cell in cells"
                :key="cell.key"
                :colspan="cell.colspan"
                :rowspan="cell.rowspan"
                :class="['px-2', cell.sub ? 'py-1.5 min-w-[56px]' : 'py-2', cell.borderLeft ? 'border-l border-line' : '']"
              >
                {{ cell.label }}
                <span v-if="cell.unit" class="block font-normal normal-case text-fg-muted">({{ cell.unit }})</span>
              </th>

              <template v-if="rowIndex === 0">
                <th :rowspan="levels" class="py-2 px-3 min-w-[150px] border-l border-line">Terakhir diubah</th>
                <th v-if="canInput" :rowspan="levels" class="py-2 px-3 sticky right-0 z-10 bg-surface-muted">Aksi</th>
              </template>
            </tr>
          </thead>
          <tbody class="divide-y divide-line">
            <tr v-for="log in logs" :key="log.id" class="transition-colors group hover:bg-surface-muted">
              <td class="py-2.5 px-3 text-center sticky left-0 z-10 bg-surface">
                {{ log.recorded_time }}
              </td>
              <td v-if="showEngineColumn" class="py-2.5 px-3 font-medium whitespace-nowrap">{{ log.engine_label }}</td>
              <td
                v-for="field in allFields"
                :key="field.key"
                :class="['py-2.5 px-2 text-center tabular-nums', log[field.key] === null ? 'text-fg-muted' : '']"
              >
                {{ formatNumber(log[field.key], 0) }}
              </td>
              <td class="py-2.5 px-3 text-center tabular-nums text-caption text-fg-muted">{{ log.last_modified }}</td>
              <td v-if="canInput" class="py-2.5 px-3 text-center sticky right-0 z-10 bg-surface">
                <RowActions
                  :editLabel="`Edit ${rowLabel(log)}`"
                  :deleteLabel="`Hapus ${rowLabel(log)}`"
                  @edit="openEditModal(log)"
                  @delete="deleteEntry(log)"
                />
              </td>
            </tr>

            <tr v-if="logs.length === 0">
              <td :colspan="emptyColspan" class="py-8 text-center text-fg-muted text-caption">
                Belum ada data {{ title }} pada tanggal ini.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Cards (< 768px) -->
      <div class="block md:hidden p-4 space-y-3">
        <div
          v-for="log in logs"
          :key="'m-' + log.id"
          class="border rounded-lg p-4 space-y-3 bg-surface border-line"
        >
          <div class="flex items-center justify-between gap-3 border-b pb-2 border-line">
            <div>
              <span class="font-medium tabular-nums text-body text-fg">{{ log.recorded_time }}</span>
              <span v-if="showEngineColumn" class="ml-2 text-body text-fg-muted">{{ log.engine_label }}</span>
            </div>
            <RowActions
              v-if="canInput"
              size="lg"
              :editLabel="`Edit ${rowLabel(log)}`"
              :deleteLabel="`Hapus ${rowLabel(log)}`"
              @edit="openEditModal(log)"
              @delete="deleteEntry(log)"
            />
          </div>

          <div v-for="group in groups" :key="'m-' + groupKey(group)" class="text-xs">
            <span class="text-caption text-fg-muted block">{{ groupTitle(group) }}<span v-if="group.unit"> ({{ group.unit }})</span></span>
            <div class="flex flex-wrap gap-x-3 tabular-nums text-fg">
              <span v-for="field in group.fields" :key="field.key">
                <span v-if="group.fields.length > 1" class="text-fg-muted font-normal">{{ field.label }}</span>
                {{ formatNumber(log[field.key], 0) }}
              </span>
            </div>
          </div>

          <div class="text-caption tabular-nums text-fg-muted">
            Terakhir diubah: {{ log.last_modified }}
          </div>
        </div>

        <p v-if="logs.length === 0" class="py-6 text-center text-fg-muted text-caption">
          Belum ada data {{ title }} pada tanggal ini.
        </p>
      </div>
    </div>

    <!-- Modal Form Add/Edit -->
    <Modal :show="showModal" max-width="2xl" :closeable="!form.processing" @close="closeModal">
      <template #title>
        <span>{{ isEditing ? 'Edit' : 'Tambah' }} Data {{ title }}</span>
      </template>

      <form :id="formId" @submit.prevent="submitForm" class="space-y-4">
        <div :class="['grid grid-cols-1 gap-3', showEngineColumn ? 'sm:grid-cols-3' : 'sm:grid-cols-2']">
          <FormField label="Tanggal" type="date" v-model="form.recorded_date" :error="form.errors.recorded_date" :disabled="isEditing" required />
          <FormField label="Jam" type="time" v-model="form.recorded_time" :error="form.errors.recorded_time" :disabled="isEditing" required />
          <FormField v-if="showEngineColumn" label="Engine" :error="form.errors.engine" required v-slot="{ id, inputClass, describedBy }">
            <select :id="id" v-model="form.engine" required :disabled="isEditing" :class="inputClass" :aria-describedby="describedBy">
              <option value="" disabled>— Pilih —</option>
              <option v-for="engine in engines" :key="engine.key" :value="engine.key">{{ engine.label }}</option>
            </select>
          </FormField>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <template v-for="(group, index) in groups" :key="'f-' + groupKey(group)">
            <h3
              v-if="group.section && (index === 0 || groups[index - 1].section !== group.section)"
              class="sm:col-span-2 -mb-2 text-label font-semibold text-fg"
            >
              {{ group.section }}
            </h3>
            <fieldset class="p-3 rounded-lg border space-y-2 border-line">
              <legend class="px-1 text-label font-medium text-fg">
                {{ group.label }}<span v-if="group.unit" class="normal-case text-fg-muted"> ({{ group.unit }})</span>
              </legend>
              <div :class="['grid gap-2', gridCols[group.fields.length]]">
                <FormField
                  v-for="field in group.fields"
                  :key="field.key"
                  :label="group.fields.length > 1 ? field.label : group.label"
                  :hideLabel="group.fields.length === 1"
                  type="number"
                  :step="field.key === 'cos_q' ? '0.001' : '0.01'"
                  min="0"
                  placeholder="-"
                  v-model="form[field.key]"
                  :error="form.errors[field.key]"
                />
              </div>
            </fieldset>
          </template>
        </div>

        <FormField label="Nama Operator" v-model="form.operator_name" :error="form.errors.operator_name" placeholder="Kosongkan untuk memakai nama akun" />
      </form>

      <template #footer>
        <Button variant="secondary" class="flex-1" :disabled="form.processing" @click="closeModal">Batal</Button>
        <Button type="submit" :form="formId" class="flex-1" :loading="form.processing">Simpan</Button>
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
import { formatNumber } from '@/utils/format';
import { currentTimeRounded } from '@/utils/date';

const props = defineProps({
  logs: { type: Array, required: true },
  // Pilihan engine: >1 = kolom & pilihan engine tampil, 1 = engine tetap (tersembunyi), kosong = tanpa kolom engine
  engines: { type: Array, default: () => [] },
  // [{ key?, section?, label, unit, fields: [{ key, label }] }]
  groups: { type: Array, required: true },
  selectedDate: { type: String, required: true },
  title: { type: String, default: 'Control Panel' },
  description: { type: String, default: '' },
  // Alamat simpan/hapus, mis. '/monitoring-operasi-engine/control-panel' (hapus: `${endpoint}/{id}`)
  endpoint: { type: String, default: '/monitoring-operasi-engine/control-panel' },
  // Tab & jenis engine yang dibuka setelah menyimpan data tanggal lain
  tab: { type: String, default: 'control_panel' },
  engineType: { type: String, default: 'cat' },
});

const { can } = usePermission();
const canInput = computed(() => can('monitoring_engine.input'));

const gridCols = { 1: 'grid-cols-1', 2: 'grid-cols-2', 3: 'grid-cols-3' };

const groupKey = (group) => group.key ?? group.label;
const groupTitle = (group) => (group.section ? `${group.section} · ${group.label}` : group.label);

const allFields = computed(() => props.groups.flatMap((group) => group.fields));
const showEngineColumn = computed(() => props.engines.length > 1);
const formId = computed(() => `grouped-log-form-${props.tab}-${props.engineType}`);

// Judul bertingkat: grup bisa berada di bawah `section` (3 tingkat) atau langsung (2 tingkat)
const levels = computed(() => (props.groups.some((group) => group.section) ? 3 : 2));

const headerRows = computed(() => {
  const depth = levels.value;
  const rows = Array.from({ length: depth }, () => []);
  const groups = props.groups;

  const pushFields = (group, row) =>
    group.fields.forEach((field, index) =>
      rows[row].push({ key: field.key, label: field.label, sub: true, borderLeft: index === 0 })
    );

  let i = 0;
  while (i < groups.length) {
    const group = groups[i];

    if (group.section) {
      let end = i;
      let span = 0;
      while (end < groups.length && groups[end].section === group.section) {
        span += groups[end].fields.length;
        end++;
      }
      rows[0].push({ key: `sec-${groupKey(group)}`, label: group.section, colspan: span, rowspan: 1, borderLeft: true });

      for (let k = i; k < end; k++) {
        const inner = groups[k];
        rows[1].push({
          key: groupKey(inner),
          label: inner.label,
          unit: inner.unit,
          colspan: inner.fields.length,
          rowspan: inner.fields.length === 1 ? depth - 1 : 1,
          borderLeft: true,
        });
        if (inner.fields.length > 1) pushFields(inner, 2);
      }
      i = end;
    } else {
      rows[0].push({
        key: groupKey(group),
        label: group.label,
        unit: group.unit,
        colspan: group.fields.length,
        rowspan: group.fields.length === 1 ? depth : depth - 1,
        borderLeft: true,
      });
      if (group.fields.length > 1) pushFields(group, depth - 1);
      i++;
    }
  }

  return rows;
});

// Jam + (Engine) + kolom isian + Terakhir diubah + (Aksi)
const emptyColspan = computed(() => allFields.value.length + 2 + (showEngineColumn.value ? 1 : 0) + (canInput.value ? 1 : 0));

const rowLabel = (log) => [props.title, log.engine_label, `jam ${log.recorded_time}`].filter(Boolean).join(' ');

const showModal = ref(false);
const isEditing = ref(false);

const emptyForm = () => ({
  recorded_date: props.selectedDate,
  recorded_time: currentTimeRounded(),
  ...(props.engines.length > 0 ? { engine: props.engines.length === 1 ? props.engines[0].key : '' } : {}),
  operator_name: '',
  ...Object.fromEntries(allFields.value.map((field) => [field.key, null])),
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
  const targetDate = form.recorded_date;
  form.post(props.endpoint, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      closeModal();
      if (targetDate && targetDate !== props.selectedDate) {
        router.visit(`/monitoring-operasi-engine?date=${targetDate}&tab=${props.tab}&engine=${props.engineType}`);
      }
    },
  });
};

const deleteEntry = (log) => {
  if (confirm(`Hapus data ${rowLabel(log)}?`)) {
    router.delete(`${props.endpoint}/${log.id}`, { preserveScroll: true, preserveState: true });
  }
};
</script>
