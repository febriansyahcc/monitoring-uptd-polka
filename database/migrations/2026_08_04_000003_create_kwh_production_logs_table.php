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
        Schema::create('kwh_production_logs', function (Blueprint $table) {
            $table->id();
            $table->date('recorded_date')->unique();
            $table->decimal('kwh_ps', 12, 2)->default(0);
            $table->decimal('kwh_digital_1', 12, 2)->default(0);
            $table->decimal('kwh_digital_2', 12, 2)->default(0);
            $table->decimal('kwh_total', 12, 2)->default(0);
            $table->string('operator_name')->nullable();
            $table->string('last_modified_time', 10)->nullable();
            $table->date('last_modified_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kwh_production_logs');
    }
};
