<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Control Panel engine ABC #6 (PA 2026 revisi): kolomnya berbeda dari CAT #4.
     */
    public function up(): void
    {
        Schema::create('abc_control_panel_logs', function (Blueprint $table) {
            $table->id();
            $table->date('recorded_date');
            $table->string('recorded_time', 5);

            // Temperature stator
            $table->decimal('stator_r', 12, 2)->nullable();
            $table->decimal('stator_s', 12, 2)->nullable();
            $table->decimal('stator_t', 12, 2)->nullable();
            $table->decimal('cd_bearing', 12, 2)->nullable();

            // Ampere
            $table->decimal('amp_r', 12, 2)->nullable();
            $table->decimal('amp_s', 12, 2)->nullable();
            $table->decimal('amp_t', 12, 2)->nullable();

            // Voltage
            $table->decimal('volt_r', 12, 2)->nullable();
            $table->decimal('volt_s', 12, 2)->nullable();
            $table->decimal('volt_t', 12, 2)->nullable();

            $table->decimal('kw', 12, 2)->nullable();
            $table->decimal('cos_q', 5, 3)->nullable();
            $table->decimal('freq_hz', 8, 2)->nullable();
            $table->decimal('kva', 12, 2)->nullable();
            $table->decimal('kvar', 12, 2)->nullable();

            $table->decimal('kwh_produksi_utama', 15, 2)->nullable();
            $table->decimal('kwh_produksi_pembanding', 15, 2)->nullable();

            $table->string('operator_name')->nullable();
            $table->string('last_modified_time', 10)->nullable();
            $table->date('last_modified_date')->nullable();
            $table->timestamps();

            $table->unique(['recorded_date', 'recorded_time'], 'unique_abc_control_panel_log');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abc_control_panel_logs');
    }
};
