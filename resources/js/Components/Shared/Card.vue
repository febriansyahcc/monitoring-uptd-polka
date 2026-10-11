<template>
  <section :class="['bg-surface border border-line rounded-lg', flush ? '' : 'p-4 sm:p-5']">
    <header
      v-if="title || $slots.header || $slots.actions"
      :class="['flex flex-wrap items-start justify-between gap-3', flush ? 'px-4 sm:px-5 pt-4 sm:pt-5 pb-3' : 'mb-4']"
    >
      <slot name="header">
        <div class="min-w-0">
          <h2 class="text-section font-semibold text-fg">{{ title }}</h2>
          <p v-if="description" class="text-caption text-fg-muted mt-0.5">{{ description }}</p>
        </div>
      </slot>
      <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2">
        <slot name="actions" />
      </div>
    </header>
    <slot />
  </section>
</template>

<script setup>
/**
 * Kartu bersama (IMPLEMENTATION.md Fase D): permukaan + border, tanpa bayangan dan tanpa kotak ikon.
 *
 *   <Card title="Tabel kWh Engine" description="Stand akhir per engine">
 *     <template #actions><Button>Tambah</Button></template>
 *     ...
 *   </Card>
 *
 * `flush`: tanpa padding isi (untuk tabel yang menempel ke tepi kartu).
 */
defineProps({
  title: { type: String, default: null },
  description: { type: String, default: null },
  flush: { type: Boolean, default: false },
});
</script>
