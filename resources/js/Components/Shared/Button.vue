<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :aria-busy="loading || undefined"
    :class="[
      'inline-flex items-center justify-center gap-2 min-h-9 py-2 px-4 rounded-lg text-body font-medium transition-colors',
      'focus:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2 focus-visible:ring-offset-surface',
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
 * Tombol bersama. Saat `loading`, tombol otomatis `disabled` (tidak bisa double-submit)
 * dan tampil redup + spinner.
 *
 *   <Button type="submit" :loading="isSubmitting">Simpan</Button>
 *   <Button variant="secondary" @click="close">Batal</Button>
 *   <Button variant="danger" @click="remove">Hapus</Button>
 *
 * Warna hanya dari token: primary = brand, danger = crit (IMPLEMENTATION.md P1/P2).
 * Teks tombol solid memakai `text-on-solid` — di mode gelap teks putih tidak lolos kontras.
 */
import { computed } from 'vue';
import { Loader2 } from 'lucide-vue-next';

const VARIANT = {
  primary: 'bg-brand text-on-solid enabled:hover:bg-brand-hover',
  secondary: 'bg-surface border border-line text-fg enabled:hover:bg-surface-muted',
  danger: 'bg-crit text-on-solid enabled:hover:opacity-90',
};

const props = defineProps({
  type: { type: String, default: 'button' },
  variant: { type: String, default: 'primary' }, // primary | secondary | danger
  loading: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
});

const variantClass = computed(() => VARIANT[props.variant] ?? VARIANT.primary);
</script>
