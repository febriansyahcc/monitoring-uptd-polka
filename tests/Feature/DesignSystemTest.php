<?php

namespace Tests\Feature;

use App\Models\AbcControlPanelLog;
use App\Models\AbcEngineAreaLog;
use App\Models\CurrentLogRecord;
use App\Models\Feeder;
use App\Models\FuelStock;
use App\Models\OperationalDisturbance;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\FeederSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Bukti verifikasi IMPLEMENTATION.md (Fase C: kode status, Fase E: shift berjalan,
 * Fase F: prop dashboard & kelengkapan input).
 */
class DesignSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed([FeederSeeder::class, UserSeeder::class]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function admin(): User
    {
        return User::where('email', 'admin@pln.co.id')->firstOrFail();
    }

    private function fuelWithDays(float $days): void
    {
        // HOP = netto / estimasi pemakaian harian; netto = main_tank - death_stock + unloading
        FuelStock::create([
            'recorded_date' => Carbon::today()->toDateString(),
            'daily_consumption' => 100,
            'main_tank' => $days * 100,
            'death_stock' => 0,
            'unloading' => 0,
            'estimated_daily_consumption' => 100,
        ]);
    }

    // ---------- Fase C: kode status ----------

    /** @dataProvider fuelStatusProvider */
    public function test_fuel_status_is_sent_as_code(?float $days, string $expected): void
    {
        if ($days !== null) {
            $this->fuelWithDays($days);
        }

        $this->actingAs($this->admin())
            ->get('/')
            ->assertInertia(fn (Assert $page) => $page->where('kpi.fuel.status', $expected));
    }

    public static function fuelStatusProvider(): array
    {
        return [
            'tanpa data' => [null, 'empty'],
            'HOP 0' => [0, 'critical'],
            'HOP 7' => [7, 'warning'],
            'HOP 20' => [20, 'ok'],
        ];
    }

    public function test_fuel_page_uses_same_status_as_dashboard(): void
    {
        // Dulu halaman BBM memakai ambang sendiri (< 7 hari, dan 0 tidak dianggap kritis)
        $this->fuelWithDays(6);

        $this->actingAs($this->admin())
            ->get('/monitoring-bbm')
            ->assertInertia(fn (Assert $page) => $page->where('summary.latestStatus', 'warning'));

        $this->actingAs($this->admin())
            ->get('/')
            ->assertInertia(fn (Assert $page) => $page->where('kpi.fuel.status', 'warning'));
    }

    public function test_recent_disturbances_carry_status_code(): void
    {
        foreach (['Selesai', 'Investigasi', 'Dalam Penanganan'] as $i => $status) {
            OperationalDisturbance::create([
                'event_date' => Carbon::today()->toDateString(),
                'event_time' => sprintf('0%d:00', $i + 1),
                'disturbance_type' => 'Trip Feeder',
                'status' => $status,
                'description' => 'uji',
            ]);
        }

        $this->actingAs($this->admin())
            ->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->where('recentDisturbances.0.status_code', 'warning')  // 03:00 Dalam Penanganan
                ->where('recentDisturbances.1.status_code', 'warning')  // 02:00 Investigasi
                ->where('recentDisturbances.2.status_code', 'ok'));     // 01:00 Selesai
    }

    // ---------- Fase E: shift berjalan untuk Header ----------

    /** @dataProvider shiftProvider */
    public function test_current_shift_is_shared(string $time, string $key, string $label): void
    {
        Carbon::setTestNow(Carbon::parse("2026-10-12 {$time}", config('app.timezone')));

        $this->actingAs($this->admin())
            ->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->where('currentShift.key', $key)
                ->where('currentShift.label', $label));
    }

    public static function shiftProvider(): array
    {
        return [
            '09.00' => ['09:00', 'pagi', 'Shift Pagi'],
            '17.00' => ['17:00', 'sore', 'Shift Sore'],
            '01.00' => ['01:00', 'malam', 'Shift Malam'],
            'batas 08.00' => ['08:00', 'pagi', 'Shift Pagi'],
            'batas 16.00' => ['16:00', 'sore', 'Shift Sore'],
            'batas 07.59' => ['07:59', 'malam', 'Shift Malam'],
        ];
    }

    // ---------- Fase F: kelengkapan input ----------

    private function progressItem(Assert $page, string $module): array
    {
        $items = $page->toArray()['props']['inputProgress']['items'];

        return collect($items)->firstWhere('module', $module);
    }

    public function test_input_progress_without_any_data(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-12 09:00', config('app.timezone')));

        $this->actingAs($this->admin())
            ->get('/')
            ->assertInertia(function (Assert $page) {
                $page->where('inputProgress.shift.key', 'pagi')
                    ->where('inputProgress.date', '2026-10-12')
                    ->has('inputProgress.items', 4);

                $arus = $this->progressItem($page, 'arus');
                $this->assertSame([0, 16], [$arus['filled'], $arus['total']]);
                $this->assertSame('/monitoring-arus?date=2026-10-12&shift=pagi', $arus['href']);

                $this->assertSame(0, $this->progressItem($page, 'kwh')['filled']);
                $this->assertNull($this->progressItem($page, 'engine')['total']);
                $this->assertSame([0, 1], [$this->progressItem($page, 'bbm')['filled'], $this->progressItem($page, 'bbm')['total']]);
            });
    }

    public function test_input_progress_counts_only_fully_filled_intervals(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-12 11:00', config('app.timezone')));
        $feederIds = Feeder::where('is_active', true)->pluck('id');

        // 3 interval terisi untuk semua feeder aktif
        foreach (['08.30', '09.00', '09.30'] as $interval) {
            foreach ($feederIds as $id) {
                CurrentLogRecord::create([
                    'recorded_date' => '2026-10-12', 'shift' => 'pagi', 'time_interval' => $interval,
                    'feeder_id' => $id, 'current_value' => 10,
                ]);
            }
        }
        // 1 interval baru terisi sebagian -> belum dihitung
        CurrentLogRecord::create([
            'recorded_date' => '2026-10-12', 'shift' => 'pagi', 'time_interval' => '10.00',
            'feeder_id' => $feederIds->first(), 'current_value' => 10,
        ]);
        // Interval shift lain tidak ikut dihitung
        foreach ($feederIds as $id) {
            CurrentLogRecord::create([
                'recorded_date' => '2026-10-12', 'shift' => 'malam', 'time_interval' => '01.00',
                'feeder_id' => $id, 'current_value' => 10,
            ]);
        }

        $this->actingAs($this->admin())
            ->get('/')
            ->assertInertia(function (Assert $page) {
                $arus = $this->progressItem($page, 'arus');
                $this->assertSame([3, 16], [$arus['filled'], $arus['total']]);
            });
    }

    public function test_critical_fuel_feeds_banner_props(): void
    {
        $this->fuelWithDays(0.17);

        $this->actingAs($this->admin())
            ->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->where('kpi.fuel.status', 'critical')
                ->where('kpi.fuel.days_of_supply', 0.17)
                ->where('kpi.fuel.netto_stock', 17));
    }

    public function test_kpi_has_data_distinguishes_empty_from_zero(): void
    {
        $this->actingAs($this->admin())
            ->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->where('kpi.current.has_data', false)
                ->where('kpi.kwh.has_data', false));

        $feeder = Feeder::where('is_active', true)->orderBy('sort_order')->first();
        CurrentLogRecord::create([
            'recorded_date' => Carbon::today()->toDateString(),
            'shift' => 'pagi',
            'time_interval' => '08.30',
            'feeder_id' => $feeder->id,
            'current_value' => 0,
        ]);

        // Nilai 0 yang benar-benar diinput tetap dianggap ada data
        $this->actingAs($this->admin())
            ->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->where('kpi.current.has_data', true)
                ->where('kpi.current.total_load', 0));
    }

    public function test_dashboard_date_filter_shows_historical_data_and_latest_date_hint(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-21 10:00', config('app.timezone')));
        $feeder = Feeder::where('is_active', true)->orderBy('sort_order')->first();

        CurrentLogRecord::create([
            'recorded_date' => '2026-10-15',
            'shift' => 'pagi',
            'time_interval' => '08.30',
            'feeder_id' => $feeder->id,
            'current_value' => 42,
        ]);

        // Hari ini kosong -> memberi petunjuk tanggal data arus terakhir
        $this->actingAs($this->admin())
            ->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->where('selectedDate', '2026-11-21')
                ->where('isToday', true)
                ->where('kpi.current.has_data', false)
                ->where('latestCurrentDate', '2026-10-15'));

        // Pilih tanggal Oktober -> data Oktober tampil di Dashboard
        $this->actingAs($this->admin())
            ->get('/?date=2026-10-15')
            ->assertInertia(fn (Assert $page) => $page
                ->where('selectedDate', '2026-10-15')
                ->where('isToday', false)
                ->where('kpi.current.has_data', true)
                ->where('kpi.current.total_load', 42)
                ->where('latestCurrentDate', null));

        // Format tanggal tidak valid -> fallback aman ke hari ini
        $this->actingAs($this->admin())
            ->get('/?date=bukan-tanggal')
            ->assertInertia(fn (Assert $page) => $page
                ->where('selectedDate', '2026-11-21')
                ->where('isToday', true));
    }

    public function test_dashboard_latest_feeder_value_follows_chronological_interval_not_id(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-15 17:00', config('app.timezone')));
        $feeders = Feeder::where('is_active', true)->orderBy('sort_order')->get();

        // Interval sore diinput duluan, lalu interval pagi menyusul (id lebih besar)
        CurrentLogRecord::create([
            'recorded_date' => '2026-10-15',
            'shift' => 'sore',
            'time_interval' => '16.30',
            'feeder_id' => $feeders[0]->id,
            'current_value' => 88,
        ]);
        CurrentLogRecord::create([
            'recorded_date' => '2026-10-15',
            'shift' => 'pagi',
            'time_interval' => '08.30',
            'feeder_id' => $feeders[0]->id,
            'current_value' => 25,
        ]);
        // Penyulang lain punya data di 08.30 tetapi kosong di 16.30 -> tidak ikut dijumlahkan ke total 16.30
        CurrentLogRecord::create([
            'recorded_date' => '2026-10-15',
            'shift' => 'pagi',
            'time_interval' => '08.30',
            'feeder_id' => $feeders[1]->id,
            'current_value' => 15,
        ]);

        $this->actingAs($this->admin())
            ->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->where('kpi.current.latest_interval', '16.30')
                ->where('kpi.current.total_load', 88)
                ->where('feederStatusList.0.current_value', 88)
                ->where('feederStatusList.1.has_data', false));
    }

    public function test_dashboard_historical_date_checks_all_shifts_for_arus_progress(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-21 05:30', config('app.timezone')));
        $feederIds = Feeder::where('is_active', true)->pluck('id');

        foreach ($feederIds as $id) {
            CurrentLogRecord::create([
                'recorded_date' => '2026-10-01',
                'shift' => 'pagi',
                'time_interval' => '08.30',
                'feeder_id' => $id,
                'current_value' => 10,
            ]);
        }

        // Meskipun jam berjalan Shift Malam, tanggal historis menghitung seluruh 3 shift (48 interval)
        $this->actingAs($this->admin())
            ->get('/?date=2026-10-01')
            ->assertInertia(function (Assert $page) {
                $page->where('inputProgress.shift.label', 'Semua shift');
                $arus = $this->progressItem($page, 'arus');
                $this->assertSame([1, 48], [$arus['filled'], $arus['total']]);
                $this->assertSame('/monitoring-arus?date=2026-10-01', $arus['href']);
            });
    }

    public function test_dashboard_input_progress_includes_abc_engine_logs(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-12 09:00', config('app.timezone')));

        AbcControlPanelLog::create([
            'recorded_date' => '2026-10-12',
            'recorded_time' => '09:00',
            'kw' => 500,
        ]);
        AbcEngineAreaLog::create([
            'recorded_date' => '2026-10-12',
            'recorded_time' => '09:00',
            'rpm' => 1500,
        ]);

        $this->actingAs($this->admin())
            ->get('/')
            ->assertInertia(function (Assert $page) {
                $engine = $this->progressItem($page, 'engine');
                $this->assertSame(2, $engine['filled']);
            });
    }

    public function test_inertia_shares_server_now_ms(): void
    {
        $now = Carbon::parse('2026-11-21 10:00:00', config('app.timezone'));
        Carbon::setTestNow($now);

        $this->actingAs($this->admin())
            ->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->where('serverNowMs', $now->getTimestampMs()));
    }
}
