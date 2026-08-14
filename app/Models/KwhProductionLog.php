<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class KwhProductionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'recorded_date',
        'kwh_ps',
        'kwh_digital_1',
        'kwh_digital_2',
        'kwh_total',
        'operator_name',
        'last_modified_time',
        'last_modified_date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Auto calculate kWh Total
            $model->kwh_ps = $model->kwh_ps ?: 0;
            $model->kwh_digital_1 = $model->kwh_digital_1 ?: 0;
            $model->kwh_digital_2 = $model->kwh_digital_2 ?: 0;
            $model->kwh_total = floatval($model->kwh_ps) + floatval($model->kwh_digital_1) + floatval($model->kwh_digital_2);

            // Auto Audit Trail
            $now = Carbon::now();
            $model->last_modified_time = $now->format('H:i:s');
            $model->last_modified_date = $now->format('Y-m-d');
        });
    }
}
