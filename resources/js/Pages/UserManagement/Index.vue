<template>
  <AppLayout v-slot="{ isDarkMode }">
    <div class="space-y-6">
      <!-- Header Bar & Search Filter (No Card Wrapper) -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2">
        <div class="flex items-center gap-3">
          <div
            :class="[
              'w-12 h-12 rounded-2xl border flex items-center justify-center shadow-inner shrink-0',
              isDarkMode
                ? 'bg-gradient-to-tr from-cyan-500/20 to-blue-500/20 border-cyan-500/30 text-cyan-400'
                : 'bg-cyan-50 border-cyan-200 text-cyan-600'
            ]"
          >
            <ShieldCheck class="w-6 h-6" />
          </div>
          <div>
            <h1 :class="['text-xl font-extrabold tracking-tight flex items-center gap-2', isDarkMode ? 'text-white' : 'text-slate-900']">
              Pengelolaan Pengguna & Hak Akses (PBAC)
            </h1>
            <p :class="['text-xs', isDarkMode ? 'text-slate-400' : 'text-slate-500']">
              Manajemen 5 Peran PBAC (Admin, Manager, TL Operasi, TL Pemeliharaan, Operator) & Matriks Izin Granular
            </p>
          </div>
        </div>

        <!-- Add User Action -->
        <button
          @click="openAddModal"
          class="py-2.5 px-4 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-slate-950 font-extrabold text-xs shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer self-start md:self-auto"
        >
          <UserPlus class="w-4 h-4" />
          <span>Tambah Pengguna Baru</span>
        </button>
      </div>

      <!-- Flash Message Alert -->
      <Transition name="fade">
        <div
          v-if="$page.props.flash && $page.props.flash.success"
          :class="[
            'p-4 rounded-xl flex items-center justify-between shadow-lg border text-xs font-semibold',
            isDarkMode ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400' : 'bg-emerald-50 border-emerald-200 text-emerald-800'
          ]"
        >
          <div class="flex items-center gap-2">
            <CheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0" />
            <span>{{ $page.props.flash.success }}</span>
          </div>
        </div>
      </Transition>
      <div
        v-if="$page.props.flash && $page.props.flash.error"
        class="p-4 rounded-xl shadow-lg border text-xs font-semibold bg-rose-500/10 border-rose-500/30 text-rose-500"
      >
        {{ $page.props.flash.error }}
      </div>

      <!-- KPI Role Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Total -->
        <div :class="['p-3.5 rounded-2xl border shadow-sm space-y-1', isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200']">
          <span class="text-[10px] font-bold text-slate-400 uppercase">Total Akun</span>
          <p class="text-lg font-black font-mono text-cyan-400">{{ roleCounts.total }}</p>
        </div>
        <!-- Admin -->
        <div :class="['p-3.5 rounded-2xl border shadow-sm space-y-1', isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200']">
          <span class="text-[10px] font-bold text-rose-400 uppercase">Admin</span>
          <p class="text-lg font-black font-mono text-rose-400">{{ roleCounts.admin }}</p>
        </div>
        <!-- Manager -->
        <div :class="['p-3.5 rounded-2xl border shadow-sm space-y-1', isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200']">
          <span class="text-[10px] font-bold text-purple-400 uppercase">Manager</span>
          <p class="text-lg font-black font-mono text-purple-400">{{ roleCounts.manager }}</p>
        </div>
        <!-- TL Operasi -->
        <div :class="['p-3.5 rounded-2xl border shadow-sm space-y-1', isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200']">
          <span class="text-[10px] font-bold text-blue-400 uppercase">TL Operasi</span>
          <p class="text-lg font-black font-mono text-blue-400">{{ roleCounts.tl_operasi }}</p>
        </div>
        <!-- TL Pemeliharaan -->
        <div :class="['p-3.5 rounded-2xl border shadow-sm space-y-1', isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200']">
          <span class="text-[10px] font-bold text-amber-400 uppercase">TL Pemeliharaan</span>
          <p class="text-lg font-black font-mono text-amber-400">{{ roleCounts.tl_pemeliharaan }}</p>
        </div>
        <!-- Operator -->
        <div :class="['p-3.5 rounded-2xl border shadow-sm space-y-1', isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200']">
          <span class="text-[10px] font-bold text-emerald-400 uppercase">Operator</span>
          <p class="text-lg font-black font-mono text-emerald-400">{{ roleCounts.operator }}</p>
        </div>
      </div>

      <!-- User Data Table Card -->
      <div
        :class="[
          'border rounded-2xl overflow-hidden shadow-sm transition-colors duration-300',
          isDarkMode ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'
        ]"
      >
        <!-- Table Search Bar -->
        <div
          :class="[
            'p-4 border-b flex flex-col sm:flex-row items-center justify-between gap-3',
            isDarkMode ? 'border-slate-800 bg-slate-900/60' : 'border-slate-100 bg-slate-50/80'
          ]"
        >
          <div class="relative w-full sm:w-72">
            <input
              type="text"
              v-model="searchTerm"
              @input="applySearch"
              placeholder="Cari nama, NIP, email..."
              :class="[
                'w-full pl-9 pr-3 py-2 rounded-xl text-xs border focus:outline-none focus:ring-1 focus:ring-cyan-500',
                isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-white border-slate-200 text-slate-900'
              ]"
            />
            <Search class="w-4 h-4 text-slate-500 absolute left-3 top-2.5" />
          </div>

          <div class="flex items-center gap-2 w-full sm:w-auto">
            <span class="text-xs text-slate-400 font-semibold">Filter Peran:</span>
            <select
              v-model="selectedRoleFilter"
              @change="applySearch"
              :class="[
                'px-3 py-2 rounded-xl text-xs border font-bold focus:outline-none cursor-pointer',
                isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-white border-slate-200 text-slate-900'
              ]"
            >
              <option value="">Semua Peran</option>
              <option value="admin">Admin</option>
              <option value="manager">Manager</option>
              <option value="tl_operasi">TL Operasi</option>
              <option value="tl_pemeliharaan">TL Pemeliharaan</option>
              <option value="operator">Operator</option>
            </select>
          </div>
        </div>

        <!-- Table View -->
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr
                :class="[
                  'border-b font-semibold uppercase tracking-wider',
                  isDarkMode ? 'bg-slate-950/80 text-slate-300 border-slate-800' : 'bg-slate-100/90 text-slate-700 border-slate-200'
                ]"
              >
                <th class="py-3 px-4">Nama Pengguna</th>
                <th class="py-3 px-3">Email & NIP</th>
                <th class="py-3 px-3 text-center">Peran (Role)</th>
                <th class="py-3 px-3 text-center">Izin PBAC</th>
                <th class="py-3 px-3 text-center">Status</th>
                <th class="py-3 px-3 w-36 text-center">Aksi (Kelola PBAC)</th>
              </tr>
            </thead>
            <tbody :class="['divide-y', isDarkMode ? 'divide-slate-800/60' : 'divide-slate-200/60']">
              <tr
                v-for="user in users"
                :key="user.id"
                :class="[
                  'transition-colors',
                  isDarkMode ? 'hover:bg-slate-850/50' : 'hover:bg-slate-50'
                ]"
              >
                <!-- Name -->
                <td class="py-3 px-4 font-bold">
                  <div class="flex items-center gap-2.5">
                    <div
                      :class="[
                        'w-8 h-8 rounded-xl border flex items-center justify-center shrink-0 font-extrabold text-xs',
                        isDarkMode ? 'bg-slate-800 border-slate-700 text-cyan-400' : 'bg-slate-100 border-slate-200 text-cyan-700'
                      ]"
                    >
                      {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <span>{{ user.name }}</span>
                  </div>
                </td>

                <!-- Email & NIP -->
                <td class="py-3 px-3">
                  <p class="font-mono text-slate-300">{{ user.email }}</p>
                  <p class="font-mono text-[10px] text-slate-500">NIP: {{ user.nip }}</p>
                </td>

                <!-- Role Badge -->
                <td class="py-3 px-3 text-center">
                  <span
                    :class="[
                      'px-2.5 py-1 rounded-full text-[10px] font-extrabold border uppercase tracking-wider',
                      getRoleBadgeClass(user.role)
                    ]"
                  >
                    {{ getRoleLabel(user.role) }}
                  </span>
                </td>

                <!-- PBAC Permissions Count Badge -->
                <td class="py-3 px-3 text-center font-mono">
                  <span
                    :class="[
                      'px-2 py-0.5 rounded text-[10px] font-bold border',
                      isDarkMode ? 'bg-slate-800 text-cyan-300 border-slate-700' : 'bg-slate-100 text-cyan-800 border-slate-200'
                    ]"
                  >
                    {{ user.role === 'admin' ? 'Akses Penuh (*)' : `${user.permissions.length} Izin Akses` }}
                  </span>
                </td>

                <!-- Active Status Toggle Button -->
                <td class="py-3 px-3 text-center">
                  <button
                    @click="toggleUserStatus(user)"
                    :class="[
                      'px-2.5 py-1 rounded-full text-[10px] font-extrabold border transition-all cursor-pointer inline-flex items-center gap-1',
                      user.is_active
                        ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/30 hover:bg-emerald-500/20'
                        : 'bg-rose-500/10 text-rose-500 border-rose-500/30 hover:bg-rose-500/20'
                    ]"
                  >
                    <span :class="['w-1.5 h-1.5 rounded-full', user.is_active ? 'bg-emerald-500' : 'bg-rose-500']"></span>
                    <span>{{ user.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                  </button>
                </td>

                <!-- Actions -->
                <td class="py-3 px-3 text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <button
                      @click="openEditModal(user)"
                      :class="[
                        'px-2 py-1 rounded-lg border text-[11px] font-bold transition-all flex items-center gap-1',
                        isDarkMode ? 'bg-slate-800 border-slate-700 text-cyan-400 hover:bg-slate-700' : 'bg-slate-100 border-slate-200 text-cyan-700 hover:bg-slate-200'
                      ]"
                      title="Edit Hak Akses PBAC"
                    >
                      <Shield class="w-3.5 h-3.5" />
                      <span>Atur PBAC</span>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Form Add/Edit User & PBAC Checklist -->
    <Transition name="fade">
      <div v-if="showModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div
          :class="[
            'w-full max-w-2xl border rounded-2xl shadow-2xl p-6 space-y-5 transition-colors overflow-y-auto max-h-[90vh]',
            isDarkMode ? 'bg-slate-900 border-slate-800 text-white' : 'bg-white border-slate-200 text-slate-900'
          ]"
        >
          <div class="flex items-center justify-between border-b pb-3 dark:border-slate-800 border-slate-100">
            <h3 class="font-bold text-base flex items-center gap-2">
              <ShieldCheck class="w-5 h-5 text-cyan-500" />
              <span>{{ isEditing ? `Atur Pengguna & Hak Akses PBAC: ${form.name}` : 'Tambah Pengguna Baru' }}</span>
            </h3>
            <button @click="closeModal" class="text-slate-400 hover:text-white">
              <X class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="submitForm" class="space-y-4 text-xs">
            <!-- Basic User Info -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div class="space-y-1">
                <label class="font-bold text-slate-400">Nama Lengkap</label>
                <input
                  type="text"
                  v-model="form.name"
                  required
                  placeholder="Nama Pengguna"
                  :class="[
                    'w-full p-2.5 rounded-xl border focus:outline-none focus:ring-1 focus:ring-cyan-500',
                    isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                  ]"
                />
              </div>

              <div class="space-y-1">
                <label class="font-bold text-slate-400">NIP Pegawai (Nomor Induk)</label>
                <input
                  type="text"
                  v-model="form.nip"
                  placeholder="Contoh: 9900112233"
                  :class="[
                    'w-full p-2.5 rounded-xl border font-mono focus:outline-none focus:ring-1 focus:ring-cyan-500',
                    isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                  ]"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div class="space-y-1">
                <label class="font-bold text-slate-400">Email Pegawai</label>
                <input
                  type="email"
                  v-model="form.email"
                  required
                  placeholder="nama@pln.co.id"
                  :class="[
                    'w-full p-2.5 rounded-xl border font-mono focus:outline-none focus:ring-1 focus:ring-cyan-500',
                    isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                  ]"
                />
              </div>

              <div class="space-y-1">
                <label class="font-bold text-slate-400">Peran Pengguna (Default Role)</label>
                <select
                  v-model="form.role"
                  @change="onRoleChange"
                  required
                  :class="[
                    'w-full p-2.5 rounded-xl border focus:outline-none focus:ring-1 focus:ring-cyan-500 cursor-pointer font-bold',
                    isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                  ]"
                >
                  <option value="admin">Admin (Akses Penuh)</option>
                  <option value="manager">Manager</option>
                  <option value="tl_operasi">Team Leader Operasi</option>
                  <option value="tl_pemeliharaan">Team Leader Pemeliharaan</option>
                  <option value="operator">Operator</option>
                </select>
              </div>
            </div>

            <!-- Password Input -->
            <div class="space-y-1">
              <label class="font-bold text-slate-400">
                Password {{ isEditing ? '(Biarkan kosong jika tidak ingin mengedit)' : '*' }}
              </label>
              <input
                type="password"
                v-model="form.password"
                :required="!isEditing"
                placeholder="••••••••"
                :class="[
                  'w-full p-2.5 rounded-xl border font-mono focus:outline-none focus:ring-1 focus:ring-cyan-500',
                  isDarkMode ? 'bg-slate-950 border-slate-800 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'
                ]"
              />
            </div>

            <!-- PBAC Permission Checklist Matrix Section -->
            <div class="pt-2 space-y-3 border-t dark:border-slate-800 border-slate-200">
              <div class="flex items-center justify-between">
                <div>
                  <h4 class="font-extrabold text-sm text-cyan-400 flex items-center gap-1.5">
                    <ShieldCheck class="w-4 h-4" />
                    <span>Matriks Izin Granular (PBAC Customs)</span>
                  </h4>
                  <p class="text-[10px] text-slate-400">Atur centang izin fitur tertentu yang boleh diakses oleh akun ini</p>
                </div>
                <button
                  type="button"
                  @click="selectAllPermissions"
                  class="text-[10px] font-bold text-cyan-400 hover:underline"
                >
                  Pilih Semua Izin
                </button>
              </div>

              <!-- Admin Notice -->
              <div v-if="form.role === 'admin'" class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-[11px] font-medium">
                Peran Admin secara otomatis memiliki seluruh izin akses (*). Matriks checklist di bawah tidak membatasi akun Admin.
              </div>

              <!-- Permission Checklist Grouped by Category -->
              <div class="space-y-3">
                <div
                  v-for="(perms, category) in masterPermissions"
                  :key="category"
                  :class="[
                    'p-3.5 rounded-xl border space-y-2',
                    isDarkMode ? 'bg-slate-950 border-slate-800' : 'bg-slate-50 border-slate-200'
                  ]"
                >
                  <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 block border-b pb-1 dark:border-slate-800 border-slate-200">
                    {{ category }}
                  </span>

                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                    <label
                      v-for="perm in perms"
                      :key="perm.id"
                      class="flex items-start gap-2 cursor-pointer p-1.5 rounded-lg hover:bg-slate-800/40"
                    >
                      <input
                        type="checkbox"
                        :value="perm.slug"
                        v-model="form.permissions"
                        :disabled="form.role === 'admin'"
                        class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-cyan-500 focus:ring-cyan-500 mt-0.5 cursor-pointer"
                      />
                      <div>
                        <p class="font-bold text-[11px] text-slate-200">{{ perm.name }}</p>
                        <p class="text-[9px] text-slate-500">{{ perm.description }}</p>
                      </div>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <ul v-if="Object.keys(formErrors).length" class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-500 space-y-0.5">
              <li v-for="(message, key) in formErrors" :key="key">{{ message }}</li>
            </ul>

            <!-- Submit Action -->
            <div class="pt-3 flex items-center gap-3">
              <button
                type="button"
                @click="closeModal"
                class="flex-1 py-2.5 rounded-xl border border-slate-700 text-slate-400 font-bold hover:bg-slate-800"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="isSubmitting"
                class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-slate-950 font-extrabold shadow-md flex items-center justify-center gap-2"
              >
                <Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin" />
                <span>Simpan Pengaturan PBAC</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
  ShieldCheck,
  UserPlus,
  Search,
  Shield,
  CheckCircle2,
  X,
  Loader2
} from 'lucide-vue-next';

