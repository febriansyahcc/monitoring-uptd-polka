<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Engine Area engine ABC #6 (PA 2026 revisi): 59 kolom isian dalam tiga bagian tabel.
     */
    public function up(): void
    {
        Schema::create('abc_engine_area_logs', function (Blueprint $table) {
            $table->id();
            $table->date('recorded_date');
            $table->string('recorded_time', 5);

            // Oil cooler (Oil & Water)
            $table->decimal('oil_cooler_oil_temp_in', 12, 2)->nullable();
            $table->decimal('oil_cooler_oil_tekanan_out', 12, 2)->nullable();
            $table->decimal('oil_cooler_water_temp_in', 12, 2)->nullable();
            $table->decimal('oil_cooler_water_tekanan_out', 12, 2)->nullable();

            // Fuel rack 1 - 6 (A/B)
            for ($i = 1; $i <= 6; $i++) {
                $table->decimal("fuel_rack_{$i}_a", 12, 2)->nullable();
                $table->decimal("fuel_rack_{$i}_b", 12, 2)->nullable();
            }

            $table->decimal('rpm', 12, 2)->nullable();
            $table->decimal('lub_oil_temp', 12, 2)->nullable();
            $table->decimal('oil_temp_bef_cooler', 12, 2)->nullable();
            $table->decimal('oil_press', 12, 2)->nullable();

            // Lanjutan 1
            $table->decimal('ht_temp', 12, 2)->nullable();
            foreach (['cac', 'engine', 'radiator'] as $part) {
                $table->decimal("water_temp_{$part}_cw_inlet", 12, 2)->nullable();
                $table->decimal("water_temp_{$part}_cw_outlet", 12, 2)->nullable();
            }
            $table->decimal('turbo_press', 12, 2)->nullable();

            // CW cylinder 1 - 6 (A/B)
            for ($i = 1; $i <= 6; $i++) {
                $table->decimal("cw_cylinder_{$i}_a", 12, 2)->nullable();
                $table->decimal("cw_cylinder_{$i}_b", 12, 2)->nullable();
            }

            // Lanjutan 2
            for ($i = 1; $i <= 6; $i++) {
                $table->decimal("exhaust_gas_{$i}_a", 12, 2)->nullable();
                $table->decimal("exhaust_gas_{$i}_b", 12, 2)->nullable();
            }
            $table->decimal('exhaust_gas_after_tc_a', 12, 2)->nullable();
            $table->decimal('exhaust_gas_after_tc_b', 12, 2)->nullable();
            $table->decimal('gov_load_limit', 12, 2)->nullable();
            $table->decimal('fuel_rack_a', 12, 2)->nullable();
            $table->decimal('fuel_rack_b', 12, 2)->nullable();
            $table->decimal('flowmeter_hsd_in', 15, 2)->nullable();
            $table->decimal('flowmeter_hsd_out', 15, 2)->nullable();

            $table->string('operator_name')->nullable();
            $table->string('last_modified_time', 10)->nullable();
            $table->date('last_modified_date')->nullable();
            $table->timestamps();

            $table->unique(['recorded_date', 'recorded_time'], 'unique_abc_engine_area_log');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abc_engine_area_logs');
    }
};
