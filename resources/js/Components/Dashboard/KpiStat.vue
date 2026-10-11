<template>
  <component
    :is="href ? Link : 'div'"
    :href="href || undefined"
    :class="[
      'block p-4 sm:p-5 min-w-0 bg-surface',
      href ? 'hover:bg-surface-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand transition-colors' : '',
    ]"
  >
    <p class="text-label font-medium text-fg-muted">{{ label }}</p>

    <p class="mt-1.5 flex items-baseline gap-1.5">
      <!-- Kosong ditampilkan sebagai "—", bukan 0 (IMPLEMENTATION.md P4) -->
      <span class="text-kpi font-semibold tabular-nums text-fg">{{ value ?? '—' }}</span>
      <span v-if="value !== null && unit" class="text-body text-fg-muted">{{ unit }}</span>
    </p>

    <div class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-caption text-fg-muted">
      <StatusBadge v-if="status" :status="status" :label="statusLabel" />
      <span v-if="hint">{{ hint }}</span>
    </div>
  </component>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import StatusBadge from '@/Components/Shared/StatusBadge.vue';

defineProps({
  label: { type: String, required: true },
  // null = belum ada data
  value: { type: String, default: null },
  unit: { type: String, default: null },
  hint: { type: String, default: null },
  status: { type: String, default: null },
  statusLabel: { type: String, default: null },
  // Kolom bisa diklik ke halaman modul (hanya bila user boleh melihatnya)
  href: { type: String, default: null },
});
</script>
