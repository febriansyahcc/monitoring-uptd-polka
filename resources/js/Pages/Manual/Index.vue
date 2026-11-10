<template>
  <Head title="Panduan" />

  <div class="manual-page lg:grid lg:grid-cols-[14rem_minmax(0,1fr)] lg:gap-6 items-start">
    <!-- Daftar isi (desktop: menempel di kiri) -->
    <nav class="manual-toc hidden lg:block sticky top-20 space-y-0.5" aria-label="Daftar isi panduan">
      <p class="px-3 pb-1 text-caption text-fg-muted">Daftar isi</p>
      <a
        v-for="section in visibleSections"
        :key="section.id"
        :href="`#${section.id}`"
        :aria-current="activeId === section.id ? 'location' : undefined"
        :class="[
          'flex items-center gap-2.5 rounded-md px-3 py-1.5 text-label transition-colors',
          'focus:outline-none focus-visible:ring-2 focus-visible:ring-brand',
          activeId === section.id ? 'bg-brand-soft text-brand-text font-medium' : 'text-fg-muted hover:text-fg hover:bg-surface-muted',
        ]"
        @click.prevent="goTo(section.id)"
      >
        <component :is="iconOf(section)" class="w-4 h-4 shrink-0" aria-hidden="true" />
        <span class="truncate">{{ section.title }}</span>
      </a>
    </nav>

    <div class="space-y-4 min-w-0">
      <!-- Sampul -->
      <section class="manual-cover bg-surface border border-line rounded-lg p-4 sm:p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div class="min-w-0">
            <p class="text-caption text-fg-muted">Versi {{ MANUAL_META.version }} · diperbarui {{ MANUAL_META.updated }}</p>
            <h2 class="text-section font-semibold text-fg mt-0.5">{{ MANUAL_META.title }}</h2>
            <p class="text-body text-fg-muted mt-1">{{ MANUAL_META.subtitle }}</p>
            <p v-if="!isAdmin" class="text-caption text-fg-muted mt-2">
              Panduan ini disesuaikan dengan hak akses akun Anda ({{ ROLE_LABELS[user?.role] ?? user?.role }}).
            </p>
          </div>
          <Button variant="secondary" class="manual-no-print" @click="printManual">
            <Printer class="w-4 h-4" aria-hidden="true" />
            <span>Cetak / Simpan PDF</span>
          </Button>
        </div>

        <!-- Pencarian -->
        <div class="manual-no-print relative mt-4">
          <label for="manual-search" class="sr-only">Cari di panduan</label>
          <input
            id="manual-search"
            v-model="query"
            type="search"
            placeholder="Cari di panduan, mis. password, HOP, simpan semua..."
            class="w-full h-9 pl-9 pr-3 rounded-lg text-body border focus:outline-none focus:border-brand focus:ring-2 focus:ring-brand/20 bg-surface border-line text-fg placeholder:text-fg-subtle"
          />
          <Search class="w-4 h-4 text-fg-muted absolute left-3 top-2.5" aria-hidden="true" />
        </div>

        <!-- Daftar isi (mobile/tablet) -->
        <div class="manual-no-print lg:hidden flex flex-wrap gap-1.5 mt-3">
          <a
            v-for="section in visibleSections"
            :key="section.id"
            :href="`#${section.id}`"
            class="px-2.5 py-1 rounded-md border border-line text-caption text-fg-muted hover:text-fg hover:bg-surface-muted"
            @click.prevent="goTo(section.id)"
          >
            {{ section.title }}
          </a>
        </div>
      </section>

      <p v-if="query && !visibleSections.length" class="text-body text-fg-muted px-1">
        Tidak ada bagian panduan yang cocok dengan "{{ query }}".
      </p>

      <!-- Isi panduan -->
      <section
        v-for="section in visibleSections"
        :id="section.id"
        :key="section.id"
        :ref="(el) => registerSection(section.id, el)"
        class="manual-section bg-surface border border-line rounded-lg p-4 sm:p-5 scroll-mt-20"
      >
        <header class="flex items-start gap-3 pb-3 mb-4 border-b border-line">
          <span class="w-8 h-8 rounded-md bg-brand-soft text-brand-text flex items-center justify-center shrink-0" aria-hidden="true">
            <component :is="iconOf(section)" class="w-4 h-4" />
          </span>
          <div class="min-w-0 flex-1">
            <h2 class="text-section font-semibold text-fg">{{ section.number }}. {{ section.title }}</h2>
            <p class="text-caption text-fg-muted mt-0.5">{{ section.summary }}</p>
          </div>
          <Link
            v-if="section.href"
            :href="section.href"
            class="manual-no-print shrink-0 inline-flex items-center gap-1 text-label font-medium text-brand-text hover:underline"
          >
            Buka halaman
            <ArrowRight class="w-4 h-4" aria-hidden="true" />
          </Link>
        </header>

        <div class="space-y-5">
          <ManualBlock v-for="(block, b) in section.blocks" :key="b" :block="block" />
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
  ArrowRight, Printer, Search, BookOpen, LogIn, ShieldCheck, LayoutDashboard, Activity, Zap, Cpu,
  AlertTriangle, Fuel, Users, HelpCircle,
} from 'lucide-vue-next';
import Button from '@/Components/Shared/Button.vue';
import ManualBlock from '@/Components/Manual/ManualBlock.vue';
import { usePermission } from '@/composables/usePermission';
import { NAV_ITEMS, ROLE_LABELS } from '@/utils/navigation';
import { MANUAL_META, MANUAL_SECTIONS } from '@/utils/manual';

