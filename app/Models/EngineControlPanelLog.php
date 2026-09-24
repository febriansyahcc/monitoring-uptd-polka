<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EngineControlPanelLog extends Model
{
    use HasFactory;

    // Kolom tabel Control Panel sesuai format PA 2026 (grup header → field => sub-label)
    public const FIELD_GROUPS = [
        ['label' => 'KW', 'unit' => 'kW', 'fields' => ['kw' => 'KW']],
        ['label' => 'Cos φ', 'unit' => '', 'fields' => ['cos_q' => 'Cos φ']],
        ['label' => 'Freq', 'unit' => 'Hz', 'fields' => ['freq_hz' => 'Freq']],
        ['label' => 'Generator Ampere', 'unit' => 'A', 'fields' => ['amp_r' => 'R', 'amp_s' => 'S', 'amp_t' => 'T']],
        ['label' => 'Generator Voltage', 'unit' => 'V', 'fields' => ['volt_rs' => 'RS', 'volt_st' => 'ST', 'volt_tr' => 'TR']],
        ['label' => 'Generator Bearing Temp', 'unit' => '°C', 'fields' => ['bearing_temp_de' => 'DE', 'bearing_temp_od' => 'OD']],
        ['label' => 'Exiter', 'unit' => '', 'fields' => ['exiter_v' => 'V', 'exiter_a' => 'A']],
        ['label' => 'Generator Winding Temp', 'unit' => '°C', 'fields' => ['winding_temp_r' => 'R', 'winding_temp_s' => 'S', 'winding_temp_t' => 'T']],
        ['label' => 'kWh Produksi Utama', 'unit' => 'kWh', 'fields' => ['kwh_produksi_utama_1' => '1', 'kwh_produksi_utama_2' => '2']],
    ];

    protected $guarded = ['id'];

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

    public static function fieldKeys(): array
    {
        return collect(self::FIELD_GROUPS)->flatMap(fn ($group) => array_keys($group['fields']))->all();
    }
}
