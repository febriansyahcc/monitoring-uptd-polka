<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function operator(): User
    {
        return User::create([
            'name' => 'Operator Uji',
            'email' => 'op@pln.co.id',
            'nip' => '123',
            'role' => 'operator',
            'is_active' => true,
            'password' => Hash::make('password-lama'),
        ]);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/profil')->assertRedirect('/login');
        $this->put('/profil/password', [])->assertRedirect('/login');
    }

    public function test_user_tanpa_izin_khusus_bisa_membuka_profil(): void
    {
        $this->actingAs($this->operator())->get('/profil')->assertOk();
    }

    public function test_ubah_nama_tidak_mengubah_email_nip_atau_role(): void
    {
        $user = $this->operator();

        $this->actingAs($user)->put('/profil', [
            'name' => 'Nama Baru',
            'email' => 'hacker@x.com',
            'nip' => '999',
            'role' => 'admin',
        ])->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('op@pln.co.id', $user->email);
        $this->assertSame('123', $user->nip);
        $this->assertSame('operator', $user->role);
    }

    public function test_ganti_password_berhasil(): void
    {
        $user = $this->operator();

        $this->actingAs($user)->put('/profil/password', [
            'current_password' => 'password-lama',
            'password' => 'password-baru-1',
            'password_confirmation' => 'password-baru-1',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('password-baru-1', $user->fresh()->password));
    }

    public function test_ganti_password_gagal_bila_password_lama_salah(): void
    {
        $user = $this->operator();

        $this->actingAs($user)->put('/profil/password', [
            'current_password' => 'salah',
            'password' => 'password-baru-1',
            'password_confirmation' => 'password-baru-1',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password-lama', $user->fresh()->password));
    }

    public function test_ganti_password_gagal_bila_konfirmasi_beda_terlalu_pendek_atau_sama(): void
    {
        $user = $this->operator();

        $this->actingAs($user)->put('/profil/password', [
            'current_password' => 'password-lama',
            'password' => 'password-baru-1',
            'password_confirmation' => 'beda',
        ])->assertSessionHasErrors('password');

        $this->actingAs($user)->put('/profil/password', [
            'current_password' => 'password-lama',
            'password' => 'pendek',
            'password_confirmation' => 'pendek',
        ])->assertSessionHasErrors('password');

        $this->actingAs($user)->put('/profil/password', [
            'current_password' => 'password-lama',
            'password' => 'password-lama',
            'password_confirmation' => 'password-lama',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('password-lama', $user->fresh()->password));
    }
}
