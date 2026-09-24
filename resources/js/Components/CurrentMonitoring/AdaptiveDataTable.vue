<template>
  <div class="space-y-6">
    <!-- Desktop Data Table (>= 768px) -->
    <div class="hidden md:block border rounded-2xl overflow-hidden shadow-sm transition-colors duration-300 bg-white border-slate-200 dark:bg-slate-900 dark:border-slate-800">
      <div class="p-4 sm:p-5 border-b flex items-center justify-between gap-4 border-slate-100 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-900/60">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg border flex items-center justify-center bg-cyan-50 border-cyan-200 text-cyan-600 dark:bg-cyan-500/10 dark:border-cyan-500/30 dark:text-cyan-400">
            <Table class="w-4 h-4" />
          </div>
          <div>
            <h3 class="text-sm font-bold tracking-wide text-slate-900 dark:text-white">
              Tabel Matriks Beban Arus Listrik (Per Jam)
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Input nilai Ampere untuk {{ feeders.length }} jalur feeder pada {{ matrixData.length }} interval jam shift
            </p>
          </div>
        </div>

        <div v-if="canInput" class="flex items-center gap-2">
          <div
            v-if="dirtyCount === 0"
            class="flex items-center gap-2 text-xs px-3 py-1.5 rounded-lg border font-medium text-slate-600 bg-white border-slate-200 dark:text-slate-400 dark:bg-slate-950 dark:border-slate-800"
          >
            <Info class="w-3.5 h-3.5 text-cyan-500 shrink-0" />
            <span>Tekan <b>Simpan</b> pada baris jam, atau <b>Simpan Semua</b> untuk semua baris yang diubah</span>
          </div>
          <template v-else>
            <span class="text-xs font-semibold text-amber-700 dark:text-amber-400">
              {{ dirtyCount }} baris belum disimpan
            </span>
            <Button accent="cyan" :loading="isSavingAll" :disabled="anySaving" @click="saveAll">
              <Save v-if="!isSavingAll" class="w-3.5 h-3.5" />
              <span>Simpan Semua ({{ dirtyCount }})</span>
            </Button>
          </template>
        </div>
      </div>

      <!-- Matrix Table Scroll Container -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="border-b font-semibold uppercase tracking-wider bg-slate-100/90 text-slate-700 border-slate-200 dark:bg-slate-950/80 dark:text-slate-300 dark:border-slate-800">
              <th class="py-3 px-3 w-20 text-center sticky left-0 z-10 border-r bg-slate-100 border-slate-200 dark:bg-slate-950 dark:border-slate-800">
                Jam
              </th>
              <th
                v-for="feeder in feeders"
                :key="feeder.id"
                class="py-3 px-2 text-center border-r min-w-[90px] border-slate-200/80 dark:border-slate-800/60"
              >
                <div class="font-bold text-[11px] truncate text-cyan-700 dark:text-cyan-400" :title="feeder.name">
                  {{ feeder.name }}
                </div>
                <div class="text-[9px] font-normal lowercase tracking-normal text-slate-400 dark:text-slate-500">
                  {{ feeder.code }}
                </div>
              </th>
              <th class="py-3 px-3 min-w-[140px] text-center border-r border-slate-200 dark:border-slate-800">
                Operator
              </th>
              <th class="py-3 px-3 min-w-[190px] text-center border-r border-slate-200 dark:border-slate-800">
                Last Modified (Audit Trail)
              </th>
              <th v-if="canInput" class="py-3 px-3 w-24 text-center sticky right-0 z-10 bg-slate-100 dark:bg-slate-950">
                Aksi
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800/60">
            <template v-for="row in matrixData" :key="row.interval">
              <tr
                :class="[
                  'transition-colors group',
                  isDirty(row)
                    ? 'bg-amber-50/70 hover:bg-amber-50 dark:bg-amber-500/5 dark:hover:bg-amber-500/10'
                    : 'hover:bg-slate-50 dark:hover:bg-slate-800/50',
                ]"
              >
                <!-- Time Interval Column -->
                <td
                  :class="[
                    'py-2.5 px-3 font-bold text-center sticky left-0 z-10 border-r text-xs transition-colors',
                    'border-slate-200 text-slate-800 dark:border-slate-800 dark:text-slate-200',
                    isDirty(row)
                      ? 'bg-amber-50 border-l-4 border-l-amber-400 dark:bg-slate-900 dark:border-l-amber-500'
                      : 'bg-white group-hover:bg-slate-50 dark:bg-slate-900 dark:group-hover:bg-slate-800',
                  ]"
                >
                  <span class="px-2 py-1 rounded font-mono font-bold border bg-cyan-50 text-cyan-700 border-cyan-200 dark:font-normal dark:bg-slate-800/80 dark:text-cyan-400 dark:border-slate-700/60">
                    {{ row.interval }}
                  </span>
                  <span v-if="isDirty(row)" class="sr-only">(belum disimpan)</span>
                </td>

                <!-- Feeder Inputs -->
                <td
                  v-for="feeder in feeders"
                  :key="feeder.id"
                  class="py-1.5 px-1 text-center border-r border-slate-200/50 dark:border-slate-800/40"
                >
                  <input
                    type="number"
                    step="0.01"
                    min="0"
                    v-model="row.formValues[feeder.id]"
                    placeholder="-"
                    :disabled="!canInput || row.isSaving"
                    :aria-label="`${feeder.name} jam ${row.interval} (Ampere)`"
                    :aria-invalid="!!fieldError(row, feeder.id) || undefined"
                    :title="fieldError(row, feeder.id) || undefined"
                    :class="[
                      'w-full text-center py-1 px-1 rounded border text-xs font-mono font-bold dark:font-medium focus:outline-none focus:ring-1 transition-all disabled:opacity-70 disabled:cursor-not-allowed',
                      'bg-slate-50 text-slate-900 placeholder:text-slate-400 dark:bg-slate-950/80 dark:text-slate-100 dark:placeholder:text-slate-600',
                      fieldError(row, feeder.id)
                        ? 'border-rose-500 focus:border-rose-500 focus:ring-rose-500'
                        : 'border-slate-200 focus:border-cyan-500 focus:ring-cyan-500 dark:border-slate-800',
                    ]"
                  />
                </td>

                <!-- Operator Input -->
                <td class="py-1.5 px-2 text-center border-r border-slate-200 dark:border-slate-800">
                  <input
                    type="text"
                    v-model="row.operatorName"
                    :placeholder="operatorPlaceholder"
                    :disabled="!canInput || row.isSaving"
                    :aria-label="`Operator jam ${row.interval}`"
                    :title="`Kosongkan untuk memakai nama akun (${operatorPlaceholder})`"
                    class="w-full py-1 px-2 rounded border text-xs font-medium dark:font-normal focus:outline-none focus:ring-1 transition-all disabled:opacity-70 disabled:cursor-not-allowed bg-slate-50 border-slate-200 text-slate-800 focus:border-cyan-500 focus:ring-cyan-500 placeholder:text-slate-400 dark:bg-slate-950/80 dark:border-slate-800 dark:text-slate-200 dark:placeholder:text-slate-600"
                  />
                </td>

                <!-- Auto-Audit Trail Output Column -->
                <td class="py-2 px-3 text-center border-r font-mono text-[10px] border-slate-200 text-slate-600 dark:border-slate-800 dark:text-slate-400">
                  <span
                    v-if="row.last_modified !== '-'"
                    class="inline-flex items-center gap-1 px-2 py-1 rounded border font-medium bg-slate-100 text-slate-700 border-slate-200 dark:font-normal dark:bg-slate-800/80 dark:text-slate-300 dark:border-slate-700/50"
                  >
                    <Clock class="w-3 h-3 text-cyan-500 shrink-0" />
                    <span>{{ row.last_modified }}</span>
                  </span>
                  <span v-else class="font-sans italic text-[11px] text-slate-400 dark:text-slate-600">-</span>
                </td>

                <!-- Save Button Column -->
                <td
                  v-if="canInput"
                  :class="[
                    'py-1.5 px-2 text-center sticky right-0 z-10 transition-colors',
                    isDirty(row) ? 'bg-amber-50 dark:bg-slate-900' : 'bg-white group-hover:bg-slate-50 dark:bg-slate-900 dark:group-hover:bg-slate-800',
                  ]"
                >
                  <button
                    type="button"
                    @click="saveRow(row)"
                    :disabled="row.isSaving || isSavingAll"
                    :class="[
                      'w-full py-1.5 px-2 rounded-lg font-medium text-xs transition-all shadow-md flex items-center justify-center gap-1 text-white',
                      'disabled:opacity-60 disabled:cursor-not-allowed disabled:shadow-none',
                      isDirty(row) ? 'bg-amber-600 enabled:hover:bg-amber-500' : 'bg-cyan-600 enabled:hover:bg-cyan-700 dark:enabled:hover:bg-cyan-500',
                    ]"
                  >
                    <Save class="w-3.5 h-3.5" v-if="!row.isSaving" />
                    <Loader2 class="w-3.5 h-3.5 animate-spin" v-else />
                    <span>Simpan</span>
                  </button>
                </td>
              </tr>

              <!-- Pesan error per baris -->
              <tr v-if="rowErrorMessages(row).length">
                <td
                  :colspan="feeders.length + (canInput ? 4 : 3)"
                  class="py-2 px-3 text-[11px] font-semibold bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400"
                  role="alert"
                >
                  Jam {{ row.interval }} gagal disimpan: {{ rowErrorMessages(row).join(' ') }}
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Mobile Card View (< 768px) -->
    <div class="block md:hidden space-y-4">
      <div class="p-3 border rounded-xl flex items-center justify-between gap-2 bg-white border-slate-200 shadow-sm dark:bg-slate-900 dark:border-slate-800 dark:shadow-none">
        <div class="flex items-center gap-2">
          <Clock class="w-4 h-4 text-cyan-500" />
          <span class="text-xs font-bold text-slate-900 dark:text-white">
            Daftar Interval Jam (Shift {{ selectedShift }})
          </span>
        </div>
        <span class="text-[10px] text-slate-500 dark:text-slate-400">{{ matrixData.length }} Interval</span>
      </div>

      <!-- Simpan Semua (mobile): menempel di bawah header saat halaman di-scroll -->
      <div
        v-if="canInput && dirtyCount > 0"
        class="sticky top-16 z-20 p-3 rounded-xl border flex items-center justify-between gap-3 shadow-md bg-amber-50 border-amber-200 dark:bg-slate-900 dark:border-amber-500/40"
      >
        <span class="text-xs font-semibold text-amber-800 dark:text-amber-400">{{ dirtyCount }} baris belum disimpan</span>
        <Button accent="cyan" :loading="isSavingAll" :disabled="anySaving" @click="saveAll">
          <span>Simpan Semua</span>
        </Button>
      </div>

      <div
        v-for="row in matrixData"
        :key="'mobile-' + row.interval"
        :class="[
          'border rounded-2xl p-4 shadow-sm space-y-3 transition-colors duration-300 bg-white dark:bg-slate-900',
          isDirty(row) ? 'border-amber-400 ring-1 ring-amber-400/50 dark:border-amber-500' : 'border-slate-200 dark:border-slate-800',
        ]"
      >
        <!-- Card Header -->
        <div class="flex items-center justify-between gap-2 border-b pb-2.5 border-slate-100 dark:border-slate-800">
          <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 rounded-lg font-mono font-bold text-sm border bg-cyan-50 border-cyan-200 text-cyan-700 dark:bg-cyan-500/10 dark:border-cyan-500/30 dark:text-cyan-400">
              Jam {{ row.interval }}
            </span>
            <span v-if="isDirty(row)" class="text-[10px] font-bold text-amber-700 dark:text-amber-400">● Belum disimpan</span>
          </div>

          <!-- Operator input -->
          <div class="w-36">
            <input
              type="text"
              v-model="row.operatorName"
              :placeholder="operatorPlaceholder"
              :disabled="!canInput || row.isSaving"
              :aria-label="`Operator jam ${row.interval}`"
              class="w-full text-right py-1 px-2 rounded-lg border text-xs focus:outline-none disabled:opacity-70 bg-slate-50 border-slate-200 text-slate-800 focus:border-cyan-500 placeholder:text-slate-400 dark:bg-slate-950 dark:border-slate-800 dark:text-slate-200 dark:placeholder:text-slate-600"
            />
          </div>
        </div>

        <!-- Audit Trail Info Mobile -->
        <div class="text-[10px] flex items-center gap-1.5 p-2 rounded-lg border text-slate-600 bg-slate-50 border-slate-200 dark:text-slate-400 dark:bg-slate-950/60 dark:border-slate-800/80">
          <UserCheck class="w-3 h-3 text-cyan-500 shrink-0" />
          <span class="font-mono">Last Modified: {{ row.last_modified }}</span>
        </div>

        <!-- Feeders Grid Inputs Mobile -->
        <div class="grid grid-cols-2 gap-2 pt-1">
          <label
            v-for="feeder in feeders"
            :key="'m-' + feeder.id"
            class="block p-2 rounded-xl border space-y-1 bg-slate-50 border-slate-200 dark:bg-slate-950 dark:border-slate-800/80"
          >
            <span class="flex items-center justify-between text-[10px]">
              <span class="font-bold truncate text-slate-700 dark:text-slate-300" :title="feeder.name">
                {{ feeder.name }}
              </span>
              <span class="font-mono text-slate-400 dark:text-slate-500">Ampere</span>
            </span>
            <input
              type="number"
              step="0.01"
              min="0"
              inputmode="decimal"
              v-model="row.formValues[feeder.id]"
              placeholder="0.00"
              :disabled="!canInput || row.isSaving"
              :aria-invalid="!!fieldError(row, feeder.id) || undefined"
              :class="[
                'w-full text-center py-2 px-2 rounded border font-mono font-bold text-sm focus:outline-none disabled:opacity-70',
                'bg-white text-cyan-700 dark:bg-slate-900 dark:text-cyan-400',
                fieldError(row, feeder.id) ? 'border-rose-500' : 'border-slate-200 focus:border-cyan-500 dark:border-slate-800',
              ]"
            />
          </label>
        </div>

        <p v-if="rowErrorMessages(row).length" role="alert" class="text-[11px] font-semibold text-rose-600 dark:text-rose-400">
          Gagal disimpan: {{ rowErrorMessages(row).join(' ') }}
        </p>

        <!-- Save Button Mobile -->
        <Button
          v-if="canInput"
          :accent="isDirty(row) ? 'amber' : 'cyan'"
          class="w-full"
          :loading="row.isSaving"
          :disabled="isSavingAll"
          @click="saveRow(row)"
        >
          <Save v-if="!row.isSaving" class="w-4 h-4" />
          <span>Simpan Data Jam {{ row.interval }}</span>
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Table, Save, Loader2, Info, Clock, UserCheck } from 'lucide-vue-next';
import Button from '@/Components/Shared/Button.vue';
import { usePermission } from '@/composables/usePermission';
import { differs, syncRows, mapBatchErrors } from '@/utils/currentMatrix';

