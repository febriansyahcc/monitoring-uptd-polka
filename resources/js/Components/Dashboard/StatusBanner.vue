<template>
  <div
    :role="status === 'critical' ? 'alert' : 'status'"
    :class="['flex flex-wrap items-center gap-x-4 gap-y-2 rounded-lg border border-line border-l-[3px] px-4 py-3', meta.soft, borderClass]"
  >
    <component :is="status === 'critical' ? AlertOctagon : AlertTriangle" :class="['w-5 h-5 shrink-0', meta.text]" aria-hidden="true" />
    <p class="flex-1 min-w-0 text-body text-fg">
      <span class="font-semibold">{{ title }}</span>
      <span v-if="detail" class="text-fg-muted"> · {{ detail }}</span>
    </p>
    <Link
      v-if="href && actionLabel"
      :href="href"
      class="inline-flex items-center gap-1 text-label font-medium text-brand-text hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-brand rounded"
    >
      {{ actionLabel }}
      <ArrowRight class="w-4 h-4" aria-hidden="true" />
    </Link>
  </div>
</template>

<script setup>
/**
 * Peringatan di atas dashboard — hanya untuk status warning/critical.
 * Merah (crit) hanya muncul di sini bila memang ada kondisi kritis (IMPLEMENTATION.md P3).
 */
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { AlertTriangle, AlertOctagon, ArrowRight } from 'lucide-vue-next';
import { statusOf } from '@/utils/status';

const props = defineProps({
  status: { type: String, required: true }, // warning | critical
  title: { type: String, required: true },
  detail: { type: String, default: null },
  href: { type: String, default: null },
  actionLabel: { type: String, default: null },
});

const meta = computed(() => statusOf(props.status));
// Kelas ditulis lengkap agar terdeteksi Tailwind
const borderClass = computed(() => (props.status === 'critical' ? 'border-l-crit' : 'border-l-warn'));
</script>
