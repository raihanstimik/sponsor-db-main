<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Kontaks\Pages\CreateKontak;
use App\Filament\Resources\Kontaks\Pages\ListKontaks;
use App\Filament\Resources\Kontaks\Tables\KontaksTable;
use App\Models\Kegiatan;
use App\Models\Kontak;
use App\Models\Perusahaan;
use App\Models\User;
use App\Support\PhoneNormalizer;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserExperienceAuditTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function persona_a_ibu_sri_dapat_input_nomor_tanpa_nol_dan_tersimpan_dengan_normalisasi(): void
    {
        $admin = User::factory()->admin()->create();
        $perusahaan = Perusahaan::factory()->create(['nama_standar' => 'PT Kalbe Farma']);

        $this->actingAs($admin);

        // Ibu Sri mengetik tanpa 0 di depan (mis. copy dari Excel: '8123456789')
        Livewire::test(CreateKontak::class)
            ->fillForm([
                'perusahaan_id' => $perusahaan->id,
                'nama' => 'Ibu Sri Handayani',
                'no_telepon' => '8123456789',
                'email' => 'sri@kalbe.co.id',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $kontak = Kontak::where('nama', 'Ibu Sri Handayani')->first();
        $this->assertNotNull($kontak);
        // Sistem otomatis menormalkan menjadi format 628123456789
        $this->assertSame('628123456789', $kontak->no_telepon);
        $this->assertTrue($kontak->status_format_valid);
    }

    #[Test]
    public function persona_a_ibu_sri_melihat_indikator_filter_pencarian_aktif_dan_dapat_meresetnya(): void
    {
        $admin = User::factory()->admin()->create();
        $perusahaan = Perusahaan::factory()->create(['nama_standar' => 'PT Novartis Indonesia']);
        $budi = Kontak::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'nama' => 'dr. Budi Santoso',
            'no_telepon' => '6281234567890',
        ]);
        $siti = Kontak::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'nama' => 'Siti Nurhaliza',
            'no_telepon' => '6281299988877',
        ]);

        $this->actingAs($admin);

        Livewire::test(ListKontaks::class)
            ->filterTable('cari', ['q' => 'Budi'])
            ->assertCanSeeTableRecords([$budi])
            ->assertCanNotSeeTableRecords([$siti])
            ->filterTable('cari', ['q' => null])
            ->assertCanSeeTableRecords([$budi, $siti]);
    }

    #[Test]
    public function persona_b_reza_dapat_akses_url_whatsapp_valid_dan_landline_tidak_muncul_wa(): void
    {
        $hpKontak = Kontak::factory()->create([
            'nama' => 'dr. Reza PIC',
            'no_telepon' => '081298765432',
        ]);

        $kantorKontak = Kontak::factory()->create([
            'nama' => 'Sekretariat Kantor',
            'no_telepon' => '021-5551234',
        ]);

        // Cek dukungan WhatsApp
        $this->assertTrue(PhoneNormalizer::isWhatsappSupported($hpKontak->no_telepon));
        $this->assertStringStartsWith('https://wa.me/6281298765432', PhoneNormalizer::whatsappUrl($hpKontak->no_telepon));

        // Nomor telepon kantor tidak mendukung WhatsApp
        $this->assertFalse(PhoneNormalizer::isWhatsappSupported($kantorKontak->no_telepon));
        $this->assertSame('#', PhoneNormalizer::whatsappUrl($kantorKontak->no_telepon));
    }

    #[Test]
    public function persona_b_reza_menemukan_kontak_dengan_smart_search_multi_token(): void
    {
        $admin = User::factory()->admin()->create();
        $perusahaan = Perusahaan::factory()->create(['nama_standar' => 'PT Sanofi Aventis']);
        $kontak = Kontak::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'nama' => 'Ahmad Fauzi',
            'no_telepon' => '628111222333',
        ]);

        $this->actingAs($admin);

        // Pencarian multi-token: nama PIC + nama perusahaan
        Livewire::test(ListKontaks::class)
            ->filterTable('cari', ['q' => 'Fauzi Sanofi'])
            ->assertCanSeeTableRecords([$kontak]);
    }

    #[Test]
    public function persona_c_kevin_dapat_menautkan_banyak_event_ke_satu_pic_tanpa_duplikasi(): void
    {
        $admin = User::factory()->admin()->create();
        $perusahaan = Perusahaan::factory()->create(['nama_standar' => 'PT Dexa Medica']);
        $event1 = Kegiatan::factory()->create(['nama_event' => 'PIT PERDAMI 2026']);
        $event2 = Kegiatan::factory()->create(['nama_event' => 'INDAAC 2026']);

        $this->actingAs($admin);

        Livewire::test(CreateKontak::class)
            ->fillForm([
                'perusahaan_id' => $perusahaan->id,
                'nama' => 'dr. Kevin Wijaya',
                'no_telepon' => '081234000111',
                'kegiatans' => [$event1->id, $event2->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $kontak = Kontak::where('nama', 'dr. Kevin Wijaya')->first();
        $this->assertNotNull($kontak);
        $this->assertCount(2, $kontak->kegiatans);
        // Memastikan tidak ada duplikasi baris kontak di tabel utama
        $this->assertEquals(1, Kontak::where('nama', 'dr. Kevin Wijaya')->count());
    }
}
