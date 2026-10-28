<?php

namespace Tests\Feature;

use App\Models\EngineControlPanelLog;
use App\Models\User;
use Database\Seeders\FeederSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Control Panel: kolom kedua "kWh Produksi Utama" diganti menjadi "kWh Produksi Pembanding".
 */
class ControlPanelKwhTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed([FeederSeeder::class, UserSeeder::class]);
        $this->admin = User::where('email', 'admin@pln.co.id')->firstOrFail();
    }

    public function test_control_panel_has_separate_utama_and_pembanding_groups(): void
    {
        $this->actingAs($this->admin)->get('/monitoring-operasi-engine')
            ->assertInertia(fn ($page) => $page
                ->where('controlPanelGroups.8.label', 'kWh Produksi Utama')
                ->has('controlPanelGroups.8.fields', 1)
                ->where('controlPanelGroups.8.fields.0.key', 'kwh_produksi_utama')
                ->where('controlPanelGroups.9.label', 'kWh Produksi Pembanding')
                ->has('controlPanelGroups.9.fields', 1)
                ->where('controlPanelGroups.9.fields.0.key', 'kwh_produksi_pembanding'));
    }

    public function test_both_kwh_values_are_saved(): void
    {
        $this->actingAs($this->admin)->post('/monitoring-operasi-engine/control-panel', [
            'recorded_date' => now()->format('Y-m-d'),
            'recorded_time' => '08:30',
            'engine' => 'CAT_4',
            'kwh_produksi_utama' => 1000.5,
            'kwh_produksi_pembanding' => 998.25,
        ])->assertSessionHasNoErrors();

        $log = EngineControlPanelLog::sole();
        $this->assertEquals(1000.5, $log->kwh_produksi_utama);
        $this->assertEquals(998.25, $log->kwh_produksi_pembanding);
    }
}
