<template>
  <Head title="Pengelolaan Pengguna" />
  <div class="space-y-6">
    <div class="space-y-6">
      <!-- Header Bar & Search Filter (No Card Wrapper) -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-2xl border flex items-center justify-center shadow-inner shrink-0 bg-cyan-50 border-cyan-200 text-cyan-600 dark:bg-transparent dark:bg-gradient-to-tr dark:from-cyan-500/20 dark:to-blue-500/20 dark:border-cyan-500/30 dark:text-cyan-400">
            <ShieldCheck class="w-6 h-6" />
          </div>
          <div>
            <h1 class="text-xl font-extrabold tracking-tight flex items-center gap-2 text-slate-900 dark:text-white">
              Pengelolaan Pengguna & Hak Akses (PBAC)
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Manajemen 5 Peran PBAC (Admin, Manager, TL Operasi, TL Pemeliharaan, Operator) & Matriks Izin Granular
            </p>
          </div>
        </div>

        <!-- Add User Action -->
        <Button accent="cyan" class="self-start md:self-auto" @click="openAddModal">
          <UserPlus class="w-4 h-4" />
          <span>Tambah Pengguna Baru</span>
        </Button>
      </div>

      <!-- KPI Role Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div
          v-for="card in roleCards"
          :key="card.key"
          class="p-3.5 rounded-2xl border shadow-sm space-y-1 bg-white border-slate-200 dark:bg-slate-900 dark:border-slate-800"
        >
          <span :class="['text-[10px] font-bold uppercase', card.labelClass]">{{ card.label }}</span>
          <p :class="['text-lg font-black font-mono', card.valueClass]">{{ roleCounts[card.key] }}</p>
        </div>
      </div>

      <!-- User Data Card -->
      <div class="border rounded-2xl overflow-hidden shadow-sm transition-colors duration-300 bg-white border-slate-200 dark:bg-slate-900 dark:border-slate-800">
        <!-- Search Bar -->
        <div class="p-4 border-b flex flex-col sm:flex-row items-center justify-between gap-3 border-slate-100 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-900/60">
          <div class="relative w-full sm:w-72">
            <label for="user-search" class="sr-only">Cari pengguna</label>
            <input
              id="user-search"
              type="search"
              v-model="searchTerm"
              @input="applySearchDebounced"
              placeholder="Cari nama, NIP, email..."
              class="w-full pl-9 pr-3 py-2 rounded-xl text-xs border focus:outline-none focus:ring-1 focus:ring-cyan-500 bg-white border-slate-200 text-slate-900 dark:bg-slate-950 dark:border-slate-800 dark:text-white"
            />
            <Search class="w-4 h-4 text-slate-500 absolute left-3 top-2.5" aria-hidden="true" />
          </div>

          <div class="flex items-center gap-2 w-full sm:w-auto">
            <label for="role-filter" class="text-xs text-slate-500 dark:text-slate-400 font-semibold">Filter Peran:</label>
            <select
              id="role-filter"
              v-model="selectedRoleFilter"
              @change="applySearch"
              class="px-3 py-2 rounded-xl text-xs border font-bold focus:outline-none cursor-pointer bg-white border-slate-200 text-slate-900 dark:bg-slate-950 dark:border-slate-800 dark:text-white"
            >
              <option value="">Semua Peran</option>
              <option v-for="role in roleOptions" :key="role.value" :value="role.value">{{ role.short }}</option>
            </select>
          </div>
        </div>

        <!-- Desktop Table View (>= 768px) -->
        <div class="hidden md:block overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="border-b font-semibold uppercase tracking-wider bg-slate-100/90 text-slate-700 border-slate-200 dark:bg-slate-950/80 dark:text-slate-300 dark:border-slate-800">
                <th class="py-3 px-4">Nama Pengguna</th>
                <th class="py-3 px-3">Email & NIP</th>
                <th class="py-3 px-3 text-center">Peran (Role)</th>
                <th class="py-3 px-3 text-center">Izin PBAC</th>
                <th class="py-3 px-3 text-center">Status</th>
                <th class="py-3 px-3 w-36 text-center">Aksi (Kelola PBAC)</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800/60">
              <tr v-for="user in users" :key="user.id" class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
                <td class="py-3 px-4 font-bold">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl border flex items-center justify-center shrink-0 font-extrabold text-xs bg-slate-100 border-slate-200 text-cyan-700 dark:bg-slate-800 dark:border-slate-700 dark:text-cyan-400">
                      {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <span>{{ user.name }}</span>
                    <span v-if="isSelf(user)" class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">(Anda)</span>
                  </div>
                </td>
                <td class="py-3 px-3">
                  <p class="font-mono text-slate-600 dark:text-slate-300">{{ user.email }}</p>
                  <p class="font-mono text-[10px] text-slate-500">NIP: {{ user.nip }}</p>
                </td>
                <td class="py-3 px-3 text-center">
                  <span :class="['px-2.5 py-1 rounded-full text-[10px] font-extrabold border uppercase tracking-wider', getRoleBadgeClass(user.role)]">
                    {{ getRoleLabel(user.role) }}
                  </span>
                </td>
                <td class="py-3 px-3 text-center font-mono">
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold border bg-slate-100 text-cyan-800 border-slate-200 dark:bg-slate-800 dark:text-cyan-300 dark:border-slate-700">
                    {{ permissionSummary(user) }}
                  </span>
                </td>
                <td class="py-3 px-3 text-center">
                  <button
                    type="button"
                    @click="toggleUserStatus(user)"
                    :disabled="isSelf(user)"
                    :title="isSelf(user) ? 'Akun Anda sendiri tidak dapat dinonaktifkan' : (user.is_active ? 'Klik untuk menonaktifkan' : 'Klik untuk mengaktifkan')"
                    :class="['px-2.5 py-1 rounded-full text-[10px] font-extrabold border transition-all inline-flex items-center gap-1 disabled:cursor-not-allowed disabled:opacity-70', statusClass(user)]"
                  >
                    <span :class="['w-1.5 h-1.5 rounded-full', user.is_active ? 'bg-emerald-500' : 'bg-rose-500']"></span>
                    <span>{{ user.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                  </button>
                </td>
                <td class="py-3 px-3 text-center">
                  <button
                    type="button"
                    @click="openEditModal(user)"
                    class="inline-flex px-2 py-1 rounded-lg border text-[11px] font-bold transition-all items-center gap-1 bg-slate-100 border-slate-200 text-cyan-700 hover:bg-slate-200 dark:bg-slate-800 dark:border-slate-700 dark:text-cyan-400 dark:hover:bg-slate-700"
                    :aria-label="`Atur PBAC ${user.name}`"
                  >
                    <Shield class="w-3.5 h-3.5" />
                    <span>Atur PBAC</span>
                  </button>
                </td>
              </tr>

              <tr v-if="users.length === 0">
                <td colspan="6" class="py-8 text-center text-slate-400 text-xs italic">Tidak ada pengguna yang cocok dengan pencarian.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile Card View (< 768px) -->
        <div class="block md:hidden p-4 space-y-3">
          <div
            v-for="user in users"
            :key="'m-user-' + user.id"
            class="border rounded-xl p-4 space-y-3 shadow-sm bg-slate-50 border-slate-200 dark:bg-slate-950 dark:border-slate-800"
          >
            <div class="flex items-start gap-3">
              <div class="w-10 h-10 rounded-xl border flex items-center justify-center shrink-0 font-extrabold text-sm bg-white border-slate-200 text-cyan-700 dark:bg-slate-800 dark:border-slate-700 dark:text-cyan-400">
                {{ user.name.charAt(0).toUpperCase() }}
              </div>
              <div class="min-w-0 flex-1">
                <p class="font-bold text-sm truncate">
                  {{ user.name }}
                  <span v-if="isSelf(user)" class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">(Anda)</span>
                </p>
                <p class="font-mono text-[11px] truncate text-slate-600 dark:text-slate-300">{{ user.email }}</p>
                <p class="font-mono text-[10px] text-slate-500">NIP: {{ user.nip }}</p>
              </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
              <span :class="['px-2.5 py-1 rounded-full text-[10px] font-extrabold border uppercase tracking-wider', getRoleBadgeClass(user.role)]">
                {{ getRoleLabel(user.role) }}
              </span>
              <span class="px-2 py-0.5 rounded text-[10px] font-bold border font-mono bg-white text-cyan-800 border-slate-200 dark:bg-slate-800 dark:text-cyan-300 dark:border-slate-700">
                {{ permissionSummary(user) }}
              </span>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-1">
              <button
                type="button"
                @click="toggleUserStatus(user)"
                :disabled="isSelf(user)"
                :class="['min-h-11 px-3 rounded-xl text-xs font-extrabold border inline-flex items-center justify-center gap-1.5 disabled:cursor-not-allowed disabled:opacity-70', statusClass(user)]"
              >
                <span :class="['w-2 h-2 rounded-full', user.is_active ? 'bg-emerald-500' : 'bg-rose-500']"></span>
                <span>{{ user.is_active ? 'Aktif' : 'Nonaktif' }}</span>
              </button>
              <button
                type="button"
                @click="openEditModal(user)"
                :aria-label="`Atur PBAC ${user.name}`"
                class="min-h-11 px-3 rounded-xl border text-xs font-bold inline-flex items-center justify-center gap-1.5 bg-white border-slate-200 text-cyan-700 hover:bg-slate-100 dark:bg-slate-800 dark:border-slate-700 dark:text-cyan-400 dark:hover:bg-slate-700"
              >
                <Shield class="w-4 h-4" />
                <span>Atur PBAC</span>
              </button>
            </div>
          </div>

          <p v-if="users.length === 0" class="py-6 text-center text-slate-400 text-xs italic">Tidak ada pengguna yang cocok dengan pencarian.</p>
        </div>
      </div>
    </div>

    <!-- Modal Form Add/Edit User & PBAC Checklist -->
    <Modal :show="showModal" max-width="2xl" :closeable="!form.processing" @close="closeModal">
      <template #title>
        <ShieldCheck class="w-5 h-5 text-cyan-500" />
        <span>{{ isEditing ? `Atur Pengguna & Hak Akses PBAC: ${editingName}` : 'Tambah Pengguna Baru' }}</span>
      </template>

      <form id="user-form" @submit.prevent="submitForm" class="space-y-4 text-xs">
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
              :class="[inputClass, 'cursor-pointer font-bold']"
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

        <!-- PBAC Permission Checklist Matrix Section -->
        <div class="pt-2 space-y-3 border-t border-slate-200 dark:border-slate-800">
          <div class="flex items-center justify-between gap-3">
            <div>
              <h4 class="font-extrabold text-sm flex items-center gap-1.5 text-cyan-700 dark:text-cyan-400">
                <ShieldCheck class="w-4 h-4" />
                <span>Matriks Izin Granular (PBAC Customs)</span>
              </h4>
              <p class="text-[10px] text-slate-500 dark:text-slate-400">Atur centang izin fitur tertentu yang boleh diakses oleh akun ini</p>
            </div>
            <button type="button" @click="selectAllPermissions" class="shrink-0 text-[10px] font-bold hover:underline text-cyan-700 dark:text-cyan-400">
              Pilih Semua Izin
            </button>
          </div>

          <div v-if="form.role === 'admin'" class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-[11px] font-medium text-rose-700 dark:text-rose-400">
            Peran Admin secara otomatis memiliki seluruh izin akses (*). Matriks checklist di bawah tidak membatasi akun Admin.
          </div>

          <div class="space-y-3">
            <fieldset
              v-for="(perms, category) in masterPermissions"
              :key="category"
              class="p-3.5 rounded-xl border space-y-2 bg-slate-50 border-slate-200 dark:bg-slate-950 dark:border-slate-800"
            >
              <legend class="text-[11px] font-extrabold uppercase tracking-wider px-1 text-slate-500 dark:text-slate-400">
                {{ category }}
              </legend>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <label
                  v-for="perm in perms"
                  :key="perm.id"
                  class="flex items-start gap-2 cursor-pointer p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800/40"
                >
                  <input
                    type="checkbox"
                    :value="perm.slug"
                    v-model="form.permissions"
                    :disabled="form.role === 'admin'"
                    class="w-4 h-4 rounded mt-0.5 cursor-pointer text-cyan-600 focus:ring-cyan-500 bg-white border-slate-300 dark:bg-slate-900 dark:border-slate-700"
                  />
                  <span>
                    <span class="block font-bold text-[11px] text-slate-800 dark:text-slate-200">{{ perm.name }}</span>
                    <span class="block text-[9px] text-slate-500">{{ perm.description }}</span>
                  </span>
                </label>
              </div>
            </fieldset>
          </div>
          <p v-if="form.errors.permissions" class="text-[11px] font-semibold text-rose-500">{{ form.errors.permissions }}</p>
        </div>
      </form>

      <template #footer>
        <Button variant="secondary" class="flex-1" :disabled="form.processing" @click="closeModal">Batal</Button>
        <Button type="submit" form="user-form" accent="cyan" class="flex-1" :loading="form.processing">Simpan Pengaturan PBAC</Button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, onBeforeUnmount } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ShieldCheck, UserPlus, Search, Shield } from 'lucide-vue-next';
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
  { key: 'total', label: 'Total Akun', labelClass: 'text-slate-500 dark:text-slate-400', valueClass: 'text-cyan-700 dark:text-cyan-400' },
  { key: 'admin', label: 'Admin', labelClass: 'text-rose-700 dark:text-rose-400', valueClass: 'text-rose-700 dark:text-rose-400' },
  { key: 'manager', label: 'Manager', labelClass: 'text-purple-700 dark:text-purple-400', valueClass: 'text-purple-700 dark:text-purple-400' },
  { key: 'tl_operasi', label: 'TL Operasi', labelClass: 'text-blue-700 dark:text-blue-400', valueClass: 'text-blue-700 dark:text-blue-400' },
  { key: 'tl_pemeliharaan', label: 'TL Pemeliharaan', labelClass: 'text-amber-700 dark:text-amber-400', valueClass: 'text-amber-700 dark:text-amber-400' },
  { key: 'operator', label: 'Operator', labelClass: 'text-emerald-700 dark:text-emerald-400', valueClass: 'text-emerald-700 dark:text-emerald-400' },
];

