<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feeder;
use App\Models\CurrentLogRecord;
use App\Models\KwhProductionLog;
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

        // Latest total current / load recorded today
        $latestInterval = $todayCurrentLogs->sortByDesc('id')->first()?->time_interval;
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
                'last_updated' => $latestFeederLog ? $latestFeederLog->last_modified_time : '-',
            ];
        }

        // Aggregate hourly load per shift interval for today's chart
        $intervalSummary = [];
        $uniqueIntervals = $todayCurrentLogs->pluck('time_interval')->unique()->values();
        foreach ($uniqueIntervals as $interval) {
            $sum = $todayCurrentLogs->where('time_interval', $interval)->sum('current_value');
            $intervalSummary[] = [
                'interval' => $interval,
                'total_current' => floatval($sum),
            ];
        }

        // 2. KWH PRODUCTION SUMMARY & 7-DAY TREND
        $kwhLogsMonth = KwhProductionLog::whereYear('recorded_date', Carbon::now()->year)
            ->whereMonth('recorded_date', Carbon::now()->month)
            ->orderBy('recorded_date', 'asc')
            ->get();

        $todayKwhLog = KwhProductionLog::where('recorded_date', $today)->first();
        $totalKwhMonth = $kwhLogsMonth->sum('kwh_total');
        $todayKwhTotal = $todayKwhLog ? floatval($todayKwhLog->kwh_total) : 0;

        // Last 7 days trend for chart
        $last7DaysKwh = KwhProductionLog::orderBy('recorded_date', 'desc')
            ->take(7)
            ->get()
            ->reverse()
            ->values()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->recorded_date)->format('d/m'),
                    'kwh_total' => floatval($item->kwh_total),
                    'kwh_ps' => floatval($item->kwh_ps),
                    'kwh_digital' => floatval($item->kwh_digital_1 + $item->kwh_digital_2),
                ];
            });

        // 3. OPERATIONAL DISTURBANCES SUMMARY
        $disturbancesMonth = OperationalDisturbance::whereYear('event_date', Carbon::now()->year)
            ->whereMonth('event_date', Carbon::now()->month)
            ->orderBy('event_date', 'desc')
            ->orderBy('event_time', 'desc')
            ->get();

        $inProgressDisturbances = $disturbancesMonth->where('status', 'Dalam Penanganan')->count();
        $investigatingDisturbances = $disturbancesMonth->where('status', 'Investigasi')->count();
        $resolvedDisturbances = $disturbancesMonth->where('status', 'Selesai')->count();

        $recentDisturbances = $disturbancesMonth->take(5)->map(function ($item) {
            return [
                'id' => $item->id,
                'event_date' => Carbon::parse($item->event_date)->format('d/m/Y'),
                'event_time' => $item->event_time,
                'disturbance_type' => $item->disturbance_type,
                'status' => $item->status,
                'description' => $item->description ?: '-',
            ];
        })->values();

        // 4. FUEL STOCK (BBM) SUMMARY
        $latestFuelLog = FuelStock::orderBy('recorded_date', 'desc')->first();
        $bmmNettoStock = $latestFuelLog ? floatval($latestFuelLog->netto_stock) : 0;
        $bmmDaysOfSupply = $latestFuelLog ? floatval($latestFuelLog->days_of_supply) : 0;
        $bmmDailyConsumption = $latestFuelLog ? floatval($latestFuelLog->daily_consumption) : 0;
        
        $bmmStatus = 'Aman';
        if ($bmmDaysOfSupply > 0 && $bmmDaysOfSupply <= 5) {
            $bmmStatus = 'Kritis';
        } elseif ($bmmDaysOfSupply > 5 && $bmmDaysOfSupply <= 10) {
            $bmmStatus = 'Waspada';
        }

        return Inertia::render('Dashboard/Index', [
            'todayDateFormatted' => Carbon::today()->translatedFormat('l, d F Y'),
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
