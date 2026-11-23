<template>
  <template v-for="(part, i) in parts" :key="i">
    <strong v-if="part.bold" class="font-semibold text-fg">{{ part.text }}</strong>
    <template v-else>{{ part.text }}</template>
  </template>
</template>

<script setup>
// Teks panduan dengan **tebal**, dirender tanpa v-html
import { computed } from 'vue';
import { richParts } from '@/utils/manual';

const props = defineProps({ text: { type: String, required: true } });
const parts = computed(() => richParts(props.text));
</script>
