<?php

namespace Tests\Feature;

use App\Filament\Resources\KategoriKegiatans\KategoriKegiatanResource;
use App\Filament\Resources\KategoriKegiatans\Pages\CreateKategoriKegiatan;
use App\Filament\Resources\KategoriKegiatans\Pages\ListKategoriKegiatans;
use App\Filament\Resources\Kegiatans\Pages\ListKegiatans;
use App\Filament\Resources\Kontaks\Pages\CreateKontak;
use App\Filament\Resources\Kontaks\Pages\ListKontaks;
use App\Filament\Resources\Perusahaans\Pages\CreatePerusahaan;
use App\Filament\Resources\Perusahaans\Pages\ListPerusahaans;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\KategoriKegiatan;
use App\Models\Kegiatan;
use App\Models\Kontak;
use App\Models\Perusahaan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ResourceCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_perusahaan_bisa_dibuat_admin(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(CreatePerusahaan::class)
            ->fillForm([
                'nama_standar' => 'PT Beta Farmasi',
                'industri' => 'Farmasi',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('perusahaans', ['nama_standar' => 'PT Beta Farmasi']);
    }

    public function test_nama_standar_perusahaan_harus_unik(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        Perusahaan::create(['nama_standar' => 'PT Beta Farmasi']);

        Livewire::test(CreatePerusahaan::class)
            ->fillForm(['nama_standar' => 'PT Beta Farmasi'])
            ->call('create')
            ->assertHasFormErrors(['nama_standar']);
    }

    public function test_kontak_tersimpan_dengan_nomor_normalisasi_628(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $perusahaan = Perusahaan::factory()->create();

        Livewire::test(CreateKontak::class)
            ->fillForm([
                'perusahaan_id' => $perusahaan->id,
                'nama' => 'Budi Santoso',
                'no_telepon' => '0811-1465-133',
                'status_verifikasi' => 'terverifikasi',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        /** @var Kontak $kontak */
        $kontak = Kontak::where('nama', 'Budi Santoso')->first();

        $this->assertSame('628111465133', $kontak->no_telepon);
        $this->assertTrue($kontak->status_format_valid);
        $this->assertSame(auth()->id(), $kontak->updated_by);
    }

    public function test_kategori_kegiatan_akses_karyawan_terlarang(): void
    {
        $this->actingAs(User::factory()->karyawan()->create());

        $this->assertFalse(KategoriKegiatanResource::canCreate());
        $this->assertTrue(KategoriKegiatanResource::canViewAny());

        Livewire::test(ListKategoriKegiatans::class)->assertSuccessful();
        Livewire::test(CreateKategoriKegiatan::class)->assertForbidden();
    }

    public function test_kategori_kegiatan_admin_bisa_buat(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(CreateKategoriKegiatan::class)
            ->fillForm([
                'nama_kategori' => 'Dokter Spesialis Jantung',
                'deskripsi' => 'Kongres jantung',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('kategori_kegiatans', ['nama_kategori' => 'Dokter Spesialis Jantung']);
    }

    public function test_kontak_tersimpan_dengan_kegiatan_dan_kategori(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $perusahaan = Perusahaan::factory()->create();
        $kegiatan = Kegiatan::factory()->create();

        Livewire::test(CreateKontak::class)
            ->fillForm([
                'perusahaan_id' => $perusahaan->id,
                'kegiatan_id' => $kegiatan->id,
                'nama' => 'Siti Aminah',
                'no_telepon' => '6281291018454',
                'status_verifikasi' => 'terverifikasi',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        /** @var Kontak $kontak */
        $kontak = Kontak::where('nama', 'Siti Aminah')->first();

        $this->assertSame($kegiatan->id, $kontak->kegiatan_id);
        $this->assertSame($kegiatan->kategori_kegiatan_id, $kontak->kategori_kegiatan_id);
    }

    public function test_kegiatan_bisa_dilihat_karyawan(): void
    {
        $this->actingAs(User::factory()->karyawan()->create());

        Livewire::test(ListKegiatans::class)->assertSuccessful();
    }

    public function test_perusahaan_infolist_dan_list_page_dapat_dirender(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $perusahaan = Perusahaan::factory()->create([
            'nama_standar' => 'PT Bio Farma Persero',
            'industri' => 'Farmasi & Suplemen',
            'alamat' => 'Jl. Pasteur No. 28, Bandung',
            'website' => 'https://biofarma.co.id',
            'catatan' => 'Mitra strategis vaksinasi',
        ]);

        Kontak::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'nama' => 'Dr. Rina Gunawan',
            'no_telepon' => '0811-1465-133',
            'email' => 'rina@biofarma.co.id',
            'status_verifikasi' => 'terverifikasi',
        ]);

        Livewire::test(ListPerusahaans::class)
            ->assertSuccessful()
            ->mountTableAction('view', $perusahaan)
            ->assertHasNoTableActionErrors();

        $modalHtml = view('filament.modals.perusahaan-pic-modal', ['record' => $perusahaan])->render();
        $this->assertStringContainsString('wa.me/628111465133', $modalHtml);
        $this->assertStringContainsString('Dr. Rina Gunawan', $modalHtml);
    }

    public function test_semua_tabel_klik_baris_membuka_detail_bukan_edit(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $perusahaan = Perusahaan::factory()->create();
        $kategori = KategoriKegiatan::factory()->create();
        $kegiatan = Kegiatan::factory()->create(['kategori_kegiatan_id' => $kategori->id]);
        $kontak = Kontak::factory()->create(['perusahaan_id' => $perusahaan->id, 'kegiatan_id' => $kegiatan->id]);
        $role = Role::firstOrCreate(['name' => 'editor_test', 'guard_name' => 'web']);

        // Verifikasi Kontak Table
        Livewire::test(ListKontaks::class)
            ->assertSuccessful()
            ->mountTableAction('view', $kontak)
            ->assertHasNoTableActionErrors();

        // Verifikasi Kegiatan Table
        Livewire::test(ListKegiatans::class)
            ->assertSuccessful()
            ->mountTableAction('view', $kegiatan)
            ->assertHasNoTableActionErrors();

        // Verifikasi Kategori Table
        Livewire::test(ListKategoriKegiatans::class)
            ->assertSuccessful()
            ->mountTableAction('view', $kategori)
            ->assertHasNoTableActionErrors();

        // Verifikasi User Table
        Livewire::test(ListUsers::class)
            ->assertSuccessful()
            ->mountTableAction('view', $admin)
            ->assertHasNoTableActionErrors();

        // Verifikasi Role Table
        Livewire::test(ListRoles::class)
            ->assertSuccessful()
            ->mountTableAction('view', $role)
            ->assertHasNoTableActionErrors();
    }
}
