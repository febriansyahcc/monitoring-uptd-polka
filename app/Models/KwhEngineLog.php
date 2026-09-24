<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class KwhEngineLog extends Model
{
    use HasFactory;

    public const ENGINES = [
        'GMT_3' => 'GMT #3',
        'CAT_4' => 'CAT #4',
        'CAT_5' => 'CAT #5',
        'ABC_6' => 'ABC #6',
    ];

    protected $fillable = [
        'recorded_date',
        'engine',
        'stand_akhir',
        'stand_edmi_mk10',
        'stand_kwh_ps',
        'flowmeter_in',
        'flowmeter_out',
        'operator_name',
        'last_modified_time',
        'last_modified_date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Auto Audit Trail
            $now = Carbon::now();
            $model->last_modified_time = $now->format('H:i:s');
            $model->last_modified_date = $now->format('Y-m-d');
        });
    }

    /**
     * Logs within the range, each with `produksi` = stand akhir minus the
     * engine's previous stand akhir (null when there is no earlier reading).
     */
    public static function withProduction(Carbon $from, Carbon $to): Collection
    {
        $logs = static::whereBetween('recorded_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('recorded_date', 'asc')
            ->get();

        $previous = [];
        foreach (array_keys(self::ENGINES) as $engine) {
            $previous[$engine] = static::where('engine', $engine)
                ->where('recorded_date', '<', $from->toDateString())
                ->whereNotNull('stand_akhir')
                ->orderBy('recorded_date', 'desc')
                ->value('stand_akhir');
        }

        foreach ($logs as $log) {
            $prev = $previous[$log->engine] ?? null;
            $log->produksi = ($prev !== null && $log->stand_akhir !== null)
                ? floatval($log->stand_akhir) - floatval($prev)
                : null;

            if ($log->stand_akhir !== null) {
                $previous[$log->engine] = $log->stand_akhir;
            }
        }

        return $logs;
    }
}
