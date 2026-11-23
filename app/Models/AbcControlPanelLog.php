<?php

namespace App\Models;

class AbcControlPanelLog extends AbcLogRecord
{
    // Kolom tabel Control Panel ABC #6 sesuai format PA 2026
    public static function groups(): array
    {
        return [
            ['label' => 'Temperature Stator', 'unit' => '°C', 'fields' => ['stator_r' => 'R', 'stator_s' => 'S', 'stator_t' => 'T']],
            ['label' => 'CD Bearing', 'unit' => '°C', 'fields' => ['cd_bearing' => 'CD Bearing']],
            ['label' => 'Ampere', 'unit' => 'A', 'fields' => ['amp_r' => 'R', 'amp_s' => 'S', 'amp_t' => 'T']],
            ['label' => 'Voltage', 'unit' => 'V', 'fields' => ['volt_r' => 'R', 'volt_s' => 'S', 'volt_t' => 'T']],
            ['label' => 'KW', 'unit' => 'kW', 'fields' => ['kw' => 'KW']],
            ['label' => 'Cos φ', 'unit' => '', 'fields' => ['cos_q' => 'Cos φ']],
            ['label' => 'Freq', 'unit' => 'Hz', 'fields' => ['freq_hz' => 'Freq']],
            ['label' => 'KVA', 'unit' => 'kVA', 'fields' => ['kva' => 'KVA']],
            ['label' => 'KVAR', 'unit' => 'kVAR', 'fields' => ['kvar' => 'KVAR']],
            ['label' => 'kWh Produksi Utama', 'unit' => 'kWh', 'fields' => ['kwh_produksi_utama' => 'kWh Produksi Utama']],
            ['label' => 'kWh Produksi Pembanding', 'unit' => 'kWh', 'fields' => ['kwh_produksi_pembanding' => 'kWh Produksi Pembanding']],
        ];
    }
}
