<?php

namespace Tests\Feature;

use App\Models\CurrentLogRecord;
use App\Models\Feeder;
use App\Models\FuelStock;
use App\Models\OperationalDisturbance;
use App\Models\User;
use Database\Seeders\FeederSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bukti verifikasi Fase 3 UX_BUG_REPORT.md (migrasi per halaman).
 */
class Phase3BugFixTest extends TestCase
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

    // ---------- PAGE-01: Monitoring Arus (BUG-01, BUG-14) ----------

    private function arusPayload(array $overrides = []): array
    {
        $feeder = Feeder::where('is_active', true)->orderBy('sort_order')->first();

        return array_merge([
            'date' => '2026-09-24',
            'shift' => 'pagi',
            'time_interval' => '08.30',
            'values' => [$feeder->id => 120.5],
        ], $overrides);
    }

    public function test_arus_operator_defaults_to_account_name(): void
    {
        $operator = $this->user('operator1@pln.co.id');

        $this->actingAs($operator)
            ->post('/monitoring-arus', $this->arusPayload())
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSame($operator->name, CurrentLogRecord::first()->operator_name);
    }

    public function test_arus_explicit_operator_name_is_kept(): void
    {
        $this->actingAs($this->user('operator1@pln.co.id'))
            ->post('/monitoring-arus', $this->arusPayload(['operator_name' => 'Budi']));

        $this->assertSame('Budi', CurrentLogRecord::first()->operator_name);
    }

    public function test_arus_clearing_a_value_deletes_the_record(): void
    {
        $user = $this->user('operator1@pln.co.id');
        $payload = $this->arusPayload();
        $feederId = array_key_first($payload['values']);

        $this->actingAs($user)->post('/monitoring-arus', $payload);
        $this->assertSame(1, CurrentLogRecord::count());

        $this->actingAs($user)->post('/monitoring-arus', $this->arusPayload(['values' => [$feederId => null]]));
        $this->assertSame(0, CurrentLogRecord::count());
    }

    public function test_arus_interval_must_belong_to_shift(): void
    {
        $this->actingAs($this->user('operator1@pln.co.id'))
            ->post('/monitoring-arus', $this->arusPayload(['time_interval' => '16.30']))
            ->assertSessionHasErrors('time_interval');

        $this->assertSame(0, CurrentLogRecord::count());
    }

    public function test_arus_batch_saves_multiple_rows(): void
    {
        $feederId = Feeder::where('is_active', true)->value('id');

        $this->actingAs($this->user('operator1@pln.co.id'))
            ->post('/monitoring-arus/batch', [
                'date' => '2026-09-24',
                'shift' => 'pagi',
                'rows' => [
                    ['time_interval' => '08.30', 'values' => [$feederId => 10]],
                    ['time_interval' => '09.00', 'values' => [$feederId => 20], 'operator_name' => 'Budi'],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', '2 baris interval berhasil disimpan.');

        $this->assertEqualsCanonicalizing(['08.30', '09.00'], CurrentLogRecord::pluck('time_interval')->all());
    }

    public function test_arus_batch_is_atomic_and_reports_errors_per_row(): void
    {
        $feederId = Feeder::where('is_active', true)->value('id');

        $this->actingAs($this->user('operator1@pln.co.id'))
            ->post('/monitoring-arus/batch', [
                'date' => '2026-09-24',
                'shift' => 'pagi',
                'rows' => [
                    ['time_interval' => '08.30', 'values' => [$feederId => 10]],
                    ['time_interval' => '09.00', 'values' => [$feederId => -1]],
                ],
            ])
            ->assertSessionHasErrors("rows.1.values.{$feederId}");

        $this->assertSame(0, CurrentLogRecord::count(), 'baris valid tidak ikut tersimpan bila ada baris gagal');
    }

    public function test_arus_batch_requires_input_permission(): void
    {
        $this->actingAs($this->user('manager@pln.co.id'))
            ->post('/monitoring-arus/batch', ['date' => '2026-09-24', 'shift' => 'pagi', 'rows' => []])
            ->assertForbidden();
    }

    // ---------- PAGE-04: Gangguan ----------

    public function test_disturbance_without_optional_fields_is_saved_with_account_name(): void
    {
        $operator = $this->user('operator1@pln.co.id');

        // Sebelumnya 500 (Undefined array key "operator_name") bila field opsional tidak dikirim
        $this->actingAs($operator)
            ->post('/monitoring-gangguan', [
                'event_date' => '2026-09-24',
                'event_time' => '10:00',
                'disturbance_type' => 'Trip Feeder',
                'status' => 'Investigasi',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSame($operator->name, OperationalDisturbance::first()->operator_name);
    }

    // ---------- PAGE-05: BBM ----------

    public function test_fuel_empty_amounts_are_saved_as_zero_with_account_name(): void
    {
        $operator = $this->user('operator1@pln.co.id');

        // Form kini diawali kosong (null) + placeholder, bukan angka 0
        $this->actingAs($operator)
            ->post('/monitoring-bbm', [
                'recorded_date' => '2026-09-24',
                'daily_consumption' => null,
                'main_tank' => 5000,
                'death_stock' => null,
                'unloading' => null,
                'estimated_daily_consumption' => 100,
                'operator_name' => '',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $log = FuelStock::first();
        $this->assertEquals(0, $log->daily_consumption);
        $this->assertEquals(5000, $log->netto_stock);
        $this->assertSame($operator->name, $log->operator_name);
    }

    // ---------- PAGE-06: User Management ----------

    public function test_new_user_requires_password(): void
    {
        // Form tidak lagi mengisi password "password" diam-diam; tanpa password -> error
        $this->actingAs($this->user('admin@pln.co.id'))
            ->post('/users', [
                'name' => 'Pegawai Baru',
                'email' => 'baru@pln.co.id',
                'role' => 'operator',
                'password' => '',
            ])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'baru@pln.co.id']);
    }

    // ---------- PAGE-07: Dashboard ----------

    public function test_dashboard_feeder_status_marks_feeders_without_data(): void
    {
        $feeders = Feeder::where('is_active', true)->orderBy('sort_order')->get();

        CurrentLogRecord::create([
            'recorded_date' => now()->toDateString(),
            'shift' => 'pagi',
            'time_interval' => '08.30',
            'feeder_id' => $feeders[0]->id,
            'current_value' => 50,
        ]);

        $this->actingAs($this->user('admin@pln.co.id'))
            ->get('/')
            ->assertInertia(fn ($page) => $page
                ->where('feederStatusList.0.has_data', true)
                ->where('feederStatusList.1.has_data', false)
                ->where('kpi.disturbance.investigating', 0));
    }

    public function test_dashboard_date_is_formatted_in_indonesian(): void
    {
        $expected = now()->locale('id')->translatedFormat('l, d F Y');

        $this->actingAs($this->user('admin@pln.co.id'))
            ->get('/')
            ->assertInertia(fn ($page) => $page->where('todayDateFormatted', $expected));

        $this->assertMatchesRegularExpression('/^(Senin|Selasa|Rabu|Kamis|Jumat|Sabtu|Minggu),/', $expected);
    }

    public function test_feeder_count_is_shared_for_sidebar_badge(): void
    {
        $this->actingAs($this->user('admin@pln.co.id'))
            ->get('/monitoring-arus')
            ->assertInertia(fn ($page) => $page->where('feederCount', Feeder::where('is_active', true)->count()));
    }
}
