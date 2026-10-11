<?php

namespace Tests\Feature;

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
}
