<?php

declare(strict_types=1);

namespace App\Actions\Kontak;

use App\Models\Kontak;
use App\Services\KontakSmartSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportKontakCsvAction
{
    public function __construct(
        protected KontakSmartSearch $smartSearch
    ) {}

    /**
     * Membangun query Kontak dengan filter dari request dan mengekspor hasilnya
     * sebagai file CSV streaming dengan chunk 500 baris.
     */
    public function execute(Request $request): StreamedResponse
    {
        $query = $this->buildQuery($request);
        $filename = 'kontak-'.now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // UTF-8 BOM untuk kompatibilitas Microsoft Excel
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Nama PIC',
                'Nama Perusahaan',
                'Industri',
                'No. Telepon',
                'Kegiatan',
                'Tahun',
                'Kategori',
            ]);

            $query->chunk(500, function ($kontaks) use ($handle): void {
                foreach ($kontaks as $kontak) {
                    /** @var Kontak $kontak */
                    fputcsv($handle, [
                        $kontak->nama,
                        $kontak->perusahaan?->nama_standar,
                        $kontak->perusahaan?->industri,
                        $kontak->no_telepon,
                        $kontak->kegiatan?->nama_event,
                        $kontak->kegiatan?->tanggal_mulai?->format('Y'),
                        $kontak->kategoriKegiatan?->nama_kategori,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Membangun kueri Kontak beserta relasi dan filternya.
     */
    public function buildQuery(Request $request): Builder
    {
        $query = Kontak::query()
            ->with(['perusahaan', 'kegiatan', 'kategoriKegiatan'])
            ->orderBy('nama');

        $q = trim((string) $request->query('q'));
        if ($q !== '') {
            $this->smartSearch->applyTo($query, $q);
        }

        if (filled($kegiatanId = $request->query('kegiatan_id'))) {
            $query->whereIn('kegiatan_id', (array) $kegiatanId);
        }

        if (filled($kategoriId = $request->query('kategori_kegiatan_id'))) {
            $query->whereIn('kategori_kegiatan_id', (array) $kategoriId);
        }

        return $query;
    }
}
