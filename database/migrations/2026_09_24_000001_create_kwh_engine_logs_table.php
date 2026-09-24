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
        Schema::create('kwh_engine_logs', function (Blueprint $table) {
            $table->id();
            $table->date('recorded_date');
            $table->string('engine', 20);
            // Stand akhir kWh produksi
            $table->decimal('stand_akhir', 15, 2)->nullable();
            $table->decimal('stand_edmi_mk10', 15, 2)->nullable();
            // Stand akhir kWh PS
            $table->decimal('stand_kwh_ps', 15, 2)->nullable();
            // Flowmeter
            $table->decimal('flowmeter_in', 15, 2)->nullable();
            $table->decimal('flowmeter_out', 15, 2)->nullable();
            $table->string('operator_name')->nullable();
            $table->string('last_modified_time', 10)->nullable();
            $table->date('last_modified_date')->nullable();
            $table->timestamps();

            $table->unique(['recorded_date', 'engine']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kwh_engine_logs');
    }
};
