<template>
  <div class="space-y-6">
    <!-- Main Card Container -->
    <div class="border rounded-lg overflow-hidden transition-colors bg-surface border-line">
      <!-- Card Header -->
      <div class="p-4 sm:p-5 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-line">
        <div class="flex items-center gap-3">
          <div>
            <h2 class="text-section font-semibold text-fg">
              Riwayat gangguan
            </h2>
            <p class="text-caption text-fg-muted">
              Jam kejadian, jenis, dan status penanganan
            </p>
          </div>
        </div>

        <Button v-if="canManage" class="self-start sm:self-auto" @click="openAddModal">
          <Plus class="w-4 h-4" />
          <span>Catat Gangguan Baru</span>
        </Button>
      </div>

      <!-- Desktop Data Table (>= 768px) -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-body border-collapse">
          <thead>
            <tr class="border-b text-label font-medium bg-surface-muted text-fg-muted border-line">
              <th class="py-3 px-3 w-20 text-center">Jam</th>
              <th class="py-3 px-3 w-28">Tanggal</th>
              <th class="py-3 px-4 min-w-[160px]">Jenis Gangguan</th>
              <th class="py-3 px-3 w-36 text-center">Status Penanganan</th>
              <th class="py-3 px-4 min-w-[200px]">Keterangan</th>
              <th class="py-3 px-4 min-w-[190px] text-center">Terakhir diubah</th>
              <th v-if="canManage" class="py-3 px-3 w-24 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-line">
            <tr v-for="item in disturbances" :key="item.id" class="transition-colors group hover:bg-surface-muted">
              <!-- Jam -->
              <td class="py-3 px-3 text-center tabular-nums font-medium">
                {{ item.event_time }}
              </td>

              <!-- Tanggal -->
              <td class="py-3 px-3 tabular-nums">
                {{ formatDate(item.event_date) }}
              </td>

              <!-- Jenis Gangguan -->
              <td class="py-3 px-4 font-medium">
                {{ item.disturbance_type }}
              </td>

              <!-- Status Penanganan Badge -->
              <td class="py-3 px-3 text-center">
                <StatusBadge :status="disturbanceStatus(item.status)" :label="item.status" dot />
              </td>

              <!-- Keterangan -->
              <td class="py-3 px-4 text-fg-muted">
                {{ item.description }}
              </td>

              <!-- Terakhir diubah -->
              <td class="py-3 px-4 text-center tabular-nums text-caption">
                {{ item.last_modified !== '-' ? item.last_modified : '—' }}
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
              <td :colspan="canManage ? 7 : 6" class="py-8 text-center text-fg-muted text-caption">
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
          class="border rounded-lg p-4 space-y-3 bg-surface border-line"
        >
          <div class="flex items-center justify-between gap-3 border-b pb-2 border-line">
            <div class="flex items-center gap-2">
              <span class="tabular-nums text-body font-medium text-fg">{{ item.event_time }}</span>
              <span class="tabular-nums text-body text-fg-muted">{{ formatDate(item.event_date) }}</span>
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

          <div class="space-y-1.5 text-caption">
            <div class="flex items-center justify-between gap-2">
              <span class="font-medium text-body text-fg">{{ item.disturbance_type }}</span>
              <StatusBadge :status="disturbanceStatus(item.status)" :label="item.status" />
            </div>
            <p class="text-fg-muted text-caption">{{ item.description }}</p>
          </div>

          <div class="text-caption tabular-nums text-fg-muted">
            Terakhir diubah: {{ item.last_modified !== '-' ? item.last_modified : '—' }}
          </div>
        </div>

        <p v-if="disturbances.length === 0" class="py-6 text-center text-fg-muted text-caption">
          Belum ada catatan gangguan operasional untuk bulan ini.
        </p>
      </div>
    </div>

    <!-- Modal Form Add/Edit -->
    <Modal :show="showModal" max-width="md" :closeable="!form.processing" @close="closeModal">
      <template #title>
        <span>{{ isEditing ? 'Edit Catatan Gangguan' : 'Catat Gangguan Baru' }}</span>
      </template>

      <form id="disturbance-form" @submit.prevent="submitForm" class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
          <FormField label="Tanggal Kejadian" type="date" v-model="form.event_date" :error="form.errors.event_date" required />
          <FormField label="Jam Kejadian" type="time" v-model="form.event_time" :error="form.errors.event_time" required />
        </div>

        <FormField label="Jenis Gangguan" :error="form.errors.disturbance_type" required v-slot="{ id, inputClass, describedBy }">
          <select :id="id" v-model="form.disturbance_type" required :class="[inputClass, 'cursor-pointer']" :aria-describedby="describedBy">
            <option v-for="type in disturbanceTypes" :key="type" :value="type">{{ type }}</option>
          </select>
        </FormField>

        <FormField label="Status Penanganan" :error="form.errors.status" required v-slot="{ id, inputClass, describedBy }">
          <select :id="id" v-model="form.status" required :class="[inputClass, 'cursor-pointer']" :aria-describedby="describedBy">
            <option v-for="opt in statusOptions" :key="opt" :value="opt">{{ opt }}</option>
          </select>
        </FormField>

        <FormField label="Keterangan / Penyebab" :error="form.errors.description" v-slot="{ id, inputClass, describedBy }">
          <textarea
            :id="id"
            v-model="form.description"
            rows="3"
            placeholder="Catatan kronologi atau penanganan gangguan..."
            :class="inputClass"
            :aria-describedby="describedBy"
          ></textarea>
        </FormField>

        <FormField label="Nama Operator" v-model="form.operator_name" :error="form.errors.operator_name" placeholder="Kosongkan untuk memakai nama akun" />
      </form>

      <template #footer>
        <Button variant="secondary" class="flex-1" :disabled="form.processing" @click="closeModal">Batal</Button>
        <Button type="submit" form="disturbance-form" class="flex-1" :loading="form.processing">Simpan</Button>
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
import StatusBadge from '@/Components/Shared/StatusBadge.vue';
import { disturbanceStatus } from '@/utils/status';
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
  selectedMonth: {
    type: String,
    default: '',
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
  const targetMonth = form.event_date?.slice(0, 7);
  form.post('/monitoring-gangguan', {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      closeModal();
      if (targetMonth && props.selectedMonth && targetMonth !== props.selectedMonth) {
        router.visit(`/monitoring-gangguan?month=${targetMonth}`);
      }
    },
  });
};

const deleteEntry = (item) => {
  if (confirm(`Apakah Anda yakin ingin menghapus catatan gangguan ${item.disturbance_type} tanggal ${formatDate(item.event_date)}?`)) {
    router.delete(`/monitoring-gangguan/${item.id}`, { preserveScroll: true, preserveState: true });
  }
};
</script>
