<template>
  <div
    :class="[
      'border rounded-2xl overflow-hidden shadow-sm transition-colors duration-300',
      isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'
    ]"
  >
    <!-- Card Header -->
    <div
      :class="[
        'p-4 sm:p-5 border-b flex flex-col lg:flex-row lg:items-center justify-between gap-3',
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
          <Waves class="w-4 h-4" />
        </div>
        <div>
          <h3 :class="['text-sm font-bold tracking-wide', isDarkMode ? 'text-white' : 'text-slate-900']">
            Tabel Arus Tiap Fasa (R / S / T)
          </h3>
          <p :class="['text-xs', isDarkMode ? 'text-slate-400' : 'text-slate-500']">
            Terisi otomatis dari tabel Beban & Arus Penyulang
          </p>
        </div>
      </div>

      <div
        :class="[
          'flex items-center gap-2 text-xs px-3 py-1.5 rounded-lg border font-medium',
          isDarkMode ? 'text-slate-400 bg-slate-950 border-slate-800' : 'text-slate-600 bg-white border-slate-200'
        ]"
      >
        <Info class="w-3.5 h-3.5 text-cyan-500 shrink-0" />
        <span>Nilai = arus penyulang dikurangi angka pada header fasa (tanpa pengurangan = sama)</span>
      </div>
    </div>

    <!-- Desktop Table (>= 768px) -->
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-xs border-collapse">
        <thead>
          <tr
            :class="[
              'border-b font-semibold uppercase tracking-wider text-center',
              isDarkMode ? 'bg-slate-950/80 text-slate-300 border-slate-800' : 'bg-slate-100/90 text-slate-700 border-slate-200'
            ]"
          >
            <th
              rowspan="2"
              :class="['py-2 px-3 w-20 sticky left-0 z-10 border-r', isDarkMode ? 'bg-slate-950 border-slate-800' : 'bg-slate-100 border-slate-200']"
            >
              Jam
            </th>
            <th
              v-for="feeder in phaseFeeders"
              :key="feeder.id"
              colspan="3"
              :class="['py-2 px-2 border-r', isDarkMode ? 'text-cyan-400 border-slate-800' : 'text-cyan-700 border-slate-200']"
            >
              {{ feeder.label }}
            </th>
            <th rowspan="2" class="py-2 px-3 min-w-[190px]">Last Modified</th>
          </tr>
          <tr
            :class="[
              'border-b font-semibold text-center text-[10px]',
              isDarkMode ? 'bg-slate-950/80 text-slate-400 border-slate-800' : 'bg-slate-100/90 text-slate-600 border-slate-200'
            ]"
          >
            <template v-for="feeder in phaseFeeders" :key="'ph-' + feeder.id">
              <th
                v-for="phase in phases"
                :key="feeder.id + phase"
                :class="['py-1.5 px-2 min-w-[64px]', phase === 'T' ? (isDarkMode ? 'border-r border-slate-800' : 'border-r border-slate-200') : '']"
              >
                {{ phase }}
                <span v-if="feeder.reductions[phase]" class="text-rose-500 font-mono">−{{ feeder.reductions[phase] }}</span>
              </th>
            </template>
          </tr>
        </thead>
        <tbody :class="['divide-y', isDarkMode ? 'divide-slate-800/60' : 'divide-slate-200/60']">
          <tr
            v-for="row in phaseMatrix"
            :key="row.interval"
            :class="['transition-colors group', isDarkMode ? 'hover:bg-slate-800/40' : 'hover:bg-slate-50']"
          >
            <td
              :class="[
                'py-2 px-3 text-center sticky left-0 z-10 border-r',
                isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'
              ]"
            >
              <span
                :class="[
                  'px-2 py-1 rounded font-mono font-bold border',
                  isDarkMode ? 'bg-slate-800/80 text-cyan-400 border-slate-700/60' : 'bg-cyan-50 text-cyan-700 border-cyan-200'
                ]"
              >
                {{ row.interval }}
              </span>
            </td>
            <template v-for="feeder in phaseFeeders" :key="row.interval + '-' + feeder.id">
              <td
                v-for="phase in phases"
                :key="row.interval + feeder.id + phase"
                :class="[
                  'py-2 px-2 text-center font-mono',
                  phase === 'T' ? (isDarkMode ? 'border-r border-slate-800/60' : 'border-r border-slate-200/60') : '',
                  row.values[feeder.id][phase] === null ? 'text-slate-400' : (isDarkMode ? 'text-slate-100' : 'text-slate-900 font-semibold')
                ]"
              >
                {{ formatValue(row.values[feeder.id][phase]) }}
              </td>
            </template>
            <td class="py-2 px-3 text-center font-mono text-[10px] text-slate-400">
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
        :class="['border rounded-xl p-3 space-y-2', isDarkMode ? 'bg-slate-950 border-slate-800' : 'bg-slate-50 border-slate-200']"
      >
        <div class="flex items-center justify-between">
          <span class="font-mono font-bold text-sm text-cyan-500">Jam {{ row.interval }}</span>
          <span class="text-[10px] font-mono text-slate-400 truncate ml-2">{{ row.last_modified }}</span>
        </div>
        <div
          v-for="feeder in phaseFeeders"
          :key="'m-' + row.interval + feeder.id"
          class="grid grid-cols-4 gap-1 text-xs items-center"
        >
          <span :class="['font-bold truncate', isDarkMode ? 'text-slate-300' : 'text-slate-700']">{{ feeder.label }}</span>
          <span v-for="phase in phases" :key="phase" class="text-center font-mono">
            <span class="text-[9px] text-slate-400">{{ phase }}</span>
            {{ formatValue(row.values[feeder.id][phase]) }}
          </span>
        </div>
      </div>

      <p v-if="filledRows.length === 0" class="py-6 text-center text-slate-400 text-xs italic">
        Belum ada data arus penyulang pada shift ini.
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Waves, Info } from 'lucide-vue-next';

const props = defineProps({
  phaseFeeders: { type: Array, required: true },
  phaseMatrix: { type: Array, required: true },
  isDarkMode: { type: Boolean, default: false },
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
