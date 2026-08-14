<template>
  <div class="space-y-6">
    <!-- Main Card Container -->
    <div
      :class="[
        'border rounded-2xl overflow-hidden shadow-sm transition-colors duration-300',
        isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'
      ]"
    >
      <!-- Card Header -->
      <div
        :class="[
          'p-4 sm:p-5 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-3',
          isDarkMode ? 'border-slate-800 bg-slate-900/60' : 'border-slate-100 bg-slate-50/80'
        ]"
      >
        <div class="flex items-center gap-3">
          <div
            :class="[
              'w-8 h-8 rounded-lg border flex items-center justify-center',
              isDarkMode ? 'bg-rose-500/10 border-rose-500/30 text-rose-400' : 'bg-rose-50 border-rose-200 text-rose-600'
            ]"
          >
            <Table class="w-4 h-4" />
          </div>
          <div>
            <h3 :class="['text-sm font-bold tracking-wide', isDarkMode ? 'text-white' : 'text-slate-900']">
              Tabel Riwayat Gangguan Operasional
            </h3>
            <p :class="['text-xs', isDarkMode ? 'text-slate-400' : 'text-slate-500']">
              Pencatatan detail jam kejadian, jenis gangguan, dan status penanganan terkini
            </p>
          </div>
        </div>

        <!-- Add Button -->
        <button
          @click="openAddModal"
          class="py-2 px-4 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-md transition-all flex items-center justify-center gap-1.5 self-start sm:self-auto"
        >
          <Plus class="w-4 h-4" />
          <span>Catat Gangguan Baru</span>
        </button>
      </div>

      <!-- Desktop Data Table (>= 768px) -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr
              :class="[
                'border-b font-semibold uppercase tracking-wider',
                isDarkMode ? 'bg-slate-950/80 text-slate-300 border-slate-800' : 'bg-slate-100/90 text-slate-700 border-slate-200'
              ]"
            >
              <th class="py-3 px-3 w-20 text-center">Jam</th>
              <th class="py-3 px-3 w-28">Tanggal</th>
              <th class="py-3 px-4 min-w-[160px]">Jenis Gangguan</th>
              <th class="py-3 px-3 w-36 text-center">Status Penanganan</th>
              <th class="py-3 px-4 min-w-[200px]">Keterangan</th>
              <th class="py-3 px-4 min-w-[190px] text-center">Last Modified (Audit Trail)</th>
              <th class="py-3 px-3 w-24 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody :class="['divide-y', isDarkMode ? 'divide-slate-800/60' : 'divide-slate-200/60']">
            <tr
              v-for="item in disturbances"
              :key="item.id"
              :class="[
                'transition-colors group',
                isDarkMode ? 'hover:bg-slate-850/50' : 'hover:bg-slate-50'
              ]"
            >
              <!-- Jam -->
              <td class="py-3 px-3 text-center font-bold font-mono">
                <span :class="['px-2 py-1 rounded border', isDarkMode ? 'bg-slate-800 text-rose-400 border-slate-700' : 'bg-rose-50 text-rose-700 border-rose-200']">
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
                <span
                  :class="[
                    'px-2.5 py-1 rounded-full text-[10px] font-bold border inline-flex items-center gap-1',
                    getStatusBadgeClass(item.status)
                  ]"
                >
                  <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                  <span>{{ item.status }}</span>
                </span>
              </td>

              <!-- Keterangan -->
              <td class="py-3 px-4 text-slate-400 dark:text-slate-300">
                {{ item.description }}
              </td>

              <!-- Last Modified -->
              <td class="py-3 px-4 text-center font-mono text-[10px]">
                <span
                  v-if="item.last_modified !== '-'"
                  :class="[
                    'inline-flex items-center gap-1 px-2.5 py-1 rounded border',
                    isDarkMode ? 'bg-slate-800/80 text-slate-300 border-slate-700/50' : 'bg-slate-100 text-slate-700 border-slate-200 font-medium'
                  ]"
                >
                  <Clock class="w-3 h-3 text-rose-500 shrink-0" />
                  <span>{{ item.last_modified }}</span>
                </span>
                <span v-else class="text-slate-400 italic text-[11px]">-</span>
              </td>

              <!-- Actions -->
              <td class="py-3 px-3 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <button
                    @click="openEditModal(item)"
                    :class="[
                      'p-1.5 rounded-lg border transition-all',
                      isDarkMode ? 'bg-slate-800 border-slate-700 text-cyan-400 hover:bg-slate-700' : 'bg-slate-100 border-slate-200 text-cyan-700 hover:bg-slate-200'
                    ]"
                    title="Edit Data"
                  >
                    <Edit3 class="w-3.5 h-3.5" />
                  </button>
                  <button
                    @click="deleteEntry(item)"
                    :class="[
                      'p-1.5 rounded-lg border transition-all',
                      isDarkMode ? 'bg-slate-800 border-slate-700 text-rose-400 hover:bg-slate-700' : 'bg-slate-100 border-slate-200 text-rose-700 hover:bg-slate-200'
                    ]"
                    title="Hapus Data"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="disturbances.length === 0">
              <td colspan="7" class="py-8 text-center text-slate-400 text-xs italic">
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
          :class="[
            'border rounded-xl p-4 space-y-3 shadow-sm',
            isDarkMode ? 'bg-slate-950 border-slate-800' : 'bg-slate-50 border-slate-200'
          ]"
        >
          <div class="flex items-center justify-between border-b pb-2 dark:border-slate-800 border-slate-200">
            <div class="flex items-center gap-2">
              <span class="px-2 py-0.5 rounded font-mono text-xs font-bold bg-rose-500/10 text-rose-500 border border-rose-500/30">
                {{ item.event_time }}
              </span>
              <span class="font-bold text-xs">{{ formatDate(item.event_date) }}</span>
            </div>
            <div class="flex items-center gap-1">
              <button @click="openEditModal(item)" class="p-1 rounded bg-cyan-500/10 text-cyan-500">
                <Edit3 class="w-4 h-4" />
              </button>
              <button @click="deleteEntry(item)" class="p-1 rounded bg-rose-500/10 text-rose-500">
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div class="space-y-1.5 text-xs">
            <div class="flex items-center justify-between">
              <span class="font-bold text-sm">{{ item.disturbance_type }}</span>
              <span :class="['px-2 py-0.5 rounded-full text-[10px] font-bold border', getStatusBadgeClass(item.status)]">
                {{ item.status }}
              </span>
            </div>
            <p class="text-slate-400 text-[11px]">{{ item.description }}</p>
          </div>

          <div class="text-[10px] font-mono text-slate-400 bg-slate-900/40 p-2 rounded border border-slate-800">
            Last Modified: {{ item.last_modified }}
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Form Add/Edit -->
    <Transition name="fade">
      <div v-if="showModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div
          :class="[
            'w-full max-w-md border rounded-2xl shadow-2xl p-6 space-y-5 transition-colors',
            isDarkMode ? 'bg-slate-900 border-slate-800 text-white' : 'bg-white border-slate-200 text-slate-900'
          ]"
        >
          <div class="flex items-center justify-between border-b pb-3 dark:border-slate-800 border-slate-100">
            <h3 class="font-bold text-base flex items-center gap-2">
              <AlertTriangle class="w-5 h-5 text-rose-500" />
              <span>{{ isEditing ? 'Edit Catatan Gangguan' : 'Catat Gangguan Baru' }}</span>
            </h3>
            <button @click="closeModal" class="text-slate-400 hover:text-white">
              <X class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="submitForm" class="space-y-4 text-xs">
            <!-- Tanggal & Jam -->
            <div class="grid grid-cols-2 gap-3">
              <div class="space-y-1">
                <label class="font-bold text-slate-400">Tanggal Kejadian</label>
                <input
                  type="date"
                  v-model="form.event_date"
                  required
                  :class="[
                    'w-full p-2.5 rounded-xl border font-mono focus:outline-none focus:ring-1 focus:ring-rose-500',
                    isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                  ]"
                />
              </div>

              <div class="space-y-1">
                <label class="font-bold text-slate-400">Jam Kejadian</label>
                <input
                  type="time"
                  v-model="form.event_time"
                  required
                  :class="[
                    'w-full p-2.5 rounded-xl border font-mono focus:outline-none focus:ring-1 focus:ring-rose-500',
                    isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                  ]"
                />
              </div>
            </div>

            <!-- Jenis Gangguan -->
            <div class="space-y-1">
              <label class="font-bold text-slate-400">Jenis Gangguan</label>
              <select
                v-model="form.disturbance_type"
                required
                :class="[
                  'w-full p-2.5 rounded-xl border focus:outline-none focus:ring-1 focus:ring-rose-500 cursor-pointer font-semibold',
                  isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                ]"
              >
                <option v-for="type in disturbanceTypes" :key="type" :value="type">
                  {{ type }}
                </option>
              </select>
            </div>

            <!-- Status Penanganan -->
            <div class="space-y-1">
              <label class="font-bold text-slate-400">Status Penanganan</label>
              <select
                v-model="form.status"
                required
                :class="[
                  'w-full p-2.5 rounded-xl border focus:outline-none focus:ring-1 focus:ring-rose-500 cursor-pointer font-semibold',
                  isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                ]"
              >
                <option v-for="opt in statusOptions" :key="opt" :value="opt">
                  {{ opt }}
                </option>
              </select>
            </div>

            <!-- Keterangan / Deskripsi -->
            <div class="space-y-1">
              <label class="font-bold text-slate-400">Keterangan / Penyebab</label>
              <textarea
                v-model="form.description"
                rows="3"
                placeholder="Catatan kronologi atau penanganan gangguan..."
                :class="[
                  'w-full p-2.5 rounded-xl border focus:outline-none focus:ring-1 focus:ring-rose-500',
                  isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                ]"
              ></textarea>
            </div>

            <!-- Operator Name -->
            <div class="space-y-1">
              <label class="font-bold text-slate-400">Nama Operator</label>
              <input
                type="text"
                v-model="form.operator_name"
                placeholder="Nama Operator"
                :class="[
                  'w-full p-2.5 rounded-xl border focus:outline-none focus:ring-1 focus:ring-rose-500',
                  isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                ]"
              />
            </div>

            <!-- Submit Buttons -->
            <div class="pt-2 flex items-center gap-3">
              <button
                type="button"
                @click="closeModal"
                class="flex-1 py-2.5 rounded-xl border border-slate-700 text-slate-400 font-bold hover:bg-slate-800"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="isSubmitting"
                class="flex-1 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold shadow-md flex items-center justify-center gap-2"
              >
                <Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin" />
                <span>Simpan</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import { Table, Plus, Clock, Edit3, Trash2, AlertTriangle, X, Loader2 } from 'lucide-vue-next';

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
  isDarkMode: {
    type: Boolean,
    default: false,
  },
});

