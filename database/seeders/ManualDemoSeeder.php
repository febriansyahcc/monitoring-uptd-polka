<?php

namespace Database\Seeders;

use App\Http\Controllers\CurrentMonitoringController;
use App\Models\AbcControlPanelLog;
use App\Models\AbcEngineAreaLog;
use App\Models\CurrentLogRecord;
use App\Models\EngineAreaLog;
use App\Models\EngineControlPanelLog;
use App\Models\Feeder;
use App\Models\FuelStock;
use App\Models\KwhEngineLog;
use App\Models\KwhFeederLog;
use App\Models\OperationalDisturbance;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Data contoh untuk screenshot Manual Book (scripts/capture-manual-screenshots.mjs).
 * JANGAN dijalankan ke database produksi/kerja: skrip memakainya pada database terpisah
 * (default `manual_demo`) lewat migrate:fresh.
 *
 * Semua tanggal relatif terhadap hari ini, jadi screenshot selalu menampilkan "hari ini" yang terisi.
 */
class ManualDemoSeeder extends Seeder
{
    private const OPERATOR = 'Operator Shift 1';

    public function run(): void
    {
        $this->call([FeederSeeder::class, UserSeeder::class]);

        mt_srand(20261110); // hasil sama setiap kali dijalankan

        $today = Carbon::today();
        $nowHour = (int) Carbon::now('Asia/Jayapura')->format('H');

        $this->seedCurrent($today, $nowHour);
        $this->seedKwh($today);
        $this->seedEngine($today, $nowHour);
        $this->seedDisturbances($today);
        $this->seedFuel($today);
    }

    private function rand(float $min, float $max, int $decimals = 1): float
    {
        return round($min + (mt_rand() / mt_getrandmax()) * ($max - $min), $decimals);
    }

    /** Beban arus: kemarin & lusa penuh, hari ini sampai interval berjalan. */
    private function seedCurrent(Carbon $today, int $nowHour): void
    {
        $feeders = Feeder::where('is_active', true)->get();
        $base = $feeders->mapWithKeys(fn ($f) => [$f->id => $this->rand(60, 210, 0)]);

        foreach ([2, 1, 0] as $daysAgo) {
            $date = $today->copy()->subDays($daysAgo)->toDateString();
            foreach (CurrentMonitoringController::SHIFT_INTERVALS as $shift => $intervals) {
                foreach ($intervals as $interval) {
                    $hour = (float) str_replace('.', '', substr($interval, 0, 2));
                    if ($daysAgo === 0 && ($shift === 'sore' || ($shift === 'pagi' && $hour >= $nowHour))) {
                        continue;
                    }
                    // Beban naik siang & malam (puncak 19.00)
                    $curve = 0.75 + 0.25 * sin(($hour - 7) / 24 * 2 * M_PI) + ($hour >= 18 && $hour <= 22 ? 0.2 : 0);
                    foreach ($feeders as $feeder) {
                        CurrentLogRecord::create([
                            'recorded_date' => $date,
                            'shift' => $shift,
                            'time_interval' => $interval,
                            'feeder_id' => $feeder->id,
                            'current_value' => round($base[$feeder->id] * $curve + $this->rand(-6, 6), 1),
                            'operator_name' => self::OPERATOR,
                        ]);
                    }
                }
            }
        }
    }

