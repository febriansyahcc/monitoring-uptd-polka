<template>
  <div
    :role="tabs ? 'tablist' : 'group'"
    :aria-label="label"
    class="inline-flex items-center gap-0.5 p-0.5 rounded-lg border border-line bg-surface-muted max-w-full overflow-x-auto"
  >
    <button
      v-for="option in options"
      :key="option.key"
      type="button"
      :role="tabs ? 'tab' : undefined"
      :aria-selected="tabs ? modelValue === option.key : undefined"
      :aria-pressed="tabs ? undefined : modelValue === option.key"
      :class="[
        'inline-flex items-center gap-1.5 min-h-8 px-3 rounded-md text-label whitespace-nowrap transition-colors',
        'focus:outline-none focus-visible:ring-2 focus-visible:ring-brand',
        modelValue === option.key
          ? 'bg-surface text-fg font-medium border border-line'
          : 'text-fg-muted hover:text-fg border border-transparent',
      ]"
      @click="$emit('update:modelValue', option.key)"
    >
      <component :is="option.icon" v-if="option.icon" class="w-4 h-4" aria-hidden="true" />
      <span>{{ option.label }}</span>
      <span v-if="option.count !== undefined" class="text-caption tabular-nums text-fg-muted">{{ option.count }}</span>
    </button>
  </div>
</template>

<script setup>
/**
 * Pilihan bersegmen netral (tab halaman, pilihan shift). Aktif = permukaan + border,
 * bukan warna modul (IMPLEMENTATION.md P2).
 *
 *   <Segmented v-model="activeTab" :options="[{ key: 'engine', label: 'Engine' }, ...]" label="Jenis data" tabs />
 */
defineProps({
  modelValue: { type: String, default: null },
  // [{ key, label, icon?, count? }]
  options: { type: Array, required: true },
  label: { type: String, default: null },
  // true = berperilaku sebagai tab (role tablist); false = tombol pilihan (aria-pressed)
  tabs: { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);
</script>