const props = defineProps({
  users: Array,
  masterPermissions: Object,
  roleCounts: Object,
  filters: Object,
});

const searchTerm = ref(props.filters.search || '');
const selectedRoleFilter = ref(props.filters.role || '');

const showModal = ref(false);
const isEditing = ref(false);
const isSubmitting = ref(false);
const formErrors = ref({});
const editingUserId = ref(null);

const form = reactive({
  name: '',
  email: '',
  nip: '',
  role: 'operator',
  password: '',
  permissions: [],
});

const getRoleLabel = (role) => {
  switch (role) {
    case 'admin': return 'Admin';
    case 'manager': return 'Manager';
    case 'tl_operasi': return 'TL Operasi';
    case 'tl_pemeliharaan': return 'TL Pemeliharaan';
    case 'operator': return 'Operator';
    default: return role;
  }
};

const getRoleBadgeClass = (role) => {
  switch (role) {
    case 'admin': return 'bg-rose-500/10 text-rose-400 border-rose-500/30';
    case 'manager': return 'bg-purple-500/10 text-purple-400 border-purple-500/30';
    case 'tl_operasi': return 'bg-blue-500/10 text-blue-400 border-blue-500/30';
    case 'tl_pemeliharaan': return 'bg-amber-500/10 text-amber-400 border-amber-500/30';
    case 'operator': return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30';
    default: return 'bg-slate-500/10 text-slate-400 border-slate-500/30';
  }
};

