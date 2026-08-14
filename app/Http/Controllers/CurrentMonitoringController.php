<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feeder;
use App\Models\CurrentLogRecord;
use Inertia\Inertia;
use Carbon\Carbon;

class CurrentMonitoringController extends Controller
{
    public const SHIFT_INTERVALS = [
        'pagi' => [
            '08.30', '09.00', '09.30', '10.00', '10.30', '11.00', '11.30', '12.00',
            '12.30', '13.00', '13.30', '14.00', '14.30', '15.00', '15.30', '16.00'
        ],
        'sore' => [
            '16.30', '17.00', '17.30', '18.00', '18.30', '19.00', '19.30', '20.00',
            '20.30', '21.00', '21.30', '22.00', '22.30', '23.00', '23.30', '24.00'
        ],
        'malam' => [
            '00.30', '01.00', '01.30', '02.00', '02.30', '03.00', '03.30', '04.00',
            '04.30', '05.00', '05.30', '06.00', '06.30', '07.00', '07.30', '08.00'
        ]
    ];

    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $shift = strtolower($request->input('shift', 'pagi'));

        if (!array_key_exists($shift, self::SHIFT_INTERVALS)) {
            $shift = 'pagi';
        }

        $feeders = Feeder::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get(['id', 'name', 'code']);

        $intervals = self::SHIFT_INTERVALS[$shift];

        // Fetch logs for date and shift
        $logs = CurrentLogRecord::where('recorded_date', $date)
            ->where('shift', $shift)
            ->get();

        // Key logs by interval and feeder_id
        $logsByKey = [];
        foreach ($logs as $log) {
            $logsByKey[$log->time_interval][$log->feeder_id] = $log;
        }

        // Construct matrix
        $matrix = [];
        foreach ($intervals as $interval) {
            $feederValues = [];
            $lastModifiedFormatted = null;
            $operatorName = null;

            foreach ($feeders as $feeder) {
                $record = $logsByKey[$interval][$feeder->id] ?? null;
                $feederValues[$feeder->id] = $record ? $record->current_value : null;

                if ($record && $record->operator_name && !$operatorName) {
                    $operatorName = $record->operator_name;
                }

                if ($record && $record->last_modified_time && !$lastModifiedFormatted) {
                    $time = $record->last_modified_time;
                    $logDate = $record->last_modified_date ? Carbon::parse($record->last_modified_date)->format('d/m/Y') : Carbon::parse($date)->format('d/m/Y');
                    $operator = $record->operator_name ?: 'Operator';
                    $lastModifiedFormatted = "{$time}, {$logDate}, {$operator}";
                }
            }

            $matrix[] = [
                'interval' => $interval,
                'values' => $feederValues,
                'operator_name' => $operatorName ?: '',
                'last_modified' => $lastModifiedFormatted ?: '-',
            ];
        }

        return Inertia::render('CurrentMonitoring/Index', [
            'feeders' => $feeders,
            'matrix' => $matrix,
            'selectedDate' => $date,
            'selectedShift' => $shift,
            'intervals' => $intervals,
            'shifts' => [
                ['key' => 'pagi', 'label' => 'Shift Pagi (08:00 - 16:00)'],
                ['key' => 'sore', 'label' => 'Shift Sore (16:00 - 00:00)'],
                ['key' => 'malam', 'label' => 'Shift Malam (00:00 - 08:00)'],
            ]
        ]);
    }

    public function storeOrUpdate(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'shift' => 'required|in:pagi,sore,malam',
            'time_interval' => 'required|string',
            'operator_name' => 'nullable|string|max:100',
            'values' => 'required|array',
            'values.*' => 'nullable|numeric|min:0|max:99999.99',
        ]);

        $date = $validated['date'];
        $shift = $validated['shift'];
        $interval = $validated['time_interval'];
        $operator = $validated['operator_name'] ?: 'Operator';
        $values = $validated['values'];

        $now = Carbon::now();

        foreach ($values as $feederId => $val) {
            if ($val !== null && $val !== '') {
                CurrentLogRecord::updateOrCreate(
                    [
                        'recorded_date' => $date,
                        'shift' => $shift,
                        'time_interval' => $interval,
                        'feeder_id' => $feederId,
                    ],
                    [
                        'current_value' => $val,
                        'operator_name' => $operator,
                        'last_modified_time' => $now->format('H:i:s'),
                        'last_modified_date' => $now->format('Y-m-d'),
                    ]
                );
            }
        }

        return redirect()->back()->with('success', "Data interval {$interval} berhasil disimpan.");
    }
}
