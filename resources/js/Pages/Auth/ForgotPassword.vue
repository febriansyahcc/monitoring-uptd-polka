<template>
  <Head title="Lupa Password" />
  <div class="min-h-screen flex items-center justify-center bg-bg text-fg p-4">
    <div class="w-full max-w-sm space-y-6">
      <div class="flex items-center gap-3">
        <span class="w-10 h-10 rounded-md bg-brand text-on-solid flex items-center justify-center text-label font-semibold" aria-hidden="true">PLN</span>
        <div>
          <h1 class="text-page font-semibold text-fg">Lupa password</h1>
          <p class="text-caption text-fg-muted">Hubungi Administrator untuk reset password</p>
        </div>
      </div>

      <div class="bg-surface border border-line rounded-lg p-5 sm:p-6 space-y-5">
        <p class="text-body text-fg">
          Untuk mengatur ulang password, silakan hubungi Administrator.
        </p>

        <Link href="/login" class="inline-flex items-center gap-1 text-label font-medium text-brand-text hover:underline">
          <ArrowLeft class="w-4 h-4" aria-hidden="true" />
          Kembali ke halaman masuk
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
</script>
