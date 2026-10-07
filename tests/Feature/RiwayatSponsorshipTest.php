<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Perusahaans\Pages\EditPerusahaan;
use App\Filament\Resources\Perusahaans\RelationManagers\RiwayatSponsorshipRelationManager;
use App\Models\Kegiatan;
use App\Models\Perusahaan;
use App\Models\PerusahaanKegiatan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RiwayatSponsorshipTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function dapat_membuat_riwayat_sponsorship_manual_dan_terkait_master_kegiatan(): void
    {
        $perusahaan = Perusahaan::factory()->create(['nama_standar' => 'PT Bio Farma Persero']);
        $masterKegiatan = Kegiatan::factory()->create([
            'nama_event' => 'KONAS IDAI 2026',
            'tanggal_mulai' => Carbon::parse('2026-08-15'),
        ]);

        // 1. Sponsorship dengan master kegiatan
        $riwayat1 = PerusahaanKegiatan::create([
            'perusahaan_id' => $perusahaan->id,
            'kegiatan_id' => $masterKegiatan->id,
            'nominal' => 75000000,
            'paket' => 'Gold',
            'bentuk_partisipasi' => 'Booth Utama & Simposium Satelit',
            'tahun' => 2026,
            'tanggal_partisipasi' => '2026-08-15',
            'catatan' => 'Sponsor utama simposium vaksinasi',
        ]);

        // 2. Sponsorship mandiri (nama event manual tanpa master kegiatan)
        $riwayat2 = PerusahaanKegiatan::create([
            'perusahaan_id' => $perusahaan->id,
            'kegiatan_id' => null,
            'nama_event_manual' => 'Workshop Pediatri Nasional 2024',
            'nominal' => 25000000,
            'paket' => 'Silver',
            'bentuk_partisipasi' => 'Sewa Booth 3x3',
            'tahun' => 2024,
            'tanggal_partisipasi' => '2024-05-10',
            'catatan' => 'Event regional independen',
        ]);

        $this->assertSame('KONAS IDAI 2026', $riwayat1->nama_event);
        $this->assertSame(2026, $riwayat1->tahun_efektif);
        $this->assertSame('Rp 75.000.000', $riwayat1->formatted_nominal);

        $this->assertSame('Workshop Pediatri Nasional 2024', $riwayat2->nama_event);
        $this->assertSame(2024, $riwayat2->tahun_efektif);
        $this->assertSame('Rp 25.000.000', $riwayat2->formatted_nominal);
    }

    #[Test]
    public function riwayat_sponsorship_diurutkan_dari_tahun_dan_tanggal_terakhir_ke_terlama(): void
    {
        $perusahaan = Perusahaan::factory()->create(['nama_standar' => 'PT Kalbe Healthcare']);

        $riwayatLama = PerusahaanKegiatan::create([
            'perusahaan_id' => $perusahaan->id,
            'nama_event_manual' => 'Simposium PABI 2023',
            'tahun' => 2023,
            'tanggal_partisipasi' => '2023-03-01',
        ]);

        $riwayatTerbaru = PerusahaanKegiatan::create([
            'perusahaan_id' => $perusahaan->id,
            'nama_event_manual' => 'PIT Bedah Anak 2026',
            'tahun' => 2026,
            'tanggal_partisipasi' => '2026-10-01',
        ]);

        $riwayatMenengah = PerusahaanKegiatan::create([
            'perusahaan_id' => $perusahaan->id,
            'nama_event_manual' => 'Kongres Nasional 2025',
            'tahun' => 2025,
            'tanggal_partisipasi' => '2025-07-20',
        ]);

        $orderedList = $perusahaan->riwayatSponsorships()->get();

        $this->assertCount(3, $orderedList);
        $this->assertSame($riwayatTerbaru->id, $orderedList[0]->id);
        $this->assertSame($riwayatMenengah->id, $orderedList[1]->id);
        $this->assertSame($riwayatLama->id, $orderedList[2]->id);
    }

    #[Test]
    public function status_keaktifan_sponsor_dihitung_dinamis_berdasarkan_histori(): void
    {
        $currentYear = (int) date('Y');

        // Perusahaan 1: Sangat Aktif (Event tahun ini atau tahun lalu)
        $p1 = Perusahaan::factory()->create(['nama_standar' => 'PT Sangat Aktif']);
        PerusahaanKegiatan::create([
            'perusahaan_id' => $p1->id,
            'nama_event_manual' => 'Event '.$currentYear,
            'tahun' => $currentYear,
        ]);
        $this->assertSame('sangat_aktif', $p1->keaktifan_sponsor['key']);
        $this->assertStringContainsString('Sangat Aktif', $p1->keaktifan_sponsor['label']);

        // Perusahaan 2: Aktif Reguler (Event 2-3 tahun lalu)
        $p2 = Perusahaan::factory()->create(['nama_standar' => 'PT Aktif Reguler']);
        PerusahaanKegiatan::create([
            'perusahaan_id' => $p2->id,
            'nama_event_manual' => 'Event '.($currentYear - 2),
            'tahun' => $currentYear - 2,
        ]);
        $this->assertSame('aktif', $p2->keaktifan_sponsor['key']);
        $this->assertStringContainsString('Aktif Reguler', $p2->keaktifan_sponsor['label']);

        // Perusahaan 3: Perlu Re-engagement (Event 4 tahun lalu)
        $p3 = Perusahaan::factory()->create(['nama_standar' => 'PT Perlu Reaktivasi']);
        PerusahaanKegiatan::create([
            'perusahaan_id' => $p3->id,
            'nama_event_manual' => 'Event '.($currentYear - 4),
            'tahun' => $currentYear - 4,
        ]);
        $this->assertSame('perlu_reaktivasi', $p3->keaktifan_sponsor['key']);

        // Perusahaan 4: Dorman (>4 tahun pasif)
        $p4 = Perusahaan::factory()->create(['nama_standar' => 'PT Dorman']);
        PerusahaanKegiatan::create([
            'perusahaan_id' => $p4->id,
            'nama_event_manual' => 'Event '.($currentYear - 6),
            'tahun' => $currentYear - 6,
        ]);
        $this->assertSame('dorman', $p4->keaktifan_sponsor['key']);

        // Perusahaan 5: Prospek Baru (Belum ada event)
        $p5 = Perusahaan::factory()->create(['nama_standar' => 'PT Belum Ada Event']);
        $this->assertSame('baru', $p5->keaktifan_sponsor['key']);
    }

    #[Test]
    public function analitik_potensi_sponsor_dan_rekomendasi_strategis_terhitung_akurat(): void
    {
        $perusahaan = Perusahaan::factory()->create(['nama_standar' => 'PT Mega Pharma']);

        // Menambahkan 2 event partisipasi dengan paket Platinum & Gold
        PerusahaanKegiatan::create([
            'perusahaan_id' => $perusahaan->id,
            'nama_event_manual' => 'Annual Conference 2025',
            'nominal' => 100000000,
            'paket' => 'Platinum',
            'tahun' => 2025,
        ]);

        PerusahaanKegiatan::create([
            'perusahaan_id' => $perusahaan->id,
            'nama_event_manual' => 'Symposium 2026',
            'nominal' => 60000000,
            'paket' => 'Gold',
            'tahun' => 2026,
        ]);

        $analitik = $perusahaan->analitik_sponsor;

        $this->assertEquals(160000000, $analitik['total_investasi']);
        $this->assertSame(2, $analitik['total_event']);
        $this->assertEquals(80000000, $analitik['rata_rata_nominal']);
        $this->assertStringContainsString('Diamond Whale', $analitik['kategori_potensi']);
        $this->assertNotEmpty($analitik['rekomendasi_strategi']);
        $this->assertArrayHasKey(2025, $analitik['trend_tahunan']);
        $this->assertArrayHasKey(2026, $analitik['trend_tahunan']);
    }

    #[Test]
    public function relation_manager_riwayat_sponsorship_dapat_mengelola_crud(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $perusahaan = Perusahaan::factory()->create(['nama_standar' => 'PT Sumber Medika']);
        $kegiatan = Kegiatan::factory()->create(['nama_event' => 'Simposium Dokter Umum 2026']);

        // Uji buat histori via Relation Manager
        Livewire::test(RiwayatSponsorshipRelationManager::class, [
            'ownerRecord' => $perusahaan,
            'pageClass' => EditPerusahaan::class,
        ])
            ->assertSuccessful()
            ->callTableAction('create', data: [
                'kegiatan_id' => $kegiatan->id,
                'nominal' => 50000000,
                'paket' => 'Gold',
                'bentuk_partisipasi' => 'Booth & Seminar',
                'tahun' => 2026,
                'tanggal_partisipasi' => '2026-06-15',
                'catatan' => 'Dikonfirmasi melalui SPK',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('perusahaan_kegiatan', [
            'perusahaan_id' => $perusahaan->id,
            'kegiatan_id' => $kegiatan->id,
            'nominal' => 50000000,
            'paket' => 'Gold',
            'tahun' => 2026,
        ]);

        $record = PerusahaanKegiatan::where('perusahaan_id', $perusahaan->id)->firstOrFail();

        // Uji edit histori via Relation Manager
        Livewire::test(RiwayatSponsorshipRelationManager::class, [
            'ownerRecord' => $perusahaan,
            'pageClass' => EditPerusahaan::class,
        ])
            ->callTableAction('edit', $record, data: [
                'nominal' => 60000000,
                'paket' => 'Platinum',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('perusahaan_kegiatan', [
            'id' => $record->id,
            'nominal' => 60000000,
            'paket' => 'Platinum',
        ]);

        // Uji hapus histori via Relation Manager
        Livewire::test(RiwayatSponsorshipRelationManager::class, [
            'ownerRecord' => $perusahaan,
            'pageClass' => EditPerusahaan::class,
        ])
            ->callTableAction('delete', $record)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('perusahaan_kegiatan', [
            'id' => $record->id,
        ]);
    }

    #[Test]
    public function detail_infolist_perusahaan_menampilkan_visual_chart_dan_matriks_keputusan(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $perusahaan = Perusahaan::factory()->create(['nama_standar' => 'PT Visual Tech Farma']);

        PerusahaanKegiatan::create([
            'perusahaan_id' => $perusahaan->id,
            'nama_event_manual' => 'Kongres Tahunan IKABI 2025',
            'nominal' => 35000000,
            'paket' => 'Gold',
            'bentuk_partisipasi' => 'Booth Platinum Corner',
            'tahun' => 2025,
            'catatan' => 'Sponsor langganan tiap kongres',
        ]);

        // Uji render view detail timeline
        $viewHtml = view('filament.infolists.entries.perusahaan-event-timeline', [
            'getRecord' => fn () => $perusahaan,
        ])->render();

        $this->assertStringContainsString('Kongres Tahunan IKABI 2025', $viewHtml);
        $this->assertStringContainsString('35.000.000', $viewHtml);
        $this->assertStringContainsString('Gold', $viewHtml);
        $this->assertStringContainsString('Grafik Tren Partisipasi &amp; Kontribusi Finansial', $viewHtml);
        $this->assertStringNotContainsString('Rekomendasi Strategi Proposal untuk Pimpro', $viewHtml);
        $this->assertStringContainsString('Booth Platinum Corner', $viewHtml);
        $this->assertStringContainsString('Sponsor langganan tiap kongres', $viewHtml);
    }

    #[Test]
    public function view_dan_edit_perusahaan_memiliki_tab_riwayat_dan_form_repeater_lengkap(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $perusahaan = Perusahaan::factory()->create(['nama_standar' => 'PT Editable Farma']);

        PerusahaanKegiatan::create([
            'perusahaan_id' => $perusahaan->id,
            'nama_event_manual' => 'Kongres Bedah 2026',
            'nominal' => 45000000,
            'paket' => 'Platinum',
            'tahun' => 2026,
        ]);

        // 1. Verifikasi View page memiliki tab gabungan
        $viewPage = new \App\Filament\Resources\Perusahaans\Pages\ViewPerusahaan();
        $this->assertTrue($viewPage->hasCombinedRelationManagerTabsWithContent());

        // 2. Verifikasi Edit page memiliki tab gabungan
        $editPage = new \App\Filament\Resources\Perusahaans\Pages\EditPerusahaan();
        $this->assertTrue($editPage->hasCombinedRelationManagerTabsWithContent());

        // 3. Verifikasi HTTP GET ke halaman edit render dengan baik dan memuat data riwayat repeater
        $response = $this->get('/admin/perusahaans/' . $perusahaan->id . '/edit');
        $response->assertOk();
        $response->assertSee('Riwayat Sponsorship & Keterlibatan Event');

        // 4. Verifikasi Infolist memiliki header action catat_riwayat_cepat
        $schema = \Filament\Schemas\Schema::make();
        $configured = \App\Filament\Resources\Perusahaans\Schemas\PerusahaanInfolist::configure($schema);
        $sectionHistori = collect($configured->getComponents())->first(fn ($s) => $s instanceof \Filament\Schemas\Components\Section && $s->getHeading() === 'Histori Partisipasi Kongres Medis');
        $this->assertNotNull($sectionHistori);
        $this->assertNotEmpty($sectionHistori->getHeaderActions());
    }
}