const props = defineProps({
  feeders: {
    type: Array,
    required: true,
  },
  matrix: {
    type: Array,
    required: true,
  },
  selectedDate: {
    type: String,
    required: true,
  },
  selectedShift: {
    type: String,
    required: true,
  },
});

const emit = defineEmits(['dirty-change']);

const page = usePage();
const { can } = usePermission();
const canInput = computed(() => can('monitoring_arus.input'));
// Operator kosong -> server memakai nama akun yang login
const operatorPlaceholder = computed(() => page.props.auth?.user?.name || 'Nama Operator');

// ---------- State baris & deteksi perubahan (BUG-01) ----------
// Logika sinkronisasi ada di utils/currentMatrix.js (diuji di tests/Frontend/currentMatrix.test.mjs)

const matrixData = ref([]);
let loadedContext = null;

const syncFromServer = () => {
  const context = `${props.selectedDate}|${props.selectedShift}`;
  matrixData.value = syncRows(matrixData.value, props.matrix, props.feeders, context === loadedContext);
  loadedContext = context;
};

watch(() => props.matrix, syncFromServer, { immediate: true });

// Baris "dirty" = isian berbeda dari data server terakhir
const isDirty = (row) => differs(row, row.server, props.feeders);
const dirtyRows = computed(() => matrixData.value.filter(isDirty));
const dirtyCount = computed(() => dirtyRows.value.length);
watch(dirtyCount, (count) => emit('dirty-change', count), { immediate: true });

