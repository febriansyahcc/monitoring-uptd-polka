<template>
  <header
    :class="[
      'h-16 sticky top-0 z-20 flex items-center justify-between px-4 sm:px-6 transition-colors duration-300 shadow-sm border-b',
      isDarkMode
        ? 'bg-slate-900/90 border-slate-800 backdrop-blur-md'
        : 'bg-white/90 border-slate-200 backdrop-blur-md'
    ]"
  >
    <div class="flex items-center gap-3">
      <!-- Mobile App Identity (Appears strictly on mobile screens < 768px) -->
      <div class="flex items-center gap-2.5 md:hidden">
        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center shadow-md shadow-cyan-500/20 shrink-0">
          <Zap class="w-4 h-4 text-white" />
        </div>
        <div>
          <h1 :class="['font-extrabold text-xs tracking-wider leading-tight', isDarkMode ? 'text-white' : 'text-slate-900']">
            PLN MONITOR
          </h1>
          <p class="text-[9px] text-cyan-600 font-semibold tracking-wide uppercase">
            ULPLTD POKA
          </p>
        </div>
      </div>

      <!-- Toggle Desktop Sidebar Button (Hanya muncul jika sidebar di-ciutkan / collapsed) -->
      <button
        v-if="sidebarCollapsed"
        @click="$emit('toggleDesktopSidebar')"
        :class="[
          'hidden md:flex p-2 rounded-xl transition-all border',
          isDarkMode
            ? 'bg-slate-800/80 border-slate-700/80 text-slate-400 hover:text-white'
            : 'bg-slate-100 border-slate-200 text-slate-600 hover:text-slate-900'
        ]"
        title="Perluas Sidebar"
      >
        <PanelLeft class="w-5 h-5 text-cyan-500" />
      </button>
    </div>

    <!-- Right Controls -->
    <div class="flex items-center gap-3">
      <!-- Dark / Light Mode Toggle Button -->
      <button
        @click="$emit('toggleTheme')"
        :class="[
          'p-2 rounded-xl border transition-all duration-200 flex items-center justify-center',
          isDarkMode
            ? 'bg-slate-800 border-slate-700 text-amber-400 hover:bg-slate-700 shadow-sm'
            : 'bg-slate-100 border-slate-200 text-indigo-600 hover:bg-slate-200 shadow-sm'
        ]"
        :title="isDarkMode ? 'Beralih ke Mode Terang (Light Mode)' : 'Beralih ke Mode Gelap (Dark Mode)'"
      >
        <Sun v-if="isDarkMode" class="w-4 h-4 text-amber-400" />
        <Moon v-else class="w-4 h-4 text-indigo-600" />
      </button>



      <!-- User Info & Logout Button -->
      <div v-if="$page.props.auth.user" :class="['flex items-center gap-3 pl-3 border-l', isDarkMode ? 'border-slate-800' : 'border-slate-200']">


        <div class="hidden sm:block text-right text-xs">
          <p :class="['font-bold truncate max-w-[180px]', isDarkMode ? 'text-white' : 'text-slate-900']">
            {{ $page.props.auth.user.name }}
          </p>
          <span :class="['text-[10px] font-extrabold px-1.5 py-0.5 rounded border uppercase inline-block mt-0.5', getRoleBadgeClass($page.props.auth.user.role)]">
            {{ getRoleLabel($page.props.auth.user.role) }}
          </span>
        </div>

        <Link
          href="/logout"
          method="post"
          as="button"
          :class="[
            'p-2 rounded-xl border transition-all text-rose-500 hover:bg-rose-500/10 border-rose-500/30',
            isDarkMode ? 'bg-slate-800' : 'bg-slate-100'
          ]"
          title="Keluar (Logout)"
        >
          <LogOut class="w-4 h-4" />
        </Link>
      </div>

      <div v-else>
        <Link
          href="/login"
          class="px-3 py-1.5 rounded-xl bg-cyan-500 text-slate-950 font-bold text-xs shadow hover:bg-cyan-400"
        >
          Masuk
        </Link>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Zap, Menu, PanelLeft, Clock, Sun, Moon, LogOut } from 'lucide-vue-next';

defineProps({
  sidebarCollapsed: {
    type: Boolean,
    default: false,
  },
  isDarkMode: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['openMobileSidebar', 'toggleDesktopSidebar', 'toggleTheme']);

const currentTime = ref('');

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
    case 'admin': return 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border-rose-300 dark:border-rose-700';
    case 'manager': return 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300 border-purple-300 dark:border-purple-700';
    case 'tl_operasi': return 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 border-blue-300 dark:border-blue-700';
    case 'tl_pemeliharaan': return 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border-amber-300 dark:border-amber-700';
    default: return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-300 dark:border-emerald-700';
  }
};

const updateTime = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
};

let timer = null;
onMounted(() => {
  updateTime();
  timer = setInterval(updateTime, 1000);
});

onUnmounted(() => {
  if (timer) clearInterval(timer);
});
</script>
