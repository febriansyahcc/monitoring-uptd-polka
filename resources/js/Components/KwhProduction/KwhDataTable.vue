<template>
  <div class="space-y-6">
    <!-- Desktop & Mobile Container Card -->
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
              isDarkMode ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400' : 'bg-emerald-50 border-emerald-200 text-emerald-600'
            ]"
          >
            <Table class="w-4 h-4" />
          </div>
          <div>
            <h3 :class="['text-sm font-bold tracking-wide', isDarkMode ? 'text-white' : 'text-slate-900']">
              Tabel Pencatatan kWh Produksi
            </h3>
            <p :class="['text-xs', isDarkMode ? 'text-slate-400' : 'text-slate-500']">
              Riwayat data produksi energi listrik harian
            </p>
          </div>
        </div>

        <!-- Add New Entry Button -->
        <button
          @click="openAddModal"
          class="py-2 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition-all flex items-center justify-center gap-1.5 self-start sm:self-auto"
        >
          <Plus class="w-4 h-4" />
          <span>Tambah Data Produksi</span>
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
              <th class="py-3 px-4 w-32">Tanggal</th>
              <th class="py-3 px-3 text-right">kWh PS</th>
              <th class="py-3 px-3 text-right">kWh Digital (1)</th>
              <th class="py-3 px-3 text-right">kWh Digital (2)</th>
              <th class="py-3 px-3 text-right text-emerald-600 dark:text-emerald-400 font-bold">kWh Total</th>
              <th class="py-3 px-4 min-w-[190px] text-center">Last Modified (Audit Trail)</th>
              <th class="py-3 px-3 w-28 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody :class="['divide-y', isDarkMode ? 'divide-slate-800/60' : 'divide-slate-200/60']">
            <tr
              v-for="log in logs"
              :key="log.id"
              :class="[
                'transition-colors group',
                isDarkMode ? 'hover:bg-slate-850/50' : 'hover:bg-slate-50'
              ]"
            >
              <!-- Tanggal -->
              <td class="py-3 px-4 font-bold font-mono text-xs">
                <span :class="['px-2.5 py-1 rounded border', isDarkMode ? 'bg-slate-800 text-slate-200 border-slate-700' : 'bg-slate-100 text-slate-800 border-slate-200']">
                  {{ formatDate(log.recorded_date) }}
                </span>
              </td>

              <!-- kWh PS -->
              <td class="py-3 px-3 text-right font-mono font-medium">
                {{ log.kwh_ps.toLocaleString('id-ID', { minimumFractionDigits: 2 }) }}
              </td>

              <!-- kWh Digital (1) -->
              <td class="py-3 px-3 text-right font-mono font-medium">
                {{ log.kwh_digital_1.toLocaleString('id-ID', { minimumFractionDigits: 2 }) }}
              </td>

              <!-- kWh Digital (2) -->
              <td class="py-3 px-3 text-right font-mono font-medium">
                {{ log.kwh_digital_2.toLocaleString('id-ID', { minimumFractionDigits: 2 }) }}
              </td>

              <!-- kWh Total (Calculated) -->
              <td class="py-3 px-3 text-right font-mono font-extrabold text-emerald-600 dark:text-emerald-400 text-xs">
                {{ log.kwh_total.toLocaleString('id-ID', { minimumFractionDigits: 2 }) }}
              </td>

              <!-- Auto Audit Trail -->
              <td class="py-3 px-4 text-center font-mono text-[10px]">
                <span
                  v-if="log.last_modified !== '-'"
                  :class="[
                    'inline-flex items-center gap-1 px-2.5 py-1 rounded border',
                    isDarkMode ? 'bg-slate-800/80 text-slate-300 border-slate-700/50' : 'bg-slate-100 text-slate-700 border-slate-200 font-medium'
                  ]"
                >
                  <Clock class="w-3 h-3 text-emerald-500 shrink-0" />
                  <span>{{ log.last_modified }}</span>
                </span>
                <span v-else class="text-slate-400 italic text-[11px]">-</span>
              </td>

              <!-- Actions -->
              <td class="py-3 px-3 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <button
                    @click="openEditModal(log)"
                    :class="[
                      'p-1.5 rounded-lg border transition-all',
                      isDarkMode ? 'bg-slate-800 border-slate-700 text-cyan-400 hover:bg-slate-700' : 'bg-slate-100 border-slate-200 text-cyan-700 hover:bg-slate-200'
                    ]"
                    title="Edit Data"
                  >
                    <Edit3 class="w-3.5 h-3.5" />
                  </button>
                  <button
                    @click="deleteEntry(log)"
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

            <tr v-if="logs.length === 0">
              <td colspan="7" class="py-8 text-center text-slate-400 text-xs italic">
                Belum ada data pencatatan kWh Produksi untuk bulan ini.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Card List (< 768px) -->
      <div class="block md:hidden p-4 space-y-3">
        <div
          v-for="log in logs"
          :key="'m-log-' + log.id"
          :class="[
            'border rounded-xl p-4 space-y-3 shadow-sm',
            isDarkMode ? 'bg-slate-950 border-slate-800' : 'bg-slate-50 border-slate-200'
          ]"
        >
          <div class="flex items-center justify-between border-b pb-2 dark:border-slate-800 border-slate-200">
            <span class="font-bold font-mono text-sm text-emerald-500">
              {{ formatDate(log.recorded_date) }}
            </span>
            <div class="flex items-center gap-1">
              <button @click="openEditModal(log)" class="p-1 rounded bg-cyan-500/10 text-cyan-500">
                <Edit3 class="w-4 h-4" />
              </button>
              <button @click="deleteEntry(log)" class="p-1 rounded bg-rose-500/10 text-rose-500">
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-2 text-xs">
            <div>
              <span class="text-[10px] text-slate-400 block">kWh PS</span>
              <span class="font-mono font-semibold">{{ log.kwh_ps.toLocaleString('id-ID') }}</span>
            </div>
            <div>
              <span class="text-[10px] text-slate-400 block">kWh Digital (1)</span>
              <span class="font-mono font-semibold">{{ log.kwh_digital_1.toLocaleString('id-ID') }}</span>
            </div>
            <div>
              <span class="text-[10px] text-slate-400 block">kWh Digital (2)</span>
              <span class="font-mono font-semibold">{{ log.kwh_digital_2.toLocaleString('id-ID') }}</span>
            </div>
            <div>
              <span class="text-[10px] text-emerald-500 font-bold block">kWh Total</span>
              <span class="font-mono font-extrabold text-emerald-500">{{ log.kwh_total.toLocaleString('id-ID') }}</span>
            </div>
          </div>

          <div class="text-[10px] font-mono text-slate-400 bg-slate-900/40 p-2 rounded border border-slate-800">
            Last Modified: {{ log.last_modified }}
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
              <Zap class="w-5 h-5 text-emerald-500" />
              <span>{{ isEditing ? 'Edit Data kWh Produksi' : 'Tambah Data kWh Produksi' }}</span>
            </h3>
            <button @click="closeModal" class="text-slate-400 hover:text-white">
              <X class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="submitForm" class="space-y-4 text-xs">
            <!-- Tanggal -->
            <div class="space-y-1">
              <label class="font-bold text-slate-400">Tanggal Pencatatan</label>
              <input
                type="date"
                v-model="form.recorded_date"
                required
                :class="[
                  'w-full p-2.5 rounded-xl border font-mono focus:outline-none focus:ring-1 focus:ring-emerald-500',
                  isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                ]"
              />
            </div>

            <!-- kWh PS -->
            <div class="space-y-1">
              <label class="font-bold text-slate-400">kWh PS</label>
              <input
                type="number"
                step="0.01"
                min="0"
                v-model.number="form.kwh_ps"
                placeholder="0.00"
                :class="[
                  'w-full p-2.5 rounded-xl border font-mono focus:outline-none focus:ring-1 focus:ring-emerald-500',
                  isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                ]"
              />
            </div>

            <!-- kWh Digital 1 -->
            <div class="space-y-1">
              <label class="font-bold text-slate-400">kWh Digital (1)</label>
              <input
                type="number"
                step="0.01"
                min="0"
                v-model.number="form.kwh_digital_1"
                placeholder="0.00"
                :class="[
                  'w-full p-2.5 rounded-xl border font-mono focus:outline-none focus:ring-1 focus:ring-emerald-500',
                  isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                ]"
              />
            </div>

            <!-- kWh Digital 2 -->
            <div class="space-y-1">
              <label class="font-bold text-slate-400">kWh Digital (2)</label>
              <input
                type="number"
                step="0.01"
                min="0"
                v-model.number="form.kwh_digital_2"
                placeholder="0.00"
                :class="[
                  'w-full p-2.5 rounded-xl border font-mono focus:outline-none focus:ring-1 focus:ring-emerald-500',
                  isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                ]"
              />
            </div>

            <!-- Live Auto Total Preview -->
            <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-between text-emerald-400 font-mono font-bold">
              <span>kWh Total (Auto):</span>
              <span class="text-sm">{{ calculatedTotal.toLocaleString('id-ID', { minimumFractionDigits: 2 }) }} kWh</span>
            </div>

            <!-- Operator Name -->
            <div class="space-y-1">
              <label class="font-bold text-slate-400">Nama Operator</label>
              <input
                type="text"
                v-model="form.operator_name"
                placeholder="Nama Operator"
                :class="[
                  'w-full p-2.5 rounded-xl border focus:outline-none focus:ring-1 focus:ring-emerald-500',
                  isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                ]"
              />
            </div>

            <!-- Submit Button -->
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
                class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold shadow-md flex items-center justify-center gap-2"
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
import { ref, computed, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import { Table, Plus, Clock, Edit3, Trash2, Zap, X, Loader2 } from 'lucide-vue-next';

const props = defineProps({
  logs: {
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
  recorded_date: new Date().toISOString().split('T')[0],
  kwh_ps: 0,
  kwh_digital_1: 0,
  kwh_digital_2: 0,
  operator_name: '',
});

const calculatedTotal = computed(() => {
  const ps = parseFloat(form.kwh_ps) || 0;
  const d1 = parseFloat(form.kwh_digital_1) || 0;
  const d2 = parseFloat(form.kwh_digital_2) || 0;
  return ps + d1 + d2;
});

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const parts = dateStr.split('-');
  return `${parts[2]}/${parts[1]}/${parts[0]}`;
};

const openAddModal = () => {
  isEditing.value = false;
  form.recorded_date = new Date().toISOString().split('T')[0];
  form.kwh_ps = 0;
  form.kwh_digital_1 = 0;
  form.kwh_digital_2 = 0;
  form.operator_name = '';
  showModal.value = true;
};

const openEditModal = (log) => {
  isEditing.value = true;
  form.recorded_date = log.recorded_date;
  form.kwh_ps = log.kwh_ps;
  form.kwh_digital_1 = log.kwh_digital_1;
  form.kwh_digital_2 = log.kwh_digital_2;
  form.operator_name = log.operator_name;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const submitForm = () => {
  isSubmitting.value = true;
  router.post(
    '/monitoring-kwh',
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

const deleteEntry = (log) => {
  if (confirm(`Apakah Anda yakin ingin menghapus pencatatan tanggal ${formatDate(log.recorded_date)}?`)) {
    router.delete(`/monitoring-kwh/${log.id}`, { preserveScroll: true });
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
