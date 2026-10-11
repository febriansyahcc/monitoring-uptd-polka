<template>
  <Head title="Pengelolaan Pengguna" />
  <div class="space-y-4">
    <!-- Ringkasan akun per peran + aksi (judul halaman ada di Header) -->
    <div class="flex flex-wrap items-center justify-between gap-3">
      <dl class="flex flex-wrap gap-x-5 gap-y-1 text-label">
        <div v-for="card in roleCards" :key="card.key" class="flex items-baseline gap-1.5">
          <dt class="text-fg-muted">{{ card.label }}</dt>
          <dd class="font-semibold tabular-nums text-fg">{{ roleCounts[card.key] }}</dd>
        </div>
      </dl>
      <Button @click="openAddModal">
        <UserPlus class="w-4 h-4" />
        <span>Tambah pengguna</span>
      </Button>
    </div>

    <!-- Daftar pengguna -->
    <div class="border rounded-lg overflow-hidden transition-colors bg-surface border-line">
      <!-- Search Bar -->
      <div class="p-4 border-b flex flex-col sm:flex-row items-center justify-between gap-3 border-line">
        <div class="relative w-full sm:w-72">
          <label for="user-search" class="sr-only">Cari pengguna</label>
          <input
            id="user-search"
            type="search"
            v-model="searchTerm"
            @input="applySearchDebounced"
            placeholder="Cari nama, NIP, email..."
            class="w-full h-9 pl-9 pr-3 rounded-lg text-body border focus:outline-none focus:border-brand focus:ring-2 focus:ring-brand/20 bg-surface border-line text-fg placeholder:text-fg-subtle"
          />
          <Search class="w-4 h-4 text-fg-muted absolute left-3 top-2.5" aria-hidden="true" />
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
          <label for="role-filter" class="text-label text-fg-muted">Peran</label>
          <select
            id="role-filter"
            v-model="selectedRoleFilter"
            @change="applySearch"
            class="h-9 px-3 rounded-lg text-body border focus:outline-none focus:border-brand focus:ring-2 focus:ring-brand/20 cursor-pointer bg-surface border-line text-fg"
          >
            <option value="">Semua peran</option>
            <option v-for="role in roleOptions" :key="role.value" :value="role.value">{{ role.short }}</option>
          </select>
        </div>
      </div>

      <!-- Desktop Table View (>= 768px) -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-body border-collapse">
          <thead>
            <tr class="border-b text-label font-medium bg-surface-muted text-fg-muted border-line">
              <th class="py-3 px-4">Nama</th>
              <th class="py-3 px-3">Email & NIP</th>
              <th class="py-3 px-3">Peran</th>
              <th class="py-3 px-3">Hak akses</th>
              <th class="py-3 px-3 text-center">Status</th>
              <th class="py-3 px-3 w-36 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-line">
            <tr v-for="user in users" :key="user.id" class="transition-colors hover:bg-surface-muted">
              <td class="py-3 px-4 font-medium">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-caption font-medium bg-surface-muted text-fg-muted" aria-hidden="true">
                    {{ user.name.charAt(0).toUpperCase() }}
                  </div>
                  <span>{{ user.name }}</span>
                  <span v-if="isSelf(user)" class="text-caption font-normal text-fg-muted">(Anda)</span>
                </div>
              </td>
              <td class="py-3 px-3">
                <p class="text-fg">{{ user.email }}</p>
                <p class="text-caption text-fg-muted">NIP <span class="font-mono">{{ user.nip }}</span></p>
              </td>
              <td class="py-3 px-3 text-fg">{{ getRoleLabel(user.role) }}</td>
              <td class="py-3 px-3 tabular-nums text-fg-muted">{{ permissionSummary(user) }}</td>
              <td class="py-3 px-3 text-center">
                <button
                  type="button"
                  @click="toggleUserStatus(user)"
                  :disabled="isSelf(user)"
                  :title="isSelf(user) ? 'Akun Anda sendiri tidak dapat dinonaktifkan' : (user.is_active ? 'Klik untuk menonaktifkan' : 'Klik untuk mengaktifkan')"
                  :class="['px-2.5 py-1 rounded-md text-caption font-medium border border-line inline-flex items-center gap-1.5 hover:bg-surface-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-brand disabled:cursor-not-allowed disabled:opacity-70', statusClass(user)]"
                >
                  <span :class="['w-1.5 h-1.5 rounded-full', user.is_active ? 'bg-ok' : 'bg-empty']" aria-hidden="true"></span>
                  <span>{{ user.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </button>
              </td>
              <td class="py-3 px-3 text-center">
                <button
                  type="button"
                  @click="openEditModal(user)"
                  class="inline-flex px-2.5 py-1 rounded-md border text-caption font-medium items-center gap-1.5 bg-surface border-line text-fg hover:bg-surface-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-brand"
                  :aria-label="`Ubah akun & hak akses ${user.name}`"
                >
                  <Pencil class="w-3.5 h-3.5" aria-hidden="true" />
                  <span>Ubah</span>
                </button>
              </td>
            </tr>

            <tr v-if="users.length === 0">
              <td colspan="6" class="py-8 text-center text-fg-muted text-caption">Tidak ada pengguna yang cocok dengan pencarian.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Card View (< 768px) -->
      <div class="block md:hidden p-4 space-y-3">
        <div
          v-for="user in users"
          :key="'m-user-' + user.id"
          class="border rounded-lg p-4 space-y-3 bg-surface border-line"
        >
          <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 text-body font-medium bg-surface-muted text-fg-muted" aria-hidden="true">
              {{ user.name.charAt(0).toUpperCase() }}
            </div>
            <div class="min-w-0 flex-1">
              <p class="font-medium text-body truncate text-fg">
                {{ user.name }}
                <span v-if="isSelf(user)" class="text-caption font-normal text-fg-muted">(Anda)</span>
              </p>
              <p class="text-caption truncate text-fg-muted">{{ user.email }}</p>
              <p class="text-caption text-fg-muted">NIP <span class="font-mono">{{ user.nip }}</span></p>
            </div>
          </div>

          <p class="text-label text-fg">
            {{ getRoleLabel(user.role) }} <span class="text-fg-muted">· {{ permissionSummary(user) }}</span>
          </p>

          <div class="grid grid-cols-2 gap-3 pt-1">
            <button
              type="button"
              @click="toggleUserStatus(user)"
              :disabled="isSelf(user)"
              :class="['min-h-11 px-3 rounded-lg text-label font-medium border border-line inline-flex items-center justify-center gap-1.5 hover:bg-surface-muted disabled:cursor-not-allowed disabled:opacity-70', statusClass(user)]"
            >
              <span :class="['w-2 h-2 rounded-full', user.is_active ? 'bg-ok' : 'bg-empty']" aria-hidden="true"></span>
              <span>{{ user.is_active ? 'Aktif' : 'Nonaktif' }}</span>
            </button>
            <button
              type="button"
              @click="openEditModal(user)"
              :aria-label="`Ubah akun & hak akses ${user.name}`"
              class="min-h-11 px-3 rounded-lg border text-label font-medium inline-flex items-center justify-center gap-1.5 bg-surface border-line text-fg hover:bg-surface-muted"
            >
              <Pencil class="w-4 h-4" aria-hidden="true" />
              <span>Ubah</span>
            </button>
          </div>
        </div>

        <p v-if="users.length === 0" class="py-6 text-center text-fg-muted text-caption">Tidak ada pengguna yang cocok dengan pencarian.</p>
      </div>
    </div>

    <!-- Modal tambah/ubah pengguna & hak akses -->
    <Modal :show="showModal" max-width="2xl" :closeable="!form.processing" @close="closeModal">
      <template #title>
        <span>{{ isEditing ? `Ubah pengguna: ${editingName}` : 'Tambah pengguna' }}</span>
      </template>

      <form id="user-form" @submit.prevent="submitForm" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <FormField label="Nama Lengkap" v-model="form.name" :error="form.errors.name" placeholder="Nama Pengguna" autocomplete="off" required />
          <FormField label="NIP Pegawai (Nomor Induk)" v-model="form.nip" :error="form.errors.nip" placeholder="Contoh: 9900112233" mono />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <FormField label="Email Pegawai" type="email" v-model="form.email" :error="form.errors.email" placeholder="nama@pln.co.id" autocomplete="off" mono required />
          <FormField
            label="Peran Pengguna (Default Role)"
            :error="form.errors.role"
            :hint="editingSelf ? 'Role akun Anda sendiri tidak dapat diubah.' : null"
            required
            v-slot="{ id, inputClass, describedBy }"
          >
            <select
              :id="id"
              v-model="form.role"
              @change="onRoleChange"
              required
              :disabled="editingSelf"
              :class="[inputClass, 'cursor-pointer']"
              :aria-describedby="describedBy"
            >
              <option v-for="role in roleOptions" :key="role.value" :value="role.value">{{ role.long }}</option>
            </select>
          </FormField>
        </div>

        <FormField
          :label="isEditing ? 'Password Baru' : 'Password'"
          type="password"
          v-model="form.password"
          :error="form.errors.password"
          :hint="isEditing ? 'Biarkan kosong jika tidak ingin mengganti password.' : 'Minimal 6 karakter.'"
          placeholder="••••••••"
          autocomplete="new-password"
          :required="!isEditing"
          mono
        />

        <!-- Hak akses per fitur -->
        <div class="pt-2 space-y-3 border-t border-line">
          <div class="flex items-center justify-between gap-3">
            <div>
              <h3 class="text-section font-semibold text-fg">Hak akses</h3>
              <p class="text-caption text-fg-muted">Centang fitur yang boleh dibuka atau diisi oleh akun ini</p>
            </div>
            <button type="button" @click="selectAllPermissions" class="shrink-0 text-label font-medium hover:underline text-brand-text">
              Pilih semua
            </button>
          </div>

          <div v-if="form.role === 'admin'" class="p-3 rounded-lg bg-brand-soft border border-line text-caption text-fg">
            Admin otomatis memiliki semua hak akses. Centang di bawah tidak membatasi akun Admin.
          </div>

          <div class="space-y-3">
            <fieldset
              v-for="(perms, category) in masterPermissions"
              :key="category"
              class="p-3 rounded-lg border space-y-2 border-line"
            >
              <legend class="text-label font-medium px-1 text-fg">
                {{ category }}
              </legend>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <label
                  v-for="perm in perms"
                  :key="perm.id"
                  class="flex items-start gap-2 cursor-pointer p-1.5 rounded-lg hover:bg-surface-muted"
                >
                  <input
                    type="checkbox"
                    :value="perm.slug"
                    v-model="form.permissions"
                    :disabled="form.role === 'admin'"
                    class="w-4 h-4 rounded mt-0.5 cursor-pointer accent-brand"
                  />
                  <span>
                    <span class="block text-label font-medium text-fg">{{ perm.name }}</span>
                    <span class="block text-caption text-fg-muted">{{ perm.description }}</span>
                  </span>
                </label>
              </div>
            </fieldset>
          </div>
          <p v-if="form.errors.permissions" class="text-caption text-crit">{{ form.errors.permissions }}</p>
        </div>
      </form>

      <template #footer>
        <Button variant="secondary" class="flex-1" :disabled="form.processing" @click="closeModal">Batal</Button>
        <Button type="submit" form="user-form" class="flex-1" :loading="form.processing">Simpan</Button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, onBeforeUnmount } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { UserPlus, Search, Pencil } from 'lucide-vue-next';
import Modal from '@/Components/Shared/Modal.vue';
import FormField from '@/Components/Shared/FormField.vue';
import Button from '@/Components/Shared/Button.vue';

const props = defineProps({
  users: Array,
  masterPermissions: Object,
  roleCounts: Object,
  filters: Object,
});

const page = usePage();
const isSelf = (user) => user.id === page.props.auth?.user?.id;

const roleOptions = [
  { value: 'admin', short: 'Admin', long: 'Admin (Akses Penuh)' },
  { value: 'manager', short: 'Manager', long: 'Manager' },
  { value: 'tl_operasi', short: 'TL Operasi', long: 'Team Leader Operasi' },
  { value: 'tl_pemeliharaan', short: 'TL Pemeliharaan', long: 'Team Leader Pemeliharaan' },
  { value: 'operator', short: 'Operator', long: 'Operator' },
];

const roleCards = [
  { key: 'total', label: 'Total akun' },
  { key: 'admin', label: 'Admin' },
  { key: 'manager', label: 'Manager' },
  { key: 'tl_operasi', label: 'TL Operasi' },
  { key: 'tl_pemeliharaan', label: 'TL Pemeliharaan' },
  { key: 'operator', label: 'Operator' },
];

// Preset izin default per role (sama dengan UserSeeder)
const ROLE_PRESETS = {
  manager: ['monitoring_arus.view', 'monitoring_kwh.view', 'monitoring_gangguan.view', 'monitoring_bbm.view', 'monitoring_engine.view', 'data.verify'],
  tl_operasi: ['monitoring_arus.view', 'monitoring_arus.input', 'monitoring_kwh.view', 'monitoring_kwh.input', 'monitoring_bbm.view', 'monitoring_engine.view', 'monitoring_engine.input', 'data.verify'],
  tl_pemeliharaan: ['monitoring_gangguan.view', 'monitoring_gangguan.manage', 'monitoring_arus.view', 'monitoring_engine.view', 'data.verify'],
  operator: ['monitoring_arus.view', 'monitoring_arus.input', 'monitoring_kwh.view', 'monitoring_kwh.input', 'monitoring_gangguan.view', 'monitoring_gangguan.manage', 'monitoring_bbm.view', 'monitoring_bbm.input', 'monitoring_engine.view', 'monitoring_engine.input'],
};

const getRoleLabel = (role) => roleOptions.find((r) => r.value === role)?.short ?? role;

const permissionSummary = (user) => (user.role === 'admin' ? 'Semua akses' : `${user.permissions.length} izin`);

// Nonaktif bukan kondisi kritis: abu (empty), bukan merah (P3)
const statusClass = (user) => (user.is_active ? 'bg-surface text-fg' : 'bg-surface-muted text-fg-muted');

// ---------- Pencarian (debounce, BUG-17) ----------

const searchTerm = ref(props.filters.search || '');
const selectedRoleFilter = ref(props.filters.role || '');
let searchTimer = null;

const applySearch = () => {
  clearTimeout(searchTimer);
  router.get(
    '/users',
    { search: searchTerm.value, role: selectedRoleFilter.value },
    { preserveState: true, preserveScroll: true, replace: true }
  );
};

// Satu request setelah user berhenti mengetik ±300 ms, bukan setiap ketikan
const applySearchDebounced = () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(applySearch, 300);
};

