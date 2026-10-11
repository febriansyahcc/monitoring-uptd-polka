<template>
  <section class="bg-surface border border-line rounded-lg">
    <div class="flex items-center justify-between gap-3 px-4 sm:px-5 py-3">
      <button
        type="button"
        class="flex-1 min-w-0 text-left rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-brand"
        :aria-expanded="!collapsed"
        @click="collapsed = !collapsed"
      >
        <h2 class="text-section font-semibold text-fg">{{ title }}</h2>
        <p v-if="description" class="text-caption text-fg-muted truncate">{{ description }}</p>
      </button>
      <button
        type="button"
        class="p-1.5 rounded-md text-fg-muted hover:text-fg hover:bg-surface-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-brand"
        :title="collapsed ? 'Tampilkan grafik' : 'Lipat grafik'"
        :aria-label="collapsed ? 'Tampilkan grafik' : 'Lipat grafik'"
        @click="collapsed = !collapsed"
      >
        <ChevronDown v-if="collapsed" class="w-5 h-5" />
        <ChevronUp v-else class="w-5 h-5" />
      </button>
    </div>

    <div v-show="!collapsed" class="px-4 sm:px-5 pb-4 sm:pb-5 space-y-3">
      <!-- Kontrol grafik (pilihan tampilan, dsb.) -->
      <div v-if="$slots.controls" class="flex flex-wrap items-center gap-2">
        <slot name="controls" />
      </div>

      <div v-if="!empty" :class="['w-full', heightClass]">
        <slot />
      </div>
      <!-- Kosong: satu baris keterangan, bukan kotak setinggi grafik -->
      <p v-else class="text-body text-fg-muted">{{ emptyText }}</p>
    </div>
  </section>
</template>

<script setup>
/**
 * Kartu grafik yang bisa dilipat (IMPLEMENTATION.md Fase F/G): judul + keterangan,
 * slot `controls`, dan keterangan satu baris bila datanya kosong.
 *
 *   <ChartCard title="Produksi harian" :empty="!dates.length" empty-text="Belum ada data">
 *     <apexchart ... height="100%" />
 *   </ChartCard>
 */
import { ref } from 'vue';
import { ChevronUp, ChevronDown } from 'lucide-vue-next';

defineProps({
  title: { type: String, required: true },
  description: { type: String, default: null },
  empty: { type: Boolean, default: false },
  emptyText: { type: String, default: 'Belum ada data untuk ditampilkan.' },
  heightClass: { type: String, default: 'h-[300px] sm:h-[340px]' },
});

const collapsed = ref(false);
</script>
