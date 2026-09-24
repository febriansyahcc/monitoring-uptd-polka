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
              isDarkMode ? 'bg-violet-500/10 border-violet-500/30 text-violet-400' : 'bg-violet-50 border-violet-200 text-violet-600'
            ]"
          >
            <Gauge class="w-4 h-4" />
          </div>
          <div>
            <h3 :class="['text-sm font-bold tracking-wide', isDarkMode ? 'text-white' : 'text-slate-900']">
              Control Panel
            </h3>
            <p :class="['text-xs', isDarkMode ? 'text-slate-400' : 'text-slate-500']">
              Pembacaan panel kontrol generator per jam per engine
            </p>
          </div>
        </div>

        <button
          @click="openAddModal"
          class="py-2 px-4 rounded-xl bg-violet-600 hover:bg-violet-500 text-white font-bold text-xs shadow-md transition-all flex items-center justify-center gap-1.5 self-start sm:self-auto"
        >
          <Plus class="w-4 h-4" />
          <span>Tambah Data</span>
        </button>
      </div>

      <!-- Desktop Table (>= 768px) -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-xs border-collapse">
          <thead>
            <tr
              :class="[
                'border-b font-semibold uppercase tracking-wider text-center text-[10px]',
                isDarkMode ? 'bg-slate-950/80 text-slate-300 border-slate-800' : 'bg-slate-100/90 text-slate-700 border-slate-200'
              ]"
            >
              <th rowspan="2" :class="['py-2 px-3 sticky left-0 z-10', isDarkMode ? 'bg-slate-950' : 'bg-slate-100']">Jam</th>
              <th rowspan="2" class="py-2 px-3">Engine</th>
              <th
                v-for="group in groups"
                :key="group.label"
                :colspan="group.fields.length"
                :rowspan="group.fields.length === 1 ? 2 : 1"
                :class="['py-2 px-2 border-l', isDarkMode ? 'border-slate-800' : 'border-slate-200']"
              >
                {{ group.label }}
                <span v-if="group.unit" class="block font-normal normal-case text-slate-400">({{ group.unit }})</span>
              </th>
              <th rowspan="2" :class="['py-2 px-3 min-w-[180px] border-l', isDarkMode ? 'border-slate-800' : 'border-slate-200']">Last Modified</th>
              <th rowspan="2" :class="['py-2 px-3 sticky right-0 z-10', isDarkMode ? 'bg-slate-950' : 'bg-slate-100']">Aksi</th>
            </tr>
            <tr
              :class="[
                'border-b font-semibold text-center text-[10px]',
                isDarkMode ? 'bg-slate-950/80 text-slate-400 border-slate-800' : 'bg-slate-100/90 text-slate-600 border-slate-200'
              ]"
            >
              <template v-for="group in groups" :key="'sub-' + group.label">
                <template v-if="group.fields.length > 1">
                  <th
                    v-for="(field, index) in group.fields"
                    :key="field.key"
                    :class="['py-1.5 px-2 min-w-[56px]', index === 0 ? (isDarkMode ? 'border-l border-slate-800' : 'border-l border-slate-200') : '']"
                  >
                    {{ field.label }}
                  </th>
                </template>
              </template>
            </tr>
          </thead>
          <tbody :class="['divide-y', isDarkMode ? 'divide-slate-800/60' : 'divide-slate-200/60']">
            <tr
              v-for="log in logs"
              :key="log.id"
              :class="['transition-colors group', isDarkMode ? 'hover:bg-slate-800/40' : 'hover:bg-slate-50']"
            >
              <td :class="['py-2.5 px-3 text-center sticky left-0 z-10', isDarkMode ? 'bg-slate-900' : 'bg-white']">
                <span
                  :class="[
                    'px-2 py-1 rounded font-mono font-bold border',
                    isDarkMode ? 'bg-slate-800/80 text-violet-400 border-slate-700/60' : 'bg-violet-50 text-violet-700 border-violet-200'
                  ]"
                >
                  {{ log.recorded_time }}
                </span>
              </td>
              <td class="py-2.5 px-3 font-bold whitespace-nowrap">{{ log.engine_label }}</td>
              <td
                v-for="field in allFields"
                :key="field.key"
                :class="['py-2.5 px-2 text-center font-mono', log[field.key] === null ? 'text-slate-400' : '']"
              >
                {{ formatNumber(log[field.key]) }}
              </td>
              <td class="py-2.5 px-3 text-center font-mono text-[10px] text-slate-400">{{ log.last_modified }}</td>
              <td :class="['py-2.5 px-3 text-center sticky right-0 z-10', isDarkMode ? 'bg-slate-900' : 'bg-white']">
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
              <td :colspan="allFields.length + 4" class="py-8 text-center text-slate-400 text-xs italic">
                Belum ada data Control Panel pada tanggal ini.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Cards (< 768px) -->
      <div class="block md:hidden p-4 space-y-3">
        <div
          v-for="log in logs"
          :key="'m-cp-' + log.id"
          :class="['border rounded-xl p-4 space-y-3 shadow-sm', isDarkMode ? 'bg-slate-950 border-slate-800' : 'bg-slate-50 border-slate-200']"
        >
          <div :class="['flex items-center justify-between border-b pb-2', isDarkMode ? 'border-slate-800' : 'border-slate-200']">
            <div>
              <span class="font-bold font-mono text-sm text-violet-500">{{ log.recorded_time }}</span>
              <span :class="['ml-2 text-xs font-bold', isDarkMode ? 'text-slate-200' : 'text-slate-800']">{{ log.engine_label }}</span>
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

          <div v-for="group in groups" :key="'m-' + group.label" class="text-xs">
            <span class="text-[10px] text-slate-400 block">{{ group.label }}<span v-if="group.unit"> ({{ group.unit }})</span></span>
            <div class="flex flex-wrap gap-x-3 font-mono font-semibold">
              <span v-for="field in group.fields" :key="field.key">
                <span v-if="group.fields.length > 1" class="text-slate-400 font-normal">{{ field.label }}</span>
                {{ formatNumber(log[field.key]) }}
              </span>
            </div>
          </div>

          <div :class="['text-[10px] font-mono p-2 rounded border', isDarkMode ? 'text-slate-400 bg-slate-900/40 border-slate-800' : 'text-slate-500 bg-white border-slate-200']">
            Last Modified: {{ log.last_modified }}
          </div>
        </div>

        <p v-if="logs.length === 0" class="py-6 text-center text-slate-400 text-xs italic">
          Belum ada data Control Panel pada tanggal ini.
        </p>
      </div>
    </div>

    <!-- Modal Form Add/Edit -->
    <Transition name="fade">
      <div v-if="showModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div
          :class="[
            'w-full max-w-2xl max-h-[90vh] overflow-y-auto border rounded-2xl shadow-2xl p-6 space-y-5 transition-colors',
            isDarkMode ? 'bg-slate-900 border-slate-800 text-white' : 'bg-white border-slate-200 text-slate-900'
          ]"
        >
          <div :class="['flex items-center justify-between border-b pb-3', isDarkMode ? 'border-slate-800' : 'border-slate-100']">
            <h3 class="font-bold text-base flex items-center gap-2">
              <Gauge class="w-5 h-5 text-violet-500" />
              <span>{{ isEditing ? 'Edit Data Control Panel' : 'Tambah Data Control Panel' }}</span>
            </h3>
            <button @click="closeModal" class="text-slate-400 hover:text-rose-500">
              <X class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="submitForm" class="space-y-4 text-xs">
            <div class="grid grid-cols-3 gap-3">
              <div class="space-y-1">
                <label class="font-bold text-slate-400">Tanggal</label>
                <input type="date" v-model="form.recorded_date" required :disabled="isEditing" :class="[inputClass, 'font-mono']" />
              </div>
              <div class="space-y-1">
                <label class="font-bold text-slate-400">Jam</label>
                <input type="time" v-model="form.recorded_time" required :disabled="isEditing" :class="[inputClass, 'font-mono']" />
              </div>
              <div class="space-y-1">
                <label class="font-bold text-slate-400">Engine</label>
                <select v-model="form.engine" required :disabled="isEditing" :class="inputClass">
                  <option value="" disabled>— Pilih —</option>
                  <option v-for="engine in engines" :key="engine.key" :value="engine.key">{{ engine.label }}</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div
                v-for="group in groups"
                :key="'f-' + group.label"
                :class="['p-3 rounded-xl border space-y-2', isDarkMode ? 'border-slate-800' : 'border-slate-200']"
              >
                <p class="font-bold text-[10px] uppercase tracking-wider text-violet-500">
                  {{ group.label }}<span v-if="group.unit" class="normal-case text-slate-400"> ({{ group.unit }})</span>
                </p>
                <div :class="['grid gap-2', gridCols[group.fields.length]]">
                  <div v-for="field in group.fields" :key="field.key" class="space-y-1">
                    <label v-if="group.fields.length > 1" class="font-bold text-slate-400">{{ field.label }}</label>
                    <input
                      type="number"
                      :step="field.key === 'cos_q' ? '0.001' : '0.01'"
                      min="0"
                      v-model.number="form[field.key]"
                      placeholder="-"
                      :class="[inputClass, 'font-mono text-center']"
                    />
                  </div>
                </div>
              </div>
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
                class="flex-1 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-white font-bold shadow-md flex items-center justify-center gap-2"
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
import { Gauge, Plus, Edit3, Trash2, X, Loader2 } from 'lucide-vue-next';

