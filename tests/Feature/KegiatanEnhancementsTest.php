<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Kegiatans\Pages\ListKegiatans;
use App\Filament\Resources\Kegiatans\Widgets\KategoriKegiatanTableWidget;
use App\Filament\Resources\Kegiatans\Widgets\KegiatanStatsOverview;
use App\Models\KategoriKegiatan;
use App\Models\Kegiatan;
use App\Models\Kontak;
use App\Models\Perusahaan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class KegiatanEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function halaman_list_kegiatan_render_dengan_widget_dan_metrik(): void
    {
        $admin = User::factory()->admin()->create();

        $kategori = KategoriKegiatan::factory()->create(['nama_kategori' => 'Obgyn', 'warna' => '#18225e']);

        $kegiatan1 = Kegiatan::factory()->for($kategori, 'kategoriKegiatan')->create([
            'nama_event' => 'PIT HOGSI 2026',
            'venue' => 'Trans Luxury Hotel, Bandung',
            'tanggal_mulai' => now()->addDays(10),
            'tanggal_selesai' => now()->addDays(13),
        ]);

        $kegiatan2 = Kegiatan::factory()->for($kategori, 'kategoriKegiatan')->create([
            'nama_event' => 'Kongres Lampau 2024',
            'venue' => 'Jakarta Convention Center',
            'tanggal_mulai' => now()->subDays(30),
            'tanggal_selesai' => now()->subDays(28),
        ]);

        $perusahaanA = Perusahaan::factory()->create(['nama_standar' => 'PT Kalbe Farma']);
        $perusahaanB = Perusahaan::factory()->create(['nama_standar' => 'PT Dexa Medica']);

        Kontak::factory()->create([
            'kegiatan_id' => $kegiatan1->id,
            'kategori_kegiatan_id' => $kategori->id,
            'perusahaan_id' => $perusahaanA->id,
        ]);

        Kontak::factory()->create([
            'kegiatan_id' => $kegiatan1->id,
            'kategori_kegiatan_id' => $kategori->id,
            'perusahaan_id' => $perusahaanB->id,
        ]);

        $this->actingAs($admin);

        // Uji HTTP get render halaman sukses
        // Uji HTTP get render halaman sukses untuk kedua tab
        $this->get('/admin/kegiatans')
            ->assertOk()
            ->assertSee('PIT HOGSI 2026');

        $this->get('/admin/kegiatans?tab=kategori')
            ->assertOk()
            ->assertSee('Obgyn');

        // Uji komponen widget langsung
        Livewire::test(KegiatanStatsOverview::class)
            ->assertOk()
            ->assertSee('Total Event Terdaftar')
            ->assertSee('2')
            ->assertSee('Kategori Spesialisasi')
            ->assertSee('Event Mendatang')
            ->assertSee('Total Kontak Terafiliasi');

        // Uji tabel kegiatan Livewire dan Infolist slideover
        Livewire::test(ListKegiatans::class)
            ->assertSuccessful()
            ->assertSee('PIT HOGSI 2026')
            ->assertSee('Trans Luxury Hotel, Bandung')
            ->assertSee('Durasi 4 Hari')
            ->assertSee('2 PIC')
            ->assertSee('2 Sponsor')
            ->mountTableAction('view', $kegiatan1)
            ->assertHasNoTableActionErrors();
    }

    #[Test]
    public function header_kegiatan_bersih_dengan_satu_tombol_primer_dan_dapat_menambah_kegiatan(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $kategori = KategoriKegiatan::factory()->create();

        Livewire::test(ListKegiatans::class)
            ->assertActionVisible('create')
            ->callAction('create', [
                'nama_event' => 'Simposium Kardiologi 2026',
                'kategori_kegiatan_id' => $kategori->id,
                'tanggal_mulai' => now()->addDays(5)->toDateString(),
                'tanggal_selesai' => now()->addDays(7)->toDateString(),
                'venue' => 'Grand Mercure Jakarta',
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('kegiatans', [
            'nama_event' => 'Simposium Kardiologi 2026',
            'venue' => 'Grand Mercure Jakarta',
        ]);
    }

    #[Test]
    public function filter_status_kegiatan_berfungsi(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $kategori = KategoriKegiatan::factory()->create();

        $mendatang = Kegiatan::factory()->for($kategori, 'kategoriKegiatan')->create([
            'nama_event' => 'Kongres Masa Depan',
            'tanggal_mulai' => now()->addDays(5),
            'tanggal_selesai' => now()->addDays(7),
        ]);

        $selesai = Kegiatan::factory()->for($kategori, 'kategoriKegiatan')->create([
            'nama_event' => 'Kongres Masa Lalu',
            'tanggal_mulai' => now()->subDays(10),
            'tanggal_selesai' => now()->subDays(8),
        ]);

        Livewire::test(ListKegiatans::class)
            ->filterTable('status', 'mendatang')
            ->assertCanSeeTableRecords([$mendatang])
            ->assertCanNotSeeTableRecords([$selesai]);
    }

    #[Test]
    public function halaman_terpadu_dapat_berpindah_antara_tab_kegiatan_dan_kategori(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $kategori = KategoriKegiatan::factory()->create([
            'nama_kategori' => 'Pulmonologi & Paru',
            'warna' => '#0ea5e9',
        ]);

        $kegiatan = Kegiatan::factory()->for($kategori, 'kategoriKegiatan')->create([
            'nama_event' => 'Simposium Paru Nasional 2026',
        ]);

        Livewire::test(ListKegiatans::class)
            ->assertSee('Daftar Kegiatan / Event')
            ->assertSee('Kelola Kategori Spesialisasi')
            ->assertSee('Simposium Paru Nasional 2026')
            ->call('setTab', 'kategori')
            ->assertSet('tab', 'kategori')
            ->assertSee('Kelola Kategori Spesialisasi')
            ->assertSee('Pulmonologi & Paru');
    }

    #[Test]
    public function tombol_header_kontekstual_sesuai_tab_kegiatan_dan_kategori(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Livewire::test(ListKegiatans::class)
            ->assertActionVisible('create')
            ->assertActionVisible('createKategori')
            ->call('setTab', 'kategori')
            ->callAction('createKategori', [
                'nama_kategori' => 'Kategori Baru via Header',
                'warna' => '#10b981',
                'deskripsi' => 'Kategori hasil uji coba tombol kontekstual',
            ])
            ->assertHasNoActionErrors()
            ->assertDispatched('refresh-kategori-widget');

        $this->assertDatabaseHas('kategori_kegiatans', [
            'nama_kategori' => 'Kategori Baru via Header',
        ]);
    }

    #[Test]
    public function pengguna_tanpa_izin_tidak_melihat_tombol_kategori(): void
    {
        $karyawan = User::factory()->karyawan()->create();
        $this->actingAs($karyawan);

        Livewire::test(ListKegiatans::class)
            ->assertActionHidden('createKategori');
    }

    #[Test]
    public function admin_dapat_melihat_data_terisi_saat_klik_edit_kategori_dan_berhasil_memperbarui(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $kategori = KategoriKegiatan::factory()->create([
            'nama_kategori' => 'Neurologi Klinis',
            'warna' => '#6366f1',
            'deskripsi' => 'Spesialisasi saraf dan otak',
        ]);

        Livewire::test(KategoriKegiatanTableWidget::class)
            ->mountTableAction('edit', $kategori)
            ->assertTableActionDataSet([
                'nama_kategori' => 'Neurologi Klinis',
                'warna' => '#6366f1',
                'deskripsi' => 'Spesialisasi saraf dan otak',
            ])
            ->setTableActionData([
                'nama_kategori' => 'Neurologi & Bedah Saraf',
                'warna' => '#4f46e5',
                'deskripsi' => 'Spesialisasi saraf terpadu',
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('kategori_kegiatans', [
            'id' => $kategori->id,
            'nama_kategori' => 'Neurologi & Bedah Saraf',
            'warna' => '#4f46e5',
            'deskripsi' => 'Spesialisasi saraf terpadu',
        ]);
    }

    #[Test]
    public function admin_dapat_menghapus_kategori_yang_tidak_memiliki_kegiatan(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $kategori = KategoriKegiatan::factory()->create([
            'nama_kategori' => 'Kategori Tanpa Event',
        ]);

        Livewire::test(KategoriKegiatanTableWidget::class)
            ->callTableAction('delete', $kategori)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('kategori_kegiatans', [
            'id' => $kategori->id,
        ]);
    }

    #[Test]
    public function kategori_yang_masih_memiliki_kegiatan_dilarang_dihapus(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $kategori = KategoriKegiatan::factory()->create([
            'nama_kategori' => 'Kardiologi Penting',
        ]);

        Kegiatan::factory()->for($kategori, 'kategoriKegiatan')->create([
            'nama_event' => 'Kongres Jantung 2026',
        ]);

        Livewire::test(KategoriKegiatanTableWidget::class)
            ->callTableAction('delete', $kategori);

        // Kategori harus tetap ada di database
        $this->assertDatabaseHas('kategori_kegiatans', [
            'id' => $kategori->id,
            'nama_kategori' => 'Kardiologi Penting',
        ]);
    }

    #[Test]
    public function infolist_kegiatan_merender_rincian_dan_afiliasi_sponsor(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $kategori = KategoriKegiatan::factory()->create([
            'nama_kategori' => 'Obstetri & Ginekologi (POGI)',
            'warna' => '#10B981',
        ]);

        $kegiatan = Kegiatan::factory()->for($kategori, 'kategoriKegiatan')->create([
            'nama_event' => 'ACE BALI 2026',
            'venue' => 'Bali Nusa Dua Convention Center',
            'catatan' => 'Catatan agenda penting simposium',
        ]);

        $perusahaan = Perusahaan::factory()->create(['nama_standar' => 'PT Bio Farma']);

        Kontak::factory()->create([
            'kegiatan_id' => $kegiatan->id,
            'kategori_kegiatan_id' => $kategori->id,
            'perusahaan_id' => $perusahaan->id,
            'nama' => 'Dr. Budi Santoso',
            'no_telepon' => '081234567890',
        ]);

        Livewire::test(ListKegiatans::class)
            ->mountTableAction('view', $kegiatan)
            ->assertHasNoTableActionErrors();

        Livewire::test(\App\Filament\Resources\Kegiatans\Pages\ViewKegiatan::class, [
            'record' => $kegiatan->getRouteKey(),
        ])
            ->assertSuccessful()
            ->assertSee('ACE BALI 2026')
            ->assertSee('Obstetri & Ginekologi (POGI)')
            ->assertSee('Bali Nusa Dua Convention Center')
            ->assertSee('Dr. Budi Santoso')
            ->assertSee('PT Bio Farma')
            ->assertDontSee('Warna Indikator');
    }
}
