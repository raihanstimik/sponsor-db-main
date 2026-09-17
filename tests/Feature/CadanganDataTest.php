<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\CadanganData;
use App\Models\KategoriKegiatan;
use App\Models\Perusahaan;
use App\Models\User;
use App\Services\BackupDataService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;
use ZipArchive;

class CadanganDataTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // Bersihkan folder backup pengujian
        $dir = storage_path('app/backups');
        if (File::isDirectory($dir)) {
            File::deleteDirectory($dir);
        }

        parent::tearDown();
    }

    #[Test]
    public function tamu_diarahkan_ke_login_saat_akses_cadangan_data(): void
    {
        $this->get('/admin/cadangan-data')->assertRedirect('/admin/login');
    }

    #[Test]
    public function karyawan_dilarang_mengakses_cadangan_data(): void
    {
        $karyawan = User::factory()->karyawan()->create();
        $this->actingAs($karyawan);

        $this->get('/admin/cadangan-data')->assertForbidden();
        $this->assertFalse(CadanganData::canAccess());
    }

    #[Test]
    public function admin_dapat_mengakses_halaman_cadangan_data(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $this->get('/admin/cadangan-data')->assertOk();
        $this->assertTrue(CadanganData::canAccess());

        Livewire::test(CadanganData::class)
            ->assertOk()
            ->assertSee('Manajemen Cadangan & Pemulihan Basis Data')
            ->assertSee('Buat Cadangan Sekarang')
            ->assertSee('Unggah Berkas Cadangan')
            ->assertSee('Penyimpanan Cadangan');
    }

    #[Test]
    public function backup_service_dapat_membuat_snapshot_zip_dan_membaca_manifest(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Perusahaan::factory()->create(['nama_standar' => 'PT Bio Farma Persero']);
        KategoriKegiatan::factory()->create(['nama_kategori' => 'Vaksinologi']);

        $service = app(BackupDataService::class);
        $result = $service->createBackup('Snapshot Uji', 'Admin Test');

        $this->assertFileExists($result['path']);
        $this->assertGreaterThan(0, $result['total_records']);

        $list = $service->listBackups();
        $this->assertCount(1, $list);
        $this->assertSame($result['filename'], $list[0]['filename']);
        $this->assertSame('Admin Test', $list[0]['created_by']);
        $this->assertSame('Snapshot Uji', $list[0]['catatan']);

        // Uji unduh
        $downloadResponse = $service->downloadBackup($result['filename']);
        $this->assertSame(200, $downloadResponse->getStatusCode());

        // Uji hapus
        $deleted = $service->deleteBackup($result['filename']);
        $this->assertTrue($deleted);
        $this->assertFileDoesNotExist($result['path']);
    }

    #[Test]
    public function restore_secara_otomatis_membuat_pre_restore_safety_snapshot(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        Perusahaan::factory()->create(['nama_standar' => 'PT Kalbe Farma Tbk']);

        $service = app(BackupDataService::class);
        $backup1 = $service->createBackup('Cadangan Awal');

        // Buat data baru setelah backup awal
        Perusahaan::factory()->create(['nama_standar' => 'PT Kimia Farma']);

        // Jalankan restore dari backup awal
        $restoreResult = $service->restoreBackup($backup1['filename']);
        $this->assertTrue($restoreResult['success']);

        // Periksa bahwa daftar backup sekarang memiliki 2 file:
        // 1 backup awal, dan 1 pre-restore safety snapshot otomatis
        $backups = $service->listBackups();
        $this->assertCount(2, $backups);

        $hasPreRestore = false;
        foreach ($backups as $b) {
            if ($b['is_pre_restore']) {
                $hasPreRestore = true;
                $this->assertStringContainsString('prerestore', $b['filename']);
            }
        }
        $this->assertTrue($hasPreRestore, 'Pre-restore safety snapshot harus otomatis tercipta.');
    }

    #[Test]
    public function restore_menolak_berkas_dengan_checksum_sha256_yang_tidak_cocok(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $service = app(BackupDataService::class);
        $result = $service->createBackup('Uji Corrupt Checksum');

        // Rusak file database.sql di dalam zip
        $zip = new ZipArchive();
        $zip->open($result['path']);
        $zip->addFromString('database.sql', '-- modified content corrupted');
        $zip->close();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('checksum SHA-256 tidak cocok');

        $service->restoreBackup($result['filename']);
    }

    #[Test]
    public function backup_service_dapat_mengimpor_berkas_zip_dan_menolak_berkas_ilegal(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $service = app(BackupDataService::class);
        $snapshot = $service->createBackup('Cadangan Asli Untuk Ekspor');

        // Simulasikan file yang diunggah
        $uploadedZip = new UploadedFile(
            $snapshot['path'],
            'arsip_eksternal.zip',
            'application/zip',
            null,
            true
        );

        $importResult = $service->importUploadedBackup($uploadedZip);
        $this->assertFileExists($importResult['path']);
        $this->assertGreaterThan(0, $importResult['total_records']);

        // Uji tolak file ekstensi ilegal
        $fakeTxt = UploadedFile::fake()->create('malicious.txt', 100);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Hanya berkas arsip .zip yang diperbolehkan');

        $service->importUploadedBackup($fakeTxt);
    }

    #[Test]
    public function livewire_dapat_membuat_mengunggah_dan_menghapus_cadangan(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Super Admin Test']);
        $this->actingAs($admin);

        Livewire::test(CadanganData::class)
            ->set('catatanBaru', 'Cadangan Pra-Rilis')
            ->call('buatCadangan')
            ->assertHasNoErrors();

        $service = app(BackupDataService::class);
        $backups = $service->listBackups();
        $this->assertCount(1, $backups);

        $filename = $backups[0]['filename'];

        Livewire::test(CadanganData::class)
            ->call('hapusCadangan', $filename)
            ->assertHasNoErrors();

        $this->assertCount(0, $service->listBackups());
    }

    #[Test]
    public function aktivitas_cadangan_tercatat_di_activity_log(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Auditor Admin']);
        $this->actingAs($admin);

        $service = app(BackupDataService::class);
        $backup = $service->createBackup('Audit Test');

        $service->downloadBackup($backup['filename']);
        $service->deleteBackup($backup['filename']);

        $logs = Activity::where('log_name', 'backup')->get();
        $this->assertGreaterThanOrEqual(3, $logs->count());

        $actions = $logs->pluck('description')->all();
        $this->assertContains('Cadangan basis data dibuat', $actions);
        $this->assertContains('Berkas cadangan diunduh', $actions);
        $this->assertContains('Berkas cadangan dihapus', $actions);
    }
}

