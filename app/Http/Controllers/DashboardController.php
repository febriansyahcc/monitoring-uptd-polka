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
        $todayKwhTotal = floatval($engineLogsMonth->where('recorded_date', $today)->sum('produksi'));

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
                'description' => $item->description ?: '-',
            ]);

        // 4. FUEL STOCK (BBM) SUMMARY
        $latestFuelLog = FuelStock::orderBy('recorded_date', 'desc')->first();
        $bmmNettoStock = $latestFuelLog ? floatval($latestFuelLog->netto_stock) : 0;
        $bmmDaysOfSupply = $latestFuelLog ? floatval($latestFuelLog->days_of_supply) : 0;
        $bmmDailyConsumption = $latestFuelLog ? floatval($latestFuelLog->daily_consumption) : 0;
        
        if (!$latestFuelLog) {
            $bmmStatus = 'Belum Ada Data';
        } elseif ($bmmDaysOfSupply <= 5) {
            // Termasuk HOP = 0 (stok habis)
            $bmmStatus = 'Kritis';
        } elseif ($bmmDaysOfSupply <= 10) {
            $bmmStatus = 'Waspada';
        } else {
            $bmmStatus = 'Aman';
        }

        return Inertia::render('Dashboard/Index', [
            'todayDateFormatted' => Carbon::today()->locale('id')->translatedFormat('l, d F Y'),
            'kpi' => [
                'current' => [
                    'total_load' => floatval($latestCurrentTotal),
                    'active_feeders' => count($feeders),
                    'latest_interval' => $latestInterval ?: '-',
                ],
                'kwh' => [
                    'today_total' => $todayKwhTotal,
                    'month_total' => floatval($totalKwhMonth),
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
        ]);
    }
}
