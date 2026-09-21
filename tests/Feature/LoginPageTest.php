<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\CustomLogin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginPageTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function halaman_login_tampil_ala_stitch_untuk_tamu(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Manajemen data Sponsor ICM', false)
            ->assertSee('Kata Sandi Akun', false)
            ->assertSee('Login', false)
            ->assertSee('daftar Akun Baru', false)
            ->assertSee('Indonesia Congress Management', false);
    }

    #[Test]
    public function login_berhasil_dengan_kredensial_benar(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::test(CustomLogin::class)
            ->fillForm([
                'email' => $admin->email,
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($admin);
    }

    #[Test]
    public function login_gagal_dengan_password_salah(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::test(CustomLogin::class)
            ->fillForm([
                'email' => $admin->email,
                'password' => 'salah-password',
            ])
            ->call('authenticate')
            ->assertHasErrors(['data.email']);
            ->assertHasErrors(['data.password'])
            ->assertSee('Kata sandi yang Anda masukkan salah');

        $this->assertGuest();
    }

    #[Test]
    public function login_gagal_dengan_email_belum_terdaftar(): void
    {
        Livewire::test(CustomLogin::class)
            ->fillForm([
                'email' => 'tidak-terdaftar@icm.co.id',
                'password' => 'password123',
            ])
            ->call('authenticate')
            ->assertHasErrors(['data.email'])
            ->assertSee('Alamat email ini belum terdaftar di sistem kami');

        $this->assertGuest();
    }

    #[Test]
    public function login_gagal_dengan_akun_nonaktif(): void
    {
        $user = User::factory()->create([
            'is_active' => false,
        ]);

        Livewire::test(CustomLogin::class)
            ->fillForm([
                'email' => $user->email,
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertHasErrors(['data.email'])
            ->assertSee('menunggu persetujuan Admin atau berstatus nonaktif');

        $this->assertGuest();
    }
}
