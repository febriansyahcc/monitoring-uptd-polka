<template>
  <Head title="Lupa Password" />
  <div class="min-h-screen flex items-center justify-center bg-bg text-fg p-4">
    <div class="w-full max-w-sm space-y-6">
      <div class="flex items-center gap-3">
        <span class="w-10 h-10 rounded-md bg-brand text-on-solid flex items-center justify-center text-label font-semibold" aria-hidden="true">PLN</span>
        <div>
          <h1 class="text-page font-semibold text-fg">Lupa password</h1>
          <p class="text-caption text-fg-muted">Masukkan email pegawai yang terdaftar</p>
        </div>
      </div>

      <p
        v-if="$page.props.flash && $page.props.flash.status"
        role="status"
        class="p-3 rounded-lg border border-line border-l-[3px] border-l-ok bg-surface text-body text-fg"
      >
        {{ $page.props.flash.status }}
      </p>

      <div class="bg-surface border border-line rounded-lg p-5 sm:p-6 space-y-5">
        <form @submit.prevent="submit" class="space-y-4">
          <FormField
            id="reset-email"
            label="Email"
            type="email"
            v-model="form.email"
            :error="form.errors.email"
            placeholder="nama@pln.co.id"
            autocomplete="email"
            required
          />
          <Button type="submit" class="w-full min-h-10" :loading="form.processing">Kirim tautan pemulihan</Button>
        </form>

        <Link href="/login" class="inline-flex items-center gap-1 text-label font-medium text-brand-text hover:underline">
          <ArrowLeft class="w-4 h-4" aria-hidden="true" />
          Kembali ke halaman masuk
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import FormField from '@/Components/Shared/FormField.vue';
import Button from '@/Components/Shared/Button.vue';

const form = useForm({
  email: '',
});

const submit = () => {
  form.post('/forgot-password');
};
</script>
