<template>
  <div class="space-y-6">
    <!-- Desktop Data Table (>= 768px) -->
    <div
      :class="[
        'hidden md:block border rounded-2xl overflow-hidden shadow-sm transition-colors duration-300',
        isDarkMode
          ? 'bg-slate-900 border-slate-800'
          : 'bg-white border-slate-200'
      ]"
    >
      <div
        :class="[
          'p-4 sm:p-5 border-b flex items-center justify-between',
          isDarkMode ? 'border-slate-800 bg-slate-900/60' : 'border-slate-100 bg-slate-50/80'
        ]"
      >
        <div class="flex items-center gap-3">
          <div
            :class="[
              'w-8 h-8 rounded-lg border flex items-center justify-center',
              isDarkMode ? 'bg-cyan-500/10 border-cyan-500/30 text-cyan-400' : 'bg-cyan-50 border-cyan-200 text-cyan-600'
            ]"
          >
            <Table class="w-4 h-4" />
          </div>
          <div>
            <h3 :class="['text-sm font-bold tracking-wide', isDarkMode ? 'text-white' : 'text-slate-900']">
              Tabel Matriks Beban Arus Listrik (Per Jam)
            </h3>
            <p :class="['text-xs', isDarkMode ? 'text-slate-400' : 'text-slate-500']">
              Input nilai Ampere untuk 12 jalur feeder pada tiap interval jam shift
            </p>
          </div>
        </div>

        <div
          :class="[
            'flex items-center gap-2 text-xs px-3 py-1.5 rounded-lg border font-medium',
            isDarkMode ? 'text-slate-400 bg-slate-950 border-slate-800' : 'text-slate-600 bg-white border-slate-200'
          ]"
        >
          <Info class="w-3.5 h-3.5 text-cyan-500" />
          <span>Tekan tombol <b>Simpan</b> pada baris jam yang bersangkutan untuk mengupdate data & grafik</span>
        </div>
      </div>

      <!-- Matrix Table Scroll Container -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr
              :class="[
                'border-b font-semibold uppercase tracking-wider',
                isDarkMode ? 'bg-slate-950/80 text-slate-300 border-slate-800' : 'bg-slate-100/90 text-slate-700 border-slate-200'
              ]"
            >
              <th
                :class="[
                  'py-3 px-3 w-20 text-center sticky left-0 z-10 border-r',
                  isDarkMode ? 'bg-slate-950 border-slate-800' : 'bg-slate-100 border-slate-200'
                ]"
              >
                Jam
              </th>
              <th
                v-for="feeder in feeders"
                :key="feeder.id"
                :class="[
                  'py-3 px-2 text-center border-r min-w-[90px]',
                  isDarkMode ? 'border-slate-800/60' : 'border-slate-200/80'
                ]"
              >
                <div :class="['font-bold text-[11px] truncate', isDarkMode ? 'text-cyan-400' : 'text-cyan-700']" :title="feeder.name">
                  {{ feeder.name }}
                </div>
                <div :class="['text-[9px] font-normal lowercase tracking-normal', isDarkMode ? 'text-slate-500' : 'text-slate-400']">
                  {{ feeder.code }}
                </div>
              </th>
              <th :class="['py-3 px-3 min-w-[140px] text-center border-r', isDarkMode ? 'border-slate-800' : 'border-slate-200']">
                Operator
              </th>
              <th :class="['py-3 px-3 min-w-[190px] text-center border-r', isDarkMode ? 'border-slate-800' : 'border-slate-200']">
                Last Modified (Audit Trail)
              </th>
              <th
                :class="[
                  'py-3 px-3 w-24 text-center sticky right-0 z-10',
                  isDarkMode ? 'bg-slate-950' : 'bg-slate-100'
                ]"
              >
                Aksi
              </th>
            </tr>
          </thead>
          <tbody :class="['divide-y', isDarkMode ? 'divide-slate-800/60' : 'divide-slate-200/60']">
            <tr
              v-for="row in matrixData"
              :key="row.interval"
              :class="[
                'transition-colors group',
                isDarkMode ? 'hover:bg-slate-850/50' : 'hover:bg-slate-50'
              ]"
            >
              <!-- Time Interval Column -->
              <td
                :class="[
                  'py-2.5 px-3 font-bold text-center sticky left-0 z-10 border-r text-xs transition-colors',
                  isDarkMode
                    ? 'bg-slate-900 group-hover:bg-slate-850 border-slate-800 text-slate-200'
                    : 'bg-white group-hover:bg-slate-50 border-slate-200 text-slate-800'
                ]"
              >
                <span
                  :class="[
                    'px-2 py-1 rounded font-mono border',
                    isDarkMode
                      ? 'bg-slate-800/80 text-cyan-400 border-slate-700/60'
                      : 'bg-cyan-50 text-cyan-700 border-cyan-200 font-bold'
                  ]"
                >
                  {{ row.interval }}
                </span>
              </td>

              <!-- Feeder Inputs (12 Jalur) -->
              <td
                v-for="feeder in feeders"
                :key="feeder.id"
                :class="['py-1.5 px-1 text-center border-r', isDarkMode ? 'border-slate-800/40' : 'border-slate-200/50']"
              >
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  v-model="row.formValues[feeder.id]"
                  placeholder="-"
                  :class="[
                    'w-full text-center py-1 px-1 rounded border text-xs font-mono font-medium focus:outline-none focus:ring-1 transition-all',
                    isDarkMode
                      ? 'bg-slate-950/80 border-slate-800 text-slate-100 focus:border-cyan-500 focus:ring-cyan-500 placeholder:text-slate-600'
                      : 'bg-slate-50 border-slate-200 text-slate-900 focus:border-cyan-500 focus:ring-cyan-500 placeholder:text-slate-400 font-bold'
                  ]"
                />
              </td>

              <!-- Operator Input -->
              <td :class="['py-1.5 px-2 text-center border-r', isDarkMode ? 'border-slate-800' : 'border-slate-200']">
                <input
                  type="text"
                  v-model="row.operatorName"
                  placeholder="Nama Operator"
                  :class="[
                    'w-full py-1 px-2 rounded border text-xs focus:outline-none focus:ring-1 transition-all',
                    isDarkMode
                      ? 'bg-slate-950/80 border-slate-800 text-slate-200 focus:border-cyan-500 focus:ring-cyan-500 placeholder:text-slate-600'
                      : 'bg-slate-50 border-slate-200 text-slate-800 focus:border-cyan-500 focus:ring-cyan-500 placeholder:text-slate-400 font-medium'
                  ]"
                />
              </td>

              <!-- Auto-Audit Trail Output Column -->
              <td :class="['py-2 px-3 text-center border-r font-mono text-[10px]', isDarkMode ? 'border-slate-800 text-slate-400' : 'border-slate-200 text-slate-600']">
                <span
                  v-if="row.last_modified !== '-'"
                  :class="[
                    'inline-flex items-center gap-1 px-2 py-1 rounded border',
                    isDarkMode
                      ? 'bg-slate-800/80 text-slate-300 border-slate-700/50'
                      : 'bg-slate-100 text-slate-700 border-slate-200 font-medium'
                  ]"
                >
                  <Clock class="w-3 h-3 text-cyan-500 shrink-0" />
                  <span>{{ row.last_modified }}</span>
                </span>
                <span v-else :class="['font-sans italic text-[11px]', isDarkMode ? 'text-slate-600' : 'text-slate-400']">-</span>
              </td>

              <!-- Save Button Column -->
              <td
                :class="[
                  'py-1.5 px-2 text-center sticky right-0 z-10 transition-colors',
                  isDarkMode
                    ? 'bg-slate-900 group-hover:bg-slate-850'
                    : 'bg-white group-hover:bg-slate-50'
                ]"
              >
                <button
                  @click="saveRow(row)"
                  :disabled="row.isSaving"
                  :class="[
                    'w-full py-1.5 px-2 rounded-lg font-medium text-xs transition-all shadow-md flex items-center justify-center gap-1 text-white',
                    isDarkMode
                      ? 'bg-cyan-600 hover:bg-cyan-500 disabled:bg-slate-800'
                      : 'bg-cyan-600 hover:bg-cyan-700 disabled:bg-slate-200'
                  ]"
                >
                  <Save class="w-3.5 h-3.5" v-if="!row.isSaving" />
                  <Loader2 class="w-3.5 h-3.5 animate-spin" v-else />
                  <span>Simpan</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Mobile Card View (< 768px) -->
    <div class="block md:hidden space-y-4">
      <div
        :class="[
          'p-3 border rounded-xl flex items-center justify-between',
          isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200 shadow-sm'
        ]"
      >
        <div class="flex items-center gap-2">
          <Clock class="w-4 h-4 text-cyan-500" />
          <span :class="['text-xs font-bold', isDarkMode ? 'text-white' : 'text-slate-900']">
            Daftar Interval Jam (Shift {{ selectedShift }})
          </span>
        </div>
        <span :class="['text-[10px]', isDarkMode ? 'text-slate-400' : 'text-slate-500']">16 Interval</span>
      </div>

      <div
        v-for="row in matrixData"
        :key="'mobile-' + row.interval"
        :class="[
          'border rounded-2xl p-4 shadow-sm space-y-3 transition-colors duration-300',
          isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'
        ]"
      >
        <!-- Card Header -->
        <div :class="['flex items-center justify-between border-b pb-2.5', isDarkMode ? 'border-slate-800' : 'border-slate-100']">
          <div class="flex items-center gap-2">
            <span
              :class="[
                'px-2.5 py-1 rounded-lg font-mono font-bold text-sm border',
                isDarkMode
                  ? 'bg-cyan-500/10 border-cyan-500/30 text-cyan-400'
                  : 'bg-cyan-50 border-cyan-200 text-cyan-700'
              ]"
            >
              Jam {{ row.interval }}
            </span>
          </div>

          <!-- Operator input -->
          <div class="w-36">
            <input
              type="text"
              v-model="row.operatorName"
              placeholder="Operator"
              :class="[
                'w-full text-right py-1 px-2 rounded-lg border text-xs focus:outline-none',
                isDarkMode
                  ? 'bg-slate-950 border-slate-800 text-slate-200 focus:border-cyan-500 placeholder:text-slate-600'
                  : 'bg-slate-50 border-slate-200 text-slate-800 focus:border-cyan-500 placeholder:text-slate-400'
              ]"
            />
          </div>
        </div>

        <!-- Audit Trail Info Mobile -->
        <div
          :class="[
            'text-[10px] flex items-center gap-1.5 p-2 rounded-lg border',
            isDarkMode ? 'text-slate-400 bg-slate-950/60 border-slate-800/80' : 'text-slate-600 bg-slate-50 border-slate-200'
          ]"
        >
          <UserCheck class="w-3 h-3 text-cyan-500 shrink-0" />
          <span class="font-mono">Last Modified: {{ row.last_modified }}</span>
        </div>

        <!-- Feeders Grid Inputs Mobile -->
        <div class="grid grid-cols-2 gap-2 pt-1">
          <div
            v-for="feeder in feeders"
            :key="'m-' + feeder.id"
            :class="[
              'p-2 rounded-xl border space-y-1',
              isDarkMode ? 'bg-slate-950 border-slate-800/80' : 'bg-slate-50 border-slate-200'
            ]"
          >
            <div class="flex items-center justify-between text-[10px]">
              <span :class="['font-bold truncate', isDarkMode ? 'text-slate-300' : 'text-slate-700']" :title="feeder.name">
                {{ feeder.name }}
              </span>
              <span :class="['font-mono', isDarkMode ? 'text-slate-500' : 'text-slate-400']">Ampere</span>
            </div>
            <input
              type="number"
              step="0.01"
              min="0"
              v-model="row.formValues[feeder.id]"
              placeholder="0.00"
              :class="[
                'w-full text-center py-1 px-2 rounded border font-mono font-bold text-xs focus:outline-none',
                isDarkMode
                  ? 'bg-slate-900 border-slate-800 text-cyan-400 focus:border-cyan-500'
                  : 'bg-white border-slate-200 text-cyan-700 focus:border-cyan-500'
              ]"
            />
          </div>
        </div>

        <!-- Save Button Mobile -->
        <button
          @click="saveRow(row)"
          :disabled="row.isSaving"
          class="w-full py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 active:scale-[0.99] text-white font-bold text-xs transition-all shadow-md flex items-center justify-center gap-2"
        >
          <Save class="w-4 h-4" v-if="!row.isSaving" />
          <Loader2 class="w-4 h-4 animate-spin" v-else />
          <span>Simpan Data Jam {{ row.interval }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, inject } from 'vue';
