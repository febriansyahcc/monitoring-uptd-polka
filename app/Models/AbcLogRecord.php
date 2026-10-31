<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Dasar catatan logsheet engine ABC #6. Anak kelas mendefinisikan groups():
 * daftar grup kolom [['label', 'unit', 'section'?, 'fields' => [key => label]]]; `section` (opsional)
 * adalah judul induk beberapa grup berurutan, mis. "Fuel Rack" untuk grup 1 - 6.
 */
abstract class AbcLogRecord extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    abstract public static function groups(): array;

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
        return collect(static::groups())->flatMap(fn ($group) => array_keys($group['fields']))->all();
    }
}
