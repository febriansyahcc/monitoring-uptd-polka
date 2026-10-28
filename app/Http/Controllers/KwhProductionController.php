<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\KwhEngineLog;
use App\Models\KwhFeederLog;
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

        $monthStart = $parsedDate->copy()->startOfMonth();
        $monthEnd = $parsedDate->copy()->endOfMonth();

        // ENGINE: stand akhir + produksi harian (selisih stand akhir)
        $engineLogs = KwhEngineLog::withProduction($monthStart, $monthEnd)
            ->sortBy([['recorded_date', 'asc'], ['engine', 'asc']])
            ->values()
            ->map(fn ($log) => [
                'id' => $log->id,
                'recorded_date' => $log->recorded_date,
                'engine' => $log->engine,
                'engine_label' => KwhEngineLog::ENGINES[$log->engine] ?? $log->engine,
                'stand_akhir' => $this->toFloat($log->stand_akhir),
                'stand_edmi_mk10' => $this->toFloat($log->stand_edmi_mk10),
                'stand_kwh_ps' => $this->toFloat($log->stand_kwh_ps),
                'flowmeter_in' => $this->toFloat($log->flowmeter_in),
                'flowmeter_out' => $this->toFloat($log->flowmeter_out),
                'produksi' => $log->produksi,
                'operator_name' => $log->operator_name ?: '',
                'last_modified' => $this->formatLastModified($log),
            ]);

        // PENYULANG
        $feederLogs = KwhFeederLog::whereBetween('recorded_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->orderBy('recorded_date', 'asc')
            ->orderBy('feeder', 'asc')
            ->get();

        // Satu baris (tanggal + penyulang) bisa berisi stand akhir (tab Penyulang) dan/atau PS (tab PS Total)
        $formattedFeederLogs = $feederLogs->filter->hasStand()->values()->map(fn ($log) => [
            'id' => $log->id,
            'recorded_date' => $log->recorded_date,
            'feeder' => $log->feeder,
            'feeder_label' => KwhFeederLog::FEEDERS[$log->feeder] ?? $log->feeder,
            'pm800_ex' => $this->toFloat($log->pm800_ex),
            'pm800_im' => $this->toFloat($log->pm800_im),
            'edmi_mk10_ex' => $this->toFloat($log->edmi_mk10_ex),
            'edmi_mk10_im' => $this->toFloat($log->edmi_mk10_im),
            'operator_name' => $log->operator_name ?: '',
            'last_modified' => $this->formatLastModified($log),
        ]);

        $psLogs = $feederLogs->filter->hasPs()->values();

        $formattedPsLogs = $psLogs->map(fn ($log) => [
            'id' => $log->id,
            'recorded_date' => $log->recorded_date,
            'feeder' => $log->feeder,
            'feeder_label' => KwhFeederLog::FEEDERS[$log->feeder] ?? $log->feeder,
            'kwh_ps' => $this->toFloat($log->kwh_ps),
            'kwh_digital' => $this->toFloat($log->kwh_digital),
            'ps_total' => floatval($log->ps_total),
            'operator_name' => $log->operator_name ?: '',
            'last_modified' => $this->formatLastModified($log),
        ]);

        return Inertia::render('KwhProduction/Index', [
            'engineLogs' => $engineLogs,
            'feederLogs' => $formattedFeederLogs,
            'psLogs' => $formattedPsLogs,
            'engines' => $this->toOptions(KwhEngineLog::ENGINES),
            'feeders' => $this->toOptions(KwhFeederLog::FEEDERS),
            'standChoices' => $this->toOptions(KwhFeederLog::STAND_CHOICES),
            'selectedMonth' => $selectedMonth,
            'summary' => [
                'totalProduksi' => floatval($engineLogs->sum('produksi')),
                'totalPsTotal' => floatval($psLogs->sum('ps_total')),
                'engineDays' => $engineLogs->pluck('recorded_date')->unique()->count(),
                'feederDays' => $formattedFeederLogs->pluck('recorded_date')->unique()->count(),
                'psDays' => $formattedPsLogs->pluck('recorded_date')->unique()->count(),
            ],
        ]);
    }

    public function storeEngine(Request $request)
    {
        $validated = $request->validate([
            'recorded_date' => 'required|date',
            'engine' => ['required', Rule::in(array_keys(KwhEngineLog::ENGINES))],
            'stand_akhir' => 'nullable|numeric|min:0|max:9999999999999.99',
            'stand_edmi_mk10' => 'nullable|numeric|min:0|max:9999999999999.99',
            'stand_kwh_ps' => 'nullable|numeric|min:0|max:9999999999999.99',
            'flowmeter_in' => 'nullable|numeric|min:0|max:9999999999999.99',
            'flowmeter_out' => 'nullable|numeric|min:0|max:9999999999999.99',
            'operator_name' => 'nullable|string|max:100',
        ]);

        KwhEngineLog::updateOrCreate(
            ['recorded_date' => $validated['recorded_date'], 'engine' => $validated['engine']],
            [
                'stand_akhir' => $validated['stand_akhir'] ?? null,
                'stand_edmi_mk10' => $validated['stand_edmi_mk10'] ?? null,
                'stand_kwh_ps' => $validated['stand_kwh_ps'] ?? null,
                'flowmeter_in' => $validated['flowmeter_in'] ?? null,
                'flowmeter_out' => $validated['flowmeter_out'] ?? null,
                'operator_name' => ($validated['operator_name'] ?? null) ?: $request->user()->name,
            ]
        );

        $dateFormatted = Carbon::parse($validated['recorded_date'])->format('d/m/Y');
        $engineLabel = KwhEngineLog::ENGINES[$validated['engine']];

        return redirect()->back()->with('success', "Data kWh Engine {$engineLabel} tanggal {$dateFormatted} berhasil disimpan.");
    }

    /**
     * Tab PENYULANG: hanya stand akhir kWh produksi. Bagian PS pada baris yang sama tidak disentuh.
     */
    public function storeFeeder(Request $request)
    {
        $validated = $request->validate([
            'recorded_date' => 'required|date',
            'feeder' => ['required', Rule::in(array_keys(KwhFeederLog::FEEDERS))],
            'pm800_ex' => 'nullable|numeric|min:0|max:9999999999999.99',
            'pm800_im' => 'nullable|numeric|min:0|max:9999999999999.99',
            'edmi_mk10_ex' => 'nullable|numeric|min:0|max:9999999999999.99',
            'edmi_mk10_im' => 'nullable|numeric|min:0|max:9999999999999.99',
            'operator_name' => 'nullable|string|max:100',
        ]);

        $stands = collect(KwhFeederLog::STAND_FIELDS)->mapWithKeys(fn ($field) => [$field => $validated[$field] ?? null]);

        if ($stands->filter(fn ($value) => $value !== null)->isEmpty()) {
            throw ValidationException::withMessages(['pm800_ex' => 'Isi minimal satu stand akhir kWh produksi.']);
        }

        $this->saveFeederPart($validated, $stands->all(), $request);

        $dateFormatted = Carbon::parse($validated['recorded_date'])->format('d/m/Y');
        $feederLabel = KwhFeederLog::FEEDERS[$validated['feeder']];

        return redirect()->back()->with('success', "Data kWh Penyulang {$feederLabel} tanggal {$dateFormatted} berhasil disimpan.");
    }

    /**
     * Tab PS TOTAL: kWh PS + kWh Digital (PS Total dihitung otomatis). Stand akhir tidak disentuh.
     */
    public function storePsTotal(Request $request)
    {
        $validated = $request->validate([
            'recorded_date' => 'required|date',
            'feeder' => ['required', Rule::in(array_keys(KwhFeederLog::FEEDERS))],
            'kwh_ps' => 'nullable|numeric|min:0|max:9999999999999.99',
            'kwh_digital' => 'nullable|numeric|min:0|max:9999999999999.99',
            'operator_name' => 'nullable|string|max:100',
        ]);

        $ps = $validated['kwh_ps'] ?? null;
        $digital = $validated['kwh_digital'] ?? null;

        if ($ps === null && $digital === null) {
            throw ValidationException::withMessages(['kwh_ps' => 'Isi kWh PS atau kWh Digital.']);
        }

        $this->saveFeederPart($validated, ['kwh_ps' => $ps ?? 0, 'kwh_digital' => $digital ?? 0], $request);

        $dateFormatted = Carbon::parse($validated['recorded_date'])->format('d/m/Y');
        $feederLabel = KwhFeederLog::FEEDERS[$validated['feeder']];

        return redirect()->back()->with('success', "Data PS Total {$feederLabel} tanggal {$dateFormatted} berhasil disimpan.");
    }

    public function destroyEngine($id)
    {
        $log = KwhEngineLog::findOrFail($id);
        $dateFormatted = Carbon::parse($log->recorded_date)->format('d/m/Y');
        $log->delete();

        return redirect()->back()->with('success', "Data kWh Engine tanggal {$dateFormatted} telah dihapus.");
    }

    public function destroyFeeder($id)
    {
        $log = KwhFeederLog::findOrFail($id);
        $dateFormatted = Carbon::parse($log->recorded_date)->format('d/m/Y');
        $this->clearFeederPart($log, array_fill_keys(KwhFeederLog::STAND_FIELDS, null), $log->hasPs());

        return redirect()->back()->with('success', "Data kWh Penyulang tanggal {$dateFormatted} telah dihapus.");
    }

    public function destroyPsTotal($id)
    {
        $log = KwhFeederLog::findOrFail($id);
        $dateFormatted = Carbon::parse($log->recorded_date)->format('d/m/Y');
        $this->clearFeederPart($log, ['kwh_ps' => null, 'kwh_digital' => null, 'ps_total' => null], $log->hasStand());

        return redirect()->back()->with('success', "Data PS Total tanggal {$dateFormatted} telah dihapus.");
    }

    /**
     * Simpan satu bagian (stand atau PS) pada baris tanggal + penyulang; bagian lain dibiarkan.
     */
    private function saveFeederPart(array $validated, array $values, Request $request): void
    {
        KwhFeederLog::updateOrCreate(
            ['recorded_date' => $validated['recorded_date'], 'feeder' => $validated['feeder']],
            $values + ['operator_name' => ($validated['operator_name'] ?? null) ?: $request->user()->name]
        );
    }

    /**
     * Kosongkan satu bagian; baris dihapus bila bagian lainnya juga kosong.
     */
    private function clearFeederPart(KwhFeederLog $log, array $nulls, bool $otherPartFilled): void
    {
        if (!$otherPartFilled) {
            $log->delete();
            return;
        }

        $log->update($nulls);
    }

    private function toFloat($value): ?float
    {
        return $value === null ? null : floatval($value);
    }

    private function toOptions(array $map): array
    {
        return collect($map)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values()->all();
    }

    private function formatLastModified($log): string
    {
        if (!$log->last_modified_time) {
            return '-';
        }

        $date = $log->last_modified_date ? Carbon::parse($log->last_modified_date)->format('d/m/Y') : '-';
        $operator = $log->operator_name ?: 'Operator';

        return "{$log->last_modified_time}, {$date}, {$operator}";
    }
}
