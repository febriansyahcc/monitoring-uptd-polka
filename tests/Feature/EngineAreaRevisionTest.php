<?php

namespace Tests\Feature;

use App\Models\EngineAreaLog;
use App\Models\User;
use Database\Seeders\FeederSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Revisi Engine Area: kolom Speed Engine dan 6 pilihan Pressure baru.
 */
class EngineAreaRevisionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed([FeederSeeder::class, UserSeeder::class]);
    }

    public function test_page_lists_speed_engine_first_and_keeps_pressure_choices(): void
    {
        $admin = User::where('email', 'admin@pln.co.id')->firstOrFail();

        $this->actingAs($admin)->get('/monitoring-operasi-engine?tab=engine_area')
            ->assertInertia(fn ($page) => $page
                ->where('engineAreaFixedFields.0.key', 'speed_engine')
                ->where('engineAreaFixedFields.1.key', 'turbo_speed_r')
                ->where('engineAreaChoiceGroups.1.key', 'pressure')
                ->has('engineAreaChoiceGroups.1.options', 6)
                ->where('engineAreaChoiceGroups.1.options.0.key', 'press_oil')
                ->where('engineAreaChoiceGroups.1.options.5.key', 'press_air_inlet_restriction')
                ->where('engineAreaChoiceGroups.1.options.5.label', 'Air Inlet Restriction RH (IN H2O)'));
    }

    public function test_speed_engine_is_saved(): void
    {
        $admin = User::where('email', 'admin@pln.co.id')->firstOrFail();

        $this->actingAs($admin)->post('/monitoring-operasi-engine/engine-area', [
            'recorded_date' => now()->format('Y-m-d'),
            'recorded_time' => '08:30',
            'engine' => 'CAT_4',
            'speed_engine' => 1500,
            'press_oil' => 45.5,
            'press_air_inlet_restriction' => 12,
        ])->assertSessionHasNoErrors();

        $log = EngineAreaLog::sole();
        $this->assertEquals(1500, $log->speed_engine);
        $this->assertEquals(45.5, $log->press_oil);
        $this->assertEquals(12, $log->press_air_inlet_restriction);
    }
}