// Preset izin default per role (sama dengan UserSeeder)
const ROLE_PRESETS = {
  manager: ['monitoring_arus.view', 'monitoring_kwh.view', 'monitoring_gangguan.view', 'monitoring_bbm.view', 'monitoring_engine.view', 'data.verify'],
  tl_operasi: ['monitoring_arus.view', 'monitoring_arus.input', 'monitoring_kwh.view', 'monitoring_kwh.input', 'monitoring_bbm.view', 'monitoring_engine.view', 'monitoring_engine.input', 'data.verify'],
  tl_pemeliharaan: ['monitoring_gangguan.view', 'monitoring_gangguan.manage', 'monitoring_arus.view', 'monitoring_engine.view', 'data.verify'],
  operator: ['monitoring_arus.view', 'monitoring_arus.input', 'monitoring_kwh.view', 'monitoring_kwh.input', 'monitoring_gangguan.view', 'monitoring_gangguan.manage', 'monitoring_bbm.view', 'monitoring_bbm.input', 'monitoring_engine.view', 'monitoring_engine.input'],
};

const getRoleLabel = (role) => roleOptions.find((r) => r.value === role)?.short ?? role;

const getRoleBadgeClass = (role) => {
  switch (role) {
    case 'admin': return 'bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-500/30';
    case 'manager': return 'bg-purple-500/10 text-purple-700 dark:text-purple-400 border-purple-500/30';
    case 'tl_operasi': return 'bg-blue-500/10 text-blue-700 dark:text-blue-400 border-blue-500/30';
    case 'tl_pemeliharaan': return 'bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-500/30';
    case 'operator': return 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-500/30';
    default: return 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/30';
  }
};

const permissionSummary = (user) => (user.role === 'admin' ? 'Akses Penuh (*)' : `${user.permissions.length} Izin Akses`);

const statusClass = (user) =>
  user.is_active
    ? 'bg-emerald-500/10 text-emerald-700 border-emerald-500/30 enabled:hover:bg-emerald-500/20 dark:text-emerald-400'
    : 'bg-rose-500/10 text-rose-700 border-rose-500/30 enabled:hover:bg-rose-500/20 dark:text-rose-400';

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
