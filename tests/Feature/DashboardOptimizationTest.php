<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Widgets\DistribusiKategoriWidget;
use App\Filament\Widgets\KontakStatsOverview;
use App\Filament\Widgets\SambutanDashboard;
use App\Filament\Widgets\TopEventWidget;
use App\Models\KategoriKegiatan;
use App\Models\Kegiatan;
use App\Models\Kontak;
use App\Models\Perusahaan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardOptimizationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function dashboard_memiliki_banner_sambutan_eksekutif_anti_ai_slop(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Super Admin ICM']);
        $this->actingAs($admin);

        // Halaman GET berhasil
        $this->get('/admin')->assertOk();

        // Uji Livewire SambutanDashboard langsung
        Livewire::test(SambutanDashboard::class)
            ->assertOk()
            ->assertSee('Selamat datang kembali, Super Admin ICM')
            ->assertSee('Indonesia Congress Management')
            ->assertSee('Kelola Kontak PIC')
            ->assertSee('Master Perusahaan')
            ->assertDontSee('animate-aurora-slow')
            ->assertDontSee('blur-3xl');
    }

    #[Test]
    public function dashboard_card_memiliki_struktur_html_valid_tanpa_pulsing_gimmick(): void
    {
        $html = Blade::render('
            <x-dashboard-card
                title="Uji Statistik Event"
                subtitle="Subjudul Deskriptif"
                badge="10 Total"
                footer-text="Keterangan Kaki"
                footer-url="/admin/kegiatans"
                footer-label="Lihat Rincian"
            >
                <div>Konten Pengujian</div>
            </x-dashboard-card>
        ');

        // Verifikasi tidak ada tag pulsing dot pada judul statis
        $this->assertStringNotContainsString('animate-pulse', $html);
        $this->assertStringContainsString('dashboard-card', $html);
        $this->assertSame(1, substr_count($html, 'dashboard-card'));
        $this->assertSame(1, substr_count($html, '<h2'));
        $this->assertStringContainsString('Lihat Rincian', $html);
        $this->assertStringContainsString('Konten Pengujian', $html);
    }

    #[Test]
    public function widget_grafik_memiliki_layout_kolom_responsif_dan_ranked_list_bersih(): void
    {
        $kat = KategoriKegiatan::factory()->create(['nama_kategori' => 'Kardiologi']);
        $event = Kegiatan::factory()->create(['kategori_kegiatan_id' => $kat->id, 'nama_event' => 'KONAS PERKI 2026']);
        $perusahaan = Perusahaan::factory()->create();
        Kontak::factory()->create(['perusahaan_id' => $perusahaan->id, 'kegiatan_id' => $event->id]);

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        // Uji render Livewire DistribusiKategoriWidget
        Livewire::test(DistribusiKategoriWidget::class)
            ->assertOk()
            ->assertSee('Distribusi Event per Kategori Medis')
            ->assertSee('Kardiologi')
            ->assertSee('Kelola Kategori')
            ->assertDontSee('&lt;div class=')
            ->assertDontSee('Dominasi event terbesar:');

        // Uji render Livewire TopEventWidget (menampilkan nama lengkap kegiatan di ranked list)
        Livewire::test(TopEventWidget::class)
            ->assertOk()
            ->assertSee('Top 5 Event Terbesar')
            ->assertSee('KONAS PERKI 2026')
            ->assertSee('Lihat Rincian Event')
            ->assertDontSee('&lt;div class=')
            ->assertDontSee('&lt;span class=');

        // Uji konfigurasi responsive columnSpan side-by-side
        $distWidget = new DistribusiKategoriWidget();
        $topWidget = new TopEventWidget();

        $refDist = new \ReflectionProperty($distWidget, 'columnSpan');
        $refDist->setAccessible(true);
        $distSpan = $refDist->getValue($distWidget);

        $refTop = new \ReflectionProperty($topWidget, 'columnSpan');
        $refTop->setAccessible(true);
        $topSpan = $refTop->getValue($topWidget);

        $this->assertIsArray($distSpan);
        $this->assertSame(1, $distSpan['xl'] ?? null);
        $this->assertIsArray($topSpan);
        $this->assertSame(1, $topSpan['xl'] ?? null);
    }
}
