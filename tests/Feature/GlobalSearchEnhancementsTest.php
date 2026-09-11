<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\KategoriKegiatans\KategoriKegiatanResource;
use App\Filament\Resources\Kegiatans\KegiatanResource;
use App\Filament\Resources\Kontaks\KontakResource;
use App\Filament\Resources\Kontaks\Pages\ViewKontak;
use App\Filament\Resources\Perusahaans\PerusahaanResource;
use App\Models\KategoriKegiatan;
use App\Models\Kegiatan;
use App\Models\Kontak;
use App\Models\Perusahaan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GlobalSearchEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Perusahaan $perusahaan;

    private KategoriKegiatan $kategori;

    private Kegiatan $kegiatan;

    private Kontak $kontak;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);

        $this->perusahaan = Perusahaan::factory()->create([
            'nama_standar' => 'PT Bio Farma Persero',
            'industri' => 'Farmasi & Vaksin',
        ]);

        $this->kategori = KategoriKegiatan::factory()->create([
            'nama_kategori' => 'Spesialis Anak',
        ]);

        $this->kegiatan = Kegiatan::factory()->create([
            'nama_event' => 'PIKAB 2026',
            'kategori_kegiatan_id' => $this->kategori->id,
            'venue' => 'Grand Ballroom Hilton',
        ]);

        $this->kontak = Kontak::factory()->create([
            'perusahaan_id' => $this->perusahaan->id,
            'kegiatan_id' => $this->kegiatan->id,
            'kategori_kegiatan_id' => $this->kategori->id,
            'nama' => 'Budi Santoso',
            'no_telepon' => '628111465133',
            'status_verifikasi' => 'terverifikasi',
        ]);
    }

    #[Test]
    public function global_search_kontak_mengarahkan_ke_halaman_view_detail(): void
    {
        $url = KontakResource::getGlobalSearchResultUrl($this->kontak);
        $expected = KontakResource::getUrl('view', ['record' => $this->kontak]);

        $this->assertSame($expected, $url);

        $details = KontakResource::getGlobalSearchResultDetails($this->kontak);
        $this->assertSame('PT Bio Farma Persero', $details['Perusahaan'] ?? null);
        $this->assertSame('628111465133', $details['No. HP'] ?? null);
        $this->assertSame('PIKAB 2026', $details['Event'] ?? null);

        $response = $this->get($url);
        $response->assertSuccessful();
        $response->assertSee('Budi Santoso');
        $response->assertSee('Kirim WhatsApp');
    }

    #[Test]
    public function global_search_perusahaan_mengarahkan_ke_halaman_view_detail(): void
    {
        $url = PerusahaanResource::getGlobalSearchResultUrl($this->perusahaan);
        $expected = PerusahaanResource::getUrl('view', ['record' => $this->perusahaan]);

        $this->assertSame($expected, $url);

        $response = $this->get($url);
        $response->assertSuccessful();
        $response->assertSee('PT Bio Farma Persero');
    }

    #[Test]
    public function global_search_kegiatan_mengarahkan_ke_halaman_view_detail(): void
    {
        $url = KegiatanResource::getGlobalSearchResultUrl($this->kegiatan);
        $expected = KegiatanResource::getUrl('view', ['record' => $this->kegiatan]);

        $this->assertSame($expected, $url);

        $details = KegiatanResource::getGlobalSearchResultDetails($this->kegiatan);
        $this->assertSame('Spesialis Anak', $details['Kategori'] ?? null);
        $this->assertSame('Grand Ballroom Hilton', $details['Venue'] ?? null);

        $response = $this->get($url);
        $response->assertSuccessful();
        $response->assertSee('PIKAB 2026');
    }

    #[Test]
    public function global_search_kategori_mengarahkan_ke_tab_kategori_pada_kegiatan(): void
    {
        $url = KategoriKegiatanResource::getGlobalSearchResultUrl($this->kategori);
        $expected = KegiatanResource::getUrl('index', ['tab' => 'kategori']);

        $this->assertSame($expected, $url);
    }

    #[Test]
    public function halaman_view_kontak_menampilkan_aksi_utama_tanpa_tombol_verifikasi(): void
    {
        $kontak = Kontak::factory()->create([
            'perusahaan_id' => $this->perusahaan->id,
            'kegiatan_id' => $this->kegiatan->id,
            'no_telepon' => '081234567890',
        ]);

        Livewire::test(ViewKontak::class, ['record' => $kontak->getKey()])
            ->assertActionVisible('whatsapp')
            ->assertActionVisible('edit')
            ->assertActionDoesNotExist('verifikasi');
    }
}