const ICONS = { BookOpen, LogIn, ShieldCheck, LayoutDashboard, Activity, Zap, Cpu, AlertTriangle, Fuel, Users, HelpCircle };
const iconOf = (section) => ICONS[section.icon] ?? BookOpen;

const { can, user } = usePermission();
const isAdmin = computed(() => user.value?.role === 'admin');

// Bagian & blok yang tidak diizinkan disembunyikan; tautan "Buka halaman" diambil dari menu
const allowedSections = computed(() =>
  MANUAL_SECTIONS.filter((s) => !s.permission || can(s.permission)).map((s, i) => ({
    ...s,
    number: i + 1, // tetap sama walau sebagian bab tersaring oleh pencarian
    href: NAV_ITEMS.find((n) => n.href !== '/' && n.href.slice(1) === s.id)?.href ?? (s.id === 'dashboard' ? '/' : null),
    blocks: s.blocks.filter((b) => !b.requires || can(b.requires)),
  }))
);

// ---------- Pencarian ----------
const query = ref('');
const blockText = (b) => [b.title, b.text, ...(b.items ?? []), ...(b.head ?? []), ...(b.rows ?? []).flat()].filter(Boolean).join(' ');

const visibleSections = computed(() => {
  const q = query.value.trim().toLowerCase();
  if (!q) return allowedSections.value;
  return allowedSections.value
    .map((s) => {
      const headerHit = `${s.title} ${s.summary}`.toLowerCase().includes(q);
      const blocks = headerHit ? s.blocks : s.blocks.filter((b) => blockText(b).toLowerCase().includes(q));
      return { ...s, blocks };
    })
    .filter((s) => s.blocks.length);
});

// ---------- Navigasi daftar isi ----------
const activeId = ref(null);
const sectionEls = new Map();
let observer = null;
const registerSection = (id, el) => {
  if (el) {
    if (sectionEls.get(id) !== el) observer?.observe(el);
    sectionEls.set(id, el);
  } else {
    sectionEls.delete(id);
  }
};

const goTo = (id) => {
  const el = document.getElementById(id);
  if (!el) return;
  el.scrollIntoView({ behavior: 'smooth', block: 'start' });
  history.replaceState(history.state, '', `#${id}`);
  activeId.value = id;
};

onMounted(async () => {
  await nextTick();
  // Dibuka dari tombol "?" di Header: /panduan#monitoring-arus
  const hash = decodeURIComponent(window.location.hash.slice(1));
  if (hash && document.getElementById(hash)) {
    document.getElementById(hash).scrollIntoView({ block: 'start' });
    activeId.value = hash;
  } else {
    activeId.value = visibleSections.value[0]?.id ?? null;
  }

  observer = new IntersectionObserver(
    (entries) => {
      const visible = entries.filter((e) => e.isIntersecting).sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);
      if (visible[0]) activeId.value = visible[0].target.id;
    },
    { rootMargin: '-80px 0px -60% 0px' }
  );
  sectionEls.forEach((el) => observer.observe(el));
});
onBeforeUnmount(() => observer?.disconnect());

// Cetak memakai seluruh panduan yang diizinkan (pencarian dikosongkan dulu)
const printManual = async () => {
  query.value = '';
  await nextTick();
  window.print();
};
</script>
