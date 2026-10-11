<template>
  <header class="h-14 sticky top-0 z-20 flex items-center justify-between gap-3 px-4 sm:px-6 border-b border-line bg-surface">
    <div class="flex items-center gap-3 min-w-0">
      <!-- Identitas di mobile (sidebar tersembunyi) -->
      <span
        class="md:hidden w-8 h-8 rounded-md bg-brand text-on-solid flex items-center justify-center text-caption font-semibold shrink-0"
        aria-hidden="true"
      >
        PLN
      </span>

      <button
        v-if="sidebarCollapsed"
        type="button"
        class="hidden md:inline-flex p-1.5 -ml-1.5 rounded-md text-fg-muted hover:text-fg hover:bg-surface-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-brand"
        title="Perluas sidebar"
        aria-label="Perluas sidebar"
        @click="$emit('toggleDesktopSidebar')"
      >
        <PanelLeftOpen class="w-4 h-4" />
      </button>

      <!-- Satu-satunya judul halaman -->
      <h1 class="text-page font-semibold text-fg truncate">{{ pageTitle }}</h1>
    </div>

    <div class="flex items-center gap-1 sm:gap-2 shrink-0">
      <!-- Konteks waktu: tanggal · jam WIT · shift berjalan -->
      <p v-if="clock" class="hidden lg:block text-label text-fg-muted tabular-nums whitespace-nowrap mr-2">
        {{ clock.date }} · {{ clock.time }} WIT · <span class="text-fg font-medium">{{ shiftLabel }}</span>
      </p>
      <p v-if="clock" class="hidden sm:block lg:hidden text-label text-fg-muted tabular-nums whitespace-nowrap mr-2">
        {{ clock.time }} WIT · <span class="text-fg font-medium">{{ shiftLabel }}</span>
      </p>

      <button
        type="button"
        class="p-2 rounded-md text-fg-muted hover:text-fg hover:bg-surface-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-brand"
        :title="isDarkMode ? 'Beralih ke mode terang' : 'Beralih ke mode gelap'"
        :aria-label="isDarkMode ? 'Beralih ke mode terang' : 'Beralih ke mode gelap'"
        @click="$emit('toggleTheme')"
      >
        <Sun v-if="isDarkMode" class="w-4 h-4" />
        <Moon v-else class="w-4 h-4" />
      </button>

      <template v-if="user">
        <div class="hidden sm:block text-right pl-3 ml-1 border-l border-line">
          <p class="text-label font-medium text-fg truncate max-w-[180px]">{{ user.name }}</p>
          <p class="text-caption text-fg-muted">{{ ROLE_LABELS[user.role] ?? user.role }}</p>
        </div>

        <Link
          href="/logout"
          method="post"
          as="button"
          class="p-2 rounded-md text-fg-muted hover:text-fg hover:bg-surface-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-brand"
          title="Keluar"
          aria-label="Keluar dari akun"
        >
          <LogOut class="w-4 h-4" />
        </Link>
      </template>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { PanelLeftOpen, Sun, Moon, LogOut } from 'lucide-vue-next';
import { navItemFor, ROLE_LABELS } from '@/utils/navigation';
import { SHIFT_LABELS, shiftKeyAt } from '@/utils/shift';

defineProps({
  sidebarCollapsed: { type: Boolean, default: false },
  isDarkMode: { type: Boolean, default: false },
});

defineEmits(['toggleDesktopSidebar', 'toggleTheme']);

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const pageTitle = computed(() => navItemFor(page.url)?.title ?? 'PLN Monitor');

// Jam operasional dipaku ke WIT (Asia/Jayapura), bukan zona waktu perangkat
const TZ = 'Asia/Jayapura';
const clock = ref(null);

const tick = () => {
  const now = new Date();
  const hour = Number(new Intl.DateTimeFormat('en-GB', { hour: 'numeric', hourCycle: 'h23', timeZone: TZ }).format(now));
  clock.value = {
    date: now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'short', year: 'numeric', timeZone: TZ }),
    time: now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', timeZone: TZ }),
    shift: shiftKeyAt(hour),
  };
};

// Shift dari server dipakai sampai jam di browser siap
const shiftLabel = computed(() => SHIFT_LABELS[clock.value?.shift] ?? page.props.currentShift?.label ?? '');

let timer = null;
onMounted(() => {
  tick();
  timer = setInterval(tick, 15000);
});
onUnmounted(() => clearInterval(timer));
</script>
