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
        Schema::create('engine_control_panel_logs', function (Blueprint $table) {
            $table->id();
            $table->date('recorded_date');
            $table->string('recorded_time', 5);
            $table->string('engine', 20);

            $table->decimal('kw', 12, 2)->nullable();
            $table->decimal('cos_q', 5, 3)->nullable();
            $table->decimal('freq_hz', 8, 2)->nullable();

            // Generator Ampere
            $table->decimal('amp_r', 12, 2)->nullable();
            $table->decimal('amp_s', 12, 2)->nullable();
            $table->decimal('amp_t', 12, 2)->nullable();

            // Generator Voltage
            $table->decimal('volt_rs', 12, 2)->nullable();
            $table->decimal('volt_st', 12, 2)->nullable();
            $table->decimal('volt_tr', 12, 2)->nullable();

            // Generator Bearing Temp (°C)
            $table->decimal('bearing_temp_de', 8, 2)->nullable();
            $table->decimal('bearing_temp_od', 8, 2)->nullable();

            // Exiter
            $table->decimal('exiter_v', 12, 2)->nullable();
            $table->decimal('exiter_a', 12, 2)->nullable();

            // Generator Winding Temp (°C)
            $table->decimal('winding_temp_r', 8, 2)->nullable();
            $table->decimal('winding_temp_s', 8, 2)->nullable();
            $table->decimal('winding_temp_t', 8, 2)->nullable();

            // kWh Produksi Utama
            $table->decimal('kwh_produksi_utama_1', 15, 2)->nullable();
            $table->decimal('kwh_produksi_utama_2', 15, 2)->nullable();

            $table->string('operator_name')->nullable();
            $table->string('last_modified_time', 10)->nullable();
            $table->date('last_modified_date')->nullable();
            $table->timestamps();

            $table->unique(['recorded_date', 'recorded_time', 'engine'], 'unique_control_panel_log');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('engine_control_panel_logs');
    }
};
