<?php

namespace App\Models;

class AbcEngineAreaLog extends AbcLogRecord
{
    // Kolom tabel Engine Area ABC #6 sesuai format PA 2026 (tiga bagian "LANJUT" digabung jadi satu urutan)
    public static function groups(): array
    {
        $ab = fn (string $prefix) => [$prefix . '_a' => 'A', $prefix . '_b' => 'B'];

        // Satu grup per nomor (1 - 6) dengan sub-kolom A dan B, di bawah satu judul induk
        $numbered = fn (string $section, string $keyPrefix, string $unit = '') => collect(range(1, 6))
            ->map(fn ($i) => ['section' => $section, 'label' => (string) $i, 'unit' => $unit, 'fields' => $ab("{$keyPrefix}_{$i}")])
            ->all();

        $single = fn (string $label, string $key, string $unit = '') => ['label' => $label, 'unit' => $unit, 'fields' => [$key => $label]];

        return [
            ['section' => 'Oil Cooler', 'label' => 'Oil', 'unit' => '', 'fields' => [
                'oil_cooler_oil_temp_in' => 'Temp (°C) IN',
                'oil_cooler_oil_tekanan_out' => 'Tekanan (Kg/Cm²) OUT',
            ]],
            ['section' => 'Oil Cooler', 'label' => 'Water', 'unit' => '', 'fields' => [
                'oil_cooler_water_temp_in' => 'Temp (°C) IN',
                'oil_cooler_water_tekanan_out' => 'Tekanan (Kg/Cm²) OUT',
            ]],
            ...$numbered('Fuel Rack', 'fuel_rack'),
            $single('RPM', 'rpm'),
            $single('Lub Oil Temp', 'lub_oil_temp'),
            $single('Oil Temp Bef Cooler', 'oil_temp_bef_cooler'),
            $single('Oil Press', 'oil_press'),

            $single('HT Temp', 'ht_temp'),
            ['section' => 'Water Temperature', 'label' => 'CAC', 'unit' => '', 'fields' => [
                'water_temp_cac_cw_inlet' => 'CW Inlet', 'water_temp_cac_cw_outlet' => 'CW Outlet',
            ]],
            ['section' => 'Water Temperature', 'label' => 'Engine', 'unit' => '', 'fields' => [
                'water_temp_engine_cw_inlet' => 'CW Inlet', 'water_temp_engine_cw_outlet' => 'CW Outlet',
            ]],
            ['section' => 'Water Temperature', 'label' => 'Radiator', 'unit' => '', 'fields' => [
                'water_temp_radiator_cw_inlet' => 'CW Inlet', 'water_temp_radiator_cw_outlet' => 'CW Outlet',
            ]],
            $single('Turbo Press', 'turbo_press'),
            ...$numbered('CW Cylinder', 'cw_cylinder'),

            ...$numbered('Exhaust Gas', 'exhaust_gas'),
            ['label' => 'Exhaust Gas After TC', 'unit' => '', 'fields' => $ab('exhaust_gas_after_tc')],
            $single('Gov Load Limit', 'gov_load_limit'),
            ['label' => 'Fuel Rack', 'unit' => '', 'fields' => $ab('fuel_rack')],
            ['label' => 'Flowmeter HSD', 'unit' => '', 'fields' => ['flowmeter_hsd_in' => 'IN', 'flowmeter_hsd_out' => 'OUT']],
        ];
    }
}
