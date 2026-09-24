<template>
  <div :class="['flex items-center', size === 'lg' ? 'gap-3' : 'justify-center gap-1.5']">
    <button
      type="button"
      :class="[base, sizeClass, 'text-cyan-700 dark:text-cyan-400']"
      :title="editLabel"
      :aria-label="editLabel"
      @click="$emit('edit')"
    >
      <Edit3 :class="iconClass" aria-hidden="true" />
    </button>
    <button
      type="button"
      :class="[base, sizeClass, 'text-rose-700 dark:text-rose-400']"
      :title="deleteLabel"
      :aria-label="deleteLabel"
      @click="$emit('delete')"
    >
      <Trash2 :class="iconClass" aria-hidden="true" />
    </button>
  </div>
</template>

<script setup>
/**
 * Tombol Edit & Hapus per baris (BUG-15).
 * - size="sm" (tabel desktop): ringkas, dipakai dengan mouse.
 * - size="lg" (kartu mobile): target sentuh >= 44px (`p-2.5 min-w-11 min-h-11`) dan berjarak
 *   `gap-3` agar Hapus tidak tertekan saat maksud Edit.
 */
import { computed } from 'vue';
import { Edit3, Trash2 } from 'lucide-vue-next';

const props = defineProps({
  size: { type: String, default: 'sm' }, // sm | lg
  editLabel: { type: String, default: 'Edit data' },
  deleteLabel: { type: String, default: 'Hapus data' },
});

defineEmits(['edit', 'delete']);

const base =
  'inline-flex items-center justify-center rounded-lg border transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500 ' +
  'bg-slate-100 border-slate-200 hover:bg-slate-200 dark:bg-slate-800 dark:border-slate-700 dark:hover:bg-slate-700';

const sizeClass = computed(() => (props.size === 'lg' ? 'p-2.5 min-w-11 min-h-11' : 'p-1.5'));
const iconClass = computed(() => (props.size === 'lg' ? 'w-5 h-5' : 'w-3.5 h-3.5'));
</script>
