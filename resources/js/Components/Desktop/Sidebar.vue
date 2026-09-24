<template>
    <!-- Desktop Sidebar Only (strictly hidden md:flex) -->
    <aside
        :class="[
            'hidden md:flex flex-col sticky top-0 h-screen border-r transition-all duration-300 ease-in-out shadow-sm shrink-0',
            isDarkMode
                ? 'bg-slate-900 border-slate-800 text-slate-100'
                : 'bg-white border-slate-200 text-slate-900',
            collapsed ? 'w-20' : 'w-64',
        ]"
    >
        <!-- Desktop Header / Logo Section -->
        <div
            :class="[
                'h-16 flex items-center justify-between px-4 shrink-0 border-b',
                isDarkMode ? 'border-slate-800' : 'border-slate-100',
                collapsed ? 'justify-center px-2' : '',
            ]"
        >
            <div class="flex items-center gap-3 overflow-hidden">
                <div
                    class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center shadow-md shadow-cyan-500/20 shrink-0"
                >
                    <Zap class="w-6 h-6 text-white" />
                </div>
                <div
                    v-if="!collapsed"
                    class="truncate transition-opacity duration-200"
                >
                    <h1
                        :class="[
                            'font-extrabold text-sm tracking-wider leading-tight',
                            isDarkMode ? 'text-white' : 'text-slate-900',
                        ]"
                    >
                        PLN MONITOR
                    </h1>
                    <p
                        class="text-[10px] text-cyan-600 font-semibold tracking-wide uppercase"
                    >
                        ULPLTD POKA
                    </p>
                </div>
            </div>

            <!-- Desktop Collapse Toggle Button -->
            <button
                v-if="!collapsed"
                @click="$emit('toggleCollapse')"
                :class="[
                    'hidden md:flex items-center justify-center w-8 h-8 rounded-lg transition-colors',
                    isDarkMode
                        ? 'text-slate-400 hover:text-white hover:bg-slate-800'
                        : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100',
                ]"
                :title="collapsed ? 'Perluas Sidebar' : 'Ciutkan Sidebar'"
            >
                <ChevronLeft class="w-5 h-5" />
            </button>
        </div>

        <!-- Desktop Navigation Items Section -->
        <div
            class="flex-1 px-3 py-5 space-y-6 overflow-y-auto custom-scrollbar"
        >
            <!-- Group 1: Monitoring Utama (PBAC Filtered) -->
            <div class="space-y-1">
                <div
                    v-if="!collapsed"
                    :class="[
                        'px-3 text-[10px] font-bold uppercase tracking-wider mb-2',
                        isDarkMode ? 'text-slate-500' : 'text-slate-400',
                    ]"
                >
                    Monitoring Utama
                </div>

                <!-- Item: Dashboard Utama -->
                <Link
                    href="/"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold text-xs transition-all group relative',
                        collapsed ? 'justify-center px-0' : '',
                        $page.url === '/' ||
                        $page.url.startsWith('/dashboard')
                            ? isDarkMode
                                ? 'bg-gradient-to-r from-blue-500/20 to-cyan-500/10 text-cyan-400 border border-cyan-500/30 shadow-sm'
                                : 'bg-blue-50 text-blue-700 border border-blue-200 shadow-sm'
                            : isDarkMode
                              ? 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'
                              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100',
                    ]"
                    :title="collapsed ? 'Dashboard Utama' : ''"
                >
                    <LayoutDashboard
                        class="w-5 h-5 text-cyan-500 shrink-0"
                    />
                    <span v-if="!collapsed" class="truncate"
                        >Dashboard Utama</span
                    >
                </Link>

                <!-- Item: Monitoring Arus -->
                <Link
                    v-if="hasPerm('monitoring_arus.view')"
                    href="/monitoring-arus"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold text-xs transition-all group relative',
                        collapsed ? 'justify-center px-0' : '',
                        $page.url.startsWith('/monitoring-arus')
                            ? isDarkMode
                                ? 'bg-gradient-to-r from-cyan-500/20 to-blue-500/10 text-cyan-400 border border-cyan-500/30 shadow-sm'
                                : 'bg-cyan-50 text-cyan-700 border border-cyan-200 shadow-sm'
                            : isDarkMode
                              ? 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'
                              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100',
                    ]"
                    :title="collapsed ? 'Monitoring Arus' : ''"
                >
                    <Activity class="w-5 h-5 text-cyan-500 shrink-0" />
                    <span v-if="!collapsed" class="truncate"
                        >Monitoring Arus</span
                    >
                    <span
                        v-if="!collapsed"
                        :class="[
                            'ml-auto px-1.5 py-0.5 rounded text-[9px] font-extrabold border',
                            isDarkMode
                                ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40'
                                : 'bg-cyan-100 text-cyan-800 border-cyan-300',
                        ]"
                    >
                        12 Feeder
                    </span>
                </Link>

                <!-- Item: Monitoring kWh Produksi -->
                <Link
                    v-if="hasPerm('monitoring_kwh.view')"
                    href="/monitoring-kwh"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold text-xs transition-all group relative',
                        collapsed ? 'justify-center px-0' : '',
                        $page.url.startsWith('/monitoring-kwh')
                            ? isDarkMode
                                ? 'bg-gradient-to-r from-emerald-500/20 to-teal-500/10 text-emerald-400 border border-emerald-500/30 shadow-sm'
                                : 'bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm'
                            : isDarkMode
                              ? 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'
                              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100',
                    ]"
                    :title="collapsed ? 'Monitoring kWh Produksi' : ''"
                >
                    <Zap class="w-5 h-5 text-cyan-500 shrink-0" />
                    <span v-if="!collapsed" class="truncate"
                        >Monitoring kWh Produksi</span
                    >
                </Link>

                <!-- Item: Monitoring Operasi Engine -->
                <Link
                    v-if="hasPerm('monitoring_engine.view')"
                    href="/monitoring-operasi-engine"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold text-xs transition-all group relative',
                        collapsed ? 'justify-center px-0' : '',
                        $page.url.startsWith('/monitoring-operasi-engine')
                            ? isDarkMode
                                ? 'bg-gradient-to-r from-violet-500/20 to-indigo-500/10 text-violet-400 border border-violet-500/30 shadow-sm'
                                : 'bg-violet-50 text-violet-700 border border-violet-200 shadow-sm'
                            : isDarkMode
                              ? 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'
                              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100',
                    ]"
                    :title="collapsed ? 'Monitoring Operasi Engine' : ''"
                >
                    <Cpu class="w-5 h-5 text-cyan-500 shrink-0" />
                    <span v-if="!collapsed" class="truncate"
                        >Monitoring Operasi Engine</span
                    >
                </Link>

                <!-- Item: Monitoring Gangguan -->
                <Link
                    v-if="hasPerm('monitoring_gangguan.view')"
                    href="/monitoring-gangguan"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold text-xs transition-all group relative',
                        collapsed ? 'justify-center px-0' : '',
                        $page.url.startsWith('/monitoring-gangguan')
                            ? isDarkMode
                                ? 'bg-gradient-to-r from-rose-500/20 to-amber-500/10 text-rose-400 border border-rose-500/30 shadow-sm'
                                : 'bg-rose-50 text-rose-700 border border-rose-200 shadow-sm'
                            : isDarkMode
                              ? 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'
                              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100',
                    ]"
                    :title="collapsed ? 'Monitoring Gangguan' : ''"
                >
                    <AlertTriangle class="w-5 h-5 text-cyan-500 shrink-0" />
                    <span v-if="!collapsed" class="truncate"
                        >Monitoring Gangguan</span
                    >
                </Link>

                <!-- Item: Monitoring Stok BBM -->
                <Link
                    v-if="hasPerm('monitoring_bbm.view')"
                    href="/monitoring-bbm"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold text-xs transition-all group relative',
                        collapsed ? 'justify-center px-0' : '',
                        $page.url.startsWith('/monitoring-bbm')
                            ? isDarkMode
                                ? 'bg-gradient-to-r from-amber-500/20 to-orange-500/10 text-amber-400 border border-amber-500/30 shadow-sm'
                                : 'bg-amber-50 text-amber-700 border border-amber-200 shadow-sm'
                            : isDarkMode
                              ? 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'
                              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100',
                    ]"
                    :title="collapsed ? 'Monitoring Stok BBM' : ''"
                >
                    <Fuel class="w-5 h-5 text-cyan-500 shrink-0" />
                    <span v-if="!collapsed" class="truncate"
                        >Monitoring Stok BBM</span
                    >
                </Link>
            </div>

            <!-- Group 2: Sistem Admin & Pengaturan PBAC -->
            <div v-if="hasPerm('users.manage')" class="space-y-1">
                <div
                    v-if="!collapsed"
                    :class="[
                        'px-3 text-[10px] font-bold uppercase tracking-wider mb-2 text-slate-400',
                    ]"
                >
                    Sistem Admin
                </div>

                <!-- Item: Pengelolaan Pengguna (User Management) -->
                <Link
                    href="/users"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold text-xs transition-all group relative',
                        collapsed ? 'justify-center px-0' : '',
                        $page.url.startsWith('/users')
                            ? isDarkMode
                                ? 'bg-gradient-to-r from-rose-500/20 to-purple-500/10 text-rose-400 border border-rose-500/30 shadow-sm'
                                : 'bg-rose-50 text-rose-700 border border-rose-200 shadow-sm'
                            : isDarkMode
                              ? 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'
                              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100',
                    ]"
                    :title="collapsed ? 'Pengelolaan Pengguna' : ''"
                >
                    <ShieldCheck class="w-5 h-5 text-cyan-500 shrink-0" />
                    <span v-if="!collapsed" class="truncate"
                        >Pengelolaan Pengguna</span
                    >
                    <span
                        v-if="!collapsed"
                        :class="[
                            'ml-auto px-1.5 py-0.5 rounded text-[9px] font-extrabold border',
                            isDarkMode
                                ? 'bg-rose-500/20 text-rose-300 border-rose-500/40'
                                : 'bg-rose-100 text-rose-800 border-rose-300',
                        ]"
                    >
                        PBAC
                    </span>
                </Link>
            </div>
        </div>
    </aside>
</template>

<script setup>
import { usePage, Link } from "@inertiajs/vue3";
import {
    LayoutDashboard,
    Zap,
    Activity,
    AlertTriangle,
    Fuel,
    Cpu,
    ShieldCheck,
    ChevronLeft,
} from "lucide-vue-next";

defineProps({
    collapsed: {
        type: Boolean,
        default: false,
    },
    isDarkMode: {
        type: Boolean,
        default: false,
    },
});

defineEmits(["toggleCollapse"]);

const page = usePage();

const hasPerm = (slug) => {
    const user = page.props.auth?.user;
    if (!user) return false;
    if (user.role === "admin") return true;
    const permissions = page.props.auth?.permissions || [];
    return permissions.includes(slug);
};
</script>
