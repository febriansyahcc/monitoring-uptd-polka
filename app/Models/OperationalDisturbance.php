<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OperationalDisturbance extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_date',
        'event_time',
        'disturbance_type',
        'status',
        'description',
        'operator_name',
        'last_modified_time',
        'last_modified_date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $now = Carbon::now();
            $model->last_modified_time = $now->format('H:i:s');
            $model->last_modified_date = $now->format('Y-m-d');
        });
    }
}
