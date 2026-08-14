<template>
  <div class="space-y-6">
    <!-- Desktop & Mobile Main Card Container -->
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
              isDarkMode ? 'bg-amber-500/10 border-amber-500/30 text-amber-400' : 'bg-amber-50 border-amber-200 text-amber-600'
            ]"
          >
            <Table class="w-4 h-4" />
          </div>
          <div>
            <h3 :class="['text-sm font-bold tracking-wide', isDarkMode ? 'text-white' : 'text-slate-900']">
              Tabel Pencatatan Stok & Pemakaian BBM
            </h3>
            <p :class="['text-xs', isDarkMode ? 'text-slate-400' : 'text-slate-500']">
              Monitoring harian main tank, death stock, unloading, netto stock, dan sisa hari operasi
            </p>
          </div>
        </div>

        <!-- Add New Entry Button -->
        <button
          @click="openAddModal"
          class="py-2 px-4 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-md transition-all flex items-center justify-center gap-1.5 self-start sm:self-auto"
        >
          <Plus class="w-4 h-4" />
          <span>Tambah Data Stok BBM</span>
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
              <th class="py-3 px-3 w-28">Tanggal</th>
              <th class="py-3 px-3 text-right">Pemakaian Hari Ini</th>
              <th class="py-3 px-3 text-right">Main Tank</th>
              <th class="py-3 px-3 text-right">Total Gross</th>
              <th class="py-3 px-3 text-right">Death Stock</th>
              <th class="py-3 px-3 text-right text-emerald-500 font-bold">Unloading</th>
              <th class="py-3 px-3 text-right font-bold text-amber-500">Netto Stock</th>
              <th class="py-3 px-3 text-right">Estimasi Harian</th>
              <th class="py-3 px-3 text-center font-bold text-cyan-400">Sisa Hari Operasi</th>
              <th class="py-3 px-3 text-center min-w-[170px]">Last Modified</th>
              <th class="py-3 px-3 w-24 text-center">Aksi</th>
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
              <td class="py-3 px-3 font-bold font-mono text-xs">
                <span :class="['px-2 py-0.5 rounded border', isDarkMode ? 'bg-slate-800 text-slate-200 border-slate-700' : 'bg-slate-100 text-slate-800 border-slate-200']">
                  {{ formatDate(log.recorded_date) }}
                </span>
              </td>

              <!-- Pemakaian BBM Hari Ini -->
              <td class="py-3 px-3 text-right font-mono">
                {{ log.daily_consumption.toLocaleString('id-ID', { minimumFractionDigits: 2 }) }} L
              </td>

              <!-- Main Tank -->
              <td class="py-3 px-3 text-right font-mono">
                {{ log.main_tank.toLocaleString('id-ID', { minimumFractionDigits: 2 }) }} L
              </td>

              <!-- Total Gross BBM (= Main Tank) -->
              <td class="py-3 px-3 text-right font-mono font-medium">
                {{ log.total_gross.toLocaleString('id-ID', { minimumFractionDigits: 2 }) }} L
              </td>

              <!-- Death Stock -->
              <td class="py-3 px-3 text-right font-mono text-rose-400">
                {{ log.death_stock.toLocaleString('id-ID', { minimumFractionDigits: 2 }) }} L
              </td>

              <!-- Unloading -->
              <td class="py-3 px-3 text-right font-mono font-semibold text-emerald-500">
                +{{ log.unloading.toLocaleString('id-ID', { minimumFractionDigits: 2 }) }} L
              </td>

              <!-- Netto Stock (Auto) -->
              <td class="py-3 px-3 text-right font-mono font-extrabold text-amber-500">
                {{ log.netto_stock.toLocaleString('id-ID', { minimumFractionDigits: 2 }) }} L
              </td>

              <!-- Estimasi Pemakaian Harian -->
              <td class="py-3 px-3 text-right font-mono">
                {{ log.estimated_daily_consumption.toLocaleString('id-ID', { minimumFractionDigits: 2 }) }} L
              </td>

              <!-- Sisa Hari Operasi (Days of Supply) -->
              <td class="py-3 px-3 text-center font-mono">
                <span
                  :class="[
                    'px-2.5 py-1 rounded-full text-xs font-black border inline-flex items-center gap-1',
                    log.days_of_supply < 7
                      ? 'bg-rose-500/20 text-rose-400 border-rose-500/40 animate-pulse'
                      : log.days_of_supply < 15
                        ? 'bg-amber-500/20 text-amber-300 border-amber-500/40'
                        : 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40'
                  ]"
                >
                  {{ log.days_of_supply.toLocaleString('id-ID', { minimumFractionDigits: 1 }) }} Hari
                </span>
              </td>

              <!-- Last Modified -->
              <td class="py-3 px-3 text-center font-mono text-[10px]">
                <span
                  v-if="log.last_modified !== '-'"
                  :class="[
                    'inline-flex items-center gap-1 px-2 py-0.5 rounded border',
                    isDarkMode ? 'bg-slate-800/80 text-slate-300 border-slate-700/50' : 'bg-slate-100 text-slate-700 border-slate-200 font-medium'
                  ]"
                >
                  <Clock class="w-3 h-3 text-amber-500 shrink-0" />
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
              <td colspan="11" class="py-8 text-center text-slate-400 text-xs italic">
                Belum ada data pencatatan stok BBM untuk bulan ini.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Card List (< 768px) -->
      <div class="block md:hidden p-4 space-y-3">
        <div
          v-for="log in logs"
          :key="'m-fuel-' + log.id"
          :class="[
            'border rounded-xl p-4 space-y-3 shadow-sm',
            isDarkMode ? 'bg-slate-950 border-slate-800' : 'bg-slate-50 border-slate-200'
          ]"
        >
          <div class="flex items-center justify-between border-b pb-2 dark:border-slate-800 border-slate-200">
            <span class="font-bold font-mono text-xs text-amber-500">
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
              <span class="text-[10px] text-slate-400 block">Netto Stock</span>
              <span class="font-mono font-bold text-amber-500">{{ log.netto_stock.toLocaleString('id-ID') }} L</span>
            </div>
            <div>
              <span class="text-[10px] text-slate-400 block">Sisa Hari Operasi</span>
              <span class="font-mono font-extrabold text-cyan-400">{{ log.days_of_supply.toLocaleString('id-ID') }} Hari</span>
            </div>
            <div>
              <span class="text-[10px] text-slate-400 block">Pemakaian Hari Ini</span>
              <span class="font-mono">{{ log.daily_consumption.toLocaleString('id-ID') }} L</span>
            </div>
            <div>
              <span class="text-[10px] text-slate-400 block">Unloading</span>
              <span class="font-mono text-emerald-500">+{{ log.unloading.toLocaleString('id-ID') }} L</span>
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
            'w-full max-w-lg border rounded-2xl shadow-2xl p-6 space-y-5 transition-colors overflow-y-auto max-h-[90vh]',
            isDarkMode ? 'bg-slate-900 border-slate-800 text-white' : 'bg-white border-slate-200 text-slate-900'
          ]"
        >
          <div class="flex items-center justify-between border-b pb-3 dark:border-slate-800 border-slate-100">
            <h3 class="font-bold text-base flex items-center gap-2">
              <Fuel class="w-5 h-5 text-amber-500" />
              <span>{{ isEditing ? 'Edit Data Stok BBM' : 'Tambah Data Stok BBM' }}</span>
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
                  'w-full p-2.5 rounded-xl border font-mono focus:outline-none focus:ring-1 focus:ring-amber-500',
                  isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                ]"
              />
            </div>

            <!-- Inputs Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <!-- Pemakaian BBM Hari Ini -->
              <div class="space-y-1">
                <label class="font-bold text-slate-400">Pemakaian Hari Ini (Liter)</label>
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  v-model.number="form.daily_consumption"
                  placeholder="0.00"
                  :class="[
                    'w-full p-2.5 rounded-xl border font-mono focus:outline-none focus:ring-1 focus:ring-amber-500',
                    isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                  ]"
                />
              </div>

              <!-- Main Tank -->
              <div class="space-y-1">
                <label class="font-bold text-slate-400">Main Tank (Liter)</label>
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  v-model.number="form.main_tank"
                  placeholder="0.00"
                  :class="[
                    'w-full p-2.5 rounded-xl border font-mono focus:outline-none focus:ring-1 focus:ring-amber-500',
                    isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                  ]"
                />
              </div>

              <!-- Death Stock -->
              <div class="space-y-1">
                <label class="font-bold text-slate-400">Death Stock (Liter)</label>
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  v-model.number="form.death_stock"
                  placeholder="0.00"
                  :class="[
                    'w-full p-2.5 rounded-xl border font-mono focus:outline-none focus:ring-1 focus:ring-amber-500',
                    isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                  ]"
                />
              </div>

              <!-- Unloading -->
              <div class="space-y-1">
                <label class="font-bold text-slate-400">Unloading (Liter)</label>
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  v-model.number="form.unloading"
                  placeholder="0.00"
                  :class="[
                    'w-full p-2.5 rounded-xl border font-mono focus:outline-none focus:ring-1 focus:ring-amber-500',
                    isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                  ]"
                />
              </div>
            </div>

            <!-- Estimasi Pemakaian Harian -->
            <div class="space-y-1">
              <label class="font-bold text-slate-400">Estimasi Pemakaian Harian (Liter/Hari)</label>
              <input
                type="number"
                step="0.01"
                min="0"
                v-model.number="form.estimated_daily_consumption"
                placeholder="0.00"
                :class="[
                  'w-full p-2.5 rounded-xl border font-mono focus:outline-none focus:ring-1 focus:ring-amber-500',
                  isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                ]"
              />
            </div>

            <!-- Live Formulations Auto Preview Card -->
            <div class="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/30 space-y-2 text-xs font-mono">
              <div class="flex items-center justify-between text-slate-300">
                <span>Total Gross BBM (= Main Tank):</span>
                <span class="font-bold text-amber-400">{{ calculatedTotalGross.toLocaleString('id-ID') }} Liter</span>
              </div>
              <div class="flex items-center justify-between text-slate-300">
                <span>Netto Stock (= Gross - Death + Unloading):</span>
                <span class="font-bold text-amber-400">{{ calculatedNettoStock.toLocaleString('id-ID') }} Liter</span>
              </div>
              <div class="flex items-center justify-between text-slate-300 pt-1 border-t border-amber-500/20">
                <span>Sisa Hari Operasi (= Netto / Estimasi):</span>
                <span class="font-black text-cyan-400 text-sm">{{ calculatedDaysOfSupply.toLocaleString('id-ID', { minimumFractionDigits: 1 }) }} Hari</span>
              </div>
            </div>

            <!-- Operator Name -->
            <div class="space-y-1">
              <label class="font-bold text-slate-400">Nama Operator</label>
              <input
                type="text"
                v-model="form.operator_name"
                placeholder="Nama Operator"
                :class="[
                  'w-full p-2.5 rounded-xl border focus:outline-none focus:ring-1 focus:ring-amber-500',
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
                class="flex-1 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold shadow-md flex items-center justify-center gap-2"
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
import { Table, Plus, Clock, Edit3, Trash2, Fuel, X, Loader2 } from 'lucide-vue-next';

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
  daily_consumption: 0,
  main_tank: 0,
  death_stock: 0,
  unloading: 0,
  estimated_daily_consumption: 0,
  operator_name: '',
});

