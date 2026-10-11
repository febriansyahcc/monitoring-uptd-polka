<template>
  <!-- Sidebar desktop (>= 768px) -->
  <aside
    :class="[
      'hidden md:flex flex-col sticky top-0 h-screen shrink-0 border-r border-line bg-surface text-fg transition-[width] duration-200',
      collapsed ? 'w-16' : 'w-60',
    ]"
  >
    <!-- Identitas aplikasi -->
    <div :class="['h-14 flex items-center gap-3 shrink-0 border-b border-line', collapsed ? 'justify-center px-2' : 'px-4']">
      <span
        class="w-8 h-8 rounded-md bg-brand text-on-solid flex items-center justify-center text-caption font-semibold tracking-wide shrink-0"
        aria-hidden="true"
      >
        PLN
      </span>
      <div v-if="!collapsed" class="min-w-0 flex-1">
        <p class="text-body font-semibold leading-tight truncate">PLN Monitor</p>
        <p class="text-caption text-fg-muted truncate">ULPLTD Poka</p>
      </div>
      <button
        v-if="!collapsed"
        type="button"
        class="p-1.5 -mr-1.5 rounded-md text-fg-muted hover:text-fg hover:bg-surface-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-brand"
        title="Ciutkan sidebar"
        aria-label="Ciutkan sidebar"
        @click="$emit('toggleCollapse')"
      >
        <PanelLeftClose class="w-4 h-4" />
      </button>
    </div>

    <!-- Menu -->
    <nav class="flex-1 overflow-y-auto px-2 py-4 space-y-5" aria-label="Menu utama">
      <div v-for="group in groups" :key="group.key" class="space-y-0.5">
        <p v-if="!collapsed" class="px-3 pb-1 text-caption text-fg-muted">{{ group.label }}</p>
        <div v-else class="mx-3 mb-2 border-t border-line" aria-hidden="true"></div>

        <Link
          v-for="item in group.items"
          :key="item.href"
          :href="item.href"
          :aria-current="isActive(item) ? 'page' : undefined"
          :title="collapsed ? item.label : undefined"
          :class="[
            'relative flex items-center gap-3 rounded-md py-2 text-body transition-colors',
            'focus:outline-none focus-visible:ring-2 focus-visible:ring-brand',
            collapsed ? 'justify-center px-0' : 'px-3',
            isActive(item)
              ? 'bg-brand-soft text-brand-text font-medium before:absolute before:left-0 before:top-1.5 before:bottom-1.5 before:w-[3px] before:rounded-full before:bg-brand'
              : 'text-fg-muted hover:text-fg hover:bg-surface-muted',
          ]"
        >
          <component :is="item.icon" class="w-[18px] h-[18px] shrink-0" aria-hidden="true" />
          <span v-if="!collapsed" class="truncate">{{ item.label }}</span>
        </Link>
      </div>
    </nav>
  </aside>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { PanelLeftClose } from 'lucide-vue-next';
import { usePermission } from '@/composables/usePermission';
import { NAV_ITEMS, NAV_GROUPS } from '@/utils/navigation';

defineProps({
  collapsed: { type: Boolean, default: false },
});

defineEmits(['toggleCollapse']);

const page = usePage();
const { can } = usePermission();

const groups = computed(() =>
  NAV_GROUPS.map((group) => ({
    ...group,
    items: NAV_ITEMS.filter((item) => item.group === group.key && (!item.permission || can(item.permission))),
  })).filter((group) => group.items.length > 0)
);

const isActive = (item) => item.match(page.url);
</script>