const props = defineProps({
  logs: { type: Array, required: true },
  engines: { type: Array, required: true },
  // [{ label, unit, fields: [{ key, label }] }]
  groups: { type: Array, required: true },
  selectedDate: { type: String, required: true },
  isDarkMode: { type: Boolean, default: false },
});

const page = usePage();
const errors = computed(() => page.props.errors || {});

const gridCols = { 1: 'grid-cols-1', 2: 'grid-cols-2', 3: 'grid-cols-3' };

const allFields = computed(() => props.groups.flatMap(group => group.fields));

const showModal = ref(false);
const isEditing = ref(false);
const isSubmitting = ref(false);

const currentTime = () => new Date().toTimeString().slice(0, 5);

const emptyForm = () => ({
  recorded_date: props.selectedDate,
  recorded_time: currentTime(),
  engine: '',
  operator_name: '',
  ...Object.fromEntries(allFields.value.map(field => [field.key, null])),
});

const form = reactive(emptyForm());

const inputClass = computed(() => [
  'w-full p-2.5 rounded-xl border focus:outline-none focus:ring-1 focus:ring-violet-500 disabled:opacity-60',
  props.isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-900',
]);

const formatNumber = (value) => (value === null || value === undefined ? '-' : value.toLocaleString('id-ID'));

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
  router.post('/monitoring-operasi-engine/control-panel', { ...form }, {
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
  if (confirm(`Hapus data Control Panel ${log.engine_label} jam ${log.recorded_time}?`)) {
    router.delete(`/monitoring-operasi-engine/control-panel/${log.id}`, { preserveScroll: true, preserveState: true });
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
