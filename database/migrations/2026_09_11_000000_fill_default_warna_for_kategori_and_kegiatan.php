<?php

use App\Models\KategoriKegiatan;
use App\Support\KlasifikasiTabel;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Isi otomatis warna yang masih kosong/null pada seluruh kategori kegiatan.
     */
    public function up(): void
    {
        foreach (KategoriKegiatan::all() as $kategori) {
            if (blank($kategori->warna)) {
                $kategori->warna = KlasifikasiTabel::warnaKategori($kategori->nama_kategori);
                $kategori->saveQuietly();
            }
        }
    }

    public function down(): void
    {
        // Tidak perlu me-null kan kembali warna
    }
};
