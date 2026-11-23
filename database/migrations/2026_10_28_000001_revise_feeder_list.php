<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Daftar penyulang tabel arus direvisi: Lateri 3, Galala 1 dan Galala 2 ditambahkan,
     * Tantui Atas dan Karpan 2 dinonaktifkan (data arusnya tetap tersimpan), urutan kolom disesuaikan.
     */
    private const ORDER = [
        'Wayame 2' => 1, 'Waiheru 1' => 2, 'Hitu' => 3, 'MVTIC 1' => 4, 'MVTIC 2' => 5,
        'Rijali' => 6, 'Lateri 3' => 7, 'Karpan 1' => 8, 'Lateri 2' => 9, 'Lateri 1' => 10,
        'Galala 1' => 11, 'Galala 2' => 12, 'MCM' => 13,
    ];

    private const NEW = ['Lateri 3' => 'LTR-03', 'Galala 1' => 'GLL-01', 'Galala 2' => 'GLL-02'];

    private const RETIRED = ['Tantui Atas', 'Karpan 2'];

    public function up(): void
    {
        // Database baru: FeederSeeder yang mengisi daftar lengkap
        if (!DB::table('feeders')->exists()) {
            return;
        }

        $now = now();

        foreach (self::NEW as $name => $code) {
            if (!DB::table('feeders')->where('name', $name)->exists()) {
                DB::table('feeders')->insert([
                    'name' => $name, 'code' => $code, 'is_active' => true, 'sort_order' => 0,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        foreach (self::ORDER as $name => $order) {
            DB::table('feeders')->where('name', $name)->update(['is_active' => true, 'sort_order' => $order, 'updated_at' => $now]);
        }

        DB::table('feeders')->whereIn('name', self::RETIRED)->update(['is_active' => false, 'updated_at' => $now]);
    }

    public function down(): void
    {
        DB::table('feeders')->whereIn('name', self::RETIRED)->update(['is_active' => true]);
        DB::table('feeders')->whereIn('name', array_keys(self::NEW))->update(['is_active' => false]);
    }
};
