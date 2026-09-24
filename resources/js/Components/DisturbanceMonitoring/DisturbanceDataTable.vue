<template>
  <div class="space-y-6">
    <!-- Main Card Container -->
    <div class="border rounded-2xl overflow-hidden shadow-sm transition-colors duration-300 bg-white border-slate-200 dark:bg-slate-900 dark:border-slate-800">
      <!-- Card Header -->
      <div class="p-4 sm:p-5 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-slate-100 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-900/60">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg border flex items-center justify-center bg-rose-50 border-rose-200 text-rose-600 dark:bg-rose-500/10 dark:border-rose-500/30 dark:text-rose-400">
            <Table class="w-4 h-4" />
          </div>
          <div>
            <h3 class="text-sm font-bold tracking-wide text-slate-900 dark:text-white">
              Tabel Riwayat Gangguan Operasional
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Pencatatan detail jam kejadian, jenis gangguan, dan status penanganan terkini
            </p>
          </div>
        </div>

        <Button v-if="canManage" accent="rose" class="self-start sm:self-auto" @click="openAddModal">
          <Plus class="w-4 h-4" />
          <span>Catat Gangguan Baru</span>
        </Button>
      </div>

      <!-- Desktop Data Table (>= 768px) -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="border-b font-semibold uppercase tracking-wider bg-slate-100/90 text-slate-700 border-slate-200 dark:bg-slate-950/80 dark:text-slate-300 dark:border-slate-800">
              <th class="py-3 px-3 w-20 text-center">Jam</th>
              <th class="py-3 px-3 w-28">Tanggal</th>
              <th class="py-3 px-4 min-w-[160px]">Jenis Gangguan</th>
              <th class="py-3 px-3 w-36 text-center">Status Penanganan</th>
              <th class="py-3 px-4 min-w-[200px]">Keterangan</th>
              <th class="py-3 px-4 min-w-[190px] text-center">Last Modified (Audit Trail)</th>
              <th v-if="canManage" class="py-3 px-3 w-24 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800/60">
            <tr v-for="item in disturbances" :key="item.id" class="transition-colors group hover:bg-slate-50 dark:hover:bg-slate-800/50">
              <!-- Jam -->
              <td class="py-3 px-3 text-center font-bold font-mono">
                <span class="px-2 py-1 rounded border bg-rose-50 text-rose-700 border-rose-200 dark:bg-slate-800 dark:text-rose-400 dark:border-slate-700">
                  {{ item.event_time }}
                </span>
              </td>

              <!-- Tanggal -->
              <td class="py-3 px-3 font-mono font-medium">
                {{ formatDate(item.event_date) }}
              </td>

              <!-- Jenis Gangguan -->
              <td class="py-3 px-4 font-bold">
                {{ item.disturbance_type }}
              </td>

              <!-- Status Penanganan Badge -->
              <td class="py-3 px-3 text-center">
                <span :class="['px-2.5 py-1 rounded-full text-[10px] font-bold border inline-flex items-center gap-1', getStatusBadgeClass(item.status)]">
                  <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                  <span>{{ item.status }}</span>
                </span>
              </td>

              <!-- Keterangan -->
              <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                {{ item.description }}
              </td>

              <!-- Last Modified -->
              <td class="py-3 px-4 text-center font-mono text-[10px]">
                <span
                  v-if="item.last_modified !== '-'"
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded border font-medium bg-slate-100 text-slate-700 border-slate-200 dark:font-normal dark:bg-slate-800/80 dark:text-slate-300 dark:border-slate-700/50"
                >
                  <Clock class="w-3 h-3 text-rose-500 shrink-0" />
                  <span>{{ item.last_modified }}</span>
                </span>
                <span v-else class="text-slate-400 italic text-[11px]">-</span>
              </td>

              <!-- Actions -->
              <td v-if="canManage" class="py-3 px-3 text-center">
                <RowActions
                  :editLabel="`Edit gangguan ${item.disturbance_type} ${formatDate(item.event_date)}`"
                  :deleteLabel="`Hapus gangguan ${item.disturbance_type} ${formatDate(item.event_date)}`"
                  @edit="openEditModal(item)"
                  @delete="deleteEntry(item)"
                />
              </td>
            </tr>

            <tr v-if="disturbances.length === 0">
              <td :colspan="canManage ? 7 : 6" class="py-8 text-center text-slate-400 text-xs italic">
                Belum ada catatan gangguan operasional untuk bulan ini.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Card List (< 768px) -->
      <div class="block md:hidden p-4 space-y-3">
        <div
          v-for="item in disturbances"
          :key="'m-dist-' + item.id"
          class="border rounded-xl p-4 space-y-3 shadow-sm bg-slate-50 border-slate-200 dark:bg-slate-950 dark:border-slate-800"
        >
          <div class="flex items-center justify-between gap-3 border-b pb-2 border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2">
              <span class="px-2 py-0.5 rounded font-mono text-xs font-bold bg-rose-500/10 text-rose-700 border border-rose-500/30 dark:text-rose-400">
                {{ item.event_time }}
              </span>
              <span class="font-bold text-xs">{{ formatDate(item.event_date) }}</span>
            </div>
            <RowActions
              v-if="canManage"
              size="lg"
              :editLabel="`Edit gangguan ${item.disturbance_type} ${formatDate(item.event_date)}`"
              :deleteLabel="`Hapus gangguan ${item.disturbance_type} ${formatDate(item.event_date)}`"
              @edit="openEditModal(item)"
              @delete="deleteEntry(item)"
            />
          </div>

          <div class="space-y-1.5 text-xs">
            <div class="flex items-center justify-between gap-2">
              <span class="font-bold text-sm">{{ item.disturbance_type }}</span>
              <span :class="['px-2 py-0.5 rounded-full text-[10px] font-bold border', getStatusBadgeClass(item.status)]">
                {{ item.status }}
              </span>
            </div>
            <p class="text-slate-600 dark:text-slate-400 text-[11px]">{{ item.description }}</p>
          </div>

          <div class="text-[10px] font-mono p-2 rounded border text-slate-500 bg-white border-slate-200 dark:text-slate-400 dark:bg-slate-900/40 dark:border-slate-800">
            Last Modified: {{ item.last_modified }}
          </div>
        </div>

        <p v-if="disturbances.length === 0" class="py-6 text-center text-slate-400 text-xs italic">
          Belum ada catatan gangguan operasional untuk bulan ini.
        </p>
      </div>
    </div>

    <!-- Modal Form Add/Edit -->
    <Modal :show="showModal" max-width="md" :closeable="!form.processing" @close="closeModal">
      <template #title>
        <AlertTriangle class="w-5 h-5 text-rose-500" />
        <span>{{ isEditing ? 'Edit Catatan Gangguan' : 'Catat Gangguan Baru' }}</span>
      </template>

      <form id="disturbance-form" @submit.prevent="submitForm" class="space-y-4 text-xs">
        <div class="grid grid-cols-2 gap-3">
          <FormField label="Tanggal Kejadian" type="date" accent="rose" v-model="form.event_date" :error="form.errors.event_date" required />
          <FormField label="Jam Kejadian" type="time" accent="rose" v-model="form.event_time" :error="form.errors.event_time" required />
        </div>

        <FormField label="Jenis Gangguan" accent="rose" :error="form.errors.disturbance_type" required v-slot="{ id, inputClass, describedBy }">
          <select :id="id" v-model="form.disturbance_type" required :class="[inputClass, 'cursor-pointer font-semibold']" :aria-describedby="describedBy">
            <option v-for="type in disturbanceTypes" :key="type" :value="type">{{ type }}</option>
          </select>
        </FormField>

        <FormField label="Status Penanganan" accent="rose" :error="form.errors.status" required v-slot="{ id, inputClass, describedBy }">
          <select :id="id" v-model="form.status" required :class="[inputClass, 'cursor-pointer font-semibold']" :aria-describedby="describedBy">
            <option v-for="opt in statusOptions" :key="opt" :value="opt">{{ opt }}</option>
          </select>
        </FormField>

        <FormField label="Keterangan / Penyebab" accent="rose" :error="form.errors.description" v-slot="{ id, inputClass, describedBy }">
          <textarea
            :id="id"
            v-model="form.description"
            rows="3"
            placeholder="Catatan kronologi atau penanganan gangguan..."
            :class="inputClass"
            :aria-describedby="describedBy"
          ></textarea>
        </FormField>

        <FormField label="Nama Operator" accent="rose" v-model="form.operator_name" :error="form.errors.operator_name" placeholder="Kosongkan untuk memakai nama akun" />
      </form>

      <template #footer>
        <Button variant="secondary" class="flex-1" :disabled="form.processing" @click="closeModal">Batal</Button>
        <Button type="submit" form="disturbance-form" accent="rose" class="flex-1" :loading="form.processing">Simpan</Button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { Table, Plus, Clock, AlertTriangle } from 'lucide-vue-next';