onBeforeUnmount(() => clearTimeout(searchTimer));

// ---------- Modal tambah/edit ----------

const showModal = ref(false);
const isEditing = ref(false);
const editingUserId = ref(null);
const editingName = ref('');
const editingSelf = computed(() => isEditing.value && editingUserId.value === page.props.auth?.user?.id);

const emptyForm = () => ({
  name: '',
  email: '',
  nip: '',
  role: 'operator',
  // Password user baru wajib diisi admin, tidak lagi diisi diam-diam dengan "password" (BUG-17)
  password: '',
  permissions: [...ROLE_PRESETS.operator],
});

const form = useForm(emptyForm());

const openModal = (data, editing, userId = null, name = '') => {
  isEditing.value = editing;
  editingUserId.value = userId;
  editingName.value = name;
  Object.assign(form, emptyForm(), data);
  form.clearErrors();
  showModal.value = true;
};

const openAddModal = () => openModal({}, false);

const openEditModal = (user) =>
  openModal(
    {
      name: user.name,
      email: user.email,
      nip: user.nip !== '-' ? user.nip : '',
      role: user.role,
      password: '',
      permissions: [...user.permissions],
    },
    true,
    user.id,
    user.name
  );

const closeModal = () => {
  showModal.value = false;
};

const selectAllPermissions = () => {
  form.permissions = Object.values(props.masterPermissions).flatMap((group) => group.map((p) => p.slug));
};

const onRoleChange = () => {
  if (form.role === 'admin') selectAllPermissions();
  else form.permissions = [...(ROLE_PRESETS[form.role] ?? [])];
};

const submitForm = () => {
  const options = { preserveScroll: true, preserveState: true, onSuccess: closeModal };
  if (isEditing.value) {
    form.put(`/users/${editingUserId.value}`, options);
  } else {
    form.post('/users', options);
  }
};

// ---------- Status aktif (konfirmasi, BUG-17) ----------

const toggleUserStatus = (user) => {
  if (isSelf(user)) return;
  const action = user.is_active ? 'menonaktifkan' : 'mengaktifkan';
  const warning = user.is_active ? ' Pengguna ini tidak akan bisa login sampai diaktifkan kembali.' : '';
  if (!confirm(`Yakin ingin ${action} akun ${user.name}?${warning}`)) return;
  router.post(`/users/${user.id}/toggle-status`, {}, { preserveScroll: true, preserveState: true });
};
</script>
