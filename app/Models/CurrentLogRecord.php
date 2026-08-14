<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CurrentLogRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'recorded_date',
        'shift',
        'time_interval',
        'feeder_id',
        'current_value',
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

    public function feeder(): BelongsTo
    {
        return $this->belongsTo(Feeder::class);
    }
}