import Modal from '@/Components/Shared/Modal.vue';
import FormField from '@/Components/Shared/FormField.vue';
import Button from '@/Components/Shared/Button.vue';
import RowActions from '@/Components/Shared/RowActions.vue';
import { usePermission } from '@/composables/usePermission';
import { todayLocal } from '@/utils/date';
import { formatDate } from '@/utils/format';

const props = defineProps({
  disturbances: {
    type: Array,
    required: true,
  },
  disturbanceTypes: {
    type: Array,
    required: true,
  },
  statusOptions: {
    type: Array,
    required: true,
  },
});

const { can } = usePermission();
const canManage = computed(() => can('monitoring_gangguan.manage'));

const showModal = ref(false);
const isEditing = ref(false);

const currentTime = () => {
  const now = new Date();
  return `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
};

const emptyForm = () => ({
  id: null,
  event_date: todayLocal(),
  event_time: currentTime(),
  disturbance_type: props.disturbanceTypes[0] || 'Trip Feeder',
  status: 'Dalam Penanganan',
  description: '',
  operator_name: '',
});

const form = useForm(emptyForm());

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'Selesai':
      return 'bg-emerald-500/10 text-emerald-700 border-emerald-500/30 dark:text-emerald-400';
    case 'Dalam Penanganan':
      return 'bg-amber-500/10 text-amber-700 border-amber-500/30 dark:text-amber-400';
    case 'Investigasi':
      return 'bg-blue-500/10 text-blue-700 border-blue-500/30 dark:text-blue-400';
    default:
      return 'bg-slate-500/10 text-slate-600 border-slate-500/30 dark:text-slate-400';
  }
};

const openModal = (data, editing) => {
  isEditing.value = editing;
  Object.assign(form, emptyForm(), data);
  form.clearErrors();
  showModal.value = true;
};

const openAddModal = () => openModal({}, false);

const openEditModal = (item) =>
  openModal(
    {
      id: item.id,
      event_date: item.event_date,
      event_time: item.event_time,
      disturbance_type: item.disturbance_type,
      status: item.status,
      description: item.description !== '-' ? item.description : '',
      operator_name: item.operator_name,
    },
    true
  );

const closeModal = () => {
  showModal.value = false;
};

const submitForm = () => {
  form.post('/monitoring-gangguan', {
    preserveScroll: true,
    preserveState: true,
    onSuccess: closeModal,
  });
};

const deleteEntry = (item) => {
  if (confirm(`Apakah Anda yakin ingin menghapus catatan gangguan ${item.disturbance_type} tanggal ${formatDate(item.event_date)}?`)) {
    router.delete(`/monitoring-gangguan/${item.id}`, { preserveScroll: true, preserveState: true });
  }
};
</script>
