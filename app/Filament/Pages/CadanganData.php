<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\BackupDataService;
use BackedEnum;
use Exception;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use UnitEnum;

class CadanganData extends Page
{
    use WithFileUploads;

    protected string $view = 'filament.pages.cadangan-data';

    protected static ?string $slug = 'cadangan-data';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCircleStack;

    protected static ?string $navigationLabel = 'Cadangan Data';

    protected static ?string $title = 'Manajemen Cadangan & Pemulihan Basis Data';

    protected static string|UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 4;

    public ?string $catatanBaru = null;

    public bool $isCreating = false;

    public $berkasUpload = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function buatCadangan(): void
    {
        $this->isCreating = true;

        try {
            /** @var BackupDataService $backupService */
            $backupService = app(BackupDataService::class);
            $result = $backupService->createBackup($this->catatanBaru, auth()->user()?->name);

            $this->catatanBaru = null;

            Notification::make()
                ->title('Cadangan Berhasil Dibuat')
                ->body("File snapshot {$result['filename']} tersimpan ({$result['total_records']} total data).")
                ->success()
                ->send();

            if (auth()->user()) {
                app(\App\Services\AppNotificationService::class)->notifyBackupBerhasil(
                    auth()->user(),
                    $result['filename'],
                    $result['size'] ?? 'snapshot'
                );
            }
        } catch (Exception $e) {
            Notification::make()
                ->title('Pembuatan Cadangan Gagal')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } finally {
            $this->isCreating = false;
        }
    }

    public function unggahCadangan(): void
    {
        $this->validate([
            'berkasUpload' => 'required|file|mimes:zip|max:51200',
        ], [
            'berkasUpload.required' => 'Pilih berkas ZIP cadangan terlebih dahulu.',
            'berkasUpload.mimes' => 'Hanya berkas format .zip yang diperbolehkan.',
            'berkasUpload.max' => 'Ukuran berkas maksimal adalah 50 MB.',
        ]);

        try {
            /** @var BackupDataService $backupService */
            $backupService = app(BackupDataService::class);
            $result = $backupService->importUploadedBackup($this->berkasUpload);

            $this->berkasUpload = null;

            Notification::make()
                ->title('Berkas Cadangan Berhasil Diunggah')
                ->body("Arsip {$result['filename']} berhasil diverifikasi dan disimpan ke server ({$result['total_records']} data).")
                ->success()
                ->send();
        } catch (Exception $e) {
            Notification::make()
                ->title('Unggah Cadangan Gagal')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function unduhCadangan(string $filename): BinaryFileResponse
    {
        /** @var BackupDataService $backupService */
        $backupService = app(BackupDataService::class);

        return $backupService->downloadBackup($filename);
    }

    public function hapusCadangan(string $filename): void
    {
        /** @var BackupDataService $backupService */
        $backupService = app(BackupDataService::class);
        $success = $backupService->deleteBackup($filename);

        if ($success) {
            Notification::make()
                ->title('Cadangan Dihapus')
                ->body("File {$filename} berhasil dihapus dari server.")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Gagal Menghapus')
                ->body('File tidak dapat ditemukan atau gagal dihapus.')
                ->danger()
                ->send();
        }
    }

    public function pulihkanCadangan(string $filename): void
    {
        try {
            /** @var BackupDataService $backupService */
            $backupService = app(BackupDataService::class);
            $result = $backupService->restoreBackup($filename);

            Notification::make()
                ->title('Pemulihan Selesai')
                ->body($result['message'] . ' Snapshot pengaman darurat (pre-restore) juga telah dibuat otomatis.')
                ->success()
                ->send();

            if (auth()->user()) {
                app(\App\Services\AppNotificationService::class)->notifyRestoreBerhasil(
                    auth()->user(),
                    $filename
                );
            }
        } catch (Exception $e) {
            Notification::make()
                ->title('Pemulihan Gagal')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function bersihkanCadanganLama(): void
    {
        /** @var BackupDataService $backupService */
        $backupService = app(BackupDataService::class);
        $deleted = $backupService->cleanOldBackups(30);

        Notification::make()
            ->title('Pembersihan Selesai')
            ->body("Sebanyak {$deleted} file cadangan usang (> 30 hari) telah dibersihkan.")
            ->info()
            ->send();
    }

    protected function getViewData(): array
    {
        /** @var BackupDataService $backupService */
        $backupService = app(BackupDataService::class);

        return [
            'backups' => $backupService->listBackups(),
            'storage' => $backupService->totalStorageUsage(),
            'dbDriver' => config('database.default'),
            'totalTables' => count(BackupDataService::TABLES),
        ];
    }
}

