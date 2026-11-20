<template>
  <header data-print-hide class="h-14 sticky top-0 z-20 flex items-center justify-between gap-3 px-4 sm:px-6 border-b border-line bg-surface">
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
      <p v-if="shiftLabel" class="hidden sm:block text-label text-fg-muted tabular-nums whitespace-nowrap mr-2">
        <template v-if="clock">
          <span class="hidden lg:inline">{{ clock.date }} · </span>{{ clock.time }} WIT ·
        </template>
        <span v-else-if="$page.props.today" class="hidden lg:inline">{{ $page.props.today }} · </span>
        <span class="text-fg font-medium">{{ shiftLabel }}</span>
      </p>

      <!-- Bantuan kontekstual: buka bagian panduan untuk halaman ini -->
      <Link
        v-if="helpHref"
        :href="helpHref"
        class="p-2 rounded-md text-fg-muted hover:text-fg hover:bg-surface-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-brand"
        title="Panduan halaman ini"
        aria-label="Buka panduan untuk halaman ini"
      >
        <CircleHelp class="w-4 h-4" />
      </Link>

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
        <div class="hidden sm:block pl-2 ml-1 border-l border-line">
          <Link
            href="/profil"
            :class="[
              'block text-right px-3 py-1 rounded-md transition-colors hover:bg-surface-muted',
              'focus:outline-none focus-visible:ring-2 focus-visible:ring-brand',
              isProfilePage ? 'bg-surface-muted' : '',
            ]"
            title="Profil & ganti password"
            :aria-current="isProfilePage ? 'page' : undefined"
          >
            <p class="text-label font-medium text-fg truncate max-w-[180px]">{{ user.name }}</p>
            <p class="text-caption text-fg-muted">{{ ROLE_LABELS[user.role] ?? user.role }}</p>
          </Link>
        </div>

        <!-- Di mobile nama disembunyikan, jadi profil diakses lewat ikon -->
        <Link
          href="/profil"
          class="sm:hidden p-2 rounded-md text-fg-muted hover:text-fg hover:bg-surface-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-brand"
          title="Profil saya"
          aria-label="Profil saya"
        >
          <UserRound class="w-4 h-4" />
        </Link>

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
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { PanelLeftOpen, Sun, Moon, LogOut, UserRound, CircleHelp } from 'lucide-vue-next';
import { navItemFor, ROLE_LABELS } from '@/utils/navigation';
import { manualAnchorFor } from '@/utils/manual';
import { SHIFT_LABELS, shiftKeyAt } from '@/utils/shift';

defineProps({
  sidebarCollapsed: { type: Boolean, default: false },
  isDarkMode: { type: Boolean, default: false },
});

defineEmits(['toggleDesktopSidebar', 'toggleTheme']);

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
// Halaman di luar menu (Profil) tidak ada di NAV_ITEMS
const isProfilePage = computed(() => page.url.startsWith('/profil'));
// Di halaman Panduan sendiri tombol "?" tidak ditampilkan
const helpHref = computed(() => {
  if (page.url.startsWith('/panduan')) return null;
  const anchor = manualAnchorFor(page.url);
  return anchor ? `/panduan#${anchor}` : '/panduan';
});
const pageTitle = computed(() => navItemFor(page.url)?.title ?? (isProfilePage.value ? 'Profil Saya' : 'PLN Monitor'));

// Jam operasional dipaku ke WIT (Asia/Jayapura) dan disinkronkan dengan waktu server
// agar tanggal di Header selalu konsisten dengan tanggal query backend.
const TZ = 'Asia/Jayapura';
const clock = ref(null);
const serverOffsetMs = ref(0);

const syncServerOffset = () => {
  const serverMs = page.props.serverNowMs;
  serverOffsetMs.value = typeof serverMs === 'number' ? serverMs - Date.now() : 0;
};

const tick = () => {
  const now = new Date(Date.now() + serverOffsetMs.value);
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
watch(
  () => page.props.serverNowMs,
  () => {
    if (clock.value === null) return;
    syncServerOffset();
    tick();
  }
);

onMounted(() => {
  syncServerOffset();
  tick();
  timer = setInterval(tick, 15000);
});
onUnmounted(() => clearInterval(timer));
</script>
