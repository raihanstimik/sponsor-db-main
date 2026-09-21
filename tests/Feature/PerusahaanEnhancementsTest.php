<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Perusahaans\Pages\ListPerusahaans;
use App\Filament\Resources\Perusahaans\Widgets\PerusahaanStatsOverview;
use App\Models\Kegiatan;
use App\Models\Kontak;
use App\Models\Perusahaan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PerusahaanEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function header_perusahaan_hanya_memiliki_satu_tombol_tambah_dan_ekspor(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        // Uji HTTP get render halaman sukses
        $response = $this->get('/admin/perusahaans');
        $response->assertOk();
        $response->assertSee('Tambah Perusahaan Baku');
        $response->assertSee('Ekspor Master');
        $response->assertDontSee('New perusahaan');

        // Uji aksi Filament Livewire
        Livewire::test(ListPerusahaans::class)
            ->assertSuccessful()
            ->assertActionVisible('create')
            ->assertActionVisible('exportMaster');
    }

    #[Test]
    public function widget_perusahaan_stats_overview_menampilkan_metrik_nyata(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $p1 = Perusahaan::factory()->create(['nama_standar' => 'PT Alpha Farma']);
        $p2 = Perusahaan::factory()->create(['nama_standar' => 'PT Beta Medika']);

        $kegiatan1 = Kegiatan::factory()->create(['nama_event' => 'KONAS 2025']);
        $kegiatan2 = Kegiatan::factory()->create(['nama_event' => 'SIMPOSIUM 2026']);

        // p1 punya 3 PIC di 2 kegiatan berbeda (mitra utama & multi-event)
        Kontak::factory()->create(['perusahaan_id' => $p1->id, 'kegiatan_id' => $kegiatan1->id, 'nama' => 'PIC 1']);
        Kontak::factory()->create(['perusahaan_id' => $p1->id, 'kegiatan_id' => $kegiatan2->id, 'nama' => 'PIC 2']);
        Kontak::factory()->create(['perusahaan_id' => $p1->id, 'kegiatan_id' => $kegiatan2->id, 'nama' => 'PIC 3']);

        // p2 punya 1 PIC di 1 kegiatan
        Kontak::factory()->create(['perusahaan_id' => $p2->id, 'kegiatan_id' => $kegiatan1->id, 'nama' => 'PIC 4']);

        Livewire::test(PerusahaanStatsOverview::class)
            ->assertSuccessful()
            ->assertSee('Total Korporasi')
            ->assertSee('2')
            ->assertSee('Total PIC Terhubung')
            ->assertSee('4')
            ->assertSee('Mitra Multi-Event')
            ->assertSee('1')
            ->assertSee('Mitra Utama (≥3 PIC)')
            ->assertDontSee('Ringkasan Master Perusahaan');
    }

    #[Test]
    public function tabel_perusahaan_menampilkan_event_terakhir_melalui_relasi_teroptimasi(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $perusahaan = Perusahaan::factory()->create(['nama_standar' => 'PT Kalbe Farma']);
        $kegiatan = Kegiatan::factory()->create(['nama_event' => 'PABI SURABAYA 2025']);

        Kontak::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'kegiatan_id' => $kegiatan->id,
            'nama' => 'Budi Santoso',
        ]);

        Livewire::test(ListPerusahaans::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$perusahaan])
            ->assertTableColumnStateSet('latest_event', 'PABI SURABAYA 2025', $perusahaan);
    }

    #[Test]
    public function view_action_dan_infolist_perusahaan_terkonfigurasi_tertata_dan_berbahasa_indonesia(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $perusahaan = Perusahaan::factory()->create([
            'nama_standar' => 'PT Kimia Farma Tbk',
            'industri' => 'Farmasi & Medis',
            'alamat' => 'Jl. Veteran No. 9, Jakarta Pusat',
            'website' => 'https://kimiafarma.co.id',
            'catatan' => 'Sponsorship rutin simposium tahunan',
        ]);

        $kegiatan = Kegiatan::factory()->create([
            'nama_event' => 'PIT IKABI 2026',
        ]);

        Kontak::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'kegiatan_id' => $kegiatan->id,
            'nama' => 'Andi Wijaya',
            'no_telepon' => '08123456789',
            'email' => 'andi@kimiafarma.co.id',
        ]);

        Livewire::test(ListPerusahaans::class)
            ->assertSuccessful()
            ->mountTableAction('view', $perusahaan)
            ->assertHasNoTableActionErrors();

        // Uji render blade timeline dan daftar pic
        $picHtml = view('filament.infolists.entries.perusahaan-pic-list', ['getRecord' => fn () => $perusahaan])->render();
        $this->assertStringContainsString('Andi Wijaya', $picHtml);
        $this->assertStringContainsString('andi@kimiafarma.co.id', $picHtml);
        $this->assertStringContainsString('Buka & Kelola Seluruh PIC di Modul Kontak', $picHtml);

        $timelineHtml = view('filament.infolists.entries.perusahaan-event-timeline', ['getRecord' => fn () => $perusahaan])->render();
        $this->assertStringContainsString('PIT IKABI 2026', $timelineHtml);
        $this->assertStringNotContainsString('Terbaru', $timelineHtml);
    }
}

