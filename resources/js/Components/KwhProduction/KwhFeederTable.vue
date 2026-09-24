<template>
  <div class="space-y-6">
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
            <Cable class="w-4 h-4" />
          </div>
          <div>
            <h3 :class="['text-sm font-bold tracking-wide', isDarkMode ? 'text-white' : 'text-slate-900']">
              Tabel kWh Produksi — PENYULANG
            </h3>
            <p :class="['text-xs', isDarkMode ? 'text-slate-400' : 'text-slate-500']">
              Stand akhir PM 800 / EDMI MK10 (EX/IM), kWh PS, kWh Digital dan PS Total per penyulang
            </p>
          </div>
        </div>

        <button
          @click="openAddModal"
          class="py-2 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition-all flex items-center justify-center gap-1.5 self-start sm:self-auto"
        >
          <Plus class="w-4 h-4" />
          <span>Tambah Data Penyulang</span>
        </button>
      </div>

      <!-- Desktop Data Table (>= 768px) -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr
              :class="[
                'border-b font-semibold uppercase tracking-wider text-center',
                isDarkMode ? 'bg-slate-950/80 text-slate-300 border-slate-800' : 'bg-slate-100/90 text-slate-700 border-slate-200'
              ]"
            >
              <th class="py-3 px-4 text-left">Tanggal</th>
              <th class="py-3 px-3 text-left">Penyulang</th>
              <th class="py-3 px-3 min-w-[170px]">Stand Akhir kWh Produksi</th>
              <th class="py-3 px-3">kWh PS</th>
              <th class="py-3 px-3">kWh Digital</th>
              <th class="py-3 px-3 text-emerald-600 dark:text-emerald-400">PS Total</th>
              <th class="py-3 px-4 min-w-[190px]">Last Modified</th>
              <th class="py-3 px-3 w-24">Aksi</th>
            </tr>
          </thead>
          <tbody :class="['divide-y', isDarkMode ? 'divide-slate-800/60' : 'divide-slate-200/60']">
            <tr
              v-for="log in logs"
              :key="log.id"
              :class="['transition-colors', isDarkMode ? 'hover:bg-slate-800/40' : 'hover:bg-slate-50']"
            >
              <td class="py-3 px-4 font-bold font-mono">
                <span :class="['px-2.5 py-1 rounded border', isDarkMode ? 'bg-slate-800 text-slate-200 border-slate-700' : 'bg-slate-100 text-slate-800 border-slate-200']">
                  {{ formatDate(log.recorded_date) }}
                </span>
              </td>
              <td class="py-3 px-3 font-bold">{{ log.feeder_label }}</td>
              <td class="py-3 px-3">
                <div class="flex flex-col gap-0.5 font-mono text-[10px]">
                  <span v-for="choice in filledStands(log)" :key="choice.key">
                    <span class="text-slate-400">{{ choice.label }}:</span> <b>{{ formatNumber(log[choice.key]) }}</b>
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
              <td colspan="8" class="py-8 text-center text-slate-400 text-xs italic">
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
          :class="['border rounded-xl p-4 space-y-3 shadow-sm', isDarkMode ? 'bg-slate-950 border-slate-800' : 'bg-slate-50 border-slate-200']"
        >
          <div :class="['flex items-center justify-between border-b pb-2', isDarkMode ? 'border-slate-800' : 'border-slate-200']">
            <div>
              <span class="font-bold font-mono text-sm text-emerald-500">{{ formatDate(log.recorded_date) }}</span>
              <span :class="['ml-2 text-xs font-bold', isDarkMode ? 'text-slate-200' : 'text-slate-800']">{{ log.feeder_label }}</span>
            </div>
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
            <div v-for="choice in filledStands(log)" :key="choice.key">
              <span class="text-[10px] text-slate-400 block">{{ choice.label }}</span>
              <span class="font-mono font-semibold">{{ formatNumber(log[choice.key]) }}</span>
            </div>
            <div>
              <span class="text-[10px] text-slate-400 block">kWh PS</span>
              <span class="font-mono font-semibold">{{ formatNumber(log.kwh_ps) }}</span>
            </div>
            <div>
              <span class="text-[10px] text-slate-400 block">kWh Digital</span>
              <span class="font-mono font-semibold">{{ formatNumber(log.kwh_digital) }}</span>
            </div>
            <div>
              <span class="text-[10px] text-emerald-500 font-bold block">PS Total</span>
              <span class="font-mono font-extrabold text-emerald-500">{{ formatNumber(log.ps_total) }}</span>
            </div>
          </div>

          <div :class="['text-[10px] font-mono p-2 rounded border', isDarkMode ? 'text-slate-400 bg-slate-900/40 border-slate-800' : 'text-slate-500 bg-white border-slate-200']">
            Last Modified: {{ log.last_modified }}
          </div>
        </div>

        <p v-if="logs.length === 0" class="py-6 text-center text-slate-400 text-xs italic">
          Belum ada data kWh Penyulang untuk bulan ini.
        </p>
      </div>
    </div>

    <!-- Modal Form Add/Edit -->
    <Transition name="fade">
      <div v-if="showModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div
          :class="[
            'w-full max-w-md max-h-[90vh] overflow-y-auto border rounded-2xl shadow-2xl p-6 space-y-5 transition-colors',
            isDarkMode ? 'bg-slate-900 border-slate-800 text-white' : 'bg-white border-slate-200 text-slate-900'
          ]"
        >
          <div :class="['flex items-center justify-between border-b pb-3', isDarkMode ? 'border-slate-800' : 'border-slate-100']">
            <h3 class="font-bold text-base flex items-center gap-2">
              <Cable class="w-5 h-5 text-emerald-500" />
              <span>{{ isEditing ? 'Edit Data kWh Penyulang' : 'Tambah Data kWh Penyulang' }}</span>
            </h3>
            <button @click="closeModal" class="text-slate-400 hover:text-rose-500">
              <X class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="submitForm" class="space-y-4 text-xs">
            <div class="grid grid-cols-2 gap-3">
              <div class="space-y-1">
                <label class="font-bold text-slate-400">Tanggal</label>
                <input type="date" v-model="form.recorded_date" required :disabled="isEditing" :class="[inputClass, 'font-mono']" />
              </div>
              <div class="space-y-1">
                <label class="font-bold text-slate-400">Penyulang</label>
                <select v-model="form.feeder" required :disabled="isEditing" :class="inputClass">
                  <option value="" disabled>— Pilih —</option>
                  <option v-for="feeder in feeders" :key="feeder.key" :value="feeder.key">{{ feeder.label }}</option>
                </select>
              </div>
            </div>

            <ChoiceValueInput
              label="Stand Akhir kWh Produksi"
              :options="standChoices"
              :values="form"
              :isDarkMode="isDarkMode"
            />

            <div class="grid grid-cols-2 gap-3">
              <div class="space-y-1">
                <label class="font-bold text-slate-400">kWh PS</label>
                <input type="number" step="0.01" min="0" v-model.number="form.kwh_ps" placeholder="0.00" :class="[inputClass, 'font-mono']" />
              </div>
              <div class="space-y-1">
                <label class="font-bold text-slate-400">kWh Digital</label>
                <input type="number" step="0.01" min="0" v-model.number="form.kwh_digital" placeholder="0.00" :class="[inputClass, 'font-mono']" />
              </div>
            </div>

            <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-between text-emerald-500 font-mono font-bold">
              <span>PS Total (Auto):</span>
              <span class="text-sm">{{ formatNumber(calculatedPsTotal) }} kWh</span>
            </div>

            <div class="space-y-1">
              <label class="font-bold text-slate-400">Nama Operator</label>
              <input type="text" v-model="form.operator_name" placeholder="Kosongkan untuk memakai nama akun" :class="inputClass" />
            </div>

            <ul v-if="Object.keys(errors).length" class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-500 space-y-0.5">
              <li v-for="(message, key) in errors" :key="key">{{ message }}</li>
            </ul>

            <div class="pt-2 flex items-center gap-3">
              <button
                type="button"
                @click="closeModal"
                :class="['flex-1 py-2.5 rounded-xl border font-bold', isDarkMode ? 'border-slate-700 text-slate-400 hover:bg-slate-800' : 'border-slate-200 text-slate-600 hover:bg-slate-100']"
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
import { router, usePage } from '@inertiajs/vue3';
import { Cable, Plus, Clock, Edit3, Trash2, X, Loader2 } from 'lucide-vue-next';
import ChoiceValueInput from '@/Components/Shared/ChoiceValueInput.vue';

