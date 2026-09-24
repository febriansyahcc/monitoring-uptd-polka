<template>
  <Head title="Masuk" />
  <div class="min-h-screen flex items-center justify-center bg-slate-50 text-slate-900 p-4 font-['Inter',sans-serif] selection:bg-cyan-500 selection:text-white">
    <div class="w-full max-w-md space-y-6">
      <!-- Logo Header -->
      <div class="text-center space-y-2">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-cyan-500 to-blue-600 shadow-md shadow-cyan-500/20 mb-2">
          <Zap class="w-9 h-9 text-white" />
        </div>
        <h1 class="text-2xl font-black tracking-wider text-slate-900">PLN MONITOR</h1>
        <p class="text-xs text-cyan-700 font-bold uppercase tracking-widest">Sistem Monitoring Operasional ULPLTD POKA</p>
      </div>

      <!-- Flash / Alert Status -->
      <div v-if="$page.props.flash && $page.props.flash.success" class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
        <CheckCircle2 class="w-4 h-4 shrink-0 text-emerald-600" />
        <span>{{ $page.props.flash.success }}</span>
      </div>

      <!-- Login Form Card (Light Theme) -->
      <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
        <form @submit.prevent="submit" class="space-y-4 text-xs">
          <!-- Email or NIP -->
          <div class="space-y-1.5">
            <label for="login-identifier" class="font-bold text-slate-700">Email atau NIP Pegawai</label>
            <div class="relative">
              <input
                id="login-identifier"
                type="text"
                v-model="form.login"
                required
                placeholder="admin@pln.co.id atau 9900112233"
                :class="[
                  'w-full pl-10 pr-4 py-3 rounded-xl bg-slate-50 border text-slate-900 font-mono placeholder:text-slate-400 focus:bg-white focus:outline-none focus:ring-1 transition-colors',
                  form.errors.login
                    ? 'border-rose-400 ring-1 ring-rose-400 focus:border-rose-500 focus:ring-rose-500'
                    : 'border-slate-200 focus:ring-cyan-500 focus:border-cyan-500'
                ]"
              />
              <User class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" />
            </div>
            <p v-if="form.errors.login" class="text-[11px] font-semibold text-rose-600 flex items-center gap-1.5 pt-0.5">
              <AlertCircle class="w-3.5 h-3.5 shrink-0" />
              <span>{{ form.errors.login }}</span>
            </p>
          </div>

          <!-- Password -->
          <div class="space-y-1.5">
            <div class="flex items-center justify-between">
              <label for="login-password" class="font-bold text-slate-700">Password</label>
              <Link href="/forgot-password" class="text-[11px] text-cyan-600 font-bold hover:underline">Lupa Password?</Link>
            </div>
            <div class="relative">
              <input
                id="login-password"
                :type="showPassword ? 'text' : 'password'"
                v-model="form.password"
                required
                placeholder="••••••••"
                :class="[
                  'w-full pl-10 pr-10 py-3 rounded-xl bg-slate-50 border text-slate-900 font-mono placeholder:text-slate-400 focus:bg-white focus:outline-none focus:ring-1 transition-colors',
                  form.errors.password
                    ? 'border-rose-400 ring-1 ring-rose-400 focus:border-rose-500 focus:ring-rose-500'
                    : 'border-slate-200 focus:ring-cyan-500 focus:border-cyan-500'
                ]"
              />
              <Lock class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3.5 top-3.5 text-slate-400 hover:text-slate-600"
                aria-label="Toggle password visibility"
              >
                <EyeOff v-if="showPassword" class="w-4 h-4" />
                <Eye v-else class="w-4 h-4" />
              </button>
            </div>
            <p v-if="form.errors.password" class="text-[11px] font-semibold text-rose-600 flex items-center gap-1.5 pt-0.5">
              <AlertCircle class="w-3.5 h-3.5 shrink-0" />
              <span>{{ form.errors.password }}</span>
            </p>
          </div>

          <!-- Remember Me -->
          <div class="flex items-center gap-2 pt-1">
            <input
              type="checkbox"
              id="remember"
              v-model="form.remember"
              class="w-4 h-4 rounded bg-slate-50 border-slate-300 text-cyan-600 focus:ring-cyan-500 cursor-pointer"
            />
            <label for="remember" class="text-slate-600 font-medium cursor-pointer select-none">Ingat Sesi Saya</label>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-3.5 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-extrabold text-sm shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer mt-2 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed disabled:active:scale-100"
          >
            <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
            <span>Masuk ke Sistem</span>
          </button>
        </form>

        <!-- Demo Preset Quick Login Selector (Hanya Tampil di Lingkungan Lokal) -->
        <div v-if="$page.props.isLocal" class="pt-4 border-t border-slate-100 space-y-2">
          <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center">Uji Coba Cepat 5 Peran RBAC/PBAC (Lokal):</p>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 text-[10px]">
            <button
              type="button"
              @click="quickLogin('admin@pln.co.id')"
              class="p-2 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 font-bold hover:bg-rose-100 transition-colors cursor-pointer"
            >
              Admin
            </button>
            <button
              type="button"
              @click="quickLogin('manager@pln.co.id')"
              class="p-2 rounded-lg border border-purple-200 bg-purple-50 text-purple-800 font-bold hover:bg-purple-100 transition-colors cursor-pointer"
            >
              Manager
            </button>
            <button
              type="button"
              @click="quickLogin('tl.operasi@pln.co.id')"
              class="p-2 rounded-lg border border-blue-200 bg-blue-50 text-blue-800 font-bold hover:bg-blue-100 transition-colors cursor-pointer"
            >
              TL Operasi
            </button>
            <button
              type="button"
              @click="quickLogin('tl.pemeliharaan@pln.co.id')"
              class="p-2 rounded-lg border border-amber-200 bg-amber-50 text-amber-800 font-bold hover:bg-amber-100 transition-colors cursor-pointer"
            >
              TL Pemeliharaan
            </button>
            <button
              type="button"
              @click="quickLogin('operator1@pln.co.id')"
              class="p-2 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 font-bold hover:bg-emerald-100 transition-colors col-span-2 sm:col-span-1 cursor-pointer"
            >
              Operator 1
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Zap, User, Lock, Eye, EyeOff, CheckCircle2, AlertCircle, Loader2 } from 'lucide-vue-next';

const showPassword = ref(false);

const form = useForm({
  login: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
};

const quickLogin = (email) => {
  form.login = email;
  form.password = 'password';
  submit();
};
</script>