// ---------- Error per baris ----------

const fieldError = (row, feederId) => row.errors[`values.${feederId}`];
const rowErrorMessages = (row) => [...new Set(Object.values(row.errors))];

// ---------- Simpan ----------

const isSavingAll = ref(false);
const anySaving = computed(() => isSavingAll.value || matrixData.value.some((row) => row.isSaving));

const payloadOf = (row) => ({
  time_interval: row.interval,
  operator_name: row.operatorName,
  values: row.formValues,
});

const saveRow = (row) => {
  row.isSaving = true;
  row.errors = {};

  router.post(
    '/monitoring-arus',
    { date: props.selectedDate, shift: props.selectedShift, ...payloadOf(row) },
    {
      preserveScroll: true,
      preserveState: true,
      onError: (errors) => {
        row.errors = errors;
      },
      onFinish: () => {
        row.isSaving = false;
      },
    }
  );
};

const saveAll = () => {
  const rows = dirtyRows.value;
  if (rows.length === 0) return;

  isSavingAll.value = true;
  rows.forEach((row) => {
    row.isSaving = true;
    row.errors = {};
  });

  router.post(
    '/monitoring-arus/batch',
    { date: props.selectedDate, shift: props.selectedShift, rows: rows.map(payloadOf) },
    {
      preserveScroll: true,
      preserveState: true,
      onError: (errors) => {
        // Kunci error: rows.<i>.values.<feederId> -> error milik baris ke-i
        mapBatchErrors(errors, rows.length).forEach((rowErrors, i) => {
          rows[i].errors = rowErrors;
        });
      },
      onFinish: () => {
        isSavingAll.value = false;
        rows.forEach((row) => (row.isSaving = false));
      },
    }
  );
};
</script>
