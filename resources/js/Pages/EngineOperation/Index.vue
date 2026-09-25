<template>
  <Head title="Operasi Engine" />
  <div class="space-y-6">
    <div class="space-y-6">
      <!-- Title & Date Selector -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2">
        <div class="flex items-center gap-3">
          <div
            :class="[
              'w-12 h-12 rounded-2xl border flex items-center justify-center shadow-inner shrink-0',
              'bg-violet-50 border-violet-200 text-violet-600 dark:bg-gradient-to-tr dark:from-violet-500/20 dark:to-indigo-500/20 dark:border-violet-500/30 dark:text-violet-400 dark:bg-transparent'
            ]"
          >
            <Cpu class="w-6 h-6" />
          </div>
          <div>
            <h1 :class="['text-xl font-extrabold tracking-tight', 'text-slate-900 dark:text-white']">
              Monitoring Operasi Engine
            </h1>
            <p :class="['text-xs', 'text-slate-500 dark:text-slate-400']">
              Pencatatan Control Panel & Engine Area per jam untuk engine {{ engines.map(engine => engine.label).join(' & ') }}
            </p>
          </div>
        </div>

        <div
          :class="[
            'flex items-center gap-2 px-3 py-1.5 rounded-xl border self-start md:self-auto',
            'bg-slate-50 border-slate-200 dark:bg-slate-950 dark:border-slate-800'
          ]"
        >
          <Calendar class="w-4 h-4 text-violet-500" />
          <input
            type="date"
            v-model="dateFilter"
            @change="applyDateFilter"
            :class="[
              'bg-transparent text-xs font-mono font-bold focus:outline-none cursor-pointer',
              'text-slate-800 dark:text-slate-100'
            ]"
          />
        </div>
      </div>

      <!-- Tabs: Control Panel / Engine Area -->
      <div
        :class="[
          'inline-flex items-center p-1 rounded-xl border',
          'bg-slate-100 border-slate-200 dark:bg-slate-950 dark:border-slate-800'
        ]"
      >
        <button
          v-for="tab in tabs"
          :key="tab.key"
          @click="activeTab = tab.key"
          :class="[
            'px-4 py-1.5 rounded-lg text-xs font-bold transition-all duration-200 flex items-center gap-1.5',
            activeTab === tab.key
              ? 'bg-violet-500 text-white shadow-md font-extrabold'
              : 'text-slate-600 hover:text-slate-900 hover:bg-white dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-900'
          ]"
        >
          <component :is="tab.icon" class="w-3.5 h-3.5" />
          <span>{{ tab.label }}</span>
          <span
            :class="[
              'px-1.5 rounded text-[10px] font-mono',
              activeTab === tab.key ? 'bg-white/20' : 'bg-slate-200 dark:bg-slate-800'
            ]"
          >
            {{ tab.count }}
          </span>
        </button>
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
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { queryParam } from '@/utils/url';
import ControlPanelTable from '@/Components/EngineOperation/ControlPanelTable.vue';
import EngineAreaTable from '@/Components/EngineOperation/EngineAreaTable.vue';
import { Cpu, Calendar, Gauge, Thermometer } from 'lucide-vue-next';

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
  { key: 'control_panel', label: 'Control Panel', icon: Gauge, count: props.controlPanelLogs.length },
  { key: 'engine_area', label: 'Engine Area', icon: Thermometer, count: props.engineAreaLogs.length },
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
