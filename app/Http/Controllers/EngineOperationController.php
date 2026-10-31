<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\EngineControlPanelLog;
use App\Models\EngineAreaLog;
use App\Models\AbcControlPanelLog;
use App\Models\AbcEngineAreaLog;
use Inertia\Inertia;
use Carbon\Carbon;

class EngineOperationController extends Controller
{
    public const ENGINES = [
        'CAT_4' => 'CAT #4',
        'ABC_6' => 'ABC #6',
    ];

    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));

        try {
            $date = Carbon::createFromFormat('Y-m-d', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            $date = Carbon::today()->format('Y-m-d');
        }

        // Jenis engine: CAT #4 memakai tabel lama, ABC #6 punya tabel & kolom sendiri
        $engineType = $request->input('engine') === 'abc' ? 'abc' : 'cat';

        $shared = [
            'selectedDate' => $date,
            'engineType' => $engineType,
            'engineTypes' => [
                ['key' => 'cat', 'label' => self::ENGINES['CAT_4']],
                ['key' => 'abc', 'label' => self::ENGINES['ABC_6']],
            ],
        ];

        if ($engineType === 'abc') {
            return Inertia::render('EngineOperation/Index', $shared + [
                'abcControlPanelLogs' => $this->abcLogs(AbcControlPanelLog::class, $date),
                'abcEngineAreaLogs' => $this->abcLogs(AbcEngineAreaLog::class, $date),
                'abcControlPanelGroups' => $this->abcGroups(AbcControlPanelLog::groups()),
                'abcEngineAreaGroups' => $this->abcGroups(AbcEngineAreaLog::groups()),
            ]);
        }

        $controlPanelLogs = EngineControlPanelLog::where('recorded_date', $date)
            ->where('engine', 'CAT_4')
            ->orderBy('recorded_time', 'asc')
            ->get()
            ->map(fn ($log) => $this->formatLog($log, EngineControlPanelLog::fieldKeys()));

        $engineAreaLogs = EngineAreaLog::where('recorded_date', $date)
            ->where('engine', 'CAT_4')
            ->orderBy('recorded_time', 'asc')
            ->get()
            ->map(fn ($log) => $this->formatLog($log, EngineAreaLog::fieldKeys()));

        return Inertia::render('EngineOperation/Index', $shared + [
            'engines' => $this->toOptions(['CAT_4' => self::ENGINES['CAT_4']]),
            'controlPanelLogs' => $controlPanelLogs,
            'engineAreaLogs' => $engineAreaLogs,
            'controlPanelGroups' => collect(EngineControlPanelLog::FIELD_GROUPS)->map(fn ($group) => [
                'label' => $group['label'],
                'unit' => $group['unit'],
                'fields' => $this->toOptions($group['fields']),
            ]),
            'engineAreaFixedFields' => collect(EngineAreaLog::FIXED_FIELDS)
                ->map(fn ($field, $key) => ['key' => $key] + $field)
                ->values(),
            'engineAreaChoiceGroups' => collect(EngineAreaLog::CHOICE_GROUPS)->map(fn ($group, $key) => [
                'key' => $key,
                'label' => $group['label'],
                'unit' => $group['unit'],
                'options' => $this->toOptions($group['options']),
            ])->values(),
        ]);
    }

    public function storeAbcControlPanel(Request $request)
    {
        $fieldRules = collect(AbcControlPanelLog::fieldKeys())
            ->mapWithKeys(fn ($key) => [$key => 'nullable|numeric|min:0|max:9999999999'])
            ->put('cos_q', 'nullable|numeric|between:0,1')
            ->all();

        return $this->storeAbc($request, AbcControlPanelLog::class, $fieldRules, 'Control Panel ABC #6');
    }

    public function storeAbcEngineArea(Request $request)
    {
        $fieldRules = collect(AbcEngineAreaLog::fieldKeys())
            ->mapWithKeys(fn ($key) => [$key => 'nullable|numeric|min:0|max:9999999999'])
            ->all();

        return $this->storeAbc($request, AbcEngineAreaLog::class, $fieldRules, 'Engine Area ABC #6');
    }

    public function destroyAbcControlPanel($id)
    {
        AbcControlPanelLog::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Data Control Panel ABC #6 telah dihapus.');
    }

    public function destroyAbcEngineArea($id)
    {
        AbcEngineAreaLog::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Data Engine Area ABC #6 telah dihapus.');
    }

    public function storeControlPanel(Request $request)
    {
        $fieldRules = collect(EngineControlPanelLog::fieldKeys())
            ->mapWithKeys(fn ($key) => [$key => 'nullable|numeric|min:0|max:9999999999'])
            ->put('cos_q', 'nullable|numeric|between:0,1')
            ->all();

        return $this->store($request, EngineControlPanelLog::class, $fieldRules, 'Control Panel');
    }

    public function storeEngineArea(Request $request)
    {
        $fieldRules = collect(EngineAreaLog::fieldKeys())
            ->mapWithKeys(fn ($key) => [$key => 'nullable|numeric|min:0|max:9999999999'])
            ->all();

        return $this->store($request, EngineAreaLog::class, $fieldRules, 'Engine Area');
    }

    public function destroyControlPanel($id)
    {
        EngineControlPanelLog::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Data Control Panel telah dihapus.');
    }

    public function destroyEngineArea($id)
    {
        EngineAreaLog::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Data Engine Area telah dihapus.');
    }

    /**
     * Simpan satu baris pencatatan (unik per tanggal + jam + engine).
     */
    private function store(Request $request, string $modelClass, array $fieldRules, string $sectionLabel)
    {
        $validated = $request->validate(array_merge([
            'recorded_date' => 'required|date',
            'recorded_time' => 'required|date_format:H:i',
            'engine' => ['required', Rule::in(array_keys(self::ENGINES))],
            'operator_name' => 'nullable|string|max:100',
        ], $fieldRules));

        $values = collect(array_keys($fieldRules))
            ->mapWithKeys(fn ($key) => [$key => $validated[$key] ?? null])
            ->put('operator_name', ($validated['operator_name'] ?? null) ?: $request->user()->name)
            ->all();

        $modelClass::updateOrCreate(
            [
                'recorded_date' => $validated['recorded_date'],
                'recorded_time' => $validated['recorded_time'],
                'engine' => $validated['engine'],
            ],
            $values
        );

        $dateFormatted = Carbon::parse($validated['recorded_date'])->format('d/m/Y');
        $engineLabel = self::ENGINES[$validated['engine']];

        return redirect()->back()->with('success', "Data {$sectionLabel} {$engineLabel} jam {$validated['recorded_time']} tanggal {$dateFormatted} berhasil disimpan.");
    }

    /**
     * Simpan satu baris pencatatan ABC #6 (unik per tanggal + jam).
     */
    private function storeAbc(Request $request, string $modelClass, array $fieldRules, string $sectionLabel)
    {
        $validated = $request->validate(array_merge([
            'recorded_date' => 'required|date',
            'recorded_time' => 'required|date_format:H:i',
            'operator_name' => 'nullable|string|max:100',
        ], $fieldRules));

        $values = collect(array_keys($fieldRules))
            ->mapWithKeys(fn ($key) => [$key => $validated[$key] ?? null])
            ->put('operator_name', ($validated['operator_name'] ?? null) ?: $request->user()->name)
            ->all();

        $modelClass::updateOrCreate(
            ['recorded_date' => $validated['recorded_date'], 'recorded_time' => $validated['recorded_time']],
            $values
        );

        $dateFormatted = Carbon::parse($validated['recorded_date'])->format('d/m/Y');

        return redirect()->back()->with('success', "Data {$sectionLabel} jam {$validated['recorded_time']} tanggal {$dateFormatted} berhasil disimpan.");
    }

    private function abcLogs(string $modelClass, string $date)
    {
        return $modelClass::where('recorded_date', $date)
            ->orderBy('recorded_time', 'asc')
            ->get()
            ->map(fn ($log) => $this->formatLog($log, $modelClass::fieldKeys()));
    }

    /**
     * Grup kolom ABC untuk frontend; `key` unik karena label (mis. "1" - "6") berulang antar bagian.
     */
    private function abcGroups(array $groups): array
    {
        return collect($groups)->values()->map(fn ($group, $index) => [
            'key' => "g{$index}",
            'section' => $group['section'] ?? null,
            'label' => $group['label'],
            'unit' => $group['unit'],
            'fields' => $this->toOptions($group['fields']),
        ])->all();
    }

    private function formatLog($log, array $fieldKeys): array
    {
        $data = [
            'id' => $log->id,
            'recorded_date' => $log->recorded_date,
            'recorded_time' => $log->recorded_time,
            'engine' => $log->engine,
            'engine_label' => self::ENGINES[$log->engine] ?? $log->engine,
            'operator_name' => $log->operator_name ?: '',
            'last_modified' => '-',
        ];

        foreach ($fieldKeys as $key) {
            $data[$key] = $log->{$key} === null ? null : floatval($log->{$key});
        }

        if ($log->last_modified_time) {
            $date = $log->last_modified_date ? Carbon::parse($log->last_modified_date)->format('d/m/Y') : '-';
            $operator = $log->operator_name ?: 'Operator';
            $data['last_modified'] = "{$log->last_modified_time}, {$date}, {$operator}";
        }

        return $data;
    }

    private function toOptions(array $map): array
    {
        return collect($map)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values()->all();
    }
}
