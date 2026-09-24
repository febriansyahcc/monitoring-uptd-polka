<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EngineAreaLog extends Model
{
    use HasFactory;

    // Kolom yang selalu diisi langsung
    public const FIXED_FIELDS = [
        'turbo_speed_r' => ['label' => 'Turbo Speed R', 'unit' => ''],
        'turbo_speed_l' => ['label' => 'Turbo Speed L', 'unit' => ''],
        'air_inlet_restriction' => ['label' => 'Air Inlet Restriction', 'unit' => 'RH'],
    ];

    // Kolom "dibuat choice": pilih salah satu, lalu isi nilainya
    public const CHOICE_GROUPS = [
        'temperature' => [
            'label' => 'Temperature',
            'unit' => '°C',
            'options' => [
                'temp_oil' => 'Oil',
                'temp_engine_coolant' => 'Engine Coolant',
                'temp_manifold_air' => 'Manifold Air',
                'temp_turbo_exhaust_l' => 'Turbo Exhaust L',
                'temp_exhaust_stack_l' => 'Exhaust Stack L',
                'temp_turbo_exhaust_r' => 'Turbo Exhaust R',
                'temp_exhaust_stack_r' => 'Exhaust Stack R',
            ],
        ],
        'pressure' => [
            'label' => 'Pressure',
            'unit' => 'PSI',
            'options' => [
                'press_fuel' => 'Fuel',
                'press_oil_filter' => 'Oil Filter',
                'press_fuel_filter' => 'Fuel Filter',
                'press_air_inlet_manifold' => 'Air Inlet Manifold',
                'press_exhaust_stack_l' => 'Exhaust Stack L',
                'press_turbo_exhaust_r' => 'Turbo Exhaust R',
                'press_exhaust_stack_r' => 'Exhaust Stack R',
            ],
        ],
        'cylinder_head' => [
            'label' => 'Cylinder Head Temp.',
            'unit' => '°C',
            'options' => [
                'cyl_head_no1' => 'No 1',
                'cyl_head_no2' => 'No 2',
                'cyl_head_no3' => 'No 3',
                'cyl_head_no4' => 'No 4',
                'cyl_head_no5' => 'No 5',
                'cyl_head_no6' => 'No 6',
                'cyl_head_no7' => 'No 7',
                'cyl_head_no8' => 'No 8',
                'cyl_head_no9' => 'No 9',
                'cyl_head_no10' => 'No 10',
                'cyl_head_no11' => 'No 11',
                'cyl_head_no12' => 'No 12',
            ],
        ],
        'jacket_water' => [
            'label' => 'Jacket Water Rad',
            'unit' => '°C',
            'options' => [
                'jacket_water_in' => 'In Temperature (Eng Out)',
                'jacket_water_out' => 'Out Temperature (Eng In)',
            ],
        ],
        'acoc_water' => [
            'label' => 'AC/OC Water Rad',
            'unit' => '°C',
            'options' => [
                'acoc_water_in' => 'In Temperature (Eng Out)',
                'acoc_water_out' => 'Out Temperature (Eng In)',
            ],
        ],
        'flowmeter' => [
            'label' => 'Flowmeter',
            'unit' => '',
            'options' => [
                'flowmeter_in' => 'In',
                'flowmeter_out' => 'Out',
            ],
        ],
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
        return collect(self::FIXED_FIELDS)
            ->keys()
            ->merge(collect(self::CHOICE_GROUPS)->flatMap(fn ($group) => array_keys($group['options'])))
            ->all();
    }
}
