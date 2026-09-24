<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class KwhFeederLog extends Model
{
    use HasFactory;

    public const FEEDERS = [
        'GALALA_1' => 'Galala 1 (MVTIC #2)',
        'GALALA_2' => 'Galala 2 (MVTIC #1)',
        'WAIHERU_1' => 'Waiheru 1',
        'WAIHERU_3' => 'Waiheru 3',
        'WAYAME_1' => 'Wayame 1',
        'WAYAME_2' => 'Wayame 2',
        'HITU' => 'Hitu',
    ];

    // Pilihan stand akhir kWh produksi: meter + arah (export/import)
    public const STAND_CHOICES = [
        'pm800_ex' => 'PM 800 - EX',
        'pm800_im' => 'PM 800 - IM',
        'edmi_mk10_ex' => 'EDMI MK10 - EX',
        'edmi_mk10_im' => 'EDMI MK10 - IM',
    ];

    protected $fillable = [
        'recorded_date',
        'feeder',
        'pm800_ex',
        'pm800_im',
        'edmi_mk10_ex',
        'edmi_mk10_im',
        'kwh_ps',
        'kwh_digital',
        'ps_total',
        'operator_name',
        'last_modified_time',
        'last_modified_date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Auto calculate PS Total
            $model->kwh_ps = floatval($model->kwh_ps ?: 0);
            $model->kwh_digital = floatval($model->kwh_digital ?: 0);
            $model->ps_total = $model->kwh_ps + $model->kwh_digital;

            // Auto Audit Trail
            $now = Carbon::now();
            $model->last_modified_time = $now->format('H:i:s');
            $model->last_modified_date = $now->format('Y-m-d');
        });
    }
}