    /** Stand kWh harian sejak awal bulan lalu sampai hari ini (stand kumulatif). */
    private function seedKwh(Carbon $today): void
    {
        $start = $today->copy()->subMonthNoOverflow()->startOfMonth();
        $stand = ['GMT_3' => 4_215_000, 'CAT_4' => 8_904_000, 'CAT_5' => 7_512_000, 'ABC_6' => 12_380_000];
        $daily = ['GMT_3' => 14_000, 'CAT_4' => 22_000, 'CAT_5' => 20_000, 'ABC_6' => 31_000];
        $feederStand = collect(array_keys(KwhFeederLog::FEEDERS))->mapWithKeys(fn ($k) => [$k => $this->rand(900_000, 3_000_000, 0)])->all();
        $flow = 1_250_000;

        for ($d = $start->copy(); $d->lte($today); $d->addDay()) {
            $date = $d->toDateString();
            foreach ($stand as $engine => $value) {
                $stand[$engine] += round($daily[$engine] * $this->rand(0.85, 1.1, 2));
                $flowIn = $flow;
                $flow += round($this->rand(9_000, 11_000, 0));
                KwhEngineLog::create([
                    'recorded_date' => $date,
                    'engine' => $engine,
                    'stand_akhir' => $stand[$engine],
                    'stand_edmi_mk10' => $stand[$engine] - $this->rand(50, 400, 0),
                    'stand_kwh_ps' => round($stand[$engine] * 0.031),
                    'flowmeter_in' => $flowIn,
                    'flowmeter_out' => $flow,
                    'operator_name' => self::OPERATOR,
                ]);
            }
            foreach ($feederStand as $feeder => $value) {
                $feederStand[$feeder] += round($this->rand(9_000, 16_000, 0));
                KwhFeederLog::create([
                    'recorded_date' => $date,
                    'feeder' => $feeder,
                    'pm800_ex' => $feederStand[$feeder],
                    'edmi_mk10_ex' => $feederStand[$feeder] - $this->rand(20, 200, 0),
                    'kwh_ps' => $this->rand(250, 420, 0),
                    'kwh_digital' => $this->rand(80, 160, 0),
                    'operator_name' => self::OPERATOR,
                ]);
            }
        }
    }

    /** Logsheet per jam hari ini (00:00 sampai jam berjalan) untuk CAT #4 dan ABC #6. */
    private function seedEngine(Carbon $today, int $nowHour): void
    {
        $date = $today->toDateString();
        $kwhUtama = 8_904_000;
        $abcKwh = 12_380_000;

        for ($h = 0; $h <= max(1, $nowHour); $h++) {
            $time = sprintf('%02d:00', $h);
            $kw = $this->rand(820, 980, 0);
            $kwhUtama += $kw;

            EngineControlPanelLog::create([
                'recorded_date' => $date, 'recorded_time' => $time, 'engine' => 'CAT_4',
                'kw' => $kw, 'cos_q' => $this->rand(0.82, 0.9, 2), 'freq_hz' => $this->rand(49.9, 50.1, 2),
                'amp_r' => $this->rand(1_150, 1_300, 0), 'amp_s' => $this->rand(1_150, 1_300, 0), 'amp_t' => $this->rand(1_150, 1_300, 0),
                'volt_rs' => $this->rand(395, 405, 0), 'volt_st' => $this->rand(395, 405, 0), 'volt_tr' => $this->rand(395, 405, 0),
                'bearing_temp_de' => $this->rand(55, 68), 'bearing_temp_od' => $this->rand(50, 62),
                'exiter_v' => $this->rand(38, 46), 'exiter_a' => $this->rand(3.2, 4.1),
                'winding_temp_r' => $this->rand(85, 98), 'winding_temp_s' => $this->rand(85, 98), 'winding_temp_t' => $this->rand(85, 98),
                'kwh_produksi_utama' => $kwhUtama, 'kwh_produksi_pembanding' => $kwhUtama - $this->rand(30, 120, 0),
                'operator_name' => self::OPERATOR,
            ]);

            EngineAreaLog::create([
                'recorded_date' => $date, 'recorded_time' => $time, 'engine' => 'CAT_4',
                'speed_engine' => 1_500, 'turbo_speed_r' => $this->rand(38_000, 42_000, 0), 'turbo_speed_l' => $this->rand(38_000, 42_000, 0),
                'air_inlet_restriction' => $this->rand(8, 12),
                'temp_oil' => $this->rand(88, 95), 'temp_engine_coolant' => $this->rand(84, 92), 'temp_manifold_air' => $this->rand(45, 55),
                'temp_turbo_exhaust_l' => $this->rand(480, 540, 0), 'temp_turbo_exhaust_r' => $this->rand(480, 540, 0),
                'press_oil' => $this->rand(58, 66), 'press_fuel' => $this->rand(85, 95),
                'cyl_head_no1' => $this->rand(420, 470, 0), 'cyl_head_no2' => $this->rand(420, 470, 0),
                'jacket_water_in' => $this->rand(84, 90), 'jacket_water_out' => $this->rand(74, 80),
                'operator_name' => self::OPERATOR,
            ]);

            $abcKw = $this->rand(1_350, 1_600, 0);
            $abcKwh += $abcKw;
            AbcControlPanelLog::create(array_merge(
                $this->fillAbc(AbcControlPanelLog::fieldKeys()),
                [
                    'recorded_date' => $date, 'recorded_time' => $time, 'kw' => $abcKw,
                    'kwh_produksi_utama' => $abcKwh, 'kwh_produksi_pembanding' => $abcKwh - $this->rand(30, 120, 0),
                    'operator_name' => self::OPERATOR,
                ]
            ));
            AbcEngineAreaLog::create(array_merge(
                $this->fillAbc(AbcEngineAreaLog::fieldKeys()),
                ['recorded_date' => $date, 'recorded_time' => $time, 'operator_name' => self::OPERATOR]
            ));
        }
    }

