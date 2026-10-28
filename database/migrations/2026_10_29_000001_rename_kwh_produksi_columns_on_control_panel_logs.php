<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dua kolom "kWh Produksi Utama" di Control Panel: yang kedua diganti menjadi
     * "kWh Produksi Pembanding". Hanya ganti nama, angka yang sudah diinput tetap utuh.
     */
    public function up(): void
    {
        Schema::table('engine_control_panel_logs', function (Blueprint $table) {
            $table->renameColumn('kwh_produksi_utama_1', 'kwh_produksi_utama');
            $table->renameColumn('kwh_produksi_utama_2', 'kwh_produksi_pembanding');
        });
    }

    public function down(): void
    {
        Schema::table('engine_control_panel_logs', function (Blueprint $table) {
            $table->renameColumn('kwh_produksi_utama', 'kwh_produksi_utama_1');
            $table->renameColumn('kwh_produksi_pembanding', 'kwh_produksi_utama_2');
        });
    }
};
