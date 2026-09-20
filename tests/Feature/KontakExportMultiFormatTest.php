<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Kontaks\Pages\ListKontaks;
use App\Models\KategoriKegiatan;
use App\Models\Kegiatan;
use App\Models\Kontak;
use App\Models\Perusahaan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class KontakExportMultiFormatTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function ekspor_excel_menghasilkan_berkas_xlsx_valid_dan_header_benar(): void
    {
        $user = User::factory()->admin()->create();
        $perusahaan = Perusahaan::factory()->create(['nama_standar' => 'PT Bio Farma']);
        $kontak = Kontak::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'nama' => 'Dr. Hendra Gunawan',
            'no_telepon' => '081234567890',
        ]);

        $response = $this->actingAs($user)
            ->get(route('kontaks.export', ['format' => 'xlsx']))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->assertNotEmpty($response->streamedContent());
    }

    #[Test]
    public function ekspor_csv_mencegah_formula_injection_cwe_1236(): void
    {
        $user = User::factory()->admin()->create();
        $perusahaan = Perusahaan::factory()->create(['nama_standar' => '=CMD|calc.exe']);
        Kontak::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'nama' => '=SUM(A1:A10)',
            'no_telepon' => '081234567890',
        ]);

        $response = $this->actingAs($user)
            ->get(route('kontaks.export', ['format' => 'csv']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        // Formula injection harus dinetralkan dengan tanda petik tunggal di depan
        $this->assertStringContainsString("'=SUM(A1:A10)", $content);
        $this->assertStringContainsString("'=CMD|calc.exe", $content);
    }

    #[Test]
    public function ekspor_json_menghasilkan_struktur_payload_dan_meta_lengkap(): void
    {
        $user = User::factory()->admin()->create();
        $perusahaan = Perusahaan::factory()->create(['nama_standar' => 'PT Kimia Sehat']);
        Kontak::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'nama' => 'Rina Wijaya',
            'no_telepon' => '08111222333',
        ]);

        $response = $this->actingAs($user)
            ->get(route('kontaks.export', ['format' => 'json', 'columns' => ['status_format_valid', 'catatan']]))
            ->assertOk()
            ->assertHeader('content-type', 'application/json; charset=UTF-8');

        $json = json_decode($response->streamedContent(), true);

        $this->assertIsArray($json);
        $this->assertArrayHasKey('meta', $json);
        $this->assertArrayHasKey('data', $json);
        $this->assertEquals(1, $json['meta']['total_records']);
        $this->assertEquals('Rina Wijaya', $json['data'][0]['nama_pic']);
        $this->assertEquals('PT Kimia Sehat', $json['data'][0]['perusahaan']);
        $this->assertArrayHasKey('status_format_valid', $json['data'][0]);
    }

    #[Test]
    public function ekspor_menghormati_cakupan_semua_vs_filter(): void
    {
        $user = User::factory()->admin()->create();
        $perusahaanA = Perusahaan::factory()->create(['nama_standar' => 'PT Alfa']);
        $perusahaanB = Perusahaan::factory()->create(['nama_standar' => 'CV Beta']);

        Kontak::factory()->create(['perusahaan_id' => $perusahaanA->id, 'nama' => 'Budi']);
        Kontak::factory()->create(['perusahaan_id' => $perusahaanB->id, 'nama' => 'Siti']);

        // Filter pencarian
        $resFilter = $this->actingAs($user)
            ->get(route('kontaks.export', ['format' => 'csv', 'q' => 'Siti', 'scope' => 'filtered']))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('Siti', $resFilter);
        $this->assertStringNotContainsString('Budi', $resFilter);

        // Cakupan 'all' mengabaikan q
        $resAll = $this->actingAs($user)
            ->get(route('kontaks.export', ['format' => 'csv', 'q' => 'Siti', 'scope' => 'all']))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('Siti', $resAll);
        $this->assertStringContainsString('Budi', $resAll);
    }

    #[Test]
    public function ekspor_mencatat_riwayat_ke_activity_log(): void
    {
        $user = User::factory()->admin()->create(['name' => 'Auditor Admin']);
        Kontak::factory()->create();

        $this->actingAs($user)
            ->get(route('kontaks.export', ['format' => 'xlsx', 'scope' => 'all']))
            ->assertOk();

        $log = Activity::where('log_name', 'export')->latest()->first();

        $this->assertNotNull($log, 'Aktivitas ekspor harus tercatat di log');
        $this->assertEquals('kontak.export', $log->event);
        $this->assertEquals($user->id, $log->causer_id);
        $this->assertEquals('xlsx', $log->properties['format']);
        $this->assertEquals('all', $log->properties['scope']);
    }

    #[Test]
    public function livewire_kontak_table_memiliki_aksi_export_dengan_modal(): void
    {
        $user = User::factory()->admin()->create();
        Kontak::factory()->create();

        $this->actingAs($user);

        Livewire::test(ListKontaks::class)
            ->assertOk()
            ->assertTableActionExists('export')
            ->callTableAction('export', data: [
                'format' => 'csv',
                'scope' => 'all',
                'columns' => ['status_format_valid'],
            ])
            ->assertHasNoTableActionErrors();
    }
}
