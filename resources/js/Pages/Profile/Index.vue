<template>
  <Head title="Profil Saya" />
  <div class="max-w-5xl grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">
    <!-- Kiri: identitas & nama -->
    <Card title="Data akun">
      <div class="flex items-center gap-3 pb-4 mb-4 border-b border-line">
        <span
          class="w-12 h-12 rounded-full bg-brand text-on-solid flex items-center justify-center text-section font-semibold shrink-0"
          aria-hidden="true"
        >
          {{ initials }}
        </span>
        <div class="min-w-0">
          <p class="text-section font-semibold text-fg truncate">{{ profile.name }}</p>
          <p class="text-caption text-fg-muted">{{ ROLE_LABELS[profile.role] ?? profile.role }}</p>
        </div>
      </div>

      <dl class="divide-y divide-line border border-line rounded-lg mb-2">
        <div class="flex items-center justify-between gap-4 px-3 py-2.5">
          <dt class="text-label text-fg-muted">Email</dt>
          <dd class="text-body text-fg truncate">{{ profile.email }}</dd>
        </div>
        <div class="flex items-center justify-between gap-4 px-3 py-2.5">
          <dt class="text-label text-fg-muted">NIP</dt>
          <dd class="text-body text-fg font-mono">{{ profile.nip }}</dd>
        </div>
      </dl>
      <p class="text-caption text-fg-muted mb-5">Email, NIP, dan role hanya dapat diubah oleh Administrator.</p>

      <form class="space-y-4" @submit.prevent="saveProfile">
        <FormField label="Nama" v-model="profileForm.name" :error="profileForm.errors.name" autocomplete="name" required />
        <div class="flex justify-end">
          <Button type="submit" :loading="profileForm.processing" :disabled="!profileForm.isDirty">Simpan profil</Button>
        </div>
      </form>
    </Card>

    <!-- Kanan: ganti password -->
    <Card title="Ganti password" description="Minimal 8 karakter. Jika lupa password saat ini, hubungi Administrator.">
      <form class="space-y-4" @submit.prevent="savePassword">
        <FormField
          label="Password saat ini"
          type="password"
          v-model="passwordForm.current_password"
          :error="passwordForm.errors.current_password"
          autocomplete="current-password"
          required
        />
        <FormField
          label="Password baru"
          type="password"
          v-model="passwordForm.password"
          :error="passwordForm.errors.password"
          autocomplete="new-password"
          required
        />
        <FormField
          label="Ulangi password baru"
          type="password"
          v-model="passwordForm.password_confirmation"
          autocomplete="new-password"
          required
        />
        <div class="flex justify-end">
          <Button type="submit" :loading="passwordForm.processing">Ganti password</Button>
        </div>
      </form>
    </Card>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import Card from '@/Components/Shared/Card.vue';
import Button from '@/Components/Shared/Button.vue';
import FormField from '@/Components/Shared/FormField.vue';
import { ROLE_LABELS } from '@/utils/navigation';

const props = defineProps({
  profile: { type: Object, required: true },
});

const initials = computed(() =>
  props.profile.name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((word) => word[0].toUpperCase())
    .join('')
);

const profileForm = useForm({ name: props.profile.name });

const saveProfile = () => {
  profileForm.put('/profil', {
    preserveScroll: true,
    onSuccess: () => profileForm.defaults({ name: profileForm.name }).reset(),
  });
};

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const savePassword = () => {
  passwordForm.put('/profil/password', {
    preserveScroll: true,
    onSuccess: () => passwordForm.reset(),
    onError: () => passwordForm.reset('password', 'password_confirmation'),
  });
};
</script>
