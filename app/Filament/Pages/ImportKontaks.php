<?php

namespace App\Filament\Pages;

use App\Models\Kontak;
use App\Services\AppNotificationService;
use App\Services\KontakImportService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportKontaks extends Page
{
    protected string $view = 'filament.pages.import-kontaks';

    protected static ?string $slug = 'import-data';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?string $navigationLabel = 'Import Data';

    protected static ?string $title = 'Import Data Kontak & Perusahaan';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('import', Kontak::class) ?? false;
    }

    /**
     * Import diakses dari dalam fitur Kontak (tombol header daftar kontak),
     * jadi halaman ini tidak muncul sebagai item navigasi tersendiri.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected static ?int $navigationSort = 2;

    public $file = null;

    /** @var array<int, string>|null Header kolom yang terdeteksi dari file */
    public ?array $detectedHeaders = null;

    /** @var array<int, array<int, string>>|null Contoh baris data untuk pratinjau pemetaan */
    public ?array $sampleRows = null;

    /** @var array<int, string> Pilihan pemetaan kolom (index => target_field) */
    public array $columnMapping = [];

    /** Menandakan sedang di langkah konfirmasi pemetaan kolom */
    public bool $mappingStep = false;

    /** @var array<int, array<string, mixed>>|null Baris mentah hasil ekstraksi (langkah pratinjau). */
    public ?array $rows = null;

    /** @var array<int, array<string, mixed>>|null Baris berklasifikasi (langkah analisis). */
    public ?array $previews = null;

    /** @var array<string, int> */
    public array $counts = [];

    public bool $saved = false;

    /** @var array{perusahaan_dibuat: int, kontak_dibuat: int, dilewati: int}|null */
    public ?array $saveResult = null;

    public string $searchRows = '';

    public string $sheetFilterRows = '';

    public string $searchPreviews = '';

    public string $sheetFilterPreviews = '';

    public string $statusFilterPreviews = '';

    public string $companyFilterPreviews = '';

    public ?int $editingIndex = null;

    public string $editNamaPerusahaan = '';

    public string $editIndustri = '';

    public string $editNama = '';

    public string $editNoTelepon = '';

    public string $editCatatan = '';

    public function startEdit(int $index): void
    {
        if ($this->previews === null || ! isset($this->previews[$index])) {
            return;
        }

        $row = $this->previews[$index];
        $this->editingIndex = $index;
        $this->editNamaPerusahaan = (string) ($row['nama_perusahaan'] ?? '');
        $this->editIndustri = (string) ($row['industri'] ?? '');
        $this->editNama = (string) ($row['nama'] ?? '');
        $this->editNoTelepon = (string) ($row['no_telepon_mentah'] ?? '');
        $this->editCatatan = (string) ($row['catatan'] ?? '');
    }

    public function cancelEdit(): void
    {
        $this->editingIndex = null;
    }

    public function saveEdit(): void
    {
        if ($this->rows === null || $this->editingIndex === null || ! isset($this->rows[$this->editingIndex])) {
            return;
        }

        $this->rows[$this->editingIndex] = array_merge($this->rows[$this->editingIndex], [
            'nama_perusahaan' => trim($this->editNamaPerusahaan),
            'industri' => trim($this->editIndustri),
            'nama' => trim($this->editNama),
            'no_telepon_mentah' => trim($this->editNoTelepon),
            'catatan' => trim($this->editCatatan),
        ]);

        $this->editingIndex = null;
        $this->reclassify();
    }

    public function deletePreview(int $index): void
    {
        if ($this->rows === null || ! isset($this->rows[$index])) {
            return;
        }

        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);

        if ($this->editingIndex !== null) {
            if ($this->editingIndex === $index) {
                $this->editingIndex = null;
            } elseif ($this->editingIndex > $index) {
                $this->editingIndex--;
            }
        }

        $this->reclassify();
    }

    protected function reclassify(): void
    {
        $this->previews = app(KontakImportService::class)->classify($this->rows);
        $this->counts = KontakImportService::summaryCounts($this->previews);
    }

    public function resetFilters(): void
    {
        $this->searchRows = '';
        $this->sheetFilterRows = '';
        $this->searchPreviews = '';
        $this->sheetFilterPreviews = '';
        $this->statusFilterPreviews = '';
        $this->companyFilterPreviews = '';
    }

    public function preview(): void
    {
        $this->validate(
            ['file' => ['required', 'file', 'max:10240', 'mimes:xlsx,xls,csv,tsv,ods']],
            [
                'file.required' => 'Pilih file terlebih dahulu. Jika baru memilih file, tunggu unggahan selesai lalu klik Pratinjau lagi.',
                'file.file' => 'File yang dipilih tidak valid.',
                'file.max' => 'Ukuran file maksimal 10 MB.',
                'file.mimes' => 'Format file harus: .xlsx, .xls, .csv, .tsv, atau .ods.',
            ]
        );

        $service = app(KontakImportService::class);
        $path = $this->file->getRealPath();
        $extension = $this->file instanceof TemporaryUploadedFile
            ? strtolower(pathinfo($this->file->getFilename(), PATHINFO_EXTENSION))
            : (string) $this->file->getClientOriginalExtension();

        $rows = $service->extractRows((string) $path, $extension);
        $structure = $service->detectStructure((string) $path, $extension);

        if (! empty($structure['headers'])) {
            $this->detectedHeaders = $structure['headers'];
            $this->sampleRows = $structure['samples'] ?? [];
            $this->columnMapping = $structure['mapping'] ?? [];
        } else {
            $this->detectedHeaders = [];
            $this->sampleRows = [];
            $this->columnMapping = [];
        }

        $rows = ! empty($this->columnMapping)
            ? $service->extractRows((string) $path, $extension, $this->columnMapping)
            : $service->extractRows((string) $path, $extension);

        if ($rows === []) {
            Notification::make()
                ->title('File tidak berisi data yang bisa diproses')
                ->body('Pastikan ada baris header lalu baris data di bawahnya. Contoh: nama_perusahaan, nama, no_telepon.')
                ->danger()
                ->send();

            return;
        }

        // Pratinjau sekaligus analisis: satu klik langsung menghasilkan
        // klasifikasi (baru/duplikat/junk) tanpa tombol "Analisis" terpisah.
        $this->rows = $rows;
        $this->mappingStep = false;
        $this->reclassify();
        $this->saved = false;
        $this->saveResult = null;
        $this->resetFilters();
    }

    public function applyMappingAndAnalyze(): void
    {
        if (! $this->file) {
            Notification::make()->title('File tidak ditemukan')->danger()->send();

            return;
        }

        $service = app(KontakImportService::class);
        $path = $this->file->getRealPath();
        $extension = $this->file instanceof TemporaryUploadedFile
            ? strtolower(pathinfo($this->file->getFilename(), PATHINFO_EXTENSION))
            : (string) $this->file->getClientOriginalExtension();

        $rows = $service->extractRows((string) $path, $extension, $this->columnMapping);

        if ($rows === []) {
            Notification::make()
                ->title('Tidak ada data yang dapat diekstrak dengan pemetaan kolom ini')
                ->body('Pastikan minimal satu kolom Perusahaan, Nama PIC, atau Nomor Telepon telah dipetakan.')
                ->warning()
                ->send();

            return;
        }

        $this->rows = $rows;
        $this->mappingStep = false;
        $this->reclassify();
        $this->saved = false;
        $this->saveResult = null;
        $this->resetFilters();
    }

    public function backToMapping(): void
    {
        if ($this->detectedHeaders) {
            $this->mappingStep = true;
            $this->previews = null;
            $this->counts = [];
            $this->resetFilters();
        } else {
            $this->backToPreview();
        }
    }

    public function analyze(): void
    {
        if (blank($this->rows)) {
            Notification::make()
                ->title('Belum ada data untuk dianalisis')
                ->body('Pratinjau file terlebih dahulu.')
                ->warning()
                ->send();

            return;
        }

        $this->previews = app(KontakImportService::class)->classify($this->rows);
        $this->counts = KontakImportService::summaryCounts($this->previews);
        $this->saved = false;
        $this->saveResult = null;
        $this->resetFilters();
    }

    public function backToPreview(): void
    {
        $this->previews = null;
        $this->counts = [];
        $this->saved = false;
        $this->saveResult = null;
        $this->resetFilters();
    }

    public function saveImport(): void
    {
        if ($this->previews === null || $this->saved) {
            return;
        }

        $result = app(KontakImportService::class)->save((int) auth()->id(), $this->previews);

        $this->saveResult = $result;
        $this->saved = true;
        $this->file = null;

        Notification::make()
            ->title(sprintf('Import selesai: %d kontak baru', $result['kontak_dibuat']))
            ->body(sprintf(
                'Perusahaan baru: %d · dilewati (duplikat/lengkap): %d',
                $result['perusahaan_dibuat'],
                $result['dilewati']
            ))
            ->success()
            ->send();

        if (auth()->user()) {
            app(AppNotificationService::class)->notifyImportSelesai(
                auth()->user(),
                $result['kontak_dibuat'],
                $result['perusahaan_dibuat'],
                $result['dilewati']
            );
        }
    }

    public function resetImport(): void
    {
        $this->file = null;
        $this->rows = null;
        $this->previews = null;
        $this->counts = [];
        $this->saved = false;
        $this->saveResult = null;
        $this->detectedHeaders = null;
        $this->sampleRows = null;
        $this->columnMapping = [];
        $this->mappingStep = false;
        $this->resetFilters();
    }

    /** @return array<int, array<string, mixed>> */
    public function filteredRows(): array
    {
        $rows = $this->rows ?? [];

        if ($this->sheetFilterRows !== '') {
            $rows = array_values(array_filter(
                $rows,
                fn (array $r): bool => (string) ($r['sheet'] ?? '') === $this->sheetFilterRows
            ));
        }

        if (trim($this->searchRows) !== '') {
            $q = mb_strtolower(trim($this->searchRows));
            $rows = array_values(array_filter(
                $rows,
                fn (array $r): bool => str_contains(mb_strtolower($this->rowSearchText($r)), $q)
            ));
        }

        return $rows;
    }

    protected function rowSearchText(array $r): string
    {
        return implode(' ', [
            (string) ($r['sheet'] ?? ''),
            (string) ($r['nama_perusahaan'] ?? ''),
            (string) ($r['nama'] ?? ''),
            (string) ($r['no_telepon_mentah'] ?? ''),
            (string) ($r['catatan'] ?? ''),
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    public function filteredPreviews(): array
    {
        $previews = $this->previews ?? [];

        if ($this->sheetFilterPreviews !== '') {
            $previews = array_filter(
                $previews,
                fn (array $p): bool => (string) ($p['sheet'] ?? '') === $this->sheetFilterPreviews
            );
        }

        if ($this->statusFilterPreviews !== '') {
            if ($this->statusFilterPreviews === 'duplikat') {
                $previews = array_filter(
                    $previews,
                    fn (array $p): bool => in_array($p['status_kontak'] ?? '', ['duplikat_telepon', 'duplikat_nama', 'duplikat_batch'], true)
                );
            } else {
                $previews = array_filter(
                    $previews,
                    fn (array $p): bool => ($p['status_kontak'] ?? '') === $this->statusFilterPreviews
                );
            }
        }

        if ($this->companyFilterPreviews !== '') {
            $previews = array_filter(
                $previews,
                fn (array $p): bool => ($p['perusahaan_status'] ?? '') === $this->companyFilterPreviews
            );
        }

        if (trim($this->searchPreviews) !== '') {
            $q = mb_strtolower(trim($this->searchPreviews));
            $previews = array_filter(
                $previews,
                fn (array $p): bool => str_contains(mb_strtolower($this->previewSearchText($p)), $q)
            );
        }

        return $previews;
    }

    public function setQuickFilter(string $status = '', string $company = ''): void
    {
        $this->statusFilterPreviews = $status;
        $this->companyFilterPreviews = $company;
    }

    protected function previewSearchText(array $p): string
    {
        return implode(' ', [
            (string) ($p['sheet'] ?? ''),
            (string) ($p['nama_perusahaan'] ?? ''),
            (string) ($p['perusahaan_nama_resmi'] ?? ''),
            (string) ($p['nama'] ?? ''),
            (string) ($p['no_telepon_mentah'] ?? ''),
            (string) ($p['no_telepon'] ?? ''),
            (string) ($p['catatan'] ?? ''),
            (string) ($p['alasan'] ?? ''),
        ]);
    }

    /** @return array<int, string> */
    public function rowSheetOptions(): array
    {
        return $this->distinctColumn($this->rows, 'sheet');
    }

    /** @return array<int, string> */
    public function previewSheetOptions(): array
    {
        return $this->distinctColumn($this->previews, 'sheet');
    }

    /** @return array<int, string> */
    protected function distinctColumn(?array $rows, string $key): array
    {
        if ($rows === null) {
            return [];
        }

        $values = [];
        foreach ($rows as $row) {
            $v = trim((string) ($row[$key] ?? ''));
            if ($v !== '') {
                $values[$v] = true;
            }
        }

        return array_keys($values);
    }

    public function kontakStatusLabel(string $status, string $perusahaanStatus): string
    {
        return match ($status) {
            'dibuat' => $perusahaanStatus === 'cocok' ? 'Disimpan (perusahaan lama)' : 'Disimpan (baru)',
            'duplikat_telepon' => 'Duplikat nomor',
            'duplikat_nama' => 'Duplikat nama',
            'duplikat_batch' => 'Duplikat dlm file',
            'data_tidak_lengkap' => 'Dilewati',
            default => $status,
        };
    }

    public function statusFilterOptions(): array
    {
        return [
            'dibuat' => 'Disimpan',
            'duplikat' => 'Semua duplikat',
            'duplikat_telepon' => 'Duplikat nomor',
            'duplikat_nama' => 'Duplikat nama',
            'duplikat_batch' => 'Duplikat dlm file',
            'data_tidak_lengkap' => 'Dilewati (tidak lengkap)',
        ];
    }

    public function downloadTemplate(): StreamedResponse
    {
        return response()->streamDownload(
            function (): void {
                $spreadsheet = $this->buildTemplateSpreadsheet();
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet);
            },
            'template-import-kontak-icm.xlsx',
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="template-import-kontak-icm.xlsx"',
            ]
        );
    }

    /**
     * Membangun spreadsheet template import dengan format visual terstandar ICM.
     */
    public function buildTemplateSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import Kontak');

        // Tampilkan garis grid excel
        $sheet->setShowGridLines(true);

        // Freeze baris header agar tetap terlihat saat scroll
        $sheet->freezePane('A2');

        // Baris Header Kolom
        $headers = [
            'A1' => 'nama_perusahaan',
            'B1' => 'industri',
            'C1' => 'nama',
            'D1' => 'no_telepon',
            'E1' => 'catatan',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Styling Header: Warna Brand ICM Navy (#18225E), Teks Putih Tebal, Rata Tengah
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => [
                'name' => 'Segoe UI',
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '18225E'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '0D153B'],
                ],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Contoh Data Nyata yang Rapi
        $samples = [
            [
                'PT Kalbe Farma Tbk',
                'Farmasi',
                'Budi Santoso',
                '08111223344',
                'Sponsor Utama Kongres Medis',
            ],
            [
                'PT Kimia Farma (Persero)',
                'Farmasi & Alkes',
                'Siti Aminah',
                '081298765432',
                'PIC Booth Pameran',
            ],
            [
                'CV Alkesindo Nusantara',
                'Distributor Alkes',
                'dr. Hendra Pratama',
                '081345678901',
                'Simposium Satelit Kardiologi',
            ],
        ];

        $rowIdx = 2;
        foreach ($samples as $sample) {
            $sheet->setCellValue('A'.$rowIdx, $sample[0]);
            $sheet->setCellValue('B'.$rowIdx, $sample[1]);
            $sheet->setCellValue('C'.$rowIdx, $sample[2]);
            // Format eksplisit string agar awalan 08 tidak hilang di Excel
            $sheet->setCellValueExplicit('D'.$rowIdx, $sample[3], DataType::TYPE_STRING);
            $sheet->setCellValue('E'.$rowIdx, $sample[4]);

            $isEven = $rowIdx % 2 === 0;
            $bgColor = $isEven ? 'FFFFFF' : 'F8FAFC';

            $sheet->getStyle('A'.$rowIdx.':E'.$rowIdx)->applyFromArray([
                'font' => [
                    'name' => 'Segoe UI',
                    'size' => 10,
                    'color' => ['rgb' => '1E293B'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $bgColor],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CBD5E1'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Perataan isi kolom
            $sheet->getStyle('B'.$rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D'.$rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D'.$rowIdx)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

            $sheet->getRowDimension($rowIdx)->setRowHeight(22);
            $rowIdx++;
        }

        // Lebar kolom terukur dan proporsional
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(26);
        $sheet->getColumnDimension('D')->setWidth(22);
        $sheet->getColumnDimension('E')->setWidth(35);

        return $spreadsheet;
    }
}
