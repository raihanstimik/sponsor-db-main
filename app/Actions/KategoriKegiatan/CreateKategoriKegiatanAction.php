<?php

declare(strict_types=1);

namespace App\Actions\KategoriKegiatan;

use App\Models\KategoriKegiatan;
use Illuminate\Support\Facades\Cache;

class CreateKategoriKegiatanAction
{
    /**
     * Membuat kategori kegiatan baru dan membersihkan cache statistik yang relevan.
     *
     * @param  array{nama_kategori: string, warna?: ?string, deskripsi?: ?string}  $data
     */
    public function execute(array $data): KategoriKegiatan
    {
        $kategori = KategoriKegiatan::create($data);

        $this->clearRelevantCaches();

        return $kategori;
    }

    /**
     * Hapus cache statistik kegiatan & dashboard yang terpengaruh penambahan kategori.
     */
    public function clearRelevantCaches(): void
    {
        Cache::forget('icm:kegiatan_stats');
        Cache::forget('icm:stats_overview');
        Cache::forget('icm:chart_kategori');
    }
}
