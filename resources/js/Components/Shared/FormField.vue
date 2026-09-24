<template>
  <div class="space-y-1">
    <label :for="fieldId" :class="['block font-bold text-slate-500 dark:text-slate-400', hideLabel ? 'sr-only' : '']">
      {{ label }}<span v-if="required" class="text-rose-500" aria-hidden="true"> *</span>
    </label>

    <!--
      Slot default untuk input kustom (select, textarea, komponen lain). Pasang `:id="id"` dan
      `:class="inputClass"` agar label & gaya error tetap terhubung:
        <FormField label="Engine" :error="errors.engine" v-slot="{ id, inputClass, describedBy }">
          <select :id="id" v-model="form.engine" :class="inputClass" :aria-describedby="describedBy">...</select>
        </FormField>
    -->
    <slot :id="fieldId" :inputClass="inputClass" :invalid="hasError" :describedBy="describedBy">
      <input
        :id="fieldId"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :step="step"
        :min="min"
        :max="max"
        :required="required"
        :disabled="disabled"
        :autocomplete="autocomplete"
        :aria-invalid="hasError || undefined"
        :aria-describedby="describedBy"
        :class="inputClass"
        @input="onInput"
      />
    </slot>

    <p v-if="hasError" :id="errorId" class="text-[11px] font-semibold text-rose-500">{{ errorText }}</p>
    <p v-else-if="hint" :id="hintId" class="text-[11px] text-slate-400">{{ hint }}</p>
  </div>
</template>

<script setup>
/**
 * Label + input + pesan error per field (BUG-13). `<label for>` otomatis terhubung ke `id` input.
 *
 *   <FormField label="Tanggal" type="date" v-model="form.recorded_date" :error="errors.recorded_date" required />
 *   <FormField label="Main Tank (L)" type="number" step="0.01" v-model="form.main_tank" :error="errors.main_tank" />
 */
import { computed, useId } from 'vue';

const ACCENT_RING = {
  cyan: 'focus:ring-cyan-500',
  emerald: 'focus:ring-emerald-500',
  amber: 'focus:ring-amber-500',
  rose: 'focus:ring-rose-500',
  violet: 'focus:ring-violet-500',
};

const props = defineProps({
  label: { type: String, required: true },
  modelValue: { type: [String, Number], default: null },
  error: { type: [String, Array], default: null },
  hint: { type: String, default: null },
  id: { type: String, default: null },
  type: { type: String, default: 'text' },
  placeholder: { type: String, default: null },
  step: { type: [String, Number], default: null },
  min: { type: [String, Number], default: null },
  max: { type: [String, Number], default: null },
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  autocomplete: { type: String, default: null },
  accent: { type: String, default: 'cyan' },
  mono: { type: Boolean, default: false },
  // Label hanya untuk pembaca layar (mis. field tunggal di bawah judul grup)
  hideLabel: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const uid = useId();
const fieldId = computed(() => props.id ?? `field-${uid}`);
const errorId = computed(() => `${fieldId.value}-error`);
const hintId = computed(() => `${fieldId.value}-hint`);

// Inertia mengirim error sebagai string; dukung juga array (mis. dari validasi manual)
const errorText = computed(() => (Array.isArray(props.error) ? props.error[0] : props.error));
const hasError = computed(() => !!errorText.value);
const describedBy = computed(() => (hasError.value ? errorId.value : props.hint ? hintId.value : undefined));

const inputClass = computed(() => [
  'w-full p-2.5 rounded-xl border focus:outline-none focus:ring-1 disabled:opacity-60 disabled:cursor-not-allowed',
  'bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-white',
  hasError.value
    ? 'border-rose-500 focus:ring-rose-500'
    : ['border-slate-200 dark:border-slate-800', ACCENT_RING[props.accent] ?? ACCENT_RING.cyan],
  props.mono || ['number', 'date', 'time'].includes(props.type) ? 'font-mono' : '',
]);

const onInput = (event) => {
  const raw = event.target.value;
  if (props.type === 'number') {
    // Field angka kosong -> null (bukan 0), agar placeholder terlihat dan server menerima nullable
    emit('update:modelValue', raw === '' ? null : Number(raw));
  } else {
    emit('update:modelValue', raw);
  }
};
</script>
