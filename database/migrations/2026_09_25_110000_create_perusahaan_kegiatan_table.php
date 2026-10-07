<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perusahaan_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perusahaan_id')->constrained('perusahaans')->cascadeOnDelete();
            $table->foreignId('kegiatan_id')->nullable()->constrained('kegiatans')->nullOnDelete();
            $table->decimal('nominal', 15, 2)->default(0);
            $table->string('paket')->nullable();
            $table->string('bentuk_partisipasi')->nullable();
            $table->smallInteger('tahun')->nullable()->index();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['perusahaan_id', 'kegiatan_id']);
            $table->index(['perusahaan_id', 'tahun']);
        });

        // Backfill relasi historis yang sudah ada dari kegiatan_kontak & kontaks
        $now = now();
        $pairs = DB::table('kegiatan_kontak')
            ->join('kontaks', 'kontaks.id', '=', 'kegiatan_kontak.kontak_id')
            ->join('kegiatans', 'kegiatans.id', '=', 'kegiatan_kontak.kegiatan_id')
            ->whereNotNull('kontaks.perusahaan_id')
            ->select(
                'kontaks.perusahaan_id',
                'kegiatan_kontak.kegiatan_id',
                'kegiatans.tanggal_mulai',
                'kegiatans.nama_event'
            )
            ->distinct()
            ->get();

        $inserts = [];
        foreach ($pairs as $pair) {
            $tahun = null;
            if ($pair->tanggal_mulai) {
                $tahun = (int) date('Y', strtotime((string) $pair->tanggal_mulai));
            } elseif (preg_match('/\b(20\d{2})\b/', (string) $pair->nama_event, $matches)) {
                $tahun = (int) $matches[1];
            } else {
                $tahun = (int) date('Y');
            }

            $inserts[] = [
                'perusahaan_id' => $pair->perusahaan_id,
                'kegiatan_id' => $pair->kegiatan_id,
                'nominal' => 0,
                'paket' => null,
                'bentuk_partisipasi' => 'Partisipasi Event',
                'tahun' => $tahun,
                'catatan' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($inserts) >= 200) {
                DB::table('perusahaan_kegiatan')->insertOrIgnore($inserts);
                $inserts = [];
            }
        }

        if (! empty($inserts)) {
            DB::table('perusahaan_kegiatan')->insertOrIgnore($inserts);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('perusahaan_kegiatan');
    }
};
