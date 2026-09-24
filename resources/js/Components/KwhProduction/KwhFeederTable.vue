<template>
  <div class="space-y-6">
    <div class="border rounded-2xl overflow-hidden shadow-sm transition-colors duration-300 bg-white border-slate-200 dark:bg-slate-900 dark:border-slate-800">
      <!-- Card Header -->
      <div class="p-4 sm:p-5 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-slate-100 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-900/60">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg border flex items-center justify-center bg-emerald-50 border-emerald-200 text-emerald-600 dark:bg-emerald-500/10 dark:border-emerald-500/30 dark:text-emerald-400">
            <Cable class="w-4 h-4" />
          </div>
          <div>
            <h3 class="text-sm font-bold tracking-wide text-slate-900 dark:text-white">
              Tabel kWh Produksi — PENYULANG
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Stand akhir PM 800 / EDMI MK10 (EX/IM), kWh PS, kWh Digital dan PS Total per penyulang
            </p>
          </div>
        </div>

        <Button v-if="canInput" accent="emerald" class="self-start sm:self-auto" @click="openAddModal">
          <Plus class="w-4 h-4" />
          <span>Tambah Data Penyulang</span>
        </Button>
      </div>

      <!-- Desktop Data Table (>= 768px) -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="border-b font-semibold uppercase tracking-wider text-center bg-slate-100/90 text-slate-700 border-slate-200 dark:bg-slate-950/80 dark:text-slate-300 dark:border-slate-800">
              <th class="py-3 px-4 text-left">Tanggal</th>
              <th class="py-3 px-3 text-left">Penyulang</th>
              <th class="py-3 px-3 min-w-[170px]">Stand Akhir kWh Produksi</th>
              <th class="py-3 px-3">kWh PS</th>
              <th class="py-3 px-3">kWh Digital</th>
              <th class="py-3 px-3 text-emerald-600 dark:text-emerald-400">PS Total</th>
              <th class="py-3 px-4 min-w-[190px]">Last Modified</th>
              <th v-if="canInput" class="py-3 px-3 w-24">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800/60">
            <tr v-for="log in logs" :key="log.id" class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/40">
              <td class="py-3 px-4 font-bold font-mono">
                <span class="px-2.5 py-1 rounded border bg-slate-100 text-slate-800 border-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700">
                  {{ formatDate(log.recorded_date) }}
                </span>
              </td>
              <td class="py-3 px-3 font-bold">{{ log.feeder_label }}</td>
              <td class="py-3 px-3">
                <div class="flex flex-col gap-0.5 font-mono text-[10px]">
                  <span v-for="choice in filledStands(log)" :key="choice.key">
                    <span class="text-slate-500 dark:text-slate-400">{{ choice.label }}:</span> <b>{{ formatNumber(log[choice.key]) }}</b>
                  </span>
                  <span v-if="filledStands(log).length === 0" class="text-slate-400 italic">-</span>
                </div>
              </td>
              <td class="py-3 px-3 text-right font-mono">{{ formatNumber(log.kwh_ps) }}</td>
              <td class="py-3 px-3 text-right font-mono">{{ formatNumber(log.kwh_digital) }}</td>
              <td class="py-3 px-3 text-right font-mono font-extrabold text-emerald-600 dark:text-emerald-400">
                {{ formatNumber(log.ps_total) }}
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
                  :editLabel="`Edit ${log.feeder_label} ${formatDate(log.recorded_date)}`"
                  :deleteLabel="`Hapus ${log.feeder_label} ${formatDate(log.recorded_date)}`"
                  @edit="openEditModal(log)"
                  @delete="deleteEntry(log)"
                />
              </td>
            </tr>

            <tr v-if="logs.length === 0">
              <td :colspan="canInput ? 8 : 7" class="py-8 text-center text-slate-400 text-xs italic">
                Belum ada data kWh Penyulang untuk bulan ini.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Card List (< 768px) -->
      <div class="block md:hidden p-4 space-y-3">
        <div
          v-for="log in logs"
          :key="'m-feeder-' + log.id"
          class="border rounded-xl p-4 space-y-3 shadow-sm bg-slate-50 border-slate-200 dark:bg-slate-950 dark:border-slate-800"
        >
          <div class="flex items-center justify-between gap-3 border-b pb-2 border-slate-200 dark:border-slate-800">
            <div>
              <span class="font-bold font-mono text-sm text-emerald-600 dark:text-emerald-500">{{ formatDate(log.recorded_date) }}</span>
              <span class="ml-2 text-xs font-bold text-slate-800 dark:text-slate-200">{{ log.feeder_label }}</span>
            </div>
            <RowActions
              v-if="canInput"
              size="lg"
              :editLabel="`Edit ${log.feeder_label} ${formatDate(log.recorded_date)}`"
              :deleteLabel="`Hapus ${log.feeder_label} ${formatDate(log.recorded_date)}`"
              @edit="openEditModal(log)"
              @delete="deleteEntry(log)"
            />
          </div>

          <div class="grid grid-cols-2 gap-2 text-xs">
            <div v-for="choice in filledStands(log)" :key="choice.key">
              <span class="text-[10px] text-slate-500 dark:text-slate-400 block">{{ choice.label }}</span>
              <span class="font-mono font-semibold">{{ formatNumber(log[choice.key]) }}</span>
            </div>
            <div>
              <span class="text-[10px] text-slate-500 dark:text-slate-400 block">kWh PS</span>
              <span class="font-mono font-semibold">{{ formatNumber(log.kwh_ps) }}</span>
            </div>
            <div>
              <span class="text-[10px] text-slate-500 dark:text-slate-400 block">kWh Digital</span>
              <span class="font-mono font-semibold">{{ formatNumber(log.kwh_digital) }}</span>
            </div>
            <div>
              <span class="text-[10px] text-emerald-600 dark:text-emerald-500 font-bold block">PS Total</span>
              <span class="font-mono font-extrabold text-emerald-600 dark:text-emerald-500">{{ formatNumber(log.ps_total) }}</span>
            </div>
          </div>

          <div class="text-[10px] font-mono p-2 rounded border text-slate-500 bg-white border-slate-200 dark:text-slate-400 dark:bg-slate-900/40 dark:border-slate-800">
            Last Modified: {{ log.last_modified }}
          </div>
        </div>

        <p v-if="logs.length === 0" class="py-6 text-center text-slate-400 text-xs italic">
          Belum ada data kWh Penyulang untuk bulan ini.
        </p>
      </div>
    </div>

    <!-- Modal Form Add/Edit -->
    <Modal :show="showModal" max-width="md" :closeable="!form.processing" @close="closeModal">
      <template #title>
        <Cable class="w-5 h-5 text-emerald-500" />
        <span>{{ isEditing ? 'Edit Data kWh Penyulang' : 'Tambah Data kWh Penyulang' }}</span>
      </template>

      <form id="kwh-feeder-form" @submit.prevent="submitForm" class="space-y-4 text-xs">
        <div class="grid grid-cols-2 gap-3">
          <FormField label="Tanggal" type="date" accent="emerald" v-model="form.recorded_date" :error="form.errors.recorded_date" :disabled="isEditing" required />
          <FormField label="Penyulang" accent="emerald" :error="form.errors.feeder" required v-slot="{ id, inputClass, describedBy }">
            <select :id="id" v-model="form.feeder" required :disabled="isEditing" :class="inputClass" :aria-describedby="describedBy">
              <option value="" disabled>— Pilih —</option>
              <option v-for="feeder in feeders" :key="feeder.key" :value="feeder.key">{{ feeder.label }}</option>
            </select>
          </FormField>
        </div>

        <div class="space-y-1">
          <ChoiceValueInput label="Stand Akhir kWh Produksi" :options="standChoices" :values="form" />
          <p v-for="message in standErrors" :key="message" class="text-[11px] font-semibold text-rose-500">{{ message }}</p>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <FormField label="kWh PS" type="number" step="0.01" min="0" placeholder="0.00" accent="emerald" v-model="form.kwh_ps" :error="form.errors.kwh_ps" />
          <FormField label="kWh Digital" type="number" step="0.01" min="0" placeholder="0.00" accent="emerald" v-model="form.kwh_digital" :error="form.errors.kwh_digital" />
        </div>

        <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-between text-emerald-700 dark:text-emerald-500 font-mono font-bold">
          <span>PS Total (Auto):</span>
          <span class="text-sm">{{ formatNumber(calculatedPsTotal) }} kWh</span>
        </div>

        <FormField label="Nama Operator" accent="emerald" v-model="form.operator_name" :error="form.errors.operator_name" placeholder="Kosongkan untuk memakai nama akun" />
      </form>

      <template #footer>
        <Button variant="secondary" class="flex-1" :disabled="form.processing" @click="closeModal">Batal</Button>
        <Button type="submit" form="kwh-feeder-form" accent="emerald" class="flex-1" :loading="form.processing">Simpan</Button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { Cable, Plus, Clock } from 'lucide-vue-next';