const applySearch = () => {
  router.get(
    '/users',
    { search: searchTerm.value, role: selectedRoleFilter.value },
    { preserveState: true, preserveScroll: true }
  );
};

const openAddModal = () => {
  isEditing.value = false;
  editingUserId.value = null;
  form.name = '';
  form.email = '';
  form.nip = '';
  form.role = 'operator';
  form.password = 'password';
  form.permissions = [
    'monitoring_arus.view', 'monitoring_arus.input',
    'monitoring_kwh.view', 'monitoring_kwh.input',
    'monitoring_gangguan.view', 'monitoring_gangguan.manage',
    'monitoring_bbm.view', 'monitoring_bbm.input',
    'monitoring_engine.view', 'monitoring_engine.input'
  ];
  formErrors.value = {};
  showModal.value = true;
};

const openEditModal = (user) => {
  isEditing.value = true;
  editingUserId.value = user.id;
  form.name = user.name;
  form.email = user.email;
  form.nip = user.nip !== '-' ? user.nip : '';
  form.role = user.role;
  form.password = '';
  form.permissions = [...user.permissions];
  formErrors.value = {};
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const onRoleChange = () => {
  // Preset default permissions based on role
  if (form.role === 'admin') {
    selectAllPermissions();
  } else if (form.role === 'manager') {
    form.permissions = ['monitoring_arus.view', 'monitoring_kwh.view', 'monitoring_gangguan.view', 'monitoring_bbm.view', 'monitoring_engine.view', 'data.verify'];
  } else if (form.role === 'tl_operasi') {
    form.permissions = ['monitoring_arus.view', 'monitoring_arus.input', 'monitoring_kwh.view', 'monitoring_kwh.input', 'monitoring_bbm.view', 'monitoring_engine.view', 'monitoring_engine.input', 'data.verify'];
  } else if (form.role === 'tl_pemeliharaan') {
    form.permissions = ['monitoring_gangguan.view', 'monitoring_gangguan.manage', 'monitoring_arus.view', 'monitoring_engine.view', 'data.verify'];
  } else {
    form.permissions = ['monitoring_arus.view', 'monitoring_arus.input', 'monitoring_kwh.view', 'monitoring_kwh.input', 'monitoring_gangguan.view', 'monitoring_gangguan.manage', 'monitoring_bbm.view', 'monitoring_bbm.input', 'monitoring_engine.view', 'monitoring_engine.input'];
  }
};

const selectAllPermissions = () => {
  const allSlugs = [];
  Object.values(props.masterPermissions).forEach(group => {
    group.forEach(p => allSlugs.push(p.slug));
  });
  form.permissions = allSlugs;
};

const submitForm = () => {
  isSubmitting.value = true;
  const options = {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      formErrors.value = {};
      showModal.value = false;
    },
    onError: (errors) => {
      formErrors.value = errors;
    },
    onFinish: () => {
      isSubmitting.value = false;
    },
  };
  if (isEditing.value) {
    router.put(`/users/${editingUserId.value}`, { ...form }, options);
  } else {
    router.post('/users', { ...form }, options);
  }
};

const toggleUserStatus = (user) => {
  router.post(`/users/${user.id}/toggle-status`, {}, { preserveScroll: true });
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
