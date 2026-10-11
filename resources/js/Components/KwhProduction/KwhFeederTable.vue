<template>
  <div class="space-y-6">
    <div class="border rounded-lg overflow-hidden transition-colors bg-surface border-line">
      <!-- Card Header -->
      <div class="p-4 sm:p-5 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-line">
        <div class="flex items-center gap-3">
          <div>
            <h2 class="text-section font-semibold text-fg">
              kWh per penyulang
            </h2>
            <p class="text-caption text-fg-muted">
              Stand akhir PM 800 / EDMI MK10 (EX/IM), kWh PS, kWh Digital dan PS Total per penyulang
            </p>
          </div>
        </div>

        <Button v-if="canInput" class="self-start sm:self-auto" @click="openAddModal">
          <Plus class="w-4 h-4" />
          <span>Tambah Data Penyulang</span>
        </Button>
      </div>

      <!-- Desktop Data Table (>= 768px) -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-body border-collapse">
          <thead>
            <tr class="border-b text-label font-medium text-center bg-surface-muted text-fg-muted border-line">
              <th class="py-3 px-4 text-left">Tanggal</th>
              <th class="py-3 px-3 text-left">Penyulang</th>
              <th class="py-3 px-3 min-w-[170px]">Stand Akhir kWh Produksi</th>
              <th class="py-3 px-3">kWh PS</th>
              <th class="py-3 px-3">kWh Digital</th>
              <th class="py-3 px-3 text-fg-muted">PS Total</th>
              <th class="py-3 px-4 min-w-[190px]">Terakhir diubah</th>
              <th v-if="canInput" class="py-3 px-3 w-24">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-line">
            <tr v-for="log in logs" :key="log.id" class="transition-colors hover:bg-surface-muted">
              <td class="py-3 px-4 tabular-nums">
                {{ formatDate(log.recorded_date) }}
              </td>
              <td class="py-3 px-3 font-medium">{{ log.feeder_label }}</td>
              <td class="py-3 px-3">
                <div class="flex flex-col gap-0.5 tabular-nums text-caption">
                  <span v-for="choice in filledStands(log)" :key="choice.key">
                    <span class="text-fg-muted">{{ choice.label }}:</span> <span class="text-fg">{{ formatNumber(log[choice.key]) }}</span>
                  </span>
                  <span v-if="filledStands(log).length === 0" class="text-fg-muted">-</span>
                </div>
              </td>
              <td class="py-3 px-3 text-right tabular-nums">{{ formatNumber(log.kwh_ps) }}</td>
              <td class="py-3 px-3 text-right tabular-nums">{{ formatNumber(log.kwh_digital) }}</td>
              <td class="py-3 px-3 text-right tabular-nums font-semibold text-fg">
                {{ formatNumber(log.ps_total) }}
              </td>
              <td class="py-3 px-4 text-center tabular-nums text-caption">
                {{ log.last_modified !== '-' ? log.last_modified : '—' }}
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
              <td :colspan="canInput ? 8 : 7" class="py-8 text-center text-fg-muted text-caption">
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
          class="border rounded-lg p-4 space-y-3 bg-surface border-line"
        >
          <div class="flex items-center justify-between gap-3 border-b pb-2 border-line">
            <div>
              <span class="font-medium tabular-nums text-body text-fg">{{ formatDate(log.recorded_date) }}</span>
              <span class="ml-2 text-body text-fg-muted">{{ log.feeder_label }}</span>
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

          <div class="grid grid-cols-2 gap-2 text-caption">
            <div v-for="choice in filledStands(log)" :key="choice.key">
              <span class="text-caption text-fg-muted block">{{ choice.label }}</span>
              <span class="tabular-nums text-fg">{{ formatNumber(log[choice.key]) }}</span>
            </div>
            <div>
              <span class="text-caption text-fg-muted block">kWh PS</span>
              <span class="tabular-nums text-fg">{{ formatNumber(log.kwh_ps) }}</span>
            </div>
            <div>
              <span class="text-caption text-fg-muted block">kWh Digital</span>
              <span class="tabular-nums text-fg">{{ formatNumber(log.kwh_digital) }}</span>
            </div>
            <div>
              <span class="text-caption text-fg-muted block">PS Total</span>
              <span class="tabular-nums font-semibold text-fg">{{ formatNumber(log.ps_total) }}</span>
            </div>
          </div>

          <div class="text-caption tabular-nums text-fg-muted">
            Terakhir diubah: {{ log.last_modified }}
          </div>
        </div>

        <p v-if="logs.length === 0" class="py-6 text-center text-fg-muted text-caption">
          Belum ada data kWh Penyulang untuk bulan ini.
        </p>
      </div>
    </div>

    <!-- Modal Form Add/Edit -->
    <Modal :show="showModal" max-width="md" :closeable="!form.processing" @close="closeModal">
      <template #title>
        <span>{{ isEditing ? 'Edit Data kWh Penyulang' : 'Tambah Data kWh Penyulang' }}</span>
      </template>

      <form id="kwh-feeder-form" @submit.prevent="submitForm" class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
          <FormField label="Tanggal" type="date" v-model="form.recorded_date" :error="form.errors.recorded_date" :disabled="isEditing" required />
          <FormField label="Penyulang" :error="form.errors.feeder" required v-slot="{ id, inputClass, describedBy }">
            <select :id="id" v-model="form.feeder" required :disabled="isEditing" :class="inputClass" :aria-describedby="describedBy">
              <option value="" disabled>— Pilih —</option>
              <option v-for="feeder in feeders" :key="feeder.key" :value="feeder.key">{{ feeder.label }}</option>
            </select>
          </FormField>
        </div>

        <div class="space-y-1">
          <ChoiceValueInput label="Stand Akhir kWh Produksi" :options="standChoices" :values="form" />
          <p v-for="message in standErrors" :key="message" class="text-caption text-crit">{{ message }}</p>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <FormField label="kWh PS" type="number" step="0.01" min="0" placeholder="0.00" v-model="form.kwh_ps" :error="form.errors.kwh_ps" />
          <FormField label="kWh Digital" type="number" step="0.01" min="0" placeholder="0.00" v-model="form.kwh_digital" :error="form.errors.kwh_digital" />
        </div>

        <div class="p-3 rounded-lg bg-surface-muted border border-line flex items-center justify-between text-fg tabular-nums">
          <span>PS Total (Auto):</span>
          <span class="text-body">{{ formatNumber(calculatedPsTotal) }} kWh</span>
        </div>

        <FormField label="Nama Operator" v-model="form.operator_name" :error="form.errors.operator_name" placeholder="Kosongkan untuk memakai nama akun" />
      </form>

      <template #footer>
        <Button variant="secondary" class="flex-1" :disabled="form.processing" @click="closeModal">Batal</Button>
        <Button type="submit" form="kwh-feeder-form" class="flex-1" :loading="form.processing">Simpan</Button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
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
  selectedMonth: { type: String, default: '' },
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
  const targetMonth = form.recorded_date?.slice(0, 7);
  form.post('/monitoring-kwh/penyulang', {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      closeModal();
      if (targetMonth && props.selectedMonth && targetMonth !== props.selectedMonth) {
        router.visit(`/monitoring-kwh?month=${targetMonth}&tab=penyulang`);
      }
    },
  });
};

const deleteEntry = (log) => {
  if (confirm(`Hapus data ${log.feeder_label} tanggal ${formatDate(log.recorded_date)}?`)) {
    router.delete(`/monitoring-kwh/penyulang/${log.id}`, { preserveScroll: true, preserveState: true });
  }
};
</script>
