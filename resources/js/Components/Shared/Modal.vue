<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div
        v-if="show"
        class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4"
        @click.self="requestClose"
      >
        <div
          ref="panel"
          role="dialog"
          aria-modal="true"
          :aria-labelledby="titleId"
          tabindex="-1"
          :class="[
            'w-full border rounded-2xl shadow-2xl p-6 space-y-5 overflow-y-auto max-h-[90vh] focus:outline-none',
            'bg-white border-slate-200 text-slate-900 dark:bg-slate-900 dark:border-slate-800 dark:text-white',
            maxWidthClass,
          ]"
        >
          <div class="flex items-center justify-between gap-3 border-b pb-3 border-slate-100 dark:border-slate-800">
            <h3 :id="titleId" class="font-bold text-base flex items-center gap-2">
              <slot name="title">{{ title }}</slot>
            </h3>
            <button
              type="button"
              class="p-1.5 -m-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 dark:hover:text-white dark:hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed"
              aria-label="Tutup"
              :disabled="!closeable"
              @click="requestClose"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <slot />

          <div v-if="$slots.footer" class="pt-2 flex items-center gap-3">
            <slot name="footer" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
/**
 * Modal bersama (BUG-13): Esc & klik backdrop menutup, fokus awal ke field pertama,
 * fokus terkunci di dalam panel (Tab/Shift+Tab), scroll halaman dikunci, dan fokus
 * dikembalikan ke elemen pemicu saat ditutup.
 *
 * Pemakaian:
 *   <Modal :show="showModal" title="Tambah Data" @close="showModal = false" :closeable="!form.processing">
 *     <form id="my-form" @submit.prevent="submit">...</form>
 *     <template #footer>
 *       <Button variant="secondary" class="flex-1" @click="showModal = false">Batal</Button>
 *       <Button type="submit" form="my-form" class="flex-1" :loading="form.processing">Simpan</Button>
 *     </template>
 *   </Modal>
 */
import { computed, nextTick, onBeforeUnmount, ref, useId, watch } from 'vue';
import { X } from 'lucide-vue-next';

const props = defineProps({
  show: { type: Boolean, default: false },
  title: { type: String, default: '' },
  maxWidth: { type: String, default: 'lg' }, // sm | md | lg | xl | 2xl | 3xl
  // false saat sedang menyimpan: Esc, backdrop dan tombol X tidak menutup modal
  closeable: { type: Boolean, default: true },
});

const emit = defineEmits(['close']);

const panel = ref(null);
const titleId = `modal-title-${useId()}`;
let previouslyFocused = null;

const maxWidthClass = computed(
  () =>
    ({
      sm: 'max-w-sm',
      md: 'max-w-md',
      lg: 'max-w-lg',
      xl: 'max-w-xl',
      '2xl': 'max-w-2xl',
      '3xl': 'max-w-3xl',
    })[props.maxWidth] ?? 'max-w-lg'
);

const FOCUSABLE =
  'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

const focusableElements = () => (panel.value ? [...panel.value.querySelectorAll(FOCUSABLE)] : []);

const requestClose = () => {
  if (props.closeable) emit('close');
};

const onKeydown = (event) => {
  if (!props.show) return;

  if (event.key === 'Escape') {
    event.preventDefault();
    requestClose();
    return;
  }

  // Kunci fokus di dalam modal
  if (event.key === 'Tab') {
    const items = focusableElements();
    if (items.length === 0) {
      event.preventDefault();
      panel.value?.focus();
      return;
    }
    const first = items[0];
    const last = items[items.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  }
};

const open = async () => {
  previouslyFocused = document.activeElement;
  document.addEventListener('keydown', onKeydown);
  document.body.style.overflow = 'hidden';

  await nextTick();
  // Fokus awal ke field form pertama; bila tidak ada, ke panel
  const firstField = panel.value?.querySelector(
    'input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled])'
  );
  (firstField ?? panel.value)?.focus();
};

const cleanup = () => {
  document.removeEventListener('keydown', onKeydown);
  document.body.style.overflow = '';
};

const close = () => {
  cleanup();
  if (previouslyFocused && typeof previouslyFocused.focus === 'function') {
    previouslyFocused.focus();
  }
  previouslyFocused = null;
};

watch(
  () => props.show,
  (visible, wasVisible) => {
    if (visible) open();
    else if (wasVisible) close();
  },
  { immediate: true }
);

onBeforeUnmount(cleanup);
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
</style>
