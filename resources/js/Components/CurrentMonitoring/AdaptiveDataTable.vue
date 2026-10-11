<template>
  <div class="space-y-6">
    <!-- Desktop Data Table (>= 768px) -->
    <div class="hidden md:block border rounded-lg overflow-hidden bg-surface border-line">
      <div class="px-5 py-3 border-b flex items-center justify-between gap-4 border-line">
        <div class="min-w-0">
          <h2 class="text-section font-semibold text-fg">Beban arus per interval</h2>
          <p class="text-caption text-fg-muted">
            Ampere untuk {{ feeders.length }} penyulang × {{ matrixData.length }} interval
          </p>
        </div>

        <div v-if="canInput" class="flex items-center gap-3 shrink-0">
          <p v-if="dirtyCount === 0" class="text-caption text-fg-muted">
            Simpan per baris, atau ubah beberapa baris lalu Simpan semua
          </p>
          <template v-else>
            <span class="text-label font-medium text-warn">
              {{ dirtyCount }} baris belum disimpan
            </span>
            <Button :loading="isSavingAll" :disabled="anySaving" @click="saveAll">
              <Save v-if="!isSavingAll" class="w-3.5 h-3.5" />
              <span>Simpan semua ({{ dirtyCount }})</span>
            </Button>
          </template>
        </div>
      </div>

      <!-- Matrix Table Scroll Container -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-caption border-collapse">
          <thead>
            <tr class="border-b text-label font-medium bg-surface-muted text-fg-muted border-line">
              <th class="py-3 px-3 w-20 text-center sticky left-0 z-10 border-r bg-surface-muted border-line">
                Jam
              </th>
              <th
                v-for="feeder in feeders"
                :key="feeder.id"
                class="py-3 px-2 text-center border-r min-w-[90px] border-line"
              >
                <div class="font-medium text-fg truncate" :title="feeder.name">{{ feeder.name }}</div>
                <div class="text-caption font-normal font-mono text-fg-muted">{{ feeder.code }}</div>
              </th>
              <th class="py-3 px-3 min-w-[140px] text-center border-r border-line">
                Operator
              </th>
              <th class="py-3 px-3 min-w-[190px] text-center border-r border-line">
                Terakhir diubah
              </th>
              <th v-if="canInput" class="py-3 px-3 w-24 text-center sticky right-0 z-10 bg-surface-muted">
                Aksi
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-line">
            <template v-for="row in matrixData" :key="row.interval">
              <tr
                :class="[
                  'transition-colors group',
                  isDirty(row) ? 'bg-warn-soft' : 'hover:bg-surface-muted',
                ]"
              >
                <!-- Time Interval Column -->
                <td
                  :class="[
                    'py-2.5 px-3 text-center sticky left-0 z-10 border-r text-body tabular-nums font-medium transition-colors',
                    'border-line text-fg',
                    // Baris belum disimpan = perlu perhatian (warn), bukan warna modul
                    isDirty(row) ? 'bg-warn-soft border-l-[3px] border-l-warn' : 'bg-surface group-hover:bg-surface-muted',
                  ]"
                >
                  {{ row.interval }}
                  <span v-if="isDirty(row)" class="sr-only">(belum disimpan)</span>
                </td>

                <!-- Feeder Inputs -->
                <td
                  v-for="feeder in feeders"
                  :key="feeder.id"
                  class="py-1.5 px-1 text-center border-r border-line"
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
                      'w-full text-center py-1 px-1 rounded-md border text-body tabular-nums focus:outline-none focus:ring-2 disabled:opacity-70 disabled:cursor-not-allowed',
                      'bg-surface text-fg placeholder:text-fg-subtle',
                      fieldError(row, feeder.id)
                        ? 'border-crit focus:border-crit focus:ring-crit/30'
                        : 'border-line focus:border-brand focus:ring-brand/20',
                    ]"
                  />
                </td>

                <!-- Operator Input -->
                <td class="py-1.5 px-2 text-center border-r border-line">
                  <input
                    type="text"
                    v-model="row.operatorName"
                    :placeholder="operatorPlaceholder"
                    :disabled="!canInput || row.isSaving"
                    :aria-label="`Operator jam ${row.interval}`"
                    :title="`Kosongkan untuk memakai nama akun (${operatorPlaceholder})`"
                    class="w-full py-1 px-2 rounded-md border text-body focus:outline-none focus:ring-2 disabled:opacity-70 disabled:cursor-not-allowed bg-surface border-line text-fg focus:border-brand focus:ring-brand/20 placeholder:text-fg-subtle"
                  />
                </td>

                <!-- Auto-Audit Trail Output Column -->
                <td class="py-2 px-3 text-center border-r tabular-nums text-caption border-line text-fg-muted">
                  {{ row.last_modified !== '-' ? row.last_modified : '—' }}
                </td>

                <!-- Save Button Column -->
                <td
                  v-if="canInput"
                  :class="[
                    'py-1.5 px-2 text-center sticky right-0 z-10 transition-colors',
                    isDirty(row) ? 'bg-warn-soft' : 'bg-surface group-hover:bg-surface-muted',
                  ]"
                >
                  <button
                    type="button"
                    @click="saveRow(row)"
                    :disabled="row.isSaving || isSavingAll"
                    :class="[
                      'w-full min-h-8 py-1 px-2 rounded-md text-label font-medium transition-colors flex items-center justify-center gap-1',
                      'focus:outline-none focus-visible:ring-2 focus-visible:ring-brand disabled:opacity-60 disabled:cursor-not-allowed',
                      // Baris yang diubah = tombol utama; baris yang sudah tersimpan = tombol netral
                      isDirty(row)
                        ? 'bg-brand text-on-solid enabled:hover:bg-brand-hover'
                        : 'bg-surface border border-line text-fg-muted enabled:hover:bg-surface-muted',
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
                  class="py-2 px-3 text-caption bg-crit-soft text-crit"
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
      <p class="text-label text-fg-muted">
        {{ matrixData.length }} interval · shift {{ selectedShift }}
      </p>

      <!-- Simpan Semua (mobile): menempel di bawah header saat halaman di-scroll -->
      <div
        v-if="canInput && dirtyCount > 0"
        class="sticky top-14 z-20 p-3 rounded-lg border border-line border-l-[3px] border-l-warn flex items-center justify-between gap-3 bg-surface"
      >
        <span class="text-label font-medium text-warn">{{ dirtyCount }} baris belum disimpan</span>
        <Button :loading="isSavingAll" :disabled="anySaving" @click="saveAll">
          <span>Simpan semua</span>
        </Button>
      </div>

      <div
        v-for="row in matrixData"
        :key="'mobile-' + row.interval"
        :class="[
          'border rounded-lg p-4 space-y-3 bg-surface border-line',
          isDirty(row) ? 'border-l-[3px] border-l-warn' : '',
        ]"
      >
        <!-- Card Header -->
        <div class="flex items-center justify-between gap-2 border-b pb-2.5 border-line">
          <div class="flex items-center gap-2">
            <span class="text-section font-semibold tabular-nums text-fg">{{ row.interval }}</span>
            <span v-if="isDirty(row)" class="text-caption font-medium text-warn">Belum disimpan</span>
          </div>

          <!-- Operator input -->
          <div class="w-36">
            <input
              type="text"
              v-model="row.operatorName"
              :placeholder="operatorPlaceholder"
              :disabled="!canInput || row.isSaving"
              :aria-label="`Operator jam ${row.interval}`"
              class="w-full text-right py-1.5 px-2 rounded-md border text-body focus:outline-none disabled:opacity-70 bg-surface border-line text-fg focus:border-brand placeholder:text-fg-subtle"
            />
          </div>
        </div>

        <!-- Audit Trail Info Mobile -->
        <p class="text-caption text-fg-muted tabular-nums">Terakhir diubah: {{ row.last_modified !== '-' ? row.last_modified : '—' }}</p>

        <!-- Feeders Grid Inputs Mobile -->
        <div class="grid grid-cols-2 gap-2 pt-1">
          <label
            v-for="feeder in feeders"
            :key="'m-' + feeder.id"
            class="block space-y-1"
          >
            <span class="flex items-center justify-between gap-2 text-caption text-fg-muted">
              <span class="truncate" :title="feeder.name">{{ feeder.name }}</span>
              <span>A</span>
            </span>
            <input
              type="number"
              step="0.01"
              min="0"
              inputmode="decimal"
              v-model="row.formValues[feeder.id]"
              placeholder="—"
              :disabled="!canInput || row.isSaving"
              :aria-invalid="!!fieldError(row, feeder.id) || undefined"
              :class="[
                'w-full text-center py-2 px-2 rounded-md border tabular-nums text-body focus:outline-none focus:ring-2 focus:ring-brand/20 disabled:opacity-70',
                'bg-surface text-fg',
                fieldError(row, feeder.id) ? 'border-crit' : 'border-line focus:border-brand',
              ]"
            />
          </label>
        </div>

        <p v-if="rowErrorMessages(row).length" role="alert" class="text-caption text-crit">
          Gagal disimpan: {{ rowErrorMessages(row).join(' ') }}
        </p>

        <!-- Save Button Mobile -->
        <Button
          v-if="canInput"
          :variant="isDirty(row) ? 'primary' : 'secondary'"
          class="w-full"
          :loading="row.isSaving"
          :disabled="isSavingAll"
          @click="saveRow(row)"
        >
          <Save v-if="!row.isSaving" class="w-4 h-4" />
          <span>Simpan jam {{ row.interval }}</span>
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Save, Loader2 } from 'lucide-vue-next';
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
