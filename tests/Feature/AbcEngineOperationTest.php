<?php

namespace Tests\Feature;

use App\Models\AbcControlPanelLog;
use App\Models\AbcEngineAreaLog;
use App\Models\EngineControlPanelLog;
use App\Models\User;
use Database\Seeders\FeederSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Operasi Engine: pilihan CAT #4 / ABC #6; ABC punya Control Panel & Engine Area dengan kolom sendiri (PA 2026 revisi).
 */
class AbcEngineOperationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private string $date;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed([FeederSeeder::class, UserSeeder::class]);
        $this->admin = User::where('email', 'admin@pln.co.id')->firstOrFail();
        $this->date = now()->format('Y-m-d');
    }

    public function test_default_page_is_cat_and_lists_only_cat_rows(): void
    {
        EngineControlPanelLog::create(['recorded_date' => $this->date, 'recorded_time' => '08:30', 'engine' => 'CAT_4', 'kw' => 100]);
        EngineControlPanelLog::create(['recorded_date' => $this->date, 'recorded_time' => '08:30', 'engine' => 'ABC_6', 'kw' => 200]);

        $this->actingAs($this->admin)->get('/monitoring-operasi-engine')
            ->assertInertia(fn ($page) => $page
                ->where('engineType', 'cat')
                ->has('engineTypes', 2)
                ->has('engines', 1)
                ->has('controlPanelLogs', 1)
                ->where('controlPanelLogs.0.kw', 100)
                ->missing('abcControlPanelLogs'));
    }

    public function test_abc_page_exposes_its_own_columns(): void
    {
        $this->actingAs($this->admin)->get('/monitoring-operasi-engine?engine=abc')
            ->assertInertia(fn ($page) => $page
                ->where('engineType', 'abc')
                ->missing('controlPanelGroups')
                ->has('abcControlPanelGroups', 11)
                ->where('abcControlPanelGroups.0.label', 'Temperature Stator')
                ->where('abcControlPanelGroups.1.label', 'CD Bearing')
                ->where('abcControlPanelGroups.7.label', 'KVA')
                ->where('abcControlPanelGroups.10.label', 'kWh Produksi Pembanding')
                ->has('abcEngineAreaGroups', 33)
                ->where('abcEngineAreaGroups.0.section', 'Oil Cooler')
                ->where('abcEngineAreaGroups.2.section', 'Fuel Rack')
                ->where('abcEngineAreaGroups.2.label', '1'));
    }

    public function test_abc_column_counts_match_the_document(): void
    {
        $this->assertCount(17, AbcControlPanelLog::fieldKeys());
        $this->assertCount(59, AbcEngineAreaLog::fieldKeys());
        $this->assertSame(59, count(array_unique(AbcEngineAreaLog::fieldKeys())));
    }

    public function test_abc_control_panel_is_saved_and_updated_per_date_and_time(): void
    {
        $payload = [
            'recorded_date' => $this->date,
            'recorded_time' => '08:30',
            'stator_r' => 80, 'cd_bearing' => 55, 'amp_r' => 120, 'volt_r' => 400,
            'kw' => 900, 'cos_q' => 0.85, 'freq_hz' => 50, 'kva' => 1000, 'kvar' => 400,
            'kwh_produksi_utama' => 1000.5, 'kwh_produksi_pembanding' => 998.25,
        ];

        $this->actingAs($this->admin)->post('/monitoring-operasi-engine/abc/control-panel', $payload)->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->post('/monitoring-operasi-engine/abc/control-panel', array_merge($payload, ['kw' => 950]))->assertSessionHasNoErrors();

        $log = AbcControlPanelLog::sole();
        $this->assertEquals(950, $log->kw);
        $this->assertEquals(1000, $log->kva);
        $this->assertEquals(998.25, $log->kwh_produksi_pembanding);
        $this->assertSame($this->admin->name, $log->operator_name);
    }

    public function test_abc_engine_area_is_saved(): void
    {
        $this->actingAs($this->admin)->post('/monitoring-operasi-engine/abc/engine-area', [
            'recorded_date' => $this->date,
            'recorded_time' => '09:00',
            'oil_cooler_oil_temp_in' => 70,
            'fuel_rack_3_b' => 12.5,
            'rpm' => 1500,
            'water_temp_radiator_cw_outlet' => 65,
            'cw_cylinder_6_a' => 60,
            'exhaust_gas_after_tc_b' => 380,
            'flowmeter_hsd_out' => 1234.5,
        ])->assertSessionHasNoErrors();

        $log = AbcEngineAreaLog::sole();
        $this->assertEquals(12.5, $log->fuel_rack_3_b);
        $this->assertEquals(1500, $log->rpm);
        $this->assertEquals(380, $log->exhaust_gas_after_tc_b);
        $this->assertEquals(1234.5, $log->flowmeter_hsd_out);

        $this->actingAs($this->admin)->get('/monitoring-operasi-engine?engine=abc&tab=engine_area')
            ->assertInertia(fn ($page) => $page->has('abcEngineAreaLogs', 1)->where('abcEngineAreaLogs.0.rpm', 1500));
    }

    public function test_abc_rows_can_be_deleted_and_validation_applies(): void
    {
        $this->actingAs($this->admin)->post('/monitoring-operasi-engine/abc/control-panel', [
            'recorded_date' => $this->date, 'recorded_time' => '08:30', 'kw' => 100,
        ]);
        $id = AbcControlPanelLog::sole()->id;

        $this->actingAs($this->admin)->post('/monitoring-operasi-engine/abc/control-panel', [
            'recorded_date' => $this->date, 'recorded_time' => '99:99', 'cos_q' => 5,
        ])->assertSessionHasErrors(['recorded_time', 'cos_q']);

        $this->actingAs($this->admin)->delete("/monitoring-operasi-engine/abc/control-panel/{$id}");
        $this->assertSame(0, AbcControlPanelLog::count());
    }

    public function test_abc_input_requires_permission(): void
    {
        $manager = User::where('email', 'manager@pln.co.id')->first();
        if (!$manager) {
            $this->markTestSkipped('Akun manager tidak ada di seeder.');
        }

        $this->actingAs($manager)->post('/monitoring-operasi-engine/abc/control-panel', [
            'recorded_date' => $this->date, 'recorded_time' => '08:30', 'kw' => 1,
        ])->assertForbidden();
    }
}
