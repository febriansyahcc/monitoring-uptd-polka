<?php

namespace Tests\Feature;

use App\Models\KwhFeederLog;
use App\Models\User;
use Database\Seeders\FeederSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Monitoring kWh Produksi dipisah: ENGINE, PENYULANG (stand akhir) dan PS TOTAL (kWh PS + Digital).
 */
class KwhSplitTest extends TestCase
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

    private function stand(array $overrides = []): array
    {
        return array_merge(['recorded_date' => $this->date, 'feeder' => 'GALALA_1', 'pm800_ex' => 1681.76], $overrides);
    }

    private function ps(array $overrides = []): array
    {
        return array_merge(['recorded_date' => $this->date, 'feeder' => 'GALALA_1', 'kwh_ps' => 10, 'kwh_digital' => 5], $overrides);
    }

    public function test_stand_and_ps_forms_save_only_their_own_part(): void
    {
        $this->actingAs($this->admin)->post('/monitoring-kwh/penyulang', $this->stand())->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->post('/monitoring-kwh/ps-total', $this->ps())->assertSessionHasNoErrors();

        $log = KwhFeederLog::sole();
        $this->assertEquals(1681.76, $log->pm800_ex);
        $this->assertEquals(10, $log->kwh_ps);
        $this->assertEquals(15, $log->ps_total);

        // Simpan ulang stand tidak menghapus PS, dan sebaliknya
        $this->actingAs($this->admin)->post('/monitoring-kwh/penyulang', $this->stand(['pm800_ex' => 2000]));
        $this->actingAs($this->admin)->post('/monitoring-kwh/ps-total', $this->ps(['kwh_ps' => 20, 'kwh_digital' => null]));

        $log = KwhFeederLog::sole();
        $this->assertEquals(2000, $log->pm800_ex);
        $this->assertEquals(20, $log->kwh_ps);
        $this->assertEquals(20, $log->ps_total);
    }

    public function test_each_tab_lists_only_rows_with_its_own_part(): void
    {
        $this->actingAs($this->admin)->post('/monitoring-kwh/penyulang', $this->stand());
        $this->actingAs($this->admin)->post('/monitoring-kwh/ps-total', $this->ps(['feeder' => 'HITU']));

        $this->actingAs($this->admin)->get('/monitoring-kwh')
            ->assertInertia(fn ($page) => $page
                ->has('feederLogs', 1)->where('feederLogs.0.feeder', 'GALALA_1')
                ->has('psLogs', 1)->where('psLogs.0.feeder', 'HITU')->where('psLogs.0.ps_total', 15)
                ->where('summary.feederDays', 1)->where('summary.psDays', 1)->where('summary.totalPsTotal', 15));
    }

    public function test_each_form_requires_something_to_save(): void
    {
        $this->actingAs($this->admin)->post('/monitoring-kwh/penyulang', $this->stand(['pm800_ex' => null]))
            ->assertSessionHasErrors('pm800_ex');
        $this->actingAs($this->admin)->post('/monitoring-kwh/ps-total', $this->ps(['kwh_ps' => null, 'kwh_digital' => null]))
            ->assertSessionHasErrors('kwh_ps');

        $this->assertSame(0, KwhFeederLog::count());
    }

    public function test_deleting_one_part_keeps_the_other_and_removes_empty_rows(): void
    {
        $this->actingAs($this->admin)->post('/monitoring-kwh/penyulang', $this->stand());
        $this->actingAs($this->admin)->post('/monitoring-kwh/ps-total', $this->ps());
        $id = KwhFeederLog::sole()->id;

        $this->actingAs($this->admin)->delete("/monitoring-kwh/ps-total/{$id}");
        $log = KwhFeederLog::sole();
        $this->assertNull($log->ps_total);
        $this->assertEquals(1681.76, $log->pm800_ex);

        $this->actingAs($this->admin)->delete("/monitoring-kwh/penyulang/{$id}");
        $this->assertSame(0, KwhFeederLog::count());
    }
}
