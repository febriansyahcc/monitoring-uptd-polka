<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('engine_area_logs', function (Blueprint $table) {
            $table->id();
            $table->date('recorded_date');
            $table->string('recorded_time', 5);
            $table->string('engine', 20);

            // Turbo Speed & Air Inlet Restriction
            $table->decimal('turbo_speed_r', 12, 2)->nullable();
            $table->decimal('turbo_speed_l', 12, 2)->nullable();
            $table->decimal('air_inlet_restriction', 12, 2)->nullable();

            // Temperature (°C)
            $table->decimal('temp_oil', 8, 2)->nullable();
            $table->decimal('temp_engine_coolant', 8, 2)->nullable();
            $table->decimal('temp_manifold_air', 8, 2)->nullable();
            $table->decimal('temp_turbo_exhaust_l', 8, 2)->nullable();
            $table->decimal('temp_exhaust_stack_l', 8, 2)->nullable();
            $table->decimal('temp_turbo_exhaust_r', 8, 2)->nullable();
            $table->decimal('temp_exhaust_stack_r', 8, 2)->nullable();

            // Pressure (PSI)
            $table->decimal('press_fuel', 8, 2)->nullable();
            $table->decimal('press_oil_filter', 8, 2)->nullable();
            $table->decimal('press_fuel_filter', 8, 2)->nullable();
            $table->decimal('press_air_inlet_manifold', 8, 2)->nullable();
            $table->decimal('press_exhaust_stack_l', 8, 2)->nullable();
            $table->decimal('press_turbo_exhaust_r', 8, 2)->nullable();
            $table->decimal('press_exhaust_stack_r', 8, 2)->nullable();

            // Cylinder Head Temp (°C) No 1 - 12
            for ($i = 1; $i <= 12; $i++) {
                $table->decimal("cyl_head_no{$i}", 8, 2)->nullable();
            }

            // Jacket Water Rad (°C)
            $table->decimal('jacket_water_in', 8, 2)->nullable();
            $table->decimal('jacket_water_out', 8, 2)->nullable();

            // AC/OC Water Rad (°C)
            $table->decimal('acoc_water_in', 8, 2)->nullable();
            $table->decimal('acoc_water_out', 8, 2)->nullable();

            // Flowmeter
            $table->decimal('flowmeter_in', 15, 2)->nullable();
            $table->decimal('flowmeter_out', 15, 2)->nullable();

            $table->string('operator_name')->nullable();
            $table->string('last_modified_time', 10)->nullable();
            $table->date('last_modified_date')->nullable();
            $table->timestamps();

            $table->unique(['recorded_date', 'recorded_time', 'engine'], 'unique_engine_area_log');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('engine_area_logs');
    }
};
