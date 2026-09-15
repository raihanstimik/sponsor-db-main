<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Divisis\DivisiResource;
use App\Filament\Resources\Divisis\Pages\ListDivisis;
use App\Models\Divisi;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DivisiManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $karyawan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create([
            'name' => 'Super Admin ICM',
            'email' => 'admin.divisi@icm.test',
        ]);

        $this->karyawan = User::factory()->karyawan()->create([
            'name' => 'Karyawan Reguler',
            'email' => 'karyawan.divisi@icm.test',
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    #[Test]
    public function admin_dapat_mengakses_halaman_manajemen_divisi(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(DivisiResource::getUrl('index'));
        $response->assertSuccessful();
        $response->assertSee('Manajemen Divisi');

        Livewire::test(ListDivisis::class)
            ->assertSuccessful()
            ->assertSee('Tambah Divisi');

        Livewire::test(\App\Filament\Resources\Divisis\Widgets\DivisiStatsOverview::class)
            ->assertSuccessful()
            ->assertSee('Total Divisi Kerja')
            ->assertSee('Karyawan Terpetakan');
    }

    #[Test]
    public function karyawan_dilarang_mengakses_halaman_manajemen_divisi(): void
    {
        $this->actingAs($this->karyawan);

        $response = $this->get(DivisiResource::getUrl('index'));
        $response->assertForbidden();
    }

    #[Test]
    public function admin_dapat_membuat_divisi_baru(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ListDivisis::class)
            ->callAction('create', [
                'name' => 'Teknologi Informasi & Multimedia',
                'description' => 'Mengelola infrastruktur server, website, dan aplikasi sponsor ICM.',
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('divisis', [
            'name' => 'Teknologi Informasi & Multimedia',
            'slug' => 'teknologi-informasi-multimedia',
        ]);
    }

    #[Test]
    public function nama_divisi_wajib_diisi_dan_harus_unik(): void
    {
        $this->actingAs($this->admin);

        Divisi::factory()->create([
            'name' => 'Keuangan & Akuntansi',
        ]);

        Livewire::test(ListDivisis::class)
            ->callAction('create', [
                'name' => '',
            ])
            ->assertHasActionErrors(['name' => 'required']);

        Livewire::test(ListDivisis::class)
            ->callAction('create', [
                'name' => 'Keuangan & Akuntansi',
            ])
            ->assertHasActionErrors(['name' => 'unique']);
    }

    #[Test]
    public function admin_dapat_mengubah_data_divisi(): void
    {
        $this->actingAs($this->admin);

        $divisi = Divisi::factory()->create([
            'name' => 'Divisi Lama',
            'description' => 'Deskripsi lama',
        ]);

        Livewire::test(ListDivisis::class)
            ->callTableAction('edit', $divisi, [
                'name' => 'Divisi Baru Terupdate',
                'description' => 'Deskripsi yang telah diperbarui.',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('divisis', [
            'id' => $divisi->id,
            'name' => 'Divisi Baru Terupdate',
            'description' => 'Deskripsi yang telah diperbarui.',
        ]);
    }

    #[Test]
    public function divisi_yang_masih_memiliki_karyawan_tidak_dapat_dihapus(): void
    {
        $this->actingAs($this->admin);

        $divisi = Divisi::factory()->create([
            'name' => 'Divisi Berisi Karyawan',
        ]);

        User::factory()->karyawan()->create([
            'divisi_id' => $divisi->id,
        ]);

        // 1. Policy check: dilarang hapus
        $this->assertFalse(Gate::forUser($this->admin)->allows('delete', $divisi));

        // 2. Table action check: aksi delete disembunyikan
        Livewire::test(ListDivisis::class)
            ->assertTableActionHidden('delete', $divisi);

        $this->assertDatabaseHas('divisis', [
            'id' => $divisi->id,
        ]);
    }

    #[Test]
    public function divisi_default_umum_tidak_dapat_dihapus(): void
    {
        $this->actingAs($this->admin);

        $divisiUmum = Divisi::firstOrCreate(
            ['name' => 'Umum'],
            ['slug' => 'umum', 'description' => 'Divisi umum sistem']
        );

        // 1. Policy check: dilarang hapus
        $this->assertFalse(Gate::forUser($this->admin)->allows('delete', $divisiUmum));

        // 2. Table action check: aksi delete disembunyikan
        Livewire::test(ListDivisis::class)
            ->assertTableActionHidden('delete', $divisiUmum);

        $this->assertDatabaseHas('divisis', [
            'id' => $divisiUmum->id,
        ]);
    }

    #[Test]
    public function admin_dapat_menghapus_divisi_kosong(): void
    {
        $this->actingAs($this->admin);

        $divisiKosong = Divisi::factory()->create([
            'name' => 'Divisi Sementara',
            'slug' => 'divisi-sementara',
        ]);

        $this->assertTrue(Gate::forUser($this->admin)->allows('delete', $divisiKosong));

        Livewire::test(ListDivisis::class)
            ->callTableAction('delete', $divisiKosong)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('divisis', [
            'id' => $divisiKosong->id,
        ]);
    }

    #[Test]
    public function klik_baris_atau_lihat_detail_membuka_slide_over_tanpa_error(): void
    {
        $this->actingAs($this->admin);

        $divisi = Divisi::factory()->create([
            'name' => 'Operasional & Lapangan',
        ]);

        User::factory()->karyawan()->create([
            'name' => 'Rian Hidayat',
            'divisi_id' => $divisi->id,
        ]);

        Livewire::test(ListDivisis::class)
            ->mountTableAction('view', $divisi)
            ->assertHasNoTableActionErrors();

        $cardHtml = view('filament.infolists.entries.divisi-card', [
            'getRecord' => fn () => $divisi,
        ])->render();

        $this->assertStringContainsString(e('Operasional & Lapangan'), $cardHtml);
        $this->assertStringContainsString('Rian Hidayat', $cardHtml);
        $this->assertStringContainsString('Daftar Karyawan Terdaftar', $cardHtml);
    }
}
