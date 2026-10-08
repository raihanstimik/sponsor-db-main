<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Advanced composite and filtering indexes for high-throughput queries,
     * subqueries, and multi-table analytics.
     */
    public function up(): void
    {
        if (Schema::hasTable('kegiatan_kontak')) {
            Schema::table('kegiatan_kontak', function (Blueprint $table): void {
                $table->index(['kegiatan_id', 'kontak_id'], 'kk_kegiatan_kontak_idx');
            });
        }

        if (Schema::hasTable('perusahaan_kegiatan')) {
            Schema::table('perusahaan_kegiatan', function (Blueprint $table): void {
                $table->index(['perusahaan_id', 'tahun', 'tanggal_partisipasi'], 'pk_prsh_tahun_tgl_idx');
                $table->index(['tahun', 'perusahaan_id'], 'pk_tahun_prsh_idx');
                $table->index('kegiatan_id', 'pk_kegiatan_id_idx');
            });
        }

        if (Schema::hasTable('kontaks')) {
            Schema::table('kontaks', function (Blueprint $table): void {
                $table->index(['deleted_at', 'status_format_valid'], 'kontaks_del_valid_idx');
                $table->index(['deleted_at', 'kegiatan_id'], 'kontaks_del_kegiatan_idx');
                $table->index(['deleted_at', 'perusahaan_id'], 'kontaks_del_prsh_idx');
            });
        }

        if (Schema::hasTable('perusahaans')) {
            Schema::table('perusahaans', function (Blueprint $table): void {
                $table->index(['deleted_at', 'industri'], 'perusahaans_del_industri_idx');
                $table->index(['deleted_at', 'nama_standar'], 'perusahaans_del_nama_idx');
            });
        }

        if (Schema::hasTable('kegiatans')) {
            Schema::table('kegiatans', function (Blueprint $table): void {
                $table->index(['tanggal_mulai', 'tanggal_selesai'], 'kegiatans_tgl_mulai_selesai_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('kegiatan_kontak')) {
            Schema::table('kegiatan_kontak', function (Blueprint $table): void {
                $table->dropIndex('kk_kegiatan_kontak_idx');
            });
        }

        if (Schema::hasTable('perusahaan_kegiatan')) {
            Schema::table('perusahaan_kegiatan', function (Blueprint $table): void {
                $table->dropIndex('pk_prsh_tahun_tgl_idx');
                $table->dropIndex('pk_tahun_prsh_idx');
                $table->dropIndex('pk_kegiatan_id_idx');
            });
        }

        if (Schema::hasTable('kontaks')) {
            Schema::table('kontaks', function (Blueprint $table): void {
                $table->dropIndex('kontaks_del_valid_idx');
                $table->dropIndex('kontaks_del_kegiatan_idx');
                $table->dropIndex('kontaks_del_prsh_idx');
            });
        }

        if (Schema::hasTable('perusahaans')) {
            Schema::table('perusahaans', function (Blueprint $table): void {
                $table->dropIndex('perusahaans_del_industri_idx');
                $table->dropIndex('perusahaans_del_nama_idx');
            });
        }

        if (Schema::hasTable('kegiatans')) {
            Schema::table('kegiatans', function (Blueprint $table): void {
                $table->dropIndex('kegiatans_tgl_mulai_selesai_idx');
            });
        }
    }
};