const showModal = ref(false);
const isEditing = ref(false);
const isSubmitting = ref(false);

const form = reactive({
  id: null,
  event_date: new Date().toISOString().split('T')[0],
  event_time: '08:00',
  disturbance_type: props.disturbanceTypes[0] || 'Trip Feeder',
  status: 'Dalam Penanganan',
  description: '',
  operator_name: '',
});

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const parts = dateStr.split('-');
  return `${parts[2]}/${parts[1]}/${parts[0]}`;
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'Selesai':
      return 'bg-emerald-500/10 text-emerald-500 border-emerald-500/30';
    case 'Dalam Penanganan':
      return 'bg-amber-500/10 text-amber-500 border-amber-500/30';
    case 'Investigasi':
      return 'bg-blue-500/10 text-blue-500 border-blue-500/30';
    default:
      return 'bg-slate-500/10 text-slate-500 border-slate-500/30';
  }
};

const openAddModal = () => {
  isEditing.value = false;
  form.id = null;
  form.event_date = new Date().toISOString().split('T')[0];

  const now = new Date();
  form.event_time = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
  form.disturbance_type = props.disturbanceTypes[0] || 'Trip Feeder';
  form.status = 'Dalam Penanganan';
  form.description = '';
  form.operator_name = '';
  showModal.value = true;
};

const openEditModal = (item) => {
  isEditing.value = true;
  form.id = item.id;
  form.event_date = item.event_date;
  form.event_time = item.event_time;
  form.disturbance_type = item.disturbance_type;
  form.status = item.status;
  form.description = item.description !== '-' ? item.description : '';
  form.operator_name = item.operator_name;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const submitForm = () => {
  isSubmitting.value = true;
  router.post(
    '/monitoring-gangguan',
    { ...form },
    {
      preserveScroll: true,
      onFinish: () => {
        isSubmitting.value = false;
        showModal.value = false;
      },
    }
  );
};

const deleteEntry = (item) => {
  if (confirm(`Apakah Anda yakin ingin menghapus catatan gangguan ${item.disturbance_type} tanggal ${formatDate(item.event_date)}?`)) {
    router.delete(`/monitoring-gangguan/${item.id}`, { preserveScroll: true });
  }
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
