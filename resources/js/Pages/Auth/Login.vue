<template>
  <Head title="Masuk" />
  <div class="min-h-screen flex items-center justify-center bg-bg text-fg p-4">
    <div class="w-full max-w-sm space-y-6">
      <!-- Identitas -->
      <div class="flex items-center gap-3">
        <span class="w-10 h-10 rounded-md bg-brand text-on-solid flex items-center justify-center text-label font-semibold" aria-hidden="true">PLN</span>
        <div>
          <h1 class="text-page font-semibold text-fg">PLN Monitor</h1>
          <p class="text-caption text-fg-muted">Monitoring operasional ULPLTD Poka</p>
        </div>
      </div>

      <p
        v-if="$page.props.flash && $page.props.flash.success"
        role="status"
        class="p-3 rounded-lg border border-line border-l-[3px] border-l-ok bg-surface text-body text-fg"
      >
        {{ $page.props.flash.success }}
      </p>

      <div class="bg-surface border border-line rounded-lg p-5 sm:p-6 space-y-5">
        <form @submit.prevent="submit" class="space-y-4">
          <FormField
            id="login-identifier"
            label="Email atau NIP"
            v-model="form.login"
            :error="form.errors.login"
            placeholder="nama@pln.co.id"
            autocomplete="username"
            required
          />

          <FormField id="login-password" label="Password" :error="form.errors.password" required v-slot="{ id, inputClass, describedBy }">
            <div class="relative">
              <input
                :id="id"
                :type="showPassword ? 'text' : 'password'"
                v-model="form.password"
                required
                autocomplete="current-password"
                :aria-invalid="!!form.errors.password || undefined"
                :aria-describedby="describedBy"
                :class="[inputClass, 'pr-10']"
              />
              <button
                type="button"
                class="absolute inset-y-0 right-0 px-3 text-fg-muted hover:text-fg focus:outline-none focus-visible:ring-2 focus-visible:ring-brand rounded-r-lg"
                :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                @click="showPassword = !showPassword"
              >
                <EyeOff v-if="showPassword" class="w-4 h-4" />
                <Eye v-else class="w-4 h-4" />
              </button>
            </div>
          </FormField>

          <div class="flex items-center justify-between gap-3">
            <label for="remember" class="flex items-center gap-2 text-label text-fg-muted cursor-pointer select-none">
              <input id="remember" type="checkbox" v-model="form.remember" class="w-4 h-4 rounded accent-brand cursor-pointer" />
              Ingat saya
            </label>
            <Link href="/forgot-password" class="text-label font-medium text-brand-text hover:underline">Lupa password?</Link>
          </div>

          <Button type="submit" class="w-full min-h-10" :loading="form.processing">Masuk</Button>
        </form>

        <!-- Login cepat untuk uji coba: hanya di lingkungan lokal -->
        <div v-if="$page.props.isLocal" class="pt-4 border-t border-line space-y-2">
          <p class="text-caption text-fg-muted">Uji Coba Cepat 5 Peran (lokal)</p>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5">
            <button
              v-for="account in demoAccounts"
              :key="account.email"
              type="button"
              class="min-h-9 px-2 rounded-md border border-line bg-surface text-label text-fg hover:bg-surface-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-brand"
              @click="quickLogin(account.email)"
            >
              {{ account.label }}
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
import { Eye, EyeOff } from 'lucide-vue-next';
import FormField from '@/Components/Shared/FormField.vue';
import Button from '@/Components/Shared/Button.vue';

const showPassword = ref(false);

const demoAccounts = [
  { email: 'admin@pln.co.id', label: 'Admin' },
  { email: 'manager@pln.co.id', label: 'Manager' },
  { email: 'tl.operasi@pln.co.id', label: 'TL Operasi' },
  { email: 'tl.pemeliharaan@pln.co.id', label: 'TL Pemeliharaan' },
  { email: 'operator1@pln.co.id', label: 'Operator 1' },
];

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
