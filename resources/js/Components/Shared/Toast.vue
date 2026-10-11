<template>
  <!-- Toast global: posisi tetap, selalu terlihat walau user menyimpan dari baris paling bawah -->
  <div
    class="fixed z-[60] top-4 inset-x-4 sm:inset-x-auto sm:right-6 sm:w-96 flex flex-col gap-2 pointer-events-none"
    aria-live="polite"
    aria-atomic="false"
  >
    <TransitionGroup name="toast">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :role="toast.type === 'error' ? 'alert' : 'status'"
        :class="[
          'pointer-events-auto flex items-start gap-3 p-3 rounded-lg border border-line border-l-[3px] bg-surface text-fg shadow-lg text-body',
          toast.type === 'error' ? 'border-l-crit' : 'border-l-ok',
        ]"
        @mouseenter="pause(toast)"
        @mouseleave="resume(toast)"
      >
        <AlertCircle v-if="toast.type === 'error'" class="w-4 h-4 shrink-0 mt-0.5 text-crit" aria-hidden="true" />
        <CheckCircle2 v-else class="w-4 h-4 shrink-0 mt-0.5 text-ok" aria-hidden="true" />
        <span class="flex-1">{{ toast.message }}</span>
        <button
          type="button"
          class="shrink-0 -m-1 p-1 rounded-md text-fg-muted hover:text-fg focus:outline-none focus-visible:ring-2 focus-visible:ring-brand"
          aria-label="Tutup notifikasi"
          @click="dismiss(toast.id)"
        >
          <X class="w-4 h-4" />
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { ref, watch, onBeforeUnmount } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CheckCircle2, AlertCircle, X } from 'lucide-vue-next';

const DURATION = 4000;

const page = usePage();
const toasts = ref([]);
let nextId = 1;

const dismiss = (id) => {
  const toast = toasts.value.find((t) => t.id === id);
  if (toast) clearTimeout(toast.timer);
  toasts.value = toasts.value.filter((t) => t.id !== id);
};

const schedule = (toast) => {
  clearTimeout(toast.timer);
  toast.timer = setTimeout(() => dismiss(toast.id), DURATION);
};

const push = (type, message) => {
  const toast = { id: nextId++, type, message, timer: null };
  toasts.value.push(toast);
  schedule(toast);
};

// Timer berhenti saat kursor di atas toast agar sempat dibaca
const pause = (toast) => clearTimeout(toast.timer);
const resume = (toast) => schedule(toast);

// Objek flash baru dibuat di setiap respons, jadi pesan yang sama dua kali tetap memunculkan toast
watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success) push('success', flash.success);
    if (flash?.error) push('error', flash.error);
  },
  { immediate: true }
);

onBeforeUnmount(() => toasts.value.forEach((t) => clearTimeout(t.timer)));
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
