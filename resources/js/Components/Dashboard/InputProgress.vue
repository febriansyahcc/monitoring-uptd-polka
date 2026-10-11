<template>
  <Card :title="`Kelengkapan input · ${progress.shift.label}`" description="Yang perlu diisi hari ini" flush>
    <ul class="divide-y divide-line border-t border-line">
      <li v-for="item in visibleItems" :key="item.module" class="flex flex-wrap items-center gap-x-4 gap-y-2 px-4 sm:px-5 py-3">
        <div class="w-32 shrink-0">
          <p class="text-body font-medium text-fg">{{ item.label }}</p>
          <p class="text-caption text-fg-muted">{{ item.scope }}</p>
        </div>

        <div class="flex-1 min-w-[10rem]">
          <template v-if="item.total">
            <div
              class="h-1.5 rounded-full bg-surface-muted overflow-hidden"
              role="progressbar"
              :aria-valuenow="item.filled"
              aria-valuemin="0"
              :aria-valuemax="item.total"
              :aria-label="`${item.label}: ${item.filled} dari ${item.total} ${item.unit}`"
            >
              <div :class="['h-full rounded-full', isComplete(item) ? 'bg-ok' : 'bg-brand']" :style="{ width: percent(item) + '%' }"></div>
            </div>
            <p class="mt-1 text-caption tabular-nums text-fg-muted">
              <span v-if="isComplete(item)" class="text-ok font-medium">Lengkap</span>
              <template v-else-if="item.filled === 0">Belum diisi</template>
              <template v-else>{{ item.filled }}/{{ item.total }} {{ item.unit }}</template>
              <template v-if="item.module === 'bbm' && item.filled === 0 && item.last_date"> · terakhir {{ item.last_date }}</template>
            </p>
          </template>
          <p v-else class="text-caption tabular-nums text-fg-muted">
            {{ item.filled > 0 ? `${item.filled} ${item.unit}` : 'Belum ada catatan' }}
          </p>
        </div>

        <Link
          v-if="can(item.input_permission) && !isComplete(item)"
          :href="item.href"
          class="ml-auto inline-flex items-center gap-1 min-h-9 text-label font-medium text-brand-text hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-brand rounded"
        >
          {{ item.filled > 0 ? 'Lanjutkan' : 'Isi' }}
          <ArrowRight class="w-4 h-4" aria-hidden="true" />
        </Link>
      </li>
    </ul>
  </Card>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
import Card from '@/Components/Shared/Card.vue';
import { usePermission } from '@/composables/usePermission';

const props = defineProps({
  // { shift: {key,label}, date, items: [{ module, label, scope, filled, total|null, unit, href, view_permission, input_permission, last_date? }] }
  progress: { type: Object, required: true },
});

const { can } = usePermission();

// Modul yang tidak boleh dilihat user tidak ditampilkan
const visibleItems = computed(() => props.progress.items.filter((item) => can(item.view_permission)));

const isComplete = (item) => item.total !== null && item.filled >= item.total;
const percent = (item) => Math.min(100, Math.round((item.filled / item.total) * 100));
</script>
