<?php

namespace Tests\Feature;

use App\Filament\Resources\Kontaks\Pages\CreateKontak;
use App\Filament\Resources\Kontaks\Pages\ListKontaks;
use App\Filament\Resources\Kontaks\Tables\KontaksTable;
use App\Models\Kontak;
use App\Models\Perusahaan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class KontakEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function ekspor_csv_mendownload_semua_kontak(): void
    {
        $user = User::factory()->admin()->create();
        $perusahaan = Perusahaan::factory()->create(['nama_standar' => 'PT Alfa Medika']);
        Kontak::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'nama' => 'Budi Santoso',
            'no_telepon' => '08111465133',
            'status_verifikasi' => 'terverifikasi',
        ]);

        $response = $this->actingAs($user)
            ->get(route('kontaks.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Budi Santoso', $csv);
        $this->assertStringContainsString('PT Alfa Medika', $csv);
        $this->assertStringContainsString('628111465133', $csv);
    }

    #[Test]
    public function ekspor_csv_menghormati_filter_pencarian(): void
    {
        $user = User::factory()->admin()->create();

        $perusahaanA = Perusahaan::factory()->create(['nama_standar' => 'PT Alfa Medika']);
        $perusahaanB = Perusahaan::factory()->create(['nama_standar' => 'CV Sinar Sehat']);

        Kontak::factory()->create([
            'perusahaan_id' => $perusahaanA->id,
            'nama' => 'Budi Santoso',
            'no_telepon' => '08111465133',
        ]);
        Kontak::factory()->create([
            'perusahaan_id' => $perusahaanB->id,
            'nama' => 'Siti Aminah',
            'no_telepon' => '08120918231',
        ]);

        $csv = $this->actingAs($user)
            ->get(route('kontaks.export', ['q' => 'Aminah']))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('Siti Aminah', $csv);
        $this->assertStringNotContainsString('Budi Santoso', $csv);
    }

    #[Test]
    public function ekspor_csv_membutuhkan_login(): void
    {
        $this->get(route('kontaks.export'))->assertRedirect();
    }

    #[Test]
    public function list_kontak_menyediakan_aksi_baris_whatsapp_view_dan_ekspor(): void
    {
        $user = User::factory()->admin()->create();
        $perusahaan = Perusahaan::factory()->create();
        $kontak = Kontak::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'no_telepon' => '08111465133',
        ]);

        $this->actingAs($user);
        Livewire::test(ListKontaks::class)
            ->assertOk()
            ->assertTableColumnExists('nama')
            ->assertTableActionExists('whatsapp')
            ->assertTableActionExists('view')
            ->assertTableActionExists('edit')
            ->assertTableActionExists('export')
            ->assertSee('Ekspor CSV')
            ->assertSee((string) $kontak->id)
            ->assertSee('wa.me/628111465133');
    }

    #[Test]
    public function daftar_kontak_menampilkan_ringkasan_di_atas_mengikuti_filter(): void
    {
        $user = User::factory()->admin()->create();

        $perusahaan = Perusahaan::factory()->create();
        Kontak::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'no_telepon' => '08111465133',
            'status_format_valid' => true,
        ]);
        Kontak::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'no_telepon' => '08120918231',
            'status_format_valid' => true,
        ]);
        Kontak::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'no_telepon' => '08137788990',
            'status_format_valid' => true,
        ]);
        Kontak::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'no_telepon' => 'bukan-nomor',
            'status_format_valid' => false,
        ]);

        $this->actingAs($user);
        $component = Livewire::test(ListKontaks::class)->assertOk();

        $cards = collect(KontaksTable::summaryCards($component->instance()))->keyBy('key');
        $this->assertSame(4, $cards['total']['count']);
        $this->assertSame(3, $cards['valid']['count']);
        $this->assertSame(1, $cards['perusahaan']['count']);

        $component->assertSee('Total kontak')->assertSee('Nomor HP valid');
    }

    #[Test]
    public function ringkasan_mengikuti_filter_pencarian_yang_aktif(): void
    {
        $user = User::factory()->admin()->create();

        $perusahaan = Perusahaan::factory()->create();
        Kontak::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'nama' => 'Budi Sudarsono',
        ]);
        Kontak::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'nama' => 'Siti Aminah',
        ]);

        $this->actingAs($user);
        $component = Livewire::test(ListKontaks::class)
            ->filterTable('cari', ['q' => 'Aminah']);

        $cards = collect(KontaksTable::summaryCards($component->instance()))->keyBy('key');
        $this->assertSame(1, $cards['total']['count']);
    }

    #[Test]
    public function semua_kolom_dapat_dipilih_lewat_pengelola_kolom(): void
    {
        $user = User::factory()->admin()->create();
        $perusahaan = Perusahaan::factory()->create();
        Kontak::factory()->create(['perusahaan_id' => $perusahaan->id]);

        $this->actingAs($user);

        $component = Livewire::test(ListKontaks::class)->assertOk();
        $table = $component->instance()->getTable();

        foreach ([
            'No',
            'perusahaan.nama_standar',
            'nama',
            'no_telepon',
            'kegiatan.nama_event',
            'kategoriKegiatan.nama_kategori',
            'updatedBy.name',
        ] as $columnName) {
            $this->assertTrue(
                $table->getColumn($columnName)->isToggleable(),
                "Kolom [$columnName] seharusnya dapat dipilih di pengelola kolom."
            );
        }
    }

    #[Test]
    public function baris_duplikat_disorot_hanya_untuk_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $perusahaanA = Perusahaan::factory()->create(['nama_standar' => 'PT Alfa Medika']);
        $perusahaanB = Perusahaan::factory()->create(['nama_standar' => 'CV Sinar Sehat']);

        $kontakA = Kontak::factory()->create([
            'perusahaan_id' => $perusahaanA->id,
            'no_telepon' => '08111465133',
        ]);
        Kontak::factory()->create([
            'perusahaan_id' => $perusahaanB->id,
            'no_telepon' => '08111465133',
        ]);

        $this->assertSame(
            ['CV Sinar Sehat'],
            $kontakA->perusahaanLainDenganNomorSama()
        );

        $this->actingAs($admin);
        Livewire::test(ListKontaks::class)
            ->assertOk()
            ->assertSee('bg-danger-500/10');

        Livewire::actingAs(User::factory()->karyawan()->create())
            ->test(ListKontaks::class)
            ->assertOk()
            ->assertDontSee('bg-danger-500/10');
    }

    #[Test]
    public function list_kontak_kosong_menampilkan_cta_import(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user);
        Livewire::test(ListKontaks::class)
            ->assertOk()
            ->assertSee('Belum ada kontak')
            ->assertSee('Import Data');
    }

    #[Test]
    public function kontak_dapat_menyimpan_email_dan_catatan_follow_up(): void
    {
        $user = User::factory()->admin()->create();
        $perusahaan = Perusahaan::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateKontak::class)
            ->fillForm([
                'perusahaan_id' => $perusahaan->id,
                'nama' => 'Dewi Sartika',
                'no_telepon' => '081234567890',
                'email' => 'dewi@example.com',
                'catatan' => 'Tertarik paket Platinum. Follow-up hari Jumat.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $kontak = Kontak::where('nama', 'Dewi Sartika')->first();
        $this->assertNotNull($kontak);
        $this->assertSame('dewi@example.com', $kontak->email);
        $this->assertSame('Tertarik paket Platinum. Follow-up hari Jumat.', $kontak->catatan);
    }

    #[Test]
    public function duplikasi_nomor_telepon_tetap_dapat_disimpan_oleh_pengguna(): void
    {
        $user = User::factory()->admin()->create();
        $perusahaanA = Perusahaan::factory()->create(['nama_standar' => 'PT Alpha']);
        $perusahaanB = Perusahaan::factory()->create(['nama_standar' => 'PT Beta']);

        // Kontak awal di perusahaan A
        Kontak::factory()->create([
            'perusahaan_id' => $perusahaanA->id,
            'nama' => 'Rudi',
            'no_telepon' => '081234567899',
        ]);

        $this->actingAs($user);

        // Pengguna membuat kontak di perusahaan B dengan nomor sama
        Livewire::test(CreateKontak::class)
            ->fillForm([
                'perusahaan_id' => $perusahaanB->id,
                'nama' => 'Rudi Cabang',
                'no_telepon' => '081234567899',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $kontakBaru = Kontak::where('nama', 'Rudi Cabang')->first();
        $this->assertNotNull($kontakBaru);
        $this->assertSame($perusahaanB->id, $kontakBaru->perusahaan_id);
    }

    #[Test]
    public function list_kontak_memiliki_kolom_email_catatan_dan_aksi_quick_whatsapp(): void
    {
        $user = User::factory()->admin()->create();
        $perusahaan = Perusahaan::factory()->create();
        Kontak::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'nama' => 'Sari',
            'no_telepon' => '081299887766',
            'email' => 'sari@example.com',
            'catatan' => 'Catatan penting',
        ]);

        $this->actingAs($user);
        Livewire::test(ListKontaks::class)
            ->assertOk()
            ->assertTableColumnExists('email')
            ->assertTableColumnExists('catatan')
            ->assertTableActionExists('quick_whatsapp');
    }

    #[Test]
    public function list_kontak_tidak_memiliki_filter_deleted_records(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $component = Livewire::test(ListKontaks::class)
            ->assertOk();

        $table = $component->instance()->getTable();
        $this->assertNull(
            $table->getFilter('trashed'),
            'Filter trashed (Deleted records) seharusnya sudah dihapus dari tabel kontak.'
        );
    }
}
