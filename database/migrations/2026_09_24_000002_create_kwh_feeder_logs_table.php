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
        Schema::create('kwh_feeder_logs', function (Blueprint $table) {
            $table->id();
            $table->date('recorded_date');
            $table->string('feeder', 30);
            // Stand akhir kWh produksi (PM 800 & EDMI MK10, export/import)
            $table->decimal('pm800_ex', 15, 2)->nullable();
            $table->decimal('pm800_im', 15, 2)->nullable();
            $table->decimal('edmi_mk10_ex', 15, 2)->nullable();
            $table->decimal('edmi_mk10_im', 15, 2)->nullable();
            $table->decimal('kwh_ps', 15, 2)->default(0);
            $table->decimal('kwh_digital', 15, 2)->default(0);
            $table->decimal('ps_total', 15, 2)->default(0);
            $table->string('operator_name')->nullable();
            $table->string('last_modified_time', 10)->nullable();
            $table->date('last_modified_date')->nullable();
            $table->timestamps();

            $table->unique(['recorded_date', 'feeder']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kwh_feeder_logs');
    }
};
