<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KwhProductionLog;
use Inertia\Inertia;
use Carbon\Carbon;

class KwhProductionController extends Controller
{
    public function index(Request $request)
    {
        $selectedMonth = $request->input('month', Carbon::now()->format('Y-m'));
        
        try {
            $parsedDate = Carbon::createFromFormat('Y-m', $selectedMonth);
        } catch (\Exception $e) {
            $parsedDate = Carbon::now();
            $selectedMonth = $parsedDate->format('Y-m');
        }

        $logs = KwhProductionLog::whereYear('recorded_date', $parsedDate->year)
            ->whereMonth('recorded_date', $parsedDate->month)
            ->orderBy('recorded_date', 'asc')
            ->get();

        // Format logs with Last Modified string
        $formattedLogs = $logs->map(function ($log) {
            $time = $log->last_modified_time ?: '-';
            $date = $log->last_modified_date ? Carbon::parse($log->last_modified_date)->format('d/m/Y') : '-';
            $operator = $log->operator_name ?: 'Operator';
            
            return [
                'id' => $log->id,
                'recorded_date' => $log->recorded_date,
                'kwh_ps' => floatval($log->kwh_ps),
                'kwh_digital_1' => floatval($log->kwh_digital_1),
                'kwh_digital_2' => floatval($log->kwh_digital_2),
                'kwh_total' => floatval($log->kwh_total),
                'operator_name' => $log->operator_name ?: '',
                'last_modified' => $log->last_modified_time ? "{$time}, {$date}, {$operator}" : '-',
            ];
        });

        // Summary Calculations
        $totalKwhPs = $logs->sum('kwh_ps');
        $totalKwhDigital1 = $logs->sum('kwh_digital_1');
        $totalKwhDigital2 = $logs->sum('kwh_digital_2');
        $totalKwh = $logs->sum('kwh_total');

        return Inertia::render('KwhProduction/Index', [
            'logs' => $formattedLogs,
            'selectedMonth' => $selectedMonth,
            'summary' => [
                'totalKwhPs' => floatval($totalKwhPs),
                'totalKwhDigital1' => floatval($totalKwhDigital1),
                'totalKwhDigital2' => floatval($totalKwhDigital2),
                'totalKwh' => floatval($totalKwh),
                'count' => $logs->count(),
            ]
        ]);
    }

    public function storeOrUpdate(Request $request)
    {
        $validated = $request->validate([
            'recorded_date' => 'required|date',
            'kwh_ps' => 'nullable|numeric|min:0|max:999999999.99',
            'kwh_digital_1' => 'nullable|numeric|min:0|max:999999999.99',
            'kwh_digital_2' => 'nullable|numeric|min:0|max:999999999.99',
            'operator_name' => 'nullable|string|max:100',
        ]);

        $log = KwhProductionLog::updateOrCreate(
            ['recorded_date' => $validated['recorded_date']],
            [
                'kwh_ps' => $validated['kwh_ps'] ?: 0,
                'kwh_digital_1' => $validated['kwh_digital_1'] ?: 0,
                'kwh_digital_2' => $validated['kwh_digital_2'] ?: 0,
                'operator_name' => $validated['operator_name'] ?: 'Operator',
            ]
        );

        $dateFormatted = Carbon::parse($validated['recorded_date'])->format('d/m/Y');

        return redirect()->back()->with('success', "Data kWh Produksi tanggal {$dateFormatted} berhasil disimpan.");
    }

    public function destroy($id)
    {
        $log = KwhProductionLog::findOrFail($id);
        $dateFormatted = Carbon::parse($log->recorded_date)->format('d/m/Y');
        $log->delete();

        return redirect()->back()->with('success', "Data kWh Produksi tanggal {$dateFormatted} telah dihapus.");
    }
}
