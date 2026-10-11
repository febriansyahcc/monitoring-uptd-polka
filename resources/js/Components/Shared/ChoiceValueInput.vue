<template>
  <div class="space-y-2">
    <div class="flex items-center justify-between">
      <label class="text-label font-medium text-fg-muted">
        {{ label }}
      </label>
      <span v-if="unit" class="text-caption text-fg-muted">{{ unit }}</span>
    </div>

    <!-- Tampilan Langsung (Direct Grid) jika pilihan <= 6 (BUG-17) -->
    <div v-if="options.length <= 6" class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
      <div
        v-for="option in options"
        :key="option.key"
        class="flex flex-col gap-1 p-2.5 rounded-lg border border-line bg-surface-muted focus-within:border-brand transition-colors"
      >
        <div class="flex items-center justify-between">
          <label :for="`choice-${option.key}`" class="text-caption font-medium text-fg-muted truncate">
            {{ option.label }}
          </label>
          <span v-if="isFilled(option.key)" class="text-caption text-ok" aria-label="terisi">✓</span>
        </div>
        <input
          :id="`choice-${option.key}`"
          type="number"
          step="0.01"
          v-model.number="values[option.key]"
          placeholder="0.00"
          class="w-full px-2.5 py-1.5 rounded-lg border border-line bg-surface text-fg text-body tabular-nums text-right placeholder:text-fg-subtle focus:outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
        />
      </div>
    </div>

    <!-- Dropdown Pilih-Lalu-Isi (Fallback jika pilihan > 6) -->
    <div v-else class="space-y-2">
      <div class="flex items-center gap-2">
        <select
          v-model="selectedKey"
          class="flex-1 min-w-0 px-3 py-2 rounded-lg border border-line bg-surface text-fg text-body focus:outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
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
          class="w-28 shrink-0 px-3 py-2 rounded-lg border border-brand bg-surface text-fg text-body tabular-nums text-right focus:outline-none focus:ring-2 focus:ring-brand/20"
        />
      </div>

      <!-- Nilai yang sudah terisi -->
      <div v-if="filledOptions.length" class="flex flex-wrap gap-1.5">
        <span
          v-for="option in filledOptions"
          :key="option.key"
          :class="[
            'inline-flex items-center gap-1 pl-2 pr-1 py-0.5 rounded-md border text-caption tabular-nums cursor-pointer',
            selectedKey === option.key
              ? 'bg-brand-soft border-brand text-brand-text'
              : 'bg-surface-muted border-line text-fg'
          ]"
          @click="selectedKey = option.key"
        >
          <span>{{ option.label }}: <span class="font-semibold">{{ values[option.key] }}</span></span>
          <button type="button" class="p-0.5 rounded text-fg-muted hover:text-crit" title="Kosongkan" aria-label="Kosongkan" @click.stop="clear(option.key)">
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
