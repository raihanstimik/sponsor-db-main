<?php

namespace Tests\Feature;

use App\Filament\Widgets\DistribusiKategoriWidget;
use App\Filament\Widgets\KontakPerluDicekWidget;
use App\Filament\Widgets\KontakStatsOverview;
use App\Filament\Widgets\SambutanDashboard;
use App\Filament\Widgets\TopEventWidget;
use App\Models\KategoriKegiatan;
use App\Models\Kegiatan;
use App\Models\Kontak;
use App\Models\Perusahaan;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

class DashboardWidgetTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function halaman_dashboard_renders_untuk_admin_dan_karyawan(): void
    {
        $admin = User::factory()->admin()->create();
        $karyawan = User::factory()->karyawan()->create();

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($karyawan)->get('/admin')->assertOk();
    }

    #[Test]
    public function widget_dashboard_tersedia_dan_ditemukan_otomatis(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $widgets = array_map(
            fn ($widget): string => is_string($widget) ? $widget : get_class($widget),
            Filament::getWidgets()
        );

        $this->assertContains(KontakStatsOverview::class, $widgets);
        $this->assertContains(DistribusiKategoriWidget::class, $widgets);
        $this->assertContains(TopEventWidget::class, $widgets);
        $this->assertContains(SambutanDashboard::class, $widgets);
        $this->assertContains(KontakPerluDicekWidget::class, $widgets);
        $this->assertNotContains('App\Filament\Widgets\StatusVerifikasiChart', $widgets);
    }

    #[Test]
    public function stat_cards_menampilkan_angka_yang_benar(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $perusahaan = Perusahaan::factory()->create();
        Perusahaan::factory()->count(3)->create();
        Kontak::factory()->count(2)->create(['perusahaan_id' => $perusahaan->id]);
        Kontak::factory()->create(['perusahaan_id' => $perusahaan->id, 'status_verifikasi' => 'perlu_dicek']);
        Kegiatan::factory()->count(2)->create();

        $widget = new KontakStatsOverview;
        $getStats = new ReflectionMethod($widget, 'getStats');
        $stats = $getStats->invoke($widget);

        // Balok Ringkasan KPI — cari by label agar tahan terhadap urutan/tambahan stat
        $byLabel = [];
        foreach ($stats as $stat) {
            $byLabel[$stat->getLabel()] = (string) $stat->getValue();
        }

        $this->assertSame('4', $byLabel['Total Perusahaan']);
        $this->assertSame('3', $byLabel['Total Kontak']);
        $this->assertSame('3', $byLabel['Nomor HP Valid']);
        $this->assertSame('2', $byLabel['Total Kegiatan']);

        Livewire::test(KontakStatsOverview::class)->assertOk();
    }

    #[Test]
    public function daftar_bar_distribusi_event_per_kategori(): void
    {
        $katA = KategoriKegiatan::factory()->create(['nama_kategori' => 'Dokter Umum & Estetik']);
        $katB = KategoriKegiatan::factory()->create(['nama_kategori' => 'Gizi Klinik']);
        // KatA: 2 event, KatB: 1 event
        Kegiatan::factory()->create(['kategori_kegiatan_id' => $katA->id]);
        Kegiatan::factory()->create(['kategori_kegiatan_id' => $katA->id]);
        Kegiatan::factory()->create(['kategori_kegiatan_id' => $katB->id]);

        $data = DistribusiKategoriWidget::data();

        $this->assertSame(3, $data['total']);
        $this->assertCount(2, $data['rows']);
        // Terurut desc: KatA (2) dulu, bar teratas selalu 100%
        $this->assertSame('Dokter Umum & Estetik', $data['rows'][0]['nama']);
        $this->assertSame(2, $data['rows'][0]['count']);
        $this->assertSame(100.0, $data['rows'][0]['width']);
        $this->assertSame(66.7, $data['rows'][0]['share']);
        $this->assertSame(1, $data['rows'][1]['count']);
        $this->assertMatchesRegularExpression('/^#[0-9A-Fa-f]{6}$/', $data['rows'][0]['hex']);

        $this->actingAs(User::factory()->admin()->create());
        Livewire::test(DistribusiKategoriWidget::class)
            ->assertOk()
            ->assertSee('Distribusi Event per Kategori Medis')
            ->assertSee('Kelola Kategori');
    }

    #[Test]
    public function daftar_bar_top5_event_terbesar(): void
    {
        $events = collect(range(1, 6))->map(fn ($i) => Kegiatan::factory()->create(['nama_event' => 'Event '.$i]))->all();
        $perusahaan = Perusahaan::factory()->create();
        // Event1: 5 kontak, Event2: 3, Event3: 4, Event4: 2, Event5: 1, Event6: 0 -> Top5 harus exclude Event6
        foreach ([5, 3, 4, 2, 1, 0] as $idx => $count) {
            Kontak::factory()->count($count)->create(['perusahaan_id' => $perusahaan->id, 'kegiatan_id' => $events[$idx]->id]);
        }

        $data = TopEventWidget::data();

        $this->assertCount(5, $data['rows']);
        // Data terurut desc: 5,4,3,2,1; peringkat dan lebar bar teratas 100%
        $this->assertSame([5, 4, 3, 2, 1], array_column($data['rows'], 'count'));
        $this->assertSame([1, 2, 3, 4, 5], array_column($data['rows'], 'rank'));
        $this->assertSame(100.0, $data['rows'][0]['width']);
        $this->assertSame(15, $data['totalTop']);
        $this->assertMatchesRegularExpression('/^#[0-9A-Fa-f]{6}$/', $data['rows'][0]['hex']);

        $this->actingAs(User::factory()->admin()->create());
        Livewire::test(TopEventWidget::class)
            ->assertOk()
            ->assertSee('Top 5 Event Terbesar')
            ->assertSee('Lihat Rincian Event');
    }

    #[Test]
    public function banner_sambutan_render_dengan_nama_pengguna(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Budi Santoso']);
        $this->actingAs($admin);

        Livewire::test(SambutanDashboard::class)
            ->assertOk()
            ->assertSee('Selamat datang kembali')
            ->assertSee('Budi Santoso');
    }

    #[Test]
    public function tabel_kontak_terbaru_menampilkan_pic_terbaru(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $perusahaan = Perusahaan::factory()->create();
        Kontak::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'nama' => 'PIC Pertama',
        ]);
        Kontak::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'nama' => 'PIC Kedua',
        ]);

        Livewire::test(KontakPerluDicekWidget::class)
            ->assertOk()
            ->assertSee('Kontak PIC Terbaru')
            ->assertSee('PIC Pertama')
            ->assertSee('PIC Kedua');
    }
}
