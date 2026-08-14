<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FuelStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'recorded_date',
        'daily_consumption',
        'main_tank',
        'total_gross',
        'death_stock',
        'unloading',
        'netto_stock',
        'estimated_daily_consumption',
        'days_of_supply',
        'operator_name',
        'last_modified_time',
        'last_modified_date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->daily_consumption = floatval($model->daily_consumption ?: 0);
            $model->main_tank = floatval($model->main_tank ?: 0);
            $model->death_stock = floatval($model->death_stock ?: 0);
            $model->unloading = floatval($model->unloading ?: 0);
            $model->estimated_daily_consumption = floatval($model->estimated_daily_consumption ?: 0);

            // Rule 1: Total gross bbm = main tank
            $model->total_gross = $model->main_tank;

            // Rule 2: Netto stock = total gross - death stock + unloading
            $model->netto_stock = $model->total_gross - $model->death_stock + $model->unloading;

            // Rule 3: Sisa hari operasi = netto / estimasi
            if ($model->estimated_daily_consumption > 0) {
                $model->days_of_supply = round($model->netto_stock / $model->estimated_daily_consumption, 2);
            } else {
                $model->days_of_supply = 0;
            }

            // Auto Audit Trail
            $now = Carbon::now();
            $model->last_modified_time = $now->format('H:i:s');
            $model->last_modified_date = $now->format('Y-m-d');
        });
    }
}
