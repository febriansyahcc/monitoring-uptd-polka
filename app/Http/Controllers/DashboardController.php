<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feeder;
use App\Models\CurrentLogRecord;
use App\Models\KwhEngineLog;
use App\Models\KwhFeederLog;
use App\Models\OperationalDisturbance;
use App\Models\FuelStock;
use Inertia\Inertia;
use App\Models\EngineAreaLog;
use App\Models\EngineControlPanelLog;
use App\Support\Shift;
use App\Support\Status;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today()->format('Y-m-d');
        $currentMonth = Carbon::now()->format('Y-m');

        // 1. MONITORING ARUS SUMMARY & HOURLY CHART
        $feeders = Feeder::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get(['id', 'name', 'code']);

        $todayCurrentLogs = CurrentLogRecord::where('recorded_date', $today)->get();

        // Urutan interval mengikuti urutan shift (pagi → sore → malam), bukan urutan insert
        $intervalOrder = array_flip(array_merge(...array_values(CurrentMonitoringController::SHIFT_INTERVALS)));
        $intervalRank = fn ($interval) => $intervalOrder[$interval] ?? PHP_INT_MAX;

        // Latest total current / load recorded today
        $latestInterval = $todayCurrentLogs->pluck('time_interval')->unique()->sortBy($intervalRank)->last();
        $latestCurrentTotal = 0;
        $feederStatusList = [];

        foreach ($feeders as $feeder) {
            $latestFeederLog = $todayCurrentLogs->where('feeder_id', $feeder->id)->sortByDesc('id')->first();
            $val = $latestFeederLog ? floatval($latestFeederLog->current_value) : 0;
            $latestCurrentTotal += $val;

            $feederStatusList[] = [
                'id' => $feeder->id,
                'name' => $feeder->name,
                'code' => $feeder->code,
                'current_value' => $val,
                // Penanda untuk titik status di dashboard: abu = belum ada input hari ini
                'has_data' => $latestFeederLog !== null,
                'last_updated' => $latestFeederLog ? $latestFeederLog->last_modified_time : '-',
            ];
        }

        // Aggregate hourly load per shift interval for today's chart
        $intervalSummary = [];
        $uniqueIntervals = $todayCurrentLogs->pluck('time_interval')->unique()->sortBy($intervalRank)->values();
        foreach ($uniqueIntervals as $interval) {
            $sum = $todayCurrentLogs->where('time_interval', $interval)->sum('current_value');
            $intervalSummary[] = [
                'interval' => $interval,
                'total_current' => floatval($sum),
            ];
        }

        // 2. KWH PRODUCTION SUMMARY & 7-DAY TREND
        // Produksi engine = selisih stand akhir terhadap pencatatan sebelumnya
        $engineLogsMonth = KwhEngineLog::withProduction(Carbon::now()->startOfMonth(), Carbon::today());
        $totalKwhMonth = $engineLogsMonth->sum('produksi');
        $todayEngineLogs = $engineLogsMonth->where('recorded_date', $today)->whereNotNull('produksi');
        $todayKwhTotal = floatval($todayEngineLogs->sum('produksi'));

        // Last 7 days trend for chart
        $trendStart = Carbon::today()->subDays(6);
        $engineLogsTrend = KwhEngineLog::withProduction($trendStart, Carbon::today());
        $feederLogsTrend = KwhFeederLog::whereBetween('recorded_date', [$trendStart->toDateString(), $today])->get();

        $last7DaysKwh = collect(range(6, 0))
            ->map(fn ($daysAgo) => Carbon::today()->subDays($daysAgo)->format('Y-m-d'))
            ->filter(fn ($date) => $engineLogsTrend->contains('recorded_date', $date) || $feederLogsTrend->contains('recorded_date', $date))
            ->values()
            ->map(fn ($date) => [
                'date' => Carbon::parse($date)->format('d/m'),
                'kwh_total' => floatval($engineLogsTrend->where('recorded_date', $date)->sum('produksi')),
                'kwh_ps' => floatval($feederLogsTrend->where('recorded_date', $date)->sum('ps_total')),
            ]);

        // 3. OPERATIONAL DISTURBANCES SUMMARY
        $disturbancesMonth = OperationalDisturbance::whereYear('event_date', Carbon::now()->year)
            ->whereMonth('event_date', Carbon::now()->month)
            ->orderBy('event_date', 'desc')
            ->orderBy('event_time', 'desc')
            ->get();

        $inProgressDisturbances = $disturbancesMonth->where('status', 'Dalam Penanganan')->count();
        $investigatingDisturbances = $disturbancesMonth->where('status', 'Investigasi')->count();
        $resolvedDisturbances = $disturbancesMonth->where('status', 'Selesai')->count();

        // 5 gangguan terakhir tanpa filter bulan, agar tidak kosong di awal bulan
        $recentDisturbances = OperationalDisturbance::orderBy('event_date', 'desc')
            ->orderBy('event_time', 'desc')
            ->take(5)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'event_date' => Carbon::parse($item->event_date)->format('d/m/Y'),
                'event_time' => $item->event_time,
                'disturbance_type' => $item->disturbance_type,
                'status' => $item->status,
                'status_code' => Status::disturbance($item->status),
                'description' => $item->description ?: '-',
            ]);

        // 4. FUEL STOCK (BBM) SUMMARY
        $latestFuelLog = FuelStock::orderBy('recorded_date', 'desc')->first();
        $bmmNettoStock = $latestFuelLog ? floatval($latestFuelLog->netto_stock) : 0;
        $bmmDaysOfSupply = $latestFuelLog ? floatval($latestFuelLog->days_of_supply) : 0;
        $bmmDailyConsumption = $latestFuelLog ? floatval($latestFuelLog->daily_consumption) : 0;
        
        $bmmStatus = Status::fuel($latestFuelLog !== null, $bmmDaysOfSupply);

        return Inertia::render('Dashboard/Index', [
            'kpi' => [
                'current' => [
                    'total_load' => floatval($latestCurrentTotal),
                    'active_feeders' => count($feeders),
                    'latest_interval' => $latestInterval ?: '-',
                    // false = belum ada input hari ini (tampil "—", bukan 0)
                    'has_data' => $todayCurrentLogs->isNotEmpty(),
                ],
                'kwh' => [
                    'today_total' => $todayKwhTotal,
                    'month_total' => floatval($totalKwhMonth),
                    'has_data' => $todayEngineLogs->isNotEmpty(),
                ],
                'disturbance' => [
                    'total_month' => $disturbancesMonth->count(),
                    'in_progress' => $inProgressDisturbances,
                    'investigating' => $investigatingDisturbances,
                    'resolved' => $resolvedDisturbances,
                ],
                'fuel' => [
                    'netto_stock' => $bmmNettoStock,
                    'days_of_supply' => $bmmDaysOfSupply,
                    'daily_consumption' => $bmmDailyConsumption,
                    'status' => $bmmStatus,
                    'last_date' => $latestFuelLog ? Carbon::parse($latestFuelLog->recorded_date)->format('d/m/Y') : '-',
                ],
            ],
            'currentChartData' => $intervalSummary,
            'kwhTrend' => $last7DaysKwh,
            'feederStatusList' => $feederStatusList,
            'recentDisturbances' => $recentDisturbances,
            'inputProgress' => $this->inputProgress($today, $feeders->pluck('id')->all(), $latestFuelLog),
        ]);
    }

    /**
     * Kelengkapan input untuk shift berjalan — "daftar tugas" operator di dashboard.
     * filled/total null = hanya jumlah (tanpa target), mis. Operasi Engine.
     */
    private function inputProgress(string $today, array $activeFeederIds, ?FuelStock $latestFuelLog): array
    {
        $shift = Shift::current();
        $intervals = CurrentMonitoringController::SHIFT_INTERVALS[$shift['key']];

        // Arus: interval shift berjalan yang SEMUA feeder aktifnya sudah terisi
        $filledFeedersPerInterval = CurrentLogRecord::where('recorded_date', $today)
            ->where('shift', $shift['key'])
            ->whereIn('feeder_id', $activeFeederIds)
            ->get(['time_interval', 'feeder_id'])
            ->groupBy('time_interval')
            ->map(fn ($rows) => $rows->pluck('feeder_id')->unique()->count());
        $arusFilled = count($activeFeederIds) === 0 ? 0 : $filledFeedersPerInterval
            ->filter(fn ($count, $interval) => $count >= count($activeFeederIds) && in_array($interval, $intervals, true))
            ->count();

        // kWh: satu catatan per engine & per penyulang per hari
        $kwhFilled = KwhEngineLog::where('recorded_date', $today)->distinct()->count('engine')
            + KwhFeederLog::where('recorded_date', $today)->distinct()->count('feeder');
        $kwhTotal = count(KwhEngineLog::ENGINES) + count(KwhFeederLog::FEEDERS);

        $engineFilled = EngineControlPanelLog::where('recorded_date', $today)->count()
            + EngineAreaLog::where('recorded_date', $today)->count();

        $fuelToday = $latestFuelLog && Carbon::parse($latestFuelLog->recorded_date)->toDateString() === $today;

        return [
            'shift' => $shift,
            'date' => $today,
            'items' => [
                [
                    'module' => 'arus',
                    'label' => 'Arus',
                    'scope' => $shift['label'],
                    'filled' => $arusFilled,
                    'total' => count($intervals),
                    'unit' => 'interval',
                    'href' => "/monitoring-arus?date={$today}&shift={$shift['key']}",
                    'view_permission' => 'monitoring_arus.view',
                    'input_permission' => 'monitoring_arus.input',
                ],
                [
                    'module' => 'kwh',
                    'label' => 'kWh produksi',
                    'scope' => 'Hari ini',
                    'filled' => $kwhFilled,
                    'total' => $kwhTotal,
                    'unit' => 'engine & penyulang',
                    'href' => '/monitoring-kwh?month=' . substr($today, 0, 7),
                    'view_permission' => 'monitoring_kwh.view',
                    'input_permission' => 'monitoring_kwh.input',
                ],
                [
                    'module' => 'engine',
                    'label' => 'Operasi engine',
                    'scope' => 'Hari ini',
                    'filled' => $engineFilled,
                    'total' => null,
                    'unit' => 'catatan',
                    'href' => "/monitoring-operasi-engine?date={$today}",
                    'view_permission' => 'monitoring_engine.view',
                    'input_permission' => 'monitoring_engine.input',
                ],
                [
                    'module' => 'bbm',
                    'label' => 'Stok BBM',
                    'scope' => 'Hari ini',
                    'filled' => $fuelToday ? 1 : 0,
                    'total' => 1,
                    'unit' => 'catatan',
                    'last_date' => $latestFuelLog ? Carbon::parse($latestFuelLog->recorded_date)->format('d/m/Y') : null,
                    'href' => '/monitoring-bbm',
                    'view_permission' => 'monitoring_bbm.view',
                    'input_permission' => 'monitoring_bbm.input',
                ],
            ],
        ];
    }
}
