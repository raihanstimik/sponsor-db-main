<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Lab404\Impersonate\Services\ImpersonateManager;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserManagementEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create([
            'name' => 'Super Admin ICM',
            'email' => 'superadmin@icm.test',
        ]);
        $this->actingAs($this->admin);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    #[Test]
    public function halaman_manajemen_akun_merender_dengan_benar_dan_label_konsisten(): void
    {
        $response = $this->get(UserResource::getUrl('index'));
        $response->assertSuccessful();
        $response->assertSee('Manajemen Akun');

        Livewire::test(ListUsers::class)
            ->assertSuccessful()
            ->assertSee('Semua Akun')
            ->assertSee('Aktif')
            ->assertSee('Menunggu Persetujuan');
    }

    #[Test]
    public function tabel_menampilkan_badge_status_aktif_dan_menunggu_approval(): void
    {
        $karyawanAktif = User::factory()->karyawan()->create([
            'name' => 'Karyawan Aktif',
            'is_active' => true,
        ]);

        $karyawanPending = User::factory()->karyawan()->create([
            'name' => 'Calon Karyawan',
            'is_active' => false,
        ]);

        Livewire::test(ListUsers::class)
            ->assertSee('Karyawan Aktif')
            ->assertSee('Aktif')
            ->assertSee('Calon Karyawan')
            ->assertSee('Menunggu Approval');
    }

    #[Test]
    public function admin_dapat_menyetujui_pendaftaran_akun_karyawan(): void
    {
        $karyawanPending = User::factory()->karyawan()->create([
            'name' => 'Budi Baru',
            'is_active' => false,
        ]);

        Livewire::test(ListUsers::class)
            ->callTableAction('quick_approve', $karyawanPending)
            ->assertHasNoTableActionErrors();

        $this->assertTrue($karyawanPending->fresh()->is_active);
    }

    #[Test]
    public function policy_dan_tabel_mencegah_penghapusan_akun_sendiri(): void
    {
        // 1. Policy layer check
        $this->assertFalse(Gate::forUser($this->admin)->allows('delete', $this->admin));

        $otherUser = User::factory()->karyawan()->create();
        $this->assertTrue(Gate::forUser($this->admin)->allows('delete', $otherUser));

        // 2. Table Action: delete disembunyikan untuk akun sendiri
        Livewire::test(ListUsers::class)
            ->assertTableActionHidden('delete', $this->admin)
            ->assertTableActionVisible('delete', $otherUser);

        // 3. Bulk Action: akun sendiri dilewati saat hapus massal
        Livewire::test(ListUsers::class)
            ->callTableBulkAction('delete', [$this->admin, $otherUser])
            ->assertHasNoTableBulkActionErrors();

        // Admin tetap ada di database, user lain terhapus
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
        $this->assertDatabaseMissing('users', ['id' => $otherUser->id]);
    }

    #[Test]
    public function rute_keluar_impersonasi_mengembalikan_sesi_ke_admin(): void
    {
        $karyawan = User::factory()->karyawan()->create([
            'name' => 'Staff Lapangan',
            'is_active' => true,
        ]);

        $manager = app(ImpersonateManager::class);
        $manager->take($this->admin, $karyawan);

        $this->assertTrue($manager->isImpersonating());
        $this->assertSame($karyawan->id, auth()->id());

        // Panggil endpoint leave impersonation
        $response = $this->get(route('impersonate.leave'));
        $response->assertRedirect('/admin');

        $this->assertFalse($manager->isImpersonating());
        $this->assertSame($this->admin->id, auth()->id());
    }
}