const props = defineProps({
  logs: { type: Array, required: true },
  feeders: { type: Array, required: true },
  standChoices: { type: Array, required: true },
  isDarkMode: { type: Boolean, default: false },
});

const page = usePage();
const errors = computed(() => page.props.errors || {});

const showModal = ref(false);
const isEditing = ref(false);
const isSubmitting = ref(false);

const emptyForm = () => ({
  recorded_date: new Date().toISOString().split('T')[0],
  feeder: '',
  pm800_ex: null,
  pm800_im: null,
  edmi_mk10_ex: null,
  edmi_mk10_im: null,
  kwh_ps: null,
  kwh_digital: null,
  operator_name: '',
});

const form = reactive(emptyForm());

const calculatedPsTotal = computed(() => (parseFloat(form.kwh_ps) || 0) + (parseFloat(form.kwh_digital) || 0));

const inputClass = computed(() => [
  'w-full p-2.5 rounded-xl border focus:outline-none focus:ring-1 focus:ring-emerald-500 disabled:opacity-60',
  props.isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-900',
]);

const filledStands = (log) => props.standChoices.filter(choice => log[choice.key] !== null && log[choice.key] !== undefined);

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const parts = dateStr.split('-');
  return `${parts[2]}/${parts[1]}/${parts[0]}`;
};

const formatNumber = (value) =>
  value === null || value === undefined ? '-' : value.toLocaleString('id-ID', { minimumFractionDigits: 2 });

const openAddModal = () => {
  isEditing.value = false;
  Object.assign(form, emptyForm());
  showModal.value = true;
};

const openEditModal = (log) => {
  isEditing.value = true;
  Object.assign(form, emptyForm());
  Object.keys(form).forEach(key => {
    if (log[key] !== undefined) form[key] = log[key];
  });
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const submitForm = () => {
  isSubmitting.value = true;
  router.post('/monitoring-kwh/penyulang', { ...form }, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      showModal.value = false;
    },
    onFinish: () => {
      isSubmitting.value = false;
    },
  });
};

const deleteEntry = (log) => {
  if (confirm(`Hapus data ${log.feeder_label} tanggal ${formatDate(log.recorded_date)}?`)) {
    router.delete(`/monitoring-kwh/penyulang/${log.id}`, { preserveScroll: true, preserveState: true });
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