import { router } from '@inertiajs/vue3';
import { Table, Save, Loader2, Info, Clock, UserCheck } from 'lucide-vue-next';

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
  isDarkMode: {
    type: Boolean,
    default: false,
  },
});

const isDarkModeRef = inject('isDarkMode', ref(props.isDarkMode));

const matrixData = ref([]);

const initMatrixData = () => {
  matrixData.value = props.matrix.map(item => {
    const formValues = {};
    props.feeders.forEach(f => {
      formValues[f.id] = item.values[f.id] !== null && item.values[f.id] !== undefined ? item.values[f.id] : '';
    });

    return {
      interval: item.interval,
      formValues: formValues,
      operatorName: item.operator_name || '',
      last_modified: item.last_modified || '-',
      isSaving: false,
    };
  });
};

watch(() => props.matrix, () => {
  initMatrixData();
}, { immediate: true, deep: true });

const saveRow = (row) => {
  row.isSaving = true;

  router.post(
    '/monitoring-arus',
    {
      date: props.selectedDate,
      shift: props.selectedShift,
      time_interval: row.interval,
      operator_name: row.operatorName,
      values: row.formValues,
    },
    {
      preserveScroll: true,
      onFinish: () => {
        row.isSaving = false;
      },
    }
  );
};
</script>
