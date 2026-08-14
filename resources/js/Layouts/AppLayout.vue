<template>
  <div
    :class="[
      'min-h-screen flex transition-colors duration-300 selection:bg-cyan-500 selection:text-slate-950',
      isDarkMode ? 'bg-slate-950 text-slate-100 dark' : 'bg-slate-50 text-slate-900'
    ]"
  >
    <!-- Sidebar Component (Desktop Only, hidden on Mobile) -->
    <Sidebar
      :collapsed="isSidebarCollapsed"
      :isDarkMode="isDarkMode"
      @toggleCollapse="isSidebarCollapsed = !isSidebarCollapsed"
    />

    <!-- Main Content Container -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen pb-16 md:pb-6">
      <!-- Top Sticky Header -->
      <Header
        :sidebarCollapsed="isSidebarCollapsed"
        :isDarkMode="isDarkMode"
        @toggleDesktopSidebar="isSidebarCollapsed = !isSidebarCollapsed"
        @toggleTheme="toggleTheme"
      />

      <!-- Page Content Slot -->
      <main class="flex-1 p-4 sm:p-6 max-w-7xl w-full mx-auto space-y-6">
        <slot :isDarkMode="isDarkMode" />
      </main>
    </div>

    <!-- Mobile Bottom Navigation -->
    <BottomNav :isDarkMode="isDarkMode" />
  </div>
</template>

<script setup>
import { ref, onMounted, provide } from 'vue';
import Sidebar from '../Components/Desktop/Sidebar.vue';
import Header from '../Components/Desktop/Header.vue';
import BottomNav from '../Components/Mobile/BottomNav.vue';

// State management for Sidebar collapse
const isSidebarCollapsed = ref(false);

// State management for Dark Mode
const isDarkMode = ref(false);

const toggleTheme = () => {
  isDarkMode.value = !isDarkMode.value;
  localStorage.setItem('pln_theme', isDarkMode.value ? 'dark' : 'light');
};

provide('isDarkMode', isDarkMode);

onMounted(() => {
  const savedTheme = localStorage.getItem('pln_theme');
  if (savedTheme) {
    isDarkMode.value = savedTheme === 'dark';
  } else {
    isDarkMode.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
  }
});
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
