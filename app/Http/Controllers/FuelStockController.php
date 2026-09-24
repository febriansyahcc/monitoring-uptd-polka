<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\FuelStock;
use Inertia\Inertia;
use Carbon\Carbon;

class FuelStockController extends Controller
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

        $logs = FuelStock::whereYear('recorded_date', $parsedDate->year)
            ->whereMonth('recorded_date', $parsedDate->month)
            ->orderBy('recorded_date', 'asc')
            ->get();

        $formattedLogs = $logs->map(function ($log) {
            $time = $log->last_modified_time ?: '-';
            $date = $log->last_modified_date ? Carbon::parse($log->last_modified_date)->format('d/m/Y') : '-';
            $operator = $log->operator_name ?: 'Operator';

            return [
                'id' => $log->id,
                'recorded_date' => $log->recorded_date,
                'daily_consumption' => floatval($log->daily_consumption),
                'main_tank' => floatval($log->main_tank),
                'total_gross' => floatval($log->total_gross),
                'death_stock' => floatval($log->death_stock),
                'unloading' => floatval($log->unloading),
                'netto_stock' => floatval($log->netto_stock),
                'estimated_daily_consumption' => floatval($log->estimated_daily_consumption),
                'days_of_supply' => floatval($log->days_of_supply),
                'operator_name' => $log->operator_name ?: '',
                'last_modified' => $log->last_modified_time ? "{$time}, {$date}, {$operator}" : '-',
            ];
        });

        // Summary Calculations
        $latestRecord = $logs->last();
        $latestNettoStock = $latestRecord ? floatval($latestRecord->netto_stock) : 0;
        $latestDaysOfSupply = $latestRecord ? floatval($latestRecord->days_of_supply) : 0;
        $totalConsumption = $logs->sum('daily_consumption');
        $totalUnloading = $logs->sum('unloading');

        return Inertia::render('FuelStock/Index', [
            'logs' => $formattedLogs,
            'selectedMonth' => $selectedMonth,
            'summary' => [
                'latestNettoStock' => $latestNettoStock,
                'latestDaysOfSupply' => $latestDaysOfSupply,
                'totalConsumption' => floatval($totalConsumption),
                'totalUnloading' => floatval($totalUnloading),
                'count' => $logs->count(),
            ]
        ]);
    }

    public function storeOrUpdate(Request $request)
    {
        // Edit = ada `id`; tambah = tanpa `id`. Tanggal tidak lagi menjadi kunci upsert,
        // sehingga edit tidak bisa memindahkan/menduplikasi data ke tanggal lain dan
        // tambah tidak bisa menimpa data tanggal yang sudah ada secara diam-diam.
        $id = $request->input('id');

        $validated = $request->validate([
            'id' => 'nullable|integer|exists:fuel_stocks,id',
            'recorded_date' => ['required', 'date', Rule::unique('fuel_stocks', 'recorded_date')->ignore($id)],
            'daily_consumption' => 'nullable|numeric|min:0|max:999999999.99',
            'main_tank' => 'nullable|numeric|min:0|max:999999999.99',
            'death_stock' => 'nullable|numeric|min:0|max:999999999.99',
            'unloading' => 'nullable|numeric|min:0|max:999999999.99',
            'estimated_daily_consumption' => 'nullable|numeric|min:0|max:999999999.99',
            'operator_name' => 'nullable|string|max:100',
        ], [
            'recorded_date.unique' => 'Data Stok BBM untuk tanggal ini sudah ada. Gunakan tombol Edit pada baris tersebut.',
        ]);

        $values = [
            'daily_consumption' => ($validated['daily_consumption'] ?? null) ?: 0,
            'main_tank' => ($validated['main_tank'] ?? null) ?: 0,
            'death_stock' => ($validated['death_stock'] ?? null) ?: 0,
            'unloading' => ($validated['unloading'] ?? null) ?: 0,
            'estimated_daily_consumption' => ($validated['estimated_daily_consumption'] ?? null) ?: 0,
            'operator_name' => ($validated['operator_name'] ?? null) ?: 'Operator',
        ];

        if (!empty($validated['id'])) {
            // Tanggal record yang diedit tidak ikut diubah
            $log = FuelStock::findOrFail($validated['id']);
            $log->update($values);
        } else {
            $log = FuelStock::create(['recorded_date' => $validated['recorded_date']] + $values);
        }

        $dateFormatted = Carbon::parse($log->recorded_date)->format('d/m/Y');

        return redirect()->back()->with('success', "Data Stok BBM tanggal {$dateFormatted} berhasil disimpan.");
    }

    public function destroy($id)
    {
        $log = FuelStock::findOrFail($id);
        $dateFormatted = Carbon::parse($log->recorded_date)->format('d/m/Y');
        $log->delete();

        return redirect()->back()->with('success', "Data Stok BBM tanggal {$dateFormatted} telah dihapus.");
    }
}
