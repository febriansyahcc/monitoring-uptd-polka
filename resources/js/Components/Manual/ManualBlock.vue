<template>
  <div class="manual-block">
    <h3 v-if="block.title" class="text-body font-semibold text-fg mb-2">{{ block.title }}</h3>

    <p v-if="block.type === 'p'" class="text-body text-fg-muted leading-relaxed">
      <RichText :text="block.text" />
    </p>

    <ol v-else-if="block.type === 'steps'" class="space-y-2">
      <li v-for="(item, i) in block.items" :key="i" class="flex gap-3 text-body text-fg-muted leading-relaxed">
        <span
          class="w-6 h-6 rounded-full bg-brand-soft text-brand-text text-caption font-semibold flex items-center justify-center shrink-0 tabular-nums"
          aria-hidden="true"
        >
          {{ i + 1 }}
        </span>
        <span class="pt-0.5"><RichText :text="item" /></span>
      </li>
    </ol>

    <ul v-else-if="block.type === 'list'" class="space-y-1.5">
      <li v-for="(item, i) in block.items" :key="i" class="flex gap-2.5 text-body text-fg-muted leading-relaxed">
        <span class="w-1.5 h-1.5 rounded-full bg-fg-subtle mt-2 shrink-0" aria-hidden="true"></span>
        <span><RichText :text="item" /></span>
      </li>
    </ul>

    <div v-else-if="block.type === 'table'" class="border border-line rounded-lg overflow-x-auto">
      <table class="w-full text-left text-body">
        <thead class="bg-surface-muted text-label text-fg-muted">
          <tr>
            <th v-for="(h, i) in block.head" :key="i" scope="col" class="py-2 px-3 font-medium whitespace-nowrap">{{ h }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-line">
          <tr v-for="(row, r) in block.rows" :key="r">
            <td
              v-for="(cell, c) in row"
              :key="c"
              :class="['py-2 px-3 align-top', c === 0 ? 'font-medium text-fg whitespace-nowrap' : 'text-fg-muted']"
            >
              <RichText :text="cell" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <figure v-else-if="block.type === 'image'" :class="['manual-figure', block.mobile ? 'max-w-[260px]' : '']">
      <a :href="block.src" target="_blank" rel="noopener" class="block focus:outline-none focus-visible:ring-2 focus-visible:ring-brand rounded-lg" title="Buka gambar ukuran penuh">
        <img
          :src="block.src"
          :alt="block.caption"
          loading="lazy"
          class="w-full h-auto rounded-lg border border-line bg-surface-muted"
        />
      </a>
      <figcaption class="mt-1.5 text-caption text-fg-muted">{{ block.caption }}</figcaption>
    </figure>

    <div
      v-else-if="block.type === 'note'"
      :class="[
        'flex gap-2.5 rounded-lg px-3 py-2.5 text-body leading-relaxed',
        block.tone === 'warn' ? 'bg-warn-soft text-fg' : 'bg-brand-soft text-fg',
      ]"
      :role="block.tone === 'warn' ? 'note' : undefined"
    >
      <component
        :is="block.tone === 'warn' ? TriangleAlert : Info"
        :class="['w-4 h-4 mt-0.5 shrink-0', block.tone === 'warn' ? 'text-warn' : 'text-brand-text']"
        aria-hidden="true"
      />
      <span><RichText :text="block.text" /></span>
    </div>
  </div>
</template>

<script setup>
import { Info, TriangleAlert } from 'lucide-vue-next';
import RichText from './RichText.vue';

defineProps({ block: { type: Object, required: true } });
</script>
