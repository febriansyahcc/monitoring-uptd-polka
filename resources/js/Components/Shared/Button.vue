<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :aria-busy="loading || undefined"
    :class="[
      'inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl font-bold text-xs transition-colors',
      'focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-slate-900',
      'disabled:opacity-50 disabled:cursor-not-allowed',
      variantClass,
    ]"
  >
    <Loader2 v-if="loading" class="w-4 h-4 animate-spin" aria-hidden="true" />
    <slot />
  </button>
</template>

<script setup>
/**
 * Tombol bersama (BUG-13). Saat `loading`, tombol otomatis `disabled` (tidak bisa double-submit)
 * dan tampil redup + spinner.
 *
 *   <Button type="submit" accent="amber" :loading="isSubmitting">Simpan</Button>
 *   <Button variant="secondary" @click="close">Batal</Button>
 *   <Button variant="danger" @click="remove">Hapus</Button>
 */
import { computed } from 'vue';
import { Loader2 } from 'lucide-vue-next';

// Kelas ditulis lengkap agar terdeteksi Tailwind
const PRIMARY = {
  cyan: 'bg-cyan-600 text-white shadow-md enabled:hover:bg-cyan-500 focus-visible:ring-cyan-500',
  emerald: 'bg-emerald-600 text-white shadow-md enabled:hover:bg-emerald-500 focus-visible:ring-emerald-500',
  amber: 'bg-amber-600 text-white shadow-md enabled:hover:bg-amber-500 focus-visible:ring-amber-500',
  rose: 'bg-rose-600 text-white shadow-md enabled:hover:bg-rose-500 focus-visible:ring-rose-500',
  violet: 'bg-violet-600 text-white shadow-md enabled:hover:bg-violet-500 focus-visible:ring-violet-500',
};

const props = defineProps({
  type: { type: String, default: 'button' },
  variant: { type: String, default: 'primary' }, // primary | secondary | danger
  accent: { type: String, default: 'cyan' }, // warna modul untuk variant primary
  loading: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
});

const variantClass = computed(() => {
  if (props.variant === 'secondary') {
    return 'border border-slate-200 text-slate-600 enabled:hover:bg-slate-100 dark:border-slate-700 dark:text-slate-400 dark:enabled:hover:bg-slate-800 focus-visible:ring-slate-400';
  }
  if (props.variant === 'danger') {
    return PRIMARY.rose;
  }
  return PRIMARY[props.accent] ?? PRIMARY.cyan;
});
</script>