// Live Formulations
const calculatedTotalGross = computed(() => {
  return parseFloat(form.main_tank) || 0;
});

const calculatedNettoStock = computed(() => {
  const gross = calculatedTotalGross.value;
  const death = parseFloat(form.death_stock) || 0;
  const unload = parseFloat(form.unloading) || 0;
  return gross - death + unload;
});

const calculatedDaysOfSupply = computed(() => {
  const netto = calculatedNettoStock.value;
  const est = parseFloat(form.estimated_daily_consumption) || 0;
  if (est > 0) {
    return Math.round((netto / est) * 100) / 100;
  }
  return 0;
});

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const parts = dateStr.split('-');
  return `${parts[2]}/${parts[1]}/${parts[0]}`;
};

const openAddModal = () => {
  isEditing.value = false;
  form.recorded_date = new Date().toISOString().split('T')[0];
  form.daily_consumption = 0;
  form.main_tank = 0;
  form.death_stock = 0;
  form.unloading = 0;
  form.estimated_daily_consumption = 0;
  form.operator_name = '';
  showModal.value = true;
};

const openEditModal = (log) => {
  isEditing.value = true;
  form.recorded_date = log.recorded_date;
  form.daily_consumption = log.daily_consumption;
  form.main_tank = log.main_tank;
  form.death_stock = log.death_stock;
  form.unloading = log.unloading;
  form.estimated_daily_consumption = log.estimated_daily_consumption;
  form.operator_name = log.operator_name;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const submitForm = () => {
  isSubmitting.value = true;
  router.post(
    '/monitoring-bbm',
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
  if (confirm(`Apakah Anda yakin ingin menghapus pencatatan stok BBM tanggal ${formatDate(log.recorded_date)}?`)) {
    router.delete(`/monitoring-bbm/${log.id}`, { preserveScroll: true });
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
