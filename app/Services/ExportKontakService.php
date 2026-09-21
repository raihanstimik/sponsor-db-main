<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Kontak;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportKontakService
{
    public function __construct(
        protected KontakSmartSearch $smartSearch
    ) {}

    /**
     * Menjalankan ekspor data kontak berdasarkan format yang diminta.
     */
    public function export(Request $request, ?User $user = null): Response
    {
        $format = strtolower(trim((string) $request->input('format', 'csv')));
        $scope = strtolower(trim((string) $request->input('scope', 'filtered')));

        $columnsRaw = $request->input('columns', []);
        $columns = is_array($columnsRaw) ? $columnsRaw : [];

        $query = $this->buildQuery($request, $scope);
        $count = (clone $query)->count();

        // Audit Trail: Catat ke activity_log
        $this->logExportActivity($user, $format, $scope, $count, $request);

        return match ($format) {
            'xlsx', 'excel' => $this->exportXlsx($query, $columns),
            'json' => $this->exportJson($query, $columns, $count),
            default => $this->exportCsv($query, $columns),
        };
    }

    /**
     * Membangun kueri Kontak beserta relasi dan filternya.
     */
    public function buildQuery(Request $request, string $scope = 'filtered'): Builder
    {
        $query = Kontak::query()
            ->with(['perusahaan', 'kegiatan', 'kegiatans', 'kategoriKegiatan'])
            ->orderBy('nama');

        if ($scope === 'all') {
            return $query;
        }

        $q = trim((string) $request->query('q', $request->input('q', '')));
        if ($q !== '') {
            $this->smartSearch->applyTo($query, $q);
        }

        $kegiatanId = $request->query('kegiatan_id', $request->input('kegiatan_id'));
        if (filled($kegiatanId)) {
            $ids = (array) $kegiatanId;
            $query->where(function (Builder $sub) use ($ids): void {
                $sub->whereIn('kontaks.kegiatan_id', $ids)
                    ->orWhereHas('kegiatans', fn (Builder $kq): Builder => $kq->whereIn('kegiatans.id', $ids));
            });
        }

        $kategoriId = $request->query('kategori_kegiatan_id', $request->input('kategori_kegiatan_id'));
        if (filled($kategoriId)) {
            $query->whereIn('kategori_kegiatan_id', (array) $kategoriId);
        }

        return $query;
    }

    /**
     * Ekspor ke format Microsoft Excel (.xlsx) dengan styling resmi dan nomor telepon aman.
     */
    public function exportXlsx(Builder $query, array $extraColumns): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Kontak Sponsor');

        $headers = $this->getHeaderTitles($extraColumns);
        $lastColumnIndex = count($headers);
        $lastColumnLetter = Coordinate::stringFromColumnIndex($lastColumnIndex);

        // Tulis Baris Judul Header
        foreach ($headers as $index => $title) {
            $colLetter = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue("{$colLetter}1", $title);
        }

        // Styling Baris Header: Navy (#18225E), Teks Putih Tebal, Alignment Center Vertikal
        $headerRange = "A1:{$lastColumnLetter}1";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
                'name' => 'Calibri',
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '18225E'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'wrapText' => false,
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '0F172A'],
                ],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);

        // Freeze baris header pertama agar tetap terlihat saat di-scroll
        $sheet->freezePane('A2');

        // Auto-filter pada kolom header
        $sheet->setAutoFilter($headerRange);

        // Isi Data Baris
        $rowIndex = 2;
        $query->chunk(500, function ($kontaks) use ($sheet, &$rowIndex, $extraColumns): void {
            foreach ($kontaks as $kontak) {
                /** @var Kontak $kontak */
                $rowData = $this->transformKontakToRow($kontak, $extraColumns, true);

                foreach ($rowData as $colIndex => $value) {
                    $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
                    $cellCoordinate = "{$colLetter}{$rowIndex}";

                    // Khusus kolom nomor telepon (kolom ke-4 / D): set tipe teks eksplisit agar angka 0 tidak hilang
                    if ($colIndex === 3) {
                        $sheet->getCell($cellCoordinate)->setValueExplicit((string) $value, DataType::TYPE_STRING);
                    } else {
                        $sheet->setCellValue($cellCoordinate, $value);
                    }
                }

                $sheet->getRowDimension($rowIndex)->setRowHeight(20);
                $rowIndex++;
            }
        });

        // Set Auto-width pada semua kolom dengan padding
        for ($col = 1; $col <= $lastColumnIndex; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Tulis ke berkas sementara dan stream download
        $filename = 'kontak-'.now()->format('Y-m-d_His').'.xlsx';
        $tempPath = (string) tempnam(sys_get_temp_dir(), 'export_kontak_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Ekspor ke format CSV dengan streaming, UTF-8 BOM, dan sanitasi formula.
     */
    public function exportCsv(Builder $query, array $extraColumns): StreamedResponse
    {
        $filename = 'kontak-'.now()->format('Y-m-d_His').'.csv';
        $headers = $this->getHeaderTitles($extraColumns);

        return response()->streamDownload(function () use ($query, $headers, $extraColumns): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // UTF-8 BOM untuk kompatibilitas Microsoft Excel
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, $headers);

            $query->chunk(500, function ($kontaks) use ($handle, $extraColumns): void {
                foreach ($kontaks as $kontak) {
                    /** @var Kontak $kontak */
                    $row = $this->transformKontakToRow($kontak, $extraColumns, false);
                    // Sanitasi formula injection untuk file CSV
                    $sanitizedRow = array_map([$this, 'sanitizeFormulaValue'], $row);
                    fputcsv($handle, $sanitizedRow);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Ekspor ke format JSON terstruktur dengan streaming.
     */
    public function exportJson(Builder $query, array $extraColumns, int $total): StreamedResponse
    {
        $filename = 'kontak-'.now()->format('Y-m-d_His').'.json';

        return response()->streamDownload(function () use ($query, $extraColumns, $total): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            $meta = [
                'exported_at' => now()->toIso8601String(),
                'total_records' => $total,
            ];

            fwrite($handle, "{\n  \"meta\": ".json_encode($meta, JSON_PRETTY_PRINT).",\n  \"data\": [\n");

            $isFirst = true;

            $query->chunk(500, function ($kontaks) use ($handle, $extraColumns, &$isFirst): void {
                foreach ($kontaks as $kontak) {
                    /** @var Kontak $kontak */
                    $namaEvent = $kontak->kegiatans->isNotEmpty()
                        ? $kontak->kegiatans->pluck('nama_event')->implode(', ')
                        : $kontak->kegiatan?->nama_event;
                    $tahunEvent = $kontak->kegiatans->isNotEmpty()
                        ? $kontak->kegiatans->pluck('tanggal_mulai')->filter()->map(fn ($d) => $d->format('Y'))->unique()->implode(', ')
                        : $kontak->kegiatan?->tanggal_mulai?->format('Y');

                    $item = [
                        'id' => $kontak->id,
                        'nama_pic' => $kontak->nama,
                        'perusahaan' => $kontak->perusahaan?->nama_standar,
                        'industri' => $kontak->perusahaan?->industri,
                        'no_telepon' => $kontak->no_telepon,
                        'kegiatan' => $namaEvent ?: $kontak->kegiatan?->nama_event,
                        'tahun' => $tahunEvent ?: $kontak->kegiatan?->tanggal_mulai?->format('Y'),
                        'kategori' => $kontak->kategoriKegiatan?->nama_kategori,
                    ];

                    if (in_array('status_format_valid', $extraColumns, true)) {
                        $item['status_format_valid'] = (bool) $kontak->status_format_valid;
                    }

                    if (in_array('catatan', $extraColumns, true)) {
                        $item['catatan'] = $kontak->catatan;
                    }

                    if (in_array('created_at', $extraColumns, true)) {
                        $item['created_at'] = $kontak->created_at?->toIso8601String();
                    }

                    $encoded = json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    $line = ($isFirst ? "    " : ",\n    ") . $encoded;
                    fwrite($handle, $line);
                    $isFirst = false;
                }
            });

            fwrite($handle, "\n  ]\n}\n");
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Mendapatkan daftar judul kolom header.
     *
     * @return array<int, string>
     */
    public function getHeaderTitles(array $extraColumns): array
    {
        $headers = [
            'Nama PIC',
            'Nama Perusahaan',
            'Industri',
            'No. Telepon',
            'Kegiatan',
            'Tahun',
            'Kategori',
        ];

        if (in_array('status_format_valid', $extraColumns, true)) {
            $headers[] = 'Status Validitas Nomor';
        }

        if (in_array('catatan', $extraColumns, true)) {
            $headers[] = 'Catatan';
        }

        if (in_array('created_at', $extraColumns, true)) {
            $headers[] = 'Tanggal Ditambahkan';
        }

        return $headers;
    }

    /**
     * Mengubah model Kontak menjadi baris data flat.
     *
     * @return array<int, mixed>
     */
    public function transformKontakToRow(Kontak $kontak, array $extraColumns, bool $forExcel = false): array
    {
        $namaEvent = $kontak->kegiatans->isNotEmpty()
            ? $kontak->kegiatans->pluck('nama_event')->implode(', ')
            : $kontak->kegiatan?->nama_event;
        $tahunEvent = $kontak->kegiatans->isNotEmpty()
            ? $kontak->kegiatans->pluck('tanggal_mulai')->filter()->map(fn ($d) => $d->format('Y'))->unique()->implode(', ')
            : $kontak->kegiatan?->tanggal_mulai?->format('Y');

        $row = [
            $kontak->nama,
            $kontak->perusahaan?->nama_standar,
            $kontak->perusahaan?->industri,
            $kontak->no_telepon,
            $namaEvent ?: $kontak->kegiatan?->nama_event,
            $tahunEvent ?: $kontak->kegiatan?->tanggal_mulai?->format('Y'),
            $kontak->kategoriKegiatan?->nama_kategori,
        ];

        if (in_array('status_format_valid', $extraColumns, true)) {
            $row[] = $kontak->status_format_valid ? 'Valid' : 'Perlu Cek';
        }

        if (in_array('catatan', $extraColumns, true)) {
            $row[] = $kontak->catatan;
        }

        if (in_array('created_at', $extraColumns, true)) {
            $row[] = $kontak->created_at?->format('d/m/Y H:i');
        }

        return $row;
    }

    /**
     * Sanitasi sel dari ancaman CSV/Excel Formula Injection (CWE-1236).
     * Jika diawali '=', '+', '-', '@', '\t', '\r', ditambahkan tanda petik satu di depan.
     */
    public function sanitizeFormulaValue(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        $firstChar = $value[0];
        if (in_array($firstChar, ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }

    /**
     * Mencatat aksi ekspor ke Spatie Activity Log untuk audit keamanan data.
     */
    protected function logExportActivity(?User $user, string $format, string $scope, int $count, Request $request): void
    {
        $activity = activity('export')
            ->event('kontak.export')
            ->withProperties([
                'format' => $format,
                'scope' => $scope,
                'total_records' => $count,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'query_params' => $request->only(['q', 'kegiatan_id', 'kategori_kegiatan_id']),
            ]);

        if ($user !== null) {
            $activity->causedBy($user);
        }

        $activity->log(sprintf(
            'Pengguna %s mengekspor %d data kontak ke format %s (%s)',
            $user?->name ?? 'Anonim',
            $count,
            strtoupper($format),
            $scope === 'all' ? 'Seluruh Data' : 'Hasil Filter'
        ));
    }
}

