<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Revisi Engine Area: kolom Speed Engine (sebelum Turbo Speed) serta pilihan Pressure
     * Oil dan Air Inlet Restriction RH (IN H2O). Kolom pressure lama (Exhaust Stack L,
     * Turbo Exhaust R, Exhaust Stack R) dibiarkan agar data lama tetap aman.
     */
    public function up(): void
    {
        Schema::table('engine_area_logs', function (Blueprint $table) {
            $table->decimal('speed_engine', 12, 2)->nullable()->after('engine');
            $table->decimal('press_oil', 8, 2)->nullable()->after('temp_exhaust_stack_r');
            $table->decimal('press_air_inlet_restriction', 8, 2)->nullable()->after('press_air_inlet_manifold');
        });
    }

    public function down(): void
    {
        Schema::table('engine_area_logs', function (Blueprint $table) {
            $table->dropColumn(['speed_engine', 'press_oil', 'press_air_inlet_restriction']);
        });
    }
};
