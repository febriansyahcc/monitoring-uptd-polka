<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Nama "Rinjali" salah ketik dari "Rijali". Seeder lama mencocokkan penyulang lewat nama,
     * sehingga di server bisa ada dua baris (Rinjali & Rijali). Data arus Rinjali dipindah ke
     * Rijali, lalu baris Rinjali dihapus.
     */
    public function up(): void
    {
        $old = DB::table('feeders')->where('name', 'Rinjali')->first();
        if (!$old) {
            return;
        }

        $new = DB::table('feeders')->where('name', 'Rijali')->first();
        if (!$new) {
            DB::table('feeders')->where('id', $old->id)->update(['name' => 'Rijali', 'updated_at' => now()]);
            return;
        }

        DB::transaction(function () use ($old, $new) {
            $oldRecords = DB::table('current_log_records')->where('feeder_id', $old->id)->get();

            foreach ($oldRecords as $record) {
                $existing = DB::table('current_log_records')
                    ->where('feeder_id', $new->id)
                    ->where('recorded_date', $record->recorded_date)
                    ->where('shift', $record->shift)
                    ->where('time_interval', $record->time_interval)
                    ->first();

                // Bila jam yang sama terisi di kedua kolom, pertahankan yang terakhir diubah
                if ($existing && $existing->updated_at >= $record->updated_at) {
                    DB::table('current_log_records')->where('id', $record->id)->delete();
                    continue;
                }

                if ($existing) {
                    DB::table('current_log_records')->where('id', $existing->id)->delete();
                }

                DB::table('current_log_records')->where('id', $record->id)->update(['feeder_id' => $new->id]);
            }

            DB::table('feeders')->where('id', $old->id)->delete();
        });
    }

    /**
     * Penggabungan data tidak bisa dibatalkan.
     */
    public function down(): void
    {
    }
};
