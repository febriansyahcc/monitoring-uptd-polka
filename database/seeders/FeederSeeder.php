<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Feeder;

class FeederSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $feeders = [
            ['name' => 'Wayame 2', 'code' => 'WYM-02', 'sort_order' => 1],
            ['name' => 'Waiheru 1', 'code' => 'WHR-01', 'sort_order' => 2],
            ['name' => 'Hitu', 'code' => 'HTU-01', 'sort_order' => 3],
            ['name' => 'MVTIC 1', 'code' => 'MVT-01', 'sort_order' => 4],
            ['name' => 'MVTIC 2', 'code' => 'MVT-02', 'sort_order' => 5],
            ['name' => 'Rijali', 'code' => 'RNJ-01', 'sort_order' => 6],
            ['name' => 'Lateri 3', 'code' => 'LTR-03', 'sort_order' => 7],
            ['name' => 'Karpan 1', 'code' => 'KRP-01', 'sort_order' => 8],
            ['name' => 'Lateri 2', 'code' => 'LTR-02', 'sort_order' => 9],
            ['name' => 'Lateri 1', 'code' => 'LTR-01', 'sort_order' => 10],
            ['name' => 'Galala 1', 'code' => 'GLL-01', 'sort_order' => 11],
            ['name' => 'Galala 2', 'code' => 'GLL-02', 'sort_order' => 12],
            ['name' => 'MCM', 'code' => 'MCM-01', 'sort_order' => 13],
        ];

        // Dicocokkan lewat kode, supaya koreksi nama tidak membuat penyulang ganda
        foreach ($feeders as $feeder) {
            Feeder::updateOrCreate(
                ['code' => $feeder['code']],
                $feeder + ['is_active' => true]
            );
        }

        // Penyulang di luar daftar (Tantui Atas, Karpan 2) dinonaktifkan, datanya tetap tersimpan
        Feeder::whereNotIn('code', array_column($feeders, 'code'))->update(['is_active' => false]);
    }
}
