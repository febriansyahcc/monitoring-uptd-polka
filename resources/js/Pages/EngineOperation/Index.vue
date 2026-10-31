<template>
  <Head title="Operasi Engine" />
  <div class="space-y-4">
    <!-- Filter tanggal + jenis engine + jenis logsheet (judul halaman ada di Header) -->
    <div class="flex flex-wrap items-center gap-2">
      <label class="sr-only" for="engine-date">Tanggal</label>
      <input
        id="engine-date"
        type="date"
        v-model="dateFilter"
        @change="applyFilter"
        class="h-9 px-3 rounded-lg border border-line bg-surface text-body tabular-nums text-fg focus:outline-none focus:border-brand focus:ring-2 focus:ring-brand/20"
      />
      <Segmented :modelValue="engineType" @update:modelValue="changeEngineType" :options="engineTypes" label="Jenis engine" />
      <Segmented v-model="activeTab" :options="tabs" label="Jenis logsheet" tabs class="sm:ml-auto" />
    </div>

    <template v-if="engineType === 'cat'">
      <GroupedLogTable
        v-if="activeTab === 'control_panel'"
        :logs="controlPanelLogs"
        :engines="engines"
        :groups="controlPanelGroups"
        :selectedDate="selectedDate"
        description="Pembacaan panel kontrol generator per jam per engine"
      />

      <EngineAreaTable
        v-else
        :logs="engineAreaLogs"
        :engines="engines"
        :fixedFields="engineAreaFixedFields"
        :choiceGroups="engineAreaChoiceGroups"
        :selectedDate="selectedDate"
      />
    </template>

    <template v-else>
      <GroupedLogTable
        v-if="activeTab === 'control_panel'"
        :logs="abcControlPanelLogs"
        :groups="abcControlPanelGroups"
        :selectedDate="selectedDate"
        title="Control Panel ABC #6"
        endpoint="/monitoring-operasi-engine/abc/control-panel"
        engineType="abc"
      />

      <GroupedLogTable
        v-else
        :logs="abcEngineAreaLogs"
        :groups="abcEngineAreaGroups"
        :selectedDate="selectedDate"
        title="Engine Area ABC #6"
        endpoint="/monitoring-operasi-engine/abc/engine-area"
        tab="engine_area"
        engineType="abc"
      />
    </template>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { queryParam } from '@/utils/url';
import GroupedLogTable from '@/Components/EngineOperation/GroupedLogTable.vue';
import EngineAreaTable from '@/Components/EngineOperation/EngineAreaTable.vue';
import Segmented from '@/Components/Shared/Segmented.vue';

const props = defineProps({
  selectedDate: String,
  engineType: { type: String, default: 'cat' },
  engineTypes: Array,
  // CAT #4
  engines: Array,
  controlPanelLogs: Array,
  engineAreaLogs: Array,
  controlPanelGroups: Array,
  engineAreaFixedFields: Array,
  engineAreaChoiceGroups: Array,
  // ABC #6
  abcControlPanelLogs: Array,
  abcEngineAreaLogs: Array,
  abcControlPanelGroups: Array,
  abcEngineAreaGroups: Array,
});

// Tab dibaca dari ?tab= agar tetap sama setelah pindah tanggal (mis. setelah simpan data tanggal lain)
const activeTab = ref(queryParam(usePage().url, 'tab', ['control_panel', 'engine_area'], 'control_panel'));

const tabs = [
  { key: 'control_panel', label: 'Control panel' },
  { key: 'engine_area', label: 'Engine area' },
];

const dateFilter = ref(props.selectedDate);

const visit = (engineType) =>
  router.get(
    '/monitoring-operasi-engine',
    { date: dateFilter.value, tab: activeTab.value, engine: engineType },
    { preserveState: true, preserveScroll: true }
  );

const applyFilter = () => visit(props.engineType);

// Data tiap jenis engine dimuat dari server, jadi pindah jenis = muat ulang halaman
const changeEngineType = (engineType) => {
  if (engineType !== props.engineType) visit(engineType);
};
</script>
