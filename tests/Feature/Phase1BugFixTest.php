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
 * Bukti verifikasi Fase 1 UX_BUG_REPORT.md (BUG-05, BUG-08 backend).
 */
class Phase1BugFixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed([FeederSeeder::class, UserSeeder::class]);
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    // ---------- BUG-05: PBAC di server ----------

    public function test_operator_cannot_open_user_management(): void
    {
        $this->actingAs($this->user('operator1@pln.co.id'))
            ->get('/users')
            ->assertForbidden();
    }

    public function test_operator_cannot_create_user_via_direct_post(): void
    {
        $this->actingAs($this->user('operator1@pln.co.id'))
            ->post('/users', [
                'name' => 'Admin Palsu',
                'email' => 'palsu@pln.co.id',
                'role' => 'admin',
                'password' => 'rahasia123',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'palsu@pln.co.id']);
    }

    public function test_manager_can_view_but_cannot_write(): void
    {
        $manager = $this->user('manager@pln.co.id');

        $this->actingAs($manager)->get('/monitoring-kwh')->assertOk();
        $this->actingAs($manager)->get('/monitoring-bbm')->assertOk();

        $this->actingAs($manager)->post('/monitoring-kwh/engine', [])->assertForbidden();
        $this->actingAs($manager)->post('/monitoring-bbm', [])->assertForbidden();
        $this->actingAs($manager)->post('/monitoring-gangguan', [])->assertForbidden();
        $this->actingAs($manager)->post('/monitoring-arus', [])->assertForbidden();
        $this->actingAs($manager)->post('/monitoring-operasi-engine/control-panel', [])->assertForbidden();
        $this->actingAs($manager)->delete('/monitoring-bbm/1')->assertForbidden();
    }

    public function test_tl_pemeliharaan_without_kwh_view_is_forbidden(): void
    {
        $this->actingAs($this->user('tl.pemeliharaan@pln.co.id'))
            ->get('/monitoring-kwh')
            ->assertForbidden();
    }

    public function test_admin_can_access_every_menu(): void
    {
        $admin = $this->user('admin@pln.co.id');

        foreach (['/', '/monitoring-arus', '/monitoring-kwh', '/monitoring-operasi-engine', '/monitoring-gangguan', '/monitoring-bbm', '/users'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        $admin = $this->user('admin@pln.co.id');

        $this->actingAs($admin)
            ->post("/users/{$admin->id}/toggle-status")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertTrue((bool) $admin->fresh()->is_active);
    }

    public function test_admin_can_deactivate_other_account(): void
    {
        $operator = $this->user('operator1@pln.co.id');

        $this->actingAs($this->user('admin@pln.co.id'))
            ->post("/users/{$operator->id}/toggle-status")
            ->assertSessionHas('success');

        $this->assertFalse((bool) $operator->fresh()->is_active);
    }

    public function test_admin_cannot_change_own_role(): void
    {
        $admin = $this->user('admin@pln.co.id');

        $this->actingAs($admin)
            ->put("/users/{$admin->id}", [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'operator',
            ])
            ->assertSessionHasErrors('role');

        $this->assertSame('admin', $admin->fresh()->role);
    }

    // ---------- BUG-03: validasi gagal mengembalikan error (bukan sukses diam-diam) ----------

    public function test_duplicate_email_returns_validation_error(): void
    {
        $this->actingAs($this->user('admin@pln.co.id'))
            ->post('/users', [
                'name' => 'Duplikat',
                'email' => 'operator1@pln.co.id',
                'role' => 'operator',
                'password' => 'rahasia123',
            ])
            ->assertSessionHasErrors('email');
    }

    // ---------- BUG-04: simpan BBM berbasis id ----------

    private function fuelPayload(array $overrides = []): array
    {
        return array_merge([
            'recorded_date' => '2026-09-10',
            'daily_consumption' => 100,
            'main_tank' => 5000,
            'death_stock' => 200,
            'unloading' => 0,
            'estimated_daily_consumption' => 100,
        ], $overrides);
    }

    public function test_editing_fuel_with_changed_date_does_not_duplicate(): void
    {
        $log = FuelStock::create($this->fuelPayload());

        $this->actingAs($this->user('operator1@pln.co.id'))
            ->post('/monitoring-bbm', $this->fuelPayload([
                'id' => $log->id,
                'recorded_date' => '2026-09-11',
                'main_tank' => 4000,
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, FuelStock::count());
        $fresh = $log->fresh();
        $this->assertSame('2026-09-10', Carbon::parse($fresh->recorded_date)->toDateString());
        $this->assertEquals(4000, $fresh->main_tank);
    }

    public function test_adding_fuel_for_existing_date_is_rejected(): void
    {
        FuelStock::create($this->fuelPayload());

        $this->actingAs($this->user('operator1@pln.co.id'))
            ->post('/monitoring-bbm', $this->fuelPayload(['main_tank' => 1]))
            ->assertSessionHasErrors('recorded_date');

        $this->assertSame(1, FuelStock::count());
        $this->assertEquals(5000, FuelStock::first()->main_tank);
    }

    public function test_adding_fuel_for_new_date_creates_record(): void
    {
        $r = $this->actingAs($this->user('operator1@pln.co.id'))
            ->post('/monitoring-bbm', $this->fuelPayload());
        fwrite(STDERR, $r->exception ? $r->exception->getMessage() : 'no-ex');
        $r->assertSessionHasNoErrors();

        $this->assertSame(1, FuelStock::count());
    }

    // ---------- BUG-08 (backend): KPI Dashboard ----------

    public function test_fuel_status_without_data_is_not_safe(): void
    {
        $this->actingAs($this->user('admin@pln.co.id'))
            ->get('/')
            ->assertInertia(fn (Assert $page) => $page->where('kpi.fuel.status', 'Belum Ada Data'));
    }

    public function test_fuel_status_with_zero_days_of_supply_is_critical(): void
    {
        FuelStock::create([
            'recorded_date' => Carbon::today()->toDateString(),
            'daily_consumption' => 0,
            'main_tank' => 0,
            'death_stock' => 0,
            'unloading' => 0,
            'estimated_daily_consumption' => 0,
        ]);

        $this->assertEquals(0, FuelStock::first()->days_of_supply);

        $this->actingAs($this->user('admin@pln.co.id'))
            ->get('/')
            ->assertInertia(fn (Assert $page) => $page->where('kpi.fuel.status', 'Kritis'));
    }

    public function test_current_chart_intervals_follow_shift_order(): void
    {
        $feeder = Feeder::first();
        $today = Carbon::today()->toDateString();

        // Disimpan tidak berurutan: 09.00 lebih dulu, lalu 08.30, lalu 16.30
        foreach (['09.00', '08.30', '16.30'] as $interval) {
            CurrentLogRecord::create([
                'recorded_date' => $today,
                'shift' => 'pagi',
                'time_interval' => $interval,
                'feeder_id' => $feeder->id,
                'current_value' => 10,
            ]);
        }

        $this->actingAs($this->user('admin@pln.co.id'))
            ->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->where('currentChartData.0.interval', '08.30')
                ->where('currentChartData.1.interval', '09.00')
                ->where('currentChartData.2.interval', '16.30')
                ->where('kpi.current.latest_interval', '16.30'));
    }

    public function test_recent_disturbances_include_previous_months(): void
    {
        OperationalDisturbance::create([
            'event_date' => Carbon::today()->subMonthsNoOverflow(2)->toDateString(),
            'event_time' => '10:00',
            'disturbance_type' => 'Trip Feeder',
            'status' => 'Selesai',
        ]);

        $this->actingAs($this->user('admin@pln.co.id'))
            ->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->has('recentDisturbances', 1)
                ->where('kpi.disturbance.total_month', 0));
    }
}
