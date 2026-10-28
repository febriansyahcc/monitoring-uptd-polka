<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stand akhir (tab Penyulang) dan kWh PS / kWh Digital / PS Total (tab PS Total) kini diisi
     * lewat form terpisah pada baris yang sama. Kolom PS dibuat nullable supaya "belum diisi"
     * berbeda dari 0; baris lama yang ketiganya 0 dianggap belum diisi.
     */
    public function up(): void
    {
        Schema::table('kwh_feeder_logs', function (Blueprint $table) {
            $table->decimal('kwh_ps', 15, 2)->nullable()->default(null)->change();
            $table->decimal('kwh_digital', 15, 2)->nullable()->default(null)->change();
            $table->decimal('ps_total', 15, 2)->nullable()->default(null)->change();
        });

        DB::table('kwh_feeder_logs')
            ->where('kwh_ps', 0)->where('kwh_digital', 0)->where('ps_total', 0)
            ->update(['kwh_ps' => null, 'kwh_digital' => null, 'ps_total' => null]);
    }

    public function down(): void
    {
        DB::table('kwh_feeder_logs')->whereNull('kwh_ps')->update(['kwh_ps' => 0]);
        DB::table('kwh_feeder_logs')->whereNull('kwh_digital')->update(['kwh_digital' => 0]);
        DB::table('kwh_feeder_logs')->whereNull('ps_total')->update(['ps_total' => 0]);

        Schema::table('kwh_feeder_logs', function (Blueprint $table) {
            $table->decimal('kwh_ps', 15, 2)->default(0)->nullable(false)->change();
            $table->decimal('kwh_digital', 15, 2)->default(0)->nullable(false)->change();
            $table->decimal('ps_total', 15, 2)->default(0)->nullable(false)->change();
        });
    }
};
