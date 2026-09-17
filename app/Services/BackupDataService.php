<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class BackupDataService
{
    /**
     * Urutan tabel yang dicadangkan disusun secara topologis berdasarkan pohon dependensi foreign key:
     * Tabel induk di-insert lebih dulu, tabel anak di-insert kemudian.
     * Pengosongan tabel saat pemulihan dilakukan secara terbalik (anak sebelum induk).
     *
     * @var array<int, string>
     */
    public const TABLES = [
        'divisis',
        'users',
        'roles',
        'permissions',
        'role_has_permissions',
        'model_has_roles',
        'model_has_permissions',
        'perusahaans',
        'kategori_kegiatans',
        'kegiatans',
        'kontaks',
        'activity_log',
    ];

    protected string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
        if (! File::isDirectory($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true, true);
        }
    }

    /**
     * Membuat snapshot cadangan baru berupa file ZIP berisi SQL dump, manifest, dan JSON data.
     *
     * @return array{filename: string, path: string, total_records: int, size: int}
     */
    public function createBackup(?string $catatan = null, ?string $userName = null, bool $isPreRestore = false): array
    {
        if (! ini_get('safe_mode')) {
            @set_time_limit(300);
        }

        $prefix = $isPreRestore ? 'icm_backup_prerestore_' : 'icm_backup_';
        $timestamp = now()->format('Y-m-d_His');
        $filename = "{$prefix}{$timestamp}.zip";
        $zipPath = "{$this->backupDir}/{$filename}";

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Gagal membuat arsip cadangan pada: {$zipPath}");
        }

        $driver = config('database.default');
        $sqlLines = [];
        $sqlLines[] = "-- Indonesia Congress Management (ICM) Database Snapshot";
        $sqlLines[] = "-- Waktu Pembuatan: " . now()->toIso8601String();
        $sqlLines[] = "-- Driver Basis Data: " . $driver;
        $sqlLines[] = "-- Jenis Cadangan: " . ($isPreRestore ? 'Snapshot Darurat Pre-Restore' : 'Cadangan Reguler');
        $sqlLines[] = "";

        if ($driver === 'sqlite') {
            $sqlLines[] = "PRAGMA foreign_keys = OFF;";
        } else {
            $sqlLines[] = "SET FOREIGN_KEY_CHECKS = 0;";
        }
        $sqlLines[] = "";

        $tableCounts = [];
        $totalRecords = 0;

        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $rows = DB::table($table)->get();
            $count = $rows->count();
            $tableCounts[$table] = $count;
            $totalRecords += $count;

            // Simpan file JSON per tabel di dalam folder data/
            $zip->addFromString("data/{$table}.json", (string) json_encode($rows->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Hasilkan SQL statements
            $sqlLines[] = "-- --------------------------------------------------------";
            $sqlLines[] = "-- Data Tabel: {$table} ({$count} baris)";
            $sqlLines[] = "-- --------------------------------------------------------";
            $sqlLines[] = "DELETE FROM {$table};";

            if ($count > 0) {
                foreach ($rows as $row) {
                    $rowArray = (array) $row;
                    $cols = array_keys($rowArray);
                    $escapedCols = implode(', ', array_map(fn ($c) => "`{$c}`", $cols));

                    $values = [];
                    foreach ($rowArray as $val) {
                        if ($val === null) {
                            $values[] = 'NULL';
                        } elseif (is_int($val) || is_float($val)) {
                            $values[] = (string) $val;
                        } elseif (is_bool($val)) {
                            $values[] = $val ? '1' : '0';
                        } else {
                            $escaped = str_replace("'", "''", (string) $val);
                            $values[] = "'{$escaped}'";
                        }
                    }

                    $valuesStr = implode(', ', $values);
                    $sqlLines[] = "INSERT INTO {$table} ({$escapedCols}) VALUES ({$valuesStr});";
                }
            }
            $sqlLines[] = "";
        }

        if ($driver === 'sqlite') {
            $sqlLines[] = "PRAGMA foreign_keys = ON;";
        } else {
            $sqlLines[] = "SET FOREIGN_KEY_CHECKS = 1;";
        }

        $sqlContent = implode("\n", $sqlLines);
        $zip->addFromString('database.sql', $sqlContent);

        // Buat manifest.json dengan metadata dan checksum SHA-256
        $manifest = [
            'app_name' => 'ICM Sponsor Database',
            'version' => '2.0',
            'filename' => $filename,
            'is_pre_restore' => $isPreRestore,
            'created_at' => now()->toIso8601String(),
            'created_by' => $userName ?? auth()->user()?->name ?? 'Administrator',
            'driver' => $driver,
            'catatan' => $catatan,
            'table_counts' => $tableCounts,
            'total_records' => $totalRecords,
            'sha256_sql' => hash('sha256', $sqlContent),
        ];

        $zip->addFromString('manifest.json', (string) json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $zip->close();

        $size = File::size($zipPath);

        $this->logActivity(
            $isPreRestore ? 'Snapshot darurat otomatis dibuat sebelum pemulihan' : 'Cadangan basis data dibuat',
            [
                'filename' => $filename,
                'total_records' => $totalRecords,
                'size_bytes' => $size,
                'catatan' => $catatan,
            ]
        );

        return [
            'filename' => $filename,
            'path' => $zipPath,
            'total_records' => $totalRecords,
            'size' => $size,
        ];
    }

    /**
     * Membaca riwayat seluruh file arsip cadangan yang tersimpan di server.
     *
     * @return array<int, array{
     *     filename: string,
     *     size_bytes: int,
     *     size_formatted: string,
     *     created_at: Carbon,
     *     created_by: string,
     *     total_records: int,
     *     table_summary: string,
     *     catatan: ?string,
     *     is_pre_restore: bool
     * }>
     */
    public function listBackups(): array
    {
        if (! File::isDirectory($this->backupDir)) {
            return [];
        }

        $files = File::glob("{$this->backupDir}/icm_backup_*.zip");
        $backups = [];

        foreach ($files as $filePath) {
            $filename = basename($filePath);
            $size = File::size($filePath);
            $modified = Carbon::createFromTimestamp(File::lastModified($filePath));

            $meta = $this->readManifestFromZip($filePath);

            $kontakCount = $meta['table_counts']['kontaks'] ?? null;
            $perusahaanCount = $meta['table_counts']['perusahaans'] ?? null;
            $eventCount = $meta['table_counts']['kegiatans'] ?? null;

            $summaryParts = [];
            if ($kontakCount !== null) {
                $summaryParts[] = number_format($kontakCount, 0, ',', '.') . ' Kontak';
            }
            if ($perusahaanCount !== null) {
                $summaryParts[] = number_format($perusahaanCount, 0, ',', '.') . ' Perusahaan';
            }
            if ($eventCount !== null) {
                $summaryParts[] = number_format($eventCount, 0, ',', '.') . ' Event';
            }

            $tableSummary = $summaryParts !== [] ? implode(' • ', $summaryParts) : 'Lengkap';

            $backups[] = [
                'filename' => $filename,
                'size_bytes' => $size,
                'size_formatted' => $this->formatBytes($size),
                'created_at' => isset($meta['created_at']) ? Carbon::parse($meta['created_at']) : $modified,
                'created_by' => $meta['created_by'] ?? 'Sistem',
                'total_records' => (int) ($meta['total_records'] ?? 0),
                'table_summary' => $tableSummary,
                'catatan' => $meta['catatan'] ?? null,
                'is_pre_restore' => (bool) ($meta['is_pre_restore'] ?? str_contains($filename, 'prerestore')),
            ];
        }

        // Urutkan dari cadangan terbaru
        usort($backups, fn ($a, $b) => $b['created_at']->getTimestamp() <=> $a['created_at']->getTimestamp());

        return $backups;
    }

    /**
     * Mengunduh file arsip cadangan.
     */
    public function downloadBackup(string $filename): BinaryFileResponse
    {
        $safeName = basename($filename);
        $path = "{$this->backupDir}/{$safeName}";

        if (! File::exists($path)) {
            abort(404, 'File cadangan tidak ditemukan.');
        }

        $this->logActivity('Berkas cadangan diunduh', ['filename' => $safeName]);

        return response()->download($path, $safeName, [
            'Content-Type' => 'application/zip',
        ]);
    }

    /**
     * Menghapus file arsip cadangan dari server.
     */
    public function deleteBackup(string $filename): bool
    {
        $safeName = basename($filename);
        $path = "{$this->backupDir}/{$safeName}";

        if (File::exists($path)) {
            $deleted = File::delete($path);
            if ($deleted) {
                $this->logActivity('Berkas cadangan dihapus', ['filename' => $safeName]);
            }

            return $deleted;
        }

        return false;
    }

    /**
     * Memulihkan data dari arsip cadangan terpilih.
     * Sebelum pemulihan dieksekusi, sistem membuat snapshot pengaman darurat (Pre-restore snapshot)
     * dan memverifikasi integritas checksum SHA-256.
     *
     * @return array{success: bool, total_restored: int, message: string}
     */
    public function restoreBackup(string $filename, bool $createSafetySnapshot = true): array
    {
        if (! ini_get('safe_mode')) {
            @set_time_limit(300);
        }

        $safeName = basename($filename);
        $path = "{$this->backupDir}/{$safeName}";

        if (! File::exists($path)) {
            throw new Exception('Berkas arsip cadangan tidak ditemukan.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new Exception('Gagal membuka berkas ZIP cadangan.');
        }

        // 1. Validasi Manifest & Identitas Aplikasi
        $manifestContent = $zip->getFromName('manifest.json');
        if ($manifestContent === false || $manifestContent === '') {
            $zip->close();
            throw new Exception('Berkas cadangan tidak valid: metadata manifest.json tidak ditemukan.');
        }

        $manifest = json_decode($manifestContent, true);
        if (! is_array($manifest) || ($manifest['app_name'] ?? null) !== 'ICM Sponsor Database') {
            $zip->close();
            throw new Exception('Berkas cadangan tidak valid atau bukan merupakan arsip resmi ICM Sponsor Database.');
        }

        // 2. Verifikasi Integritas SHA-256 Checksum
        $expectedSha = $manifest['sha256_sql'] ?? null;
        $sqlContent = $zip->getFromName('database.sql');
        if ($sqlContent === false) {
            $zip->close();
            throw new Exception('Berkas cadangan rusak: file database.sql tidak ditemukan di dalam arsip.');
        }

        if ($expectedSha !== null && hash('sha256', $sqlContent) !== $expectedSha) {
            $zip->close();
            throw new Exception('Integritas berkas cadangan tidak valid (checksum SHA-256 tidak cocok). Berkas kemungkinan rusak saat unduh/unggah.');
        }

        $totalRestored = (int) ($manifest['total_records'] ?? 0);

        // 3. Buat Snapshot Darurat Otomatis (Pre-restore Safety Snapshot)
        if ($createSafetySnapshot && ! str_contains($safeName, 'prerestore')) {
            $this->createBackup(
                catatan: "Snapshot otomatis sebelum memulihkan arsip {$safeName}",
                userName: auth()->user()?->name ?? 'Sistem (Auto Pre-Restore)',
                isPreRestore: true
            );
        }

        // 4. Eksekusi pemulihan data dalam transaksi basis data
        DB::transaction(function () use ($zip): void {
            $driver = config('database.default');
            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
            }

            // Kosongkan tabel dengan urutan aman (anak sebelum induk)
            foreach (array_reverse(self::TABLES) as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->delete();
                }
            }

            // Masukkan data per tabel dari JSON snapshot (induk sebelum anak)
            foreach (self::TABLES as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }

                $jsonContent = $zip->getFromName("data/{$table}.json");
                if ($jsonContent !== false && $jsonContent !== '') {
                    $records = json_decode($jsonContent, true);
                    if (is_array($records) && count($records) > 0) {
                        foreach (array_chunk($records, 100) as $chunk) {
                            DB::table($table)->insert($chunk);
                        }
                    }
                }
            }

            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            }
        });

        $zip->close();

        $this->logActivity('Basis data berhasil dipulihkan dari cadangan', [
            'filename' => $safeName,
            'total_restored' => $totalRestored,
        ]);

        return [
            'success' => true,
            'total_restored' => $totalRestored,
            'message' => "Pemulihan basis data berhasil. Sebanyak {$totalRestored} data dipulihkan dari cadangan {$safeName}.",
        ];
    }

    /**
     * Memvalidasi dan mengimpor berkas ZIP cadangan yang diunggah pengguna ke server.
     *
     * @return array{filename: string, path: string, total_records: int}
     */
    public function importUploadedBackup(UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if ($ext !== 'zip') {
            throw new Exception('Format berkas tidak valid. Hanya berkas arsip .zip yang diperbolehkan.');
        }

        $realPath = $file->getRealPath();
        $zip = new ZipArchive();
        if ($zip->open($realPath) !== true) {
            throw new Exception('Berkas ZIP tidak dapat dibuka atau rusak.');
        }

        $manifestContent = $zip->getFromName('manifest.json');
        if ($manifestContent === false || $manifestContent === '') {
            $zip->close();
            throw new Exception('Berkas yang diunggah tidak memiliki manifest.json yang sah.');
        }

        $manifest = json_decode($manifestContent, true);
        $expectedSha = $manifest['sha256_sql'] ?? null;
        $sqlContent = $zip->getFromName('database.sql');
        $zip->close();

        if (! is_array($manifest) || ($manifest['app_name'] ?? null) !== 'ICM Sponsor Database') {
            throw new Exception('Berkas yang diunggah bukan merupakan cadangan resmi dari ICM Sponsor Database.');
        }

        if ($expectedSha !== null && $sqlContent !== false && hash('sha256', $sqlContent) !== $expectedSha) {
            throw new Exception('Berkas yang diunggah terindikasi rusak (checksum SHA-256 tidak cocok).');
        }

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeBase = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $originalName) ?: 'icm_backup_upload';
        $targetFilename = "{$safeBase}.zip";
        $targetPath = "{$this->backupDir}/{$targetFilename}";

        // Cegah tabrakan nama file yang ada
        if (File::exists($targetPath)) {
            $targetFilename = "{$safeBase}_" . now()->format('His') . '.zip';
            $targetPath = "{$this->backupDir}/{$targetFilename}";
        }

        $file->move($this->backupDir, $targetFilename);

        $totalRecords = (int) ($manifest['total_records'] ?? 0);

        $this->logActivity('Berkas cadangan eksternal diunggah', [
            'filename' => $targetFilename,
            'total_records' => $totalRecords,
        ]);

        return [
            'filename' => $targetFilename,
            'path' => $targetPath,
            'total_records' => $totalRecords,
        ];
    }

    /**
     * Membersihkan file cadangan yang lebih tua dari N hari (kebijakan retensi).
     *
     * @return int Jumlah file yang dihapus
     */
    public function cleanOldBackups(int $days = 30): int
    {
        $files = File::glob("{$this->backupDir}/icm_backup_*.zip");
        $threshold = now()->subDays($days)->getTimestamp();
        $deleted = 0;

        foreach ($files as $filePath) {
            if (File::lastModified($filePath) < $threshold) {
                if (File::delete($filePath)) {
                    $deleted++;
                }
            }
        }

        if ($deleted > 0) {
            $this->logActivity('Pembersihan cadangan usang dijalankan', [
                'retention_days' => $days,
                'deleted_files_count' => $deleted,
            ]);
        }

        return $deleted;
    }

    /**
     * Total kapasitas penyimpanan yang digunakan oleh folder cadangan.
     *
     * @return array{count: int, bytes: int, formatted: string}
     */
    public function totalStorageUsage(): array
    {
        $files = File::glob("{$this->backupDir}/icm_backup_*.zip");
        $totalBytes = 0;
        foreach ($files as $f) {
            $totalBytes += File::size($f);
        }

        return [
            'count' => count($files),
            'bytes' => $totalBytes,
            'formatted' => $this->formatBytes($totalBytes),
        ];
    }

    protected function readManifestFromZip(string $zipPath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) === true) {
            $content = $zip->getFromName('manifest.json');
            $zip->close();
            if ($content !== false) {
                $decoded = json_decode($content, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        return [];
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1, ',', '.') . ' KB';
        }

        return $bytes . ' B';
    }

    /**
     * @param array<string, mixed> $properties
     */
    protected function logActivity(string $description, array $properties = []): void
    {
        if (class_exists(\Spatie\Activitylog\Models\Activity::class) && function_exists('activity')) {
            activity('backup')
                ->by(auth()->user())
                ->withProperties($properties)
                ->log($description);
        }
    }
}

