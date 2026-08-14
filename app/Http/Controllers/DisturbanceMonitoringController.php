<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OperationalDisturbance;
use Inertia\Inertia;
use Carbon\Carbon;

class DisturbanceMonitoringController extends Controller
{
    public const DISTURBANCE_TYPES = [
        'Trip Feeder',
        'Pohon Tumbang',
        'Sambaran Petir',
        'Overload / Beban Lebih',
        'Kerusakan Komponen',
        'Lain-lain',
    ];

    public const STATUS_OPTIONS = [
        'Dalam Penanganan',
        'Selesai',
        'Investigasi',
    ];

    public function index(Request $request)
    {
        $selectedMonth = $request->input('month', Carbon::now()->format('Y-m'));

        try {
            $parsedDate = Carbon::createFromFormat('Y-m', $selectedMonth);
        } catch (\Exception $e) {
            $parsedDate = Carbon::now();
            $selectedMonth = $parsedDate->format('Y-m');
        }

        $records = OperationalDisturbance::whereYear('event_date', $parsedDate->year)
            ->whereMonth('event_date', $parsedDate->month)
            ->orderBy('event_date', 'desc')
            ->orderBy('event_time', 'desc')
            ->get();

        $formattedRecords = $records->map(function ($record) {
            $time = $record->last_modified_time ?: '-';
            $date = $record->last_modified_date ? Carbon::parse($record->last_modified_date)->format('d/m/Y') : '-';
            $operator = $record->operator_name ?: 'Operator';

            return [
                'id' => $record->id,
                'event_date' => $record->event_date,
                'event_time' => $record->event_time,
                'disturbance_type' => $record->disturbance_type,
                'status' => $record->status,
                'description' => $record->description ?: '-',
                'operator_name' => $record->operator_name ?: '',
                'last_modified' => $record->last_modified_time ? "{$time}, {$date}, {$operator}" : '-',
            ];
        });

        // Summary Calculations
        $totalCount = $records->count();
        $inProgressCount = $records->where('status', 'Dalam Penanganan')->count();
        $resolvedCount = $records->where('status', 'Selesai')->count();
        $investigatingCount = $records->where('status', 'Investigasi')->count();

        // Distribution by Disturbance Type (for Pie/Donut Chart)
        $typeCounts = [];
        foreach (self::DISTURBANCE_TYPES as $type) {
            $typeCounts[$type] = $records->where('disturbance_type', $type)->count();
        }

        return Inertia::render('DisturbanceMonitoring/Index', [
            'disturbances' => $formattedRecords,
            'selectedMonth' => $selectedMonth,
            'disturbanceTypes' => self::DISTURBANCE_TYPES,
            'statusOptions' => self::STATUS_OPTIONS,
            'typeDistribution' => $typeCounts,
            'summary' => [
                'total' => $totalCount,
                'inProgress' => $inProgressCount,
                'resolved' => $resolvedCount,
                'investigating' => $investigatingCount,
            ]
        ]);
    }

    public function storeOrUpdate(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer|exists:operational_disturbances,id',
            'event_date' => 'required|date',
            'event_time' => 'required|string|max:10',
            'disturbance_type' => 'required|string|max:100',
            'status' => 'required|string|in:Dalam Penanganan,Selesai,Investigasi',
            'description' => 'nullable|string|max:1000',
            'operator_name' => 'nullable|string|max:100',
        ]);

        $disturbance = OperationalDisturbance::updateOrCreate(
            ['id' => $validated['id'] ?? null],
            [
                'event_date' => $validated['event_date'],
                'event_time' => $validated['event_time'],
                'disturbance_type' => $validated['disturbance_type'],
                'status' => $validated['status'],
                'description' => $validated['description'] ?? null,
                'operator_name' => $validated['operator_name'] ?: 'Operator',
            ]
        );

        $dateFormatted = Carbon::parse($validated['event_date'])->format('d/m/Y');
        $actionText = isset($validated['id']) ? 'diperbarui' : 'ditambahkan';

        return redirect()->back()->with('success', "Data gangguan {$validated['disturbance_type']} tanggal {$dateFormatted} berhasil {$actionText}.");
    }

    public function destroy($id)
    {
        $record = OperationalDisturbance::findOrFail($id);
        $dateFormatted = Carbon::parse($record->event_date)->format('d/m/Y');
        $record->delete();

        return redirect()->back()->with('success', "Catatan gangguan tanggal {$dateFormatted} berhasil dihapus.");
    }
}
