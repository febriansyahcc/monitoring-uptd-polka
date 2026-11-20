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
use App\Models\AbcControlPanelLog;
use App\Models\AbcEngineAreaLog;
use App\Models\EngineAreaLog;
use App\Models\EngineControlPanelLog;
use App\Support\Shift;
use App\Support\Status;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $todayDate = Carbon::today();
        $selectedDate = $this->parseDate($request->input('date'), $todayDate);
        $targetDate = $selectedDate->format('Y-m-d');
        $isToday = $targetDate === $todayDate->format('Y-m-d');

        // 1. MONITORING ARUS SUMMARY & HOURLY CHART
        $feeders = Feeder::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get(['id', 'name', 'code']);

        $dayCurrentLogs = CurrentLogRecord::where('recorded_date', $targetDate)->get();

        // Urutan interval mengikuti urutan shift (pagi → sore → malam), bukan urutan insert
        $intervalOrder = array_flip(array_merge(...array_values(CurrentMonitoringController::SHIFT_INTERVALS)));
        $intervalRank = fn ($interval) => $intervalOrder[$interval] ?? PHP_INT_MAX;

        // Latest total current / load recorded on selected date (berdasarkan interval paling akhir secara kronologis
        // agar nilai KPI, titik terakhir grafik, dan kartu penyulang selalu konsisten pada interval yang sama)
        $latestInterval = $dayCurrentLogs->pluck('time_interval')->unique()->sortBy($intervalRank)->last();
        $latestIntervalLogs = $latestInterval !== null
            ? $dayCurrentLogs->where('time_interval', $latestInterval)
            : collect();
        $latestCurrentTotal = 0;
        $feederStatusList = [];

        foreach ($feeders as $feeder) {
            $latestFeederLog = $latestIntervalLogs
                ->where('feeder_id', $feeder->id)
                ->sortByDesc('id')
                ->first();
            $val = $latestFeederLog ? floatval($latestFeederLog->current_value) : 0;
            $latestCurrentTotal += $val;

            $feederStatusList[] = [
                'id' => $feeder->id,
                'name' => $feeder->name,
                'code' => $feeder->code,
                'current_value' => $val,
                // Penanda untuk titik status di dashboard: abu = belum ada input pada interval terakhir tanggal terpilih
                'has_data' => $latestFeederLog !== null,
                'last_updated' => $latestFeederLog ? $latestFeederLog->last_modified_time : '-',
            ];
        }

        // Aggregate hourly load per shift interval for selected date chart
        $intervalSummary = [];
        $uniqueIntervals = $dayCurrentLogs->pluck('time_interval')->unique()->sortBy($intervalRank)->values();
        foreach ($uniqueIntervals as $interval) {
            $sum = $dayCurrentLogs->where('time_interval', $interval)->sum('current_value');
            $intervalSummary[] = [
                'interval' => $interval,
                'total_current' => floatval($sum),
            ];
        }

        // Petunjuk tanggal data arus terakhir jika tanggal yang dibuka belum punya data
        $latestCurrentDate = $dayCurrentLogs->isEmpty() ? CurrentLogRecord::max('recorded_date') : null;

        // 2. KWH PRODUCTION SUMMARY & 7-DAY TREND
        // Produksi engine = selisih stand akhir terhadap pencatatan sebelumnya
        $monthStart = $selectedDate->copy()->startOfMonth();
        $monthEnd = $isToday ? $selectedDate->copy() : $selectedDate->copy()->endOfMonth();
        $engineLogsMonth = KwhEngineLog::withProduction($monthStart, $monthEnd);
        $totalKwhMonth = $engineLogsMonth->sum('produksi');
        $dayEngineLogs = $engineLogsMonth->where('recorded_date', $targetDate)->whereNotNull('produksi');
        $dayKwhTotal = floatval($dayEngineLogs->sum('produksi'));

        // Last 7 days trend ending at selected date
        $trendStart = $selectedDate->copy()->subDays(6);
        $engineLogsTrend = KwhEngineLog::withProduction($trendStart, $selectedDate);
        $feederLogsTrend = KwhFeederLog::whereBetween('recorded_date', [$trendStart->toDateString(), $targetDate])->get();

        $last7DaysKwh = collect(range(6, 0))
            ->map(fn ($daysAgo) => $selectedDate->copy()->subDays($daysAgo)->format('Y-m-d'))
            ->filter(fn ($date) => $engineLogsTrend->contains('recorded_date', $date) || $feederLogsTrend->contains('recorded_date', $date))
            ->values()
            ->map(fn ($date) => [
                'date' => Carbon::parse($date)->format('d/m'),
                'kwh_total' => floatval($engineLogsTrend->where('recorded_date', $date)->sum('produksi')),
                'kwh_ps' => floatval($feederLogsTrend->where('recorded_date', $date)->sum('ps_total')),
            ]);

        // 3. OPERATIONAL DISTURBANCES SUMMARY
        $disturbancesMonth = OperationalDisturbance::whereYear('event_date', $selectedDate->year)
            ->whereMonth('event_date', $selectedDate->month)
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
            'selectedDate' => $targetDate,
            'isToday' => $isToday,
            'selectedMonthLabel' => $selectedDate->copy()->locale('id')->translatedFormat('F Y'),
            'latestCurrentDate' => $latestCurrentDate,
            'kpi' => [
                'current' => [
                    'total_load' => floatval($latestCurrentTotal),
                    'active_feeders' => count($feeders),
                    'latest_interval' => $latestInterval ?: '-',
                    // false = belum ada input pada tanggal terpilih (tampil "—", bukan 0)
                    'has_data' => $dayCurrentLogs->isNotEmpty(),
                ],
                'kwh' => [
                    'today_total' => $dayKwhTotal,
                    'month_total' => floatval($totalKwhMonth),
                    'has_data' => $dayEngineLogs->isNotEmpty(),
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
            'inputProgress' => $this->inputProgress($targetDate, $isToday, $feeders->pluck('id')->all(), $latestFuelLog),
        ]);
    }

    private function parseDate(mixed $rawDate, Carbon $fallback): Carbon
    {
        if (!is_string($rawDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate)) {
            return $fallback->copy();
        }

        try {
            $parsed = Carbon::createFromFormat('Y-m-d', $rawDate)->startOfDay();
            return $parsed->format('Y-m-d') === $rawDate ? $parsed : $fallback->copy();
        } catch (\Exception $e) {
            return $fallback->copy();
        }
    }

    /**
     * Kelengkapan input untuk shift berjalan (hari ini) atau rekap seharian (tanggal lain) — "daftar tugas" operator.
     * filled/total null = hanya jumlah (tanpa target), mis. Operasi Engine.
     */
    private function inputProgress(string $targetDate, bool $isToday, array $activeFeederIds, ?FuelStock $latestFuelLog): array
    {
        $shift = $isToday ? Shift::current() : ['key' => 'all', 'label' => 'Semua shift'];
        $intervals = $isToday
            ? CurrentMonitoringController::SHIFT_INTERVALS[$shift['key']]
            : array_merge(...array_values(CurrentMonitoringController::SHIFT_INTERVALS));

        // Arus: interval pada cakupan shift yang SEMUA feeder aktifnya sudah terisi
        $currentQuery = CurrentLogRecord::where('recorded_date', $targetDate)
            ->whereIn('feeder_id', $activeFeederIds);
        if ($isToday) {
            $currentQuery->where('shift', $shift['key']);
        }

        $filledFeedersPerInterval = $currentQuery
            ->get(['shift', 'time_interval', 'feeder_id'])
            ->groupBy(fn ($row) => "{$row->shift}|{$row->time_interval}")
            ->map(fn ($rows) => $rows->pluck('feeder_id')->unique()->count());
        $activeFeederCount = count($activeFeederIds);
        $arusFilled = $activeFeederCount === 0 ? 0 : $filledFeedersPerInterval
            ->filter(function ($count, $compositeKey) use ($intervals, $activeFeederCount) {
                [, $interval] = explode('|', $compositeKey, 2);
                return $count >= $activeFeederCount && in_array($interval, $intervals, true);
            })
            ->count();

        // kWh: satu catatan per engine & per penyulang per hari
        $kwhFilled = KwhEngineLog::where('recorded_date', $targetDate)->distinct()->count('engine')
            + KwhFeederLog::where('recorded_date', $targetDate)->distinct()->count('feeder');
        $kwhTotal = count(KwhEngineLog::ENGINES) + count(KwhFeederLog::FEEDERS);

        $engineFilled = EngineControlPanelLog::where('recorded_date', $targetDate)->count()
            + EngineAreaLog::where('recorded_date', $targetDate)->count()
            + AbcControlPanelLog::where('recorded_date', $targetDate)->count()
            + AbcEngineAreaLog::where('recorded_date', $targetDate)->count();

        $fuelForDate = FuelStock::where('recorded_date', $targetDate)->exists();

        return [
            'shift' => $shift,
            'date' => $targetDate,
            'items' => [
                [
                    'module' => 'arus',
                    'label' => 'Arus',
                    'scope' => $isToday ? $shift['label'] : 'Harian (3 shift)',
                    'filled' => $arusFilled,
                    'total' => count($intervals),
                    'unit' => 'interval',
                    'href' => $isToday
                        ? "/monitoring-arus?date={$targetDate}&shift={$shift['key']}"
                        : "/monitoring-arus?date={$targetDate}",
                    'view_permission' => 'monitoring_arus.view',
                    'input_permission' => 'monitoring_arus.input',
                ],
                [
                    'module' => 'kwh',
                    'label' => 'kWh produksi',
                    'scope' => 'Harian',
                    'filled' => $kwhFilled,
                    'total' => $kwhTotal,
                    'unit' => 'engine & penyulang',
                    'href' => '/monitoring-kwh?month=' . substr($targetDate, 0, 7),
                    'view_permission' => 'monitoring_kwh.view',
                    'input_permission' => 'monitoring_kwh.input',
                ],
                [
                    'module' => 'engine',
                    'label' => 'Operasi engine',
                    'scope' => 'Harian',
                    'filled' => $engineFilled,
                    'total' => null,
                    'unit' => 'catatan',
                    'href' => "/monitoring-operasi-engine?date={$targetDate}",
                    'view_permission' => 'monitoring_engine.view',
                    'input_permission' => 'monitoring_engine.input',
                ],
                [
                    'module' => 'bbm',
                    'label' => 'Stok BBM',
                    'scope' => 'Harian',
                    'filled' => $fuelForDate ? 1 : 0,
                    'total' => 1,
                    'unit' => 'catatan',
                    'last_date' => $latestFuelLog ? Carbon::parse($latestFuelLog->recorded_date)->format('d/m/Y') : null,
                    'href' => '/monitoring-bbm?month=' . substr($targetDate, 0, 7),
                    'view_permission' => 'monitoring_bbm.view',
                    'input_permission' => 'monitoring_bbm.input',
                ],
            ],
        ];
    }
}
