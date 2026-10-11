<template>
  <div>
    <!-- Bar navigasi mobile (< 768px): maks. 4 menu + "Lainnya" -->
    <nav
      class="fixed bottom-0 inset-x-0 md:hidden z-40 border-t border-line bg-surface px-1 pb-[env(safe-area-inset-bottom)] flex items-stretch justify-around"
      aria-label="Menu utama"
    >
      <Link
        v-for="item in primaryItems"
        :key="item.href"
        :href="item.href"
        :aria-current="isActive(item) ? 'page' : undefined"
        :class="[
          'flex-1 flex flex-col items-center justify-center gap-0.5 min-h-14 text-caption transition-colors',
          'focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand',
          isActive(item) ? 'text-brand-text font-medium' : 'text-fg-muted',
        ]"
      >
        <component :is="item.icon" class="w-5 h-5" aria-hidden="true" />
        <span>{{ item.short }}</span>
      </Link>

      <button
        v-if="secondaryItems.length || user"
        type="button"
        :class="[
          'flex-1 flex flex-col items-center justify-center gap-0.5 min-h-14 text-caption transition-colors',
          'focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand',
          isMoreActive || showMore ? 'text-brand-text font-medium' : 'text-fg-muted',
        ]"
        :aria-expanded="showMore"
        aria-label="Menu lainnya"
        @click="showMore = !showMore"
      >
        <MoreHorizontal class="w-5 h-5" aria-hidden="true" />
        <span>Lainnya</span>
      </button>
    </nav>

    <!-- Bottom sheet "Lainnya" -->
    <Teleport to="body">
      <div v-if="showMore" class="fixed inset-0 z-50 md:hidden flex flex-col justify-end">
        <div class="fixed inset-0 bg-black/50" @click="showMore = false" />

        <div
          class="relative w-full max-h-[85vh] overflow-y-auto rounded-t-lg border-t border-line bg-surface text-fg p-4 pb-[calc(1rem+env(safe-area-inset-bottom))] space-y-4"
          role="dialog"
          aria-modal="true"
          aria-label="Menu lainnya"
        >
          <div class="flex items-center justify-between">
            <h2 class="text-section font-semibold">Menu lainnya</h2>
            <button
              type="button"
              class="p-2 -m-2 rounded-md text-fg-muted hover:text-fg focus:outline-none focus-visible:ring-2 focus-visible:ring-brand"
              aria-label="Tutup"
              @click="showMore = false"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <div v-if="secondaryItems.length" class="divide-y divide-line border border-line rounded-lg">
            <Link
              v-for="item in secondaryItems"
              :key="item.href"
              :href="item.href"
              :aria-current="isActive(item) ? 'page' : undefined"
              :class="['flex items-center gap-3 px-3 min-h-12', isActive(item) ? 'text-brand-text' : 'text-fg']"
              @click="showMore = false"
            >
              <component :is="item.icon" class="w-5 h-5 shrink-0 text-fg-muted" aria-hidden="true" />
              <span class="flex-1 min-w-0">
                <span class="block text-body font-medium">{{ item.label }}</span>
                <span class="block text-caption text-fg-muted truncate">{{ item.desc }}</span>
              </span>
            </Link>
          </div>

          <div v-if="user" class="flex items-center justify-between gap-3 border border-line rounded-lg p-3">
            <div class="min-w-0">
              <p class="text-body font-medium truncate">{{ user.name }}</p>
              <p class="text-caption text-fg-muted truncate">{{ ROLE_LABELS[user.role] ?? user.role }} · {{ user.email }}</p>
            </div>
            <Link
              href="/logout"
              method="post"
              as="button"
              class="shrink-0 inline-flex items-center gap-2 min-h-11 px-3 rounded-lg border border-line text-body text-fg hover:bg-surface-muted"
              @click="showMore = false"
            >
              <LogOut class="w-4 h-4" aria-hidden="true" />
              Keluar
            </Link>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { MoreHorizontal, X, LogOut } from 'lucide-vue-next';
import { usePermission } from '@/composables/usePermission';
import { NAV_ITEMS, ROLE_LABELS } from '@/utils/navigation';

const page = usePage();
const { can } = usePermission();
const showMore = ref(false);

const user = computed(() => page.props.auth?.user ?? null);
const permittedItems = computed(() => NAV_ITEMS.filter((item) => !item.permission || can(item.permission)));
const primaryItems = computed(() => permittedItems.value.slice(0, 4));
const secondaryItems = computed(() => permittedItems.value.slice(4));

const isActive = (item) => item.match(page.url);
const isMoreActive = computed(() => secondaryItems.value.some(isActive));

const onKeydown = (e) => {
  if (e.key === 'Escape') showMore.value = false;
};
onMounted(() => window.addEventListener('keydown', onKeydown));
onUnmounted(() => window.removeEventListener('keydown', onKeydown));
</script>