    /** Nilai wajar untuk kolom ABC #6 berdasarkan nama kolom. */
    private function fillAbc(array $keys): array
    {
        $ranges = [
            'stator' => [85, 98], 'bearing' => [55, 68], 'amp_' => [1_900, 2_100], 'volt_' => [6_250, 6_350],
            'cos_q' => [0.82, 0.9], 'freq' => [49.9, 50.1], 'kva' => [1_700, 1_900], 'kvar' => [800, 950],
            'fuel_rack' => [18, 24], 'rpm' => [745, 755], 'exhaust_gas' => [380, 440], 'press' => [3.5, 4.5],
            'tekanan' => [3.5, 4.5], 'cw_cylinder' => [72, 80], 'flowmeter' => [1_250_000, 1_260_000],
            'gov_load' => [7, 9], 'temp' => [70, 85],
        ];
        $values = [];
        foreach ($keys as $key) {
            $range = collect($ranges)->first(fn ($r, $needle) => str_contains($key, $needle)) ?? [60, 80];
            $values[$key] = $this->rand($range[0], $range[1], $range[1] < 10 ? 2 : 1);
        }
        return $values;
    }

    private function seedDisturbances(Carbon $today): void
    {
        $items = [
            [9, '14:20', 'Pohon Tumbang', 'Selesai', 'Pohon tumbang menimpa jaringan penyulang Hitu di km 4. Pemangkasan dan normalisasi oleh tim yantek.'],
            [7, '19:05', 'Trip Feeder', 'Selesai', 'Penyulang Wayame 2 trip OCR saat beban puncak. Dimasukkan kembali setelah pengecekan.'],
            [5, '02:40', 'Sambaran Petir', 'Selesai', 'Sambaran petir di sekitar gardu Waiheru, arrester diganti.'],
            [3, '10:15', 'Kerusakan Komponen', 'Investigasi', 'Temperatur bearing DE CAT #4 naik di atas normal, sedang diselidiki tim pemeliharaan.'],
            [1, '20:30', 'Overload / Beban Lebih', 'Dalam Penanganan', 'Beban penyulang Galala 1 mendekati batas, dilakukan manuver beban ke Galala 2.'],
            [0, '07:50', 'Trip Feeder', 'Dalam Penanganan', 'Penyulang Lateri 2 trip GFR, petugas menuju lokasi.'],
        ];
        foreach ($items as [$daysAgo, $time, $type, $status, $desc]) {
            $date = $today->copy()->subDays($daysAgo);
            if ($date->month !== $today->month) {
                $date = $today->copy()->startOfMonth();
            }
            OperationalDisturbance::create([
                'event_date' => $date->toDateString(),
                'event_time' => $time,
                'disturbance_type' => $type,
                'status' => $status,
                'description' => $desc,
                'operator_name' => self::OPERATOR,
            ]);
        }
    }

    /** Stok BBM harian bulan ini; hari ini berakhir di status waspada (± 8 hari) agar peringatan tampil. */
    private function seedFuel(Carbon $today): void
    {
        $tank = 180_000;
        for ($d = $today->copy()->startOfMonth(); $d->lte($today); $d->addDay()) {
            $use = $this->rand(9_800, 11_200, 0);
            $unloading = $d->day === 3 ? 40_000 : 0;
            $tank = $tank - $use;
            FuelStock::create([
                'recorded_date' => $d->toDateString(),
                'daily_consumption' => $use,
                'main_tank' => $tank,
                'death_stock' => 30_000,
                'unloading' => $unloading,
                'estimated_daily_consumption' => 10_500,
                'operator_name' => self::OPERATOR,
            ]);
            $tank += $unloading;
        }
    }
}