import ChoiceValueInput from '@/Components/Shared/ChoiceValueInput.vue';
import Modal from '@/Components/Shared/Modal.vue';
import FormField from '@/Components/Shared/FormField.vue';
import Button from '@/Components/Shared/Button.vue';
import RowActions from '@/Components/Shared/RowActions.vue';
import { usePermission } from '@/composables/usePermission';
import { todayLocal } from '@/utils/date';
import { formatDate, formatNumber } from '@/utils/format';

const props = defineProps({
  logs: { type: Array, required: true },
  feeders: { type: Array, required: true },
  standChoices: { type: Array, required: true },
});

const { can } = usePermission();
const canInput = computed(() => can('monitoring_kwh.input'));

const showModal = ref(false);
const isEditing = ref(false);

const emptyForm = () => ({
  recorded_date: todayLocal(),
  feeder: '',
  pm800_ex: null,
  pm800_im: null,
  edmi_mk10_ex: null,
  edmi_mk10_im: null,
  kwh_ps: null,
  kwh_digital: null,
  operator_name: '',
});

const form = useForm(emptyForm());

const calculatedPsTotal = computed(() => (parseFloat(form.kwh_ps) || 0) + (parseFloat(form.kwh_digital) || 0));

// Error untuk field stand yang diisi lewat ChoiceValueInput
const standErrors = computed(() => props.standChoices.map((choice) => form.errors[choice.key]).filter(Boolean));

const filledStands = (log) => props.standChoices.filter((choice) => log[choice.key] !== null && log[choice.key] !== undefined);

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
  form.post('/monitoring-kwh/penyulang', {
    preserveScroll: true,
    preserveState: true,
    onSuccess: closeModal,
  });
};

const deleteEntry = (log) => {
  if (confirm(`Hapus data ${log.feeder_label} tanggal ${formatDate(log.recorded_date)}?`)) {
    router.delete(`/monitoring-kwh/penyulang/${log.id}`, { preserveScroll: true, preserveState: true });
  }
};
</script>
