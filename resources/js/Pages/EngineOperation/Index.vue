<template>
  <AppLayout v-slot="{ isDarkMode }">
    <div class="space-y-6">
      <!-- Title & Date Selector -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2">
        <div class="flex items-center gap-3">
          <div
            :class="[
              'w-12 h-12 rounded-2xl border flex items-center justify-center shadow-inner shrink-0',
              isDarkMode
                ? 'bg-gradient-to-tr from-violet-500/20 to-indigo-500/20 border-violet-500/30 text-violet-400'
                : 'bg-violet-50 border-violet-200 text-violet-600'
            ]"
          >
            <Cpu class="w-6 h-6" />
          </div>
          <div>
            <h1 :class="['text-xl font-extrabold tracking-tight', isDarkMode ? 'text-white' : 'text-slate-900']">
              Monitoring Operasi Engine
            </h1>
            <p :class="['text-xs', isDarkMode ? 'text-slate-400' : 'text-slate-500']">
              Pencatatan Control Panel & Engine Area per jam untuk engine {{ engines.map(engine => engine.label).join(' & ') }}
            </p>
          </div>
        </div>

        <div
          :class="[
            'flex items-center gap-2 px-3 py-1.5 rounded-xl border self-start md:self-auto',
            isDarkMode ? 'bg-slate-950 border-slate-800' : 'bg-slate-50 border-slate-200'
          ]"
        >
          <Calendar class="w-4 h-4 text-violet-500" />
          <input
            type="date"
            v-model="dateFilter"
            @change="applyDateFilter"
            :class="[
              'bg-transparent text-xs font-mono font-bold focus:outline-none cursor-pointer',
              isDarkMode ? 'text-slate-100' : 'text-slate-800'
            ]"
          />
        </div>
      </div>

      <!-- Flash Message Alert -->
      <Transition name="fade">
        <div
          v-if="$page.props.flash && $page.props.flash.success"
          :class="[
            'p-4 rounded-xl flex items-center justify-between shadow-lg border',
            isDarkMode
              ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400'
              : 'bg-emerald-50 border-emerald-200 text-emerald-800 font-semibold'
          ]"
        >
          <div class="flex items-center gap-2 text-xs font-medium">
            <CheckCircle2 class="w-4 h-4 shrink-0 text-emerald-500" />
            <span>{{ $page.props.flash.success }}</span>
          </div>
        </div>
      </Transition>

      <!-- Tabs: Control Panel / Engine Area -->
      <div
        :class="[
          'inline-flex items-center p-1 rounded-xl border',
          isDarkMode ? 'bg-slate-950 border-slate-800' : 'bg-slate-100 border-slate-200'
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
              : isDarkMode
                ? 'text-slate-400 hover:text-slate-200 hover:bg-slate-900'
                : 'text-slate-600 hover:text-slate-900 hover:bg-white'
          ]"
        >
          <component :is="tab.icon" class="w-3.5 h-3.5" />
          <span>{{ tab.label }}</span>
          <span
            :class="[
              'px-1.5 rounded text-[10px] font-mono',
              activeTab === tab.key ? 'bg-white/20' : isDarkMode ? 'bg-slate-800' : 'bg-slate-200'
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
        :isDarkMode="isDarkMode"
      />

      <EngineAreaTable
        v-else
        :logs="engineAreaLogs"
        :engines="engines"
        :fixedFields="engineAreaFixedFields"
        :choiceGroups="engineAreaChoiceGroups"
        :selectedDate="selectedDate"
        :isDarkMode="isDarkMode"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ControlPanelTable from '@/Components/EngineOperation/ControlPanelTable.vue';
import EngineAreaTable from '@/Components/EngineOperation/EngineAreaTable.vue';
import { Cpu, Calendar, Gauge, Thermometer, CheckCircle2 } from 'lucide-vue-next';

const props = defineProps({
  selectedDate: String,
  engines: Array,
  controlPanelLogs: Array,
  engineAreaLogs: Array,
  controlPanelGroups: Array,
  engineAreaFixedFields: Array,
  engineAreaChoiceGroups: Array,
});

const activeTab = ref('control_panel');

const tabs = computed(() => [
  { key: 'control_panel', label: 'Control Panel', icon: Gauge, count: props.controlPanelLogs.length },
  { key: 'engine_area', label: 'Engine Area', icon: Thermometer, count: props.engineAreaLogs.length },
]);

const dateFilter = ref(props.selectedDate);

const applyDateFilter = () => {
  router.get(
    '/monitoring-operasi-engine',
    { date: dateFilter.value },
    { preserveState: true, preserveScroll: true }
  );
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
