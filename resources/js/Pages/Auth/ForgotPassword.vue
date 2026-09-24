<template>
  <Head title="Lupa Password" />
  <div class="min-h-screen flex items-center justify-center bg-slate-50 text-slate-900 p-4 font-['Inter',sans-serif]">
    <div class="w-full max-w-md space-y-6">
      <div class="text-center space-y-2">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-cyan-500 to-blue-600 shadow-md shadow-cyan-500/20 mb-2">
          <Zap class="w-8 h-8 text-white" />
        </div>
        <h1 class="text-xl font-extrabold tracking-wider text-slate-900">Lupa Password Akun</h1>
        <p class="text-xs text-slate-500">Masukkan email pegawai PLN yang terdaftar untuk memulihkan password</p>
      </div>

      <div v-if="$page.props.flash && $page.props.flash.status" class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
        <CheckCircle2 class="w-4 h-4 shrink-0 text-emerald-600" />
        <span>{{ $page.props.flash.status }}</span>
      </div>

      <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
        <form @submit.prevent="submit" class="space-y-4 text-xs">
          <div class="space-y-1.5">
            <label for="reset-email" class="font-bold text-slate-700">Email Terdaftar</label>
            <div class="relative">
              <input
                id="reset-email"
                type="email"
                v-model="form.email"
                required
                placeholder="nama@pln.co.id"
                :class="[
                  'w-full pl-10 pr-4 py-3 rounded-xl bg-slate-50 border text-slate-900 font-mono placeholder:text-slate-400 focus:bg-white focus:outline-none focus:ring-1 transition-colors',
                  form.errors.email
                    ? 'border-rose-400 ring-1 ring-rose-400 focus:border-rose-500 focus:ring-rose-500'
                    : 'border-slate-200 focus:ring-cyan-500 focus:border-cyan-500'
                ]"
              />
              <Mail class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" />
            </div>
            <p v-if="form.errors.email" class="text-[11px] font-semibold text-rose-600 flex items-center gap-1.5 pt-0.5">
              <AlertCircle class="w-3.5 h-3.5 shrink-0" />
              <span>{{ form.errors.email }}</span>
            </p>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-3.5 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-extrabold text-xs shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
            <span>Kirim Tautan Pemulihan</span>
          </button>
        </form>

        <div class="pt-4 border-t border-slate-100 text-center">
          <Link href="/login" class="text-xs text-cyan-600 font-bold hover:underline flex items-center justify-center gap-1">
            <ArrowLeft class="w-4 h-4" />
            <span>Kembali ke Halaman Login</span>
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Zap, Mail, ArrowLeft, CheckCircle2, AlertCircle, Loader2 } from 'lucide-vue-next';

const form = useForm({
  email: '',
});

const submit = () => {
  form.post('/forgot-password');
};
</script>
