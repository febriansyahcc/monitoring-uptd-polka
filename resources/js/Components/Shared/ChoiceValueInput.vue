<template>
  <div class="space-y-2">
    <div class="flex items-center justify-between">
      <label class="font-bold text-slate-700 dark:text-slate-300 text-xs">
        {{ label }}
      </label>
      <span v-if="unit" class="text-[10px] font-mono text-slate-400">{{ unit }}</span>
    </div>

    <!-- Tampilan Langsung (Direct Grid) jika pilihan <= 6 (BUG-17) -->
    <div v-if="options.length <= 6" class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
      <div
        v-for="option in options"
        :key="option.key"
        class="flex flex-col gap-1 p-2.5 rounded-xl border bg-slate-50/50 border-slate-200 dark:bg-slate-900/50 dark:border-slate-800 focus-within:border-cyan-500/50 transition-colors"
      >
        <div class="flex items-center justify-between">
          <label :for="`choice-${option.key}`" class="text-[11px] font-semibold text-slate-600 dark:text-slate-400 truncate">
            {{ option.label }}
          </label>
          <span v-if="isFilled(option.key)" class="text-[10px] text-cyan-600 dark:text-cyan-400 font-bold">✓</span>
        </div>
        <input
          :id="`choice-${option.key}`"
          type="number"
          step="0.01"
          v-model.number="values[option.key]"
          placeholder="0.00"
          class="w-full px-2.5 py-1.5 rounded-lg border font-mono text-sm text-right focus:outline-none focus:ring-1 focus:ring-cyan-500 bg-white border-slate-200 text-slate-900 dark:bg-slate-950 dark:border-slate-700 dark:text-white"
        />
      </div>
    </div>

    <!-- Dropdown Pilih-Lalu-Isi (Fallback jika pilihan > 6) -->
    <div v-else class="space-y-2">
      <div class="flex items-center gap-2">
        <select
          v-model="selectedKey"
          class="flex-1 min-w-0 p-2.5 rounded-xl border focus:outline-none focus:ring-1 focus:ring-cyan-500 bg-slate-50 border-slate-200 text-slate-900 dark:bg-slate-950 dark:border-slate-800 dark:text-white"
        >
          <option value="">— Pilih —</option>
          <option v-for="(option, index) in options" :key="option.key" :value="option.key">
            {{ index + 1 }}. {{ option.label }}{{ isFilled(option.key) ? ' ✓' : '' }}
          </option>
        </select>

        <input
          v-if="selectedKey"
          ref="valueInput"
          type="number"
          step="0.01"
          v-model.number="values[selectedKey]"
          placeholder="Nilai"
          class="w-28 shrink-0 p-2.5 rounded-xl border font-mono text-right focus:outline-none focus:ring-1 focus:ring-cyan-500 bg-white border-cyan-400 text-slate-900 dark:bg-slate-950 dark:border-cyan-500/50 dark:text-white"
        />
      </div>

      <!-- Nilai yang sudah terisi -->
      <div v-if="filledOptions.length" class="flex flex-wrap gap-1.5">
        <span
          v-for="option in filledOptions"
          :key="option.key"
          :class="[
            'inline-flex items-center gap-1 pl-2 pr-1 py-0.5 rounded-lg border text-[10px] font-mono cursor-pointer',
            selectedKey === option.key
              ? 'bg-cyan-500/15 border-cyan-500/50 text-cyan-500'
              : 'bg-slate-100 border-slate-200 text-slate-700 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300'
          ]"
          @click="selectedKey = option.key"
        >
          <span>{{ option.label }}: <b>{{ values[option.key] }}</b></span>
          <button type="button" class="p-0.5 rounded hover:text-rose-500" title="Kosongkan" @click.stop="clear(option.key)">
            <X class="w-3 h-3" />
          </button>
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { X } from 'lucide-vue-next';

const props = defineProps({
  label: { type: String, required: true },
  unit: { type: String, default: '' },
  // [{ key, label }] — key adalah nama field di `values`
  options: { type: Array, required: true },
  // Objek form reaktif milik parent; nilai tiap choice disimpan di values[key]
  values: { type: Object, required: true },
});

const selectedKey = ref('');
const valueInput = ref(null);

const isFilled = (key) => props.values[key] !== null && props.values[key] !== undefined && props.values[key] !== '';

const filledOptions = computed(() => props.options.filter(option => isFilled(option.key)));

const clear = (key) => {
  props.values[key] = null;
  if (selectedKey.value === key) selectedKey.value = '';
};

watch(selectedKey, async (key) => {
  if (!key) return;
  await nextTick();
  valueInput.value?.focus();
});
</script>
