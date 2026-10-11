<template>
  <Head title="Operasi Engine" />
  <div class="space-y-4">
    <!-- Filter tanggal + jenis logsheet (judul halaman ada di Header) -->
    <div class="flex flex-wrap items-center gap-2">
      <label class="sr-only" for="engine-date">Tanggal</label>
      <input
        id="engine-date"
        type="date"
        v-model="dateFilter"
        @change="applyDateFilter"
        class="h-9 px-3 rounded-lg border border-line bg-surface text-body tabular-nums text-fg focus:outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
      />
      <p class="text-caption text-fg-muted">Engine {{ engines.map((engine) => engine.label).join(' & ') }}</p>
      <Segmented v-model="activeTab" :options="tabs" label="Jenis logsheet" tabs class="sm:ml-auto" />
    </div>

    <ControlPanelTable
      v-if="activeTab === 'control_panel'"
      :logs="controlPanelLogs"
      :engines="engines"
      :groups="controlPanelGroups"
      :selectedDate="selectedDate"
    />

    <EngineAreaTable
      v-else
      :logs="engineAreaLogs"
      :engines="engines"
      :fixedFields="engineAreaFixedFields"
      :choiceGroups="engineAreaChoiceGroups"
      :selectedDate="selectedDate"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { queryParam } from '@/utils/url';
import ControlPanelTable from '@/Components/EngineOperation/ControlPanelTable.vue';
import EngineAreaTable from '@/Components/EngineOperation/EngineAreaTable.vue';
import Segmented from '@/Components/Shared/Segmented.vue';

const props = defineProps({
  selectedDate: String,
  engines: Array,
  controlPanelLogs: Array,
  engineAreaLogs: Array,
  controlPanelGroups: Array,
  engineAreaFixedFields: Array,
  engineAreaChoiceGroups: Array,
});

// Tab dibaca dari ?tab= agar tetap sama setelah pindah tanggal (mis. setelah simpan data tanggal lain)
const activeTab = ref(queryParam(usePage().url, 'tab', ['control_panel', 'engine_area'], 'control_panel'));

const tabs = computed(() => [
  { key: 'control_panel', label: 'Control panel', count: props.controlPanelLogs.length },
  { key: 'engine_area', label: 'Engine area', count: props.engineAreaLogs.length },
]);

const dateFilter = ref(props.selectedDate);

const applyDateFilter = () => {
  router.get(
    '/monitoring-operasi-engine',
    { date: dateFilter.value, tab: activeTab.value },
    { preserveState: true, preserveScroll: true }
  );
};
</script>
