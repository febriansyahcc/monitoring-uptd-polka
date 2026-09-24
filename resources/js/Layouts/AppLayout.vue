<template>
  <div
    :class="[
      'min-h-screen flex transition-colors duration-300 selection:bg-cyan-500 selection:text-slate-950',
      isDarkMode ? 'bg-slate-950 text-slate-100' : 'bg-slate-50 text-slate-900'
    ]"
  >
    <!-- Sidebar Component (Desktop Only, hidden on Mobile) -->
    <Sidebar
      :collapsed="isSidebarCollapsed"
      :isDarkMode="isDarkMode"
      @toggleCollapse="toggleSidebar"
    />

    <!-- Main Content Container -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen pb-16 md:pb-6">
      <!-- Top Sticky Header -->
      <Header
        :sidebarCollapsed="isSidebarCollapsed"
        :isDarkMode="isDarkMode"
        @toggleDesktopSidebar="toggleSidebar"
        @toggleTheme="toggleTheme"
      />

      <!-- Page Content (layout persisten: di-set di app.js, halaman tidak membungkus AppLayout) -->
      <main class="flex-1 p-4 sm:p-6 max-w-7xl w-full mx-auto space-y-6">
        <slot />
      </main>
    </div>

    <!-- Mobile Bottom Navigation -->
    <BottomNav :isDarkMode="isDarkMode" />

    <!-- Notifikasi flash global -->
    <Toast />
  </div>
</template>

<script setup>
import { ref, provide } from 'vue';
import Sidebar from '../Components/Desktop/Sidebar.vue';
import Header from '../Components/Desktop/Header.vue';
import BottomNav from '../Components/Mobile/BottomNav.vue';
import Toast from '../Components/Shared/Toast.vue';

const THEME_KEY = 'pln_theme';
const SIDEBAR_KEY = 'pln_sidebar_collapsed';

const readStorage = (key) => {
  try {
    return localStorage.getItem(key);
  } catch (e) {
    return null;
  }
};

const writeStorage = (key, value) => {
  try {
    localStorage.setItem(key, value);
  } catch (e) {
    // Storage diblokir (mode privat, dsb.): state tetap berlaku selama sesi ini
  }
};

// Status collapse sidebar disimpan agar tetap sama setelah pindah menu / reload
const isSidebarCollapsed = ref(readStorage(SIDEBAR_KEY) === '1');

const toggleSidebar = () => {
  isSidebarCollapsed.value = !isSidebarCollapsed.value;
  writeStorage(SIDEBAR_KEY, isSidebarCollapsed.value ? '1' : '0');
};

// Class `dark` sudah dipasang di <html> oleh script inline di app.blade.php sebelum render,
// jadi nilai awal dibaca dari sana (tanpa kedip tema terang).
const isDarkMode = ref(document.documentElement.classList.contains('dark'));

const toggleTheme = () => {
  isDarkMode.value = !isDarkMode.value;
  document.documentElement.classList.toggle('dark', isDarkMode.value);
  writeStorage(THEME_KEY, isDarkMode.value ? 'dark' : 'light');
};

provide('isDarkMode', isDarkMode);
</script>

<style>
/* Global Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
  height: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(148, 163, 184, 0.3);
  border-radius: 9999px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(148, 163, 184, 0.5);
}
</style>
