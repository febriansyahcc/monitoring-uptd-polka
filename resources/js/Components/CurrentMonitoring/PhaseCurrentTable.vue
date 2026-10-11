<template>
  <div
    :class="[
      'border rounded-lg overflow-hidden transition-colors',
      'bg-surface border-line'
    ]"
  >
    <!-- Card Header -->
    <div
      :class="[
        'p-4 sm:p-5 border-b flex flex-col lg:flex-row lg:items-center justify-between gap-3',
        'border-line'
      ]"
    >
      <div class="flex items-center gap-3">
        <div>
          <h2 class="text-section font-semibold text-fg">
            Arus per fasa (R / S / T)
          </h2>
          <p :class="['text-caption', 'text-fg-muted']">
            Terisi otomatis dari tabel beban penyulang
          </p>
        </div>
      </div>

      <p class="text-caption text-fg-muted max-w-md">Nilai = arus penyulang dikurangi angka pada header fasa (tanpa pengurangan = sama)</p>
    </div>

    <!-- Desktop Table (>= 768px) -->
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-body border-collapse">
        <thead>
          <tr
            :class="[
              'border-b text-label font-medium text-center',
              'bg-surface-muted text-fg-muted border-line'
            ]"
          >
            <th
              rowspan="2"
              :class="['py-2 px-3 w-20 sticky left-0 z-10 border-r', 'bg-surface border-line']"
            >
              Jam
            </th>
            <th
              v-for="feeder in phaseFeeders"
              :key="feeder.id"
              colspan="3"
              :class="['py-2 px-2 border-r', 'text-fg-muted border-line']"
            >
              {{ feeder.label }}
            </th>
            <th rowspan="2" class="py-2 px-3 min-w-[190px]">Terakhir diubah</th>
          </tr>
          <tr
            :class="[
              'border-b text-label font-medium text-center text-caption',
              'bg-surface-muted text-fg-muted border-line'
            ]"
          >
            <template v-for="feeder in phaseFeeders" :key="'ph-' + feeder.id">
              <th
                v-for="phase in phases"
                :key="feeder.id + phase"
                :class="['py-1.5 px-2 min-w-[64px]', phase === 'T' ? ('border-r border-line') : '']"
              >
                {{ phase }}
                <span v-if="feeder.reductions[phase]" class="text-fg-muted font-normal tabular-nums">−{{ feeder.reductions[phase] }}</span>
              </th>
            </template>
          </tr>
        </thead>
        <tbody :class="['divide-y', 'divide-line']">
          <tr
            v-for="row in phaseMatrix"
            :key="row.interval"
            :class="['transition-colors group', 'hover:bg-surface-muted']"
          >
            <td
              :class="[
                'py-2 px-3 text-center sticky left-0 z-10 border-r',
                'bg-surface border-line'
              ]"
            >
              {{ row.interval }}
            </td>
            <template v-for="feeder in phaseFeeders" :key="row.interval + '-' + feeder.id">
              <td
                v-for="phase in phases"
                :key="row.interval + feeder.id + phase"
                :class="[
                  'py-2 px-2 text-center tabular-nums',
                  phase === 'T' ? ('border-r border-line') : '',
                  row.values[feeder.id][phase] === null ? 'text-fg-muted' : 'text-fg'
                ]"
              >
                {{ formatValue(row.values[feeder.id][phase]) }}
              </td>
            </template>
            <td class="py-2 px-3 text-center tabular-nums text-caption text-fg-muted">
              {{ row.last_modified }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Mobile Cards (< 768px) -->
    <div class="block md:hidden p-4 space-y-3">
      <div
        v-for="row in filledRows"
        :key="'m-phase-' + row.interval"
        :class="['border rounded-lg p-3 space-y-2', 'bg-surface border-line']"
      >
        <div class="flex items-center justify-between">
          <span class="tabular-nums font-semibold text-body text-fg-muted">Jam {{ row.interval }}</span>
          <span class="text-caption tabular-nums text-fg-muted truncate ml-2">{{ row.last_modified }}</span>
        </div>
        <div
          v-for="feeder in phaseFeeders"
          :key="'m-' + row.interval + feeder.id"
          class="grid grid-cols-4 gap-1 text-caption items-center"
        >
          <span :class="['font-semibold truncate', 'text-fg-muted']">{{ feeder.label }}</span>
          <span v-for="phase in phases" :key="phase" class="text-center tabular-nums">
            <span class="text-caption text-fg-muted">{{ phase }}</span>
            {{ formatValue(row.values[feeder.id][phase]) }}
          </span>
        </div>
      </div>

      <p v-if="filledRows.length === 0" class="py-6 text-center text-fg-muted text-caption">
        Belum ada data arus penyulang pada shift ini.
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  phaseFeeders: { type: Array, required: true },
  phaseMatrix: { type: Array, required: true },
});

const phases = ['R', 'S', 'T'];

// Di mobile hanya tampilkan jam yang sudah ada data arusnya
const filledRows = computed(() =>
  props.phaseMatrix.filter(row =>
    props.phaseFeeders.some(feeder => phases.some(phase => row.values[feeder.id][phase] !== null))
  )
);

const formatValue = (value) => (value === null ? '-' : value.toLocaleString('id-ID'));
</script>
