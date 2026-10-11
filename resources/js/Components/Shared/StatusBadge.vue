<template>
  <span :class="['inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-caption font-medium whitespace-nowrap', meta.soft, meta.text]">
    <span v-if="dot" :class="['w-1.5 h-1.5 rounded-full', meta.dot]" aria-hidden="true"></span>
    {{ label ?? meta.label }}
  </span>
</template>

<script setup>
import { computed } from 'vue';
import { statusOf } from '@/utils/status';

const props = defineProps({
  // ok | warning | critical | empty
  status: { type: String, default: 'empty' },
  // Teks tampilan; bila kosong memakai label bawaan status
  label: { type: String, default: null },
  dot: { type: Boolean, default: false },
});

const meta = computed(() => statusOf(props.status));
</script>
