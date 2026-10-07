<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perusahaan_kegiatan', function (Blueprint $table) {
            if (! Schema::hasColumn('perusahaan_kegiatan', 'nama_event_manual')) {
                $table->string('nama_event_manual')->nullable()->after('kegiatan_id');
            }
            if (! Schema::hasColumn('perusahaan_kegiatan', 'tanggal_partisipasi')) {
                $table->date('tanggal_partisipasi')->nullable()->after('tahun');
            }
            $table->index(['perusahaan_id', 'tahun', 'tanggal_partisipasi'], 'pk_prsh_thn_tgl_idx');
        });
    }

    public function down(): void
    {
        Schema::table('perusahaan_kegiatan', function (Blueprint $table) {
            $table->dropIndex('pk_prsh_thn_tgl_idx');
            if (Schema::hasColumn('perusahaan_kegiatan', 'nama_event_manual')) {
                $table->dropColumn('nama_event_manual');
            }
            if (Schema::hasColumn('perusahaan_kegiatan', 'tanggal_partisipasi')) {
                $table->dropColumn('tanggal_partisipasi');
            }
        });
    }
};
