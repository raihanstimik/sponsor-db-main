<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Composite and soft-delete indexes to speed up dashboard widgets and large table queries.
     */
    public function up(): void
    {
        Schema::table('perusahaan_kegiatan', function (Blueprint $table): void {
            $table->index(['perusahaan_id', 'nominal'], 'pk_prsh_nominal_idx');
        });

        Schema::table('kontaks', function (Blueprint $table): void {
            $table->index('deleted_at', 'kontaks_deleted_at_idx');
            $table->index(['perusahaan_id', 'status_format_valid'], 'kontaks_prsh_valid_idx');
        });

        Schema::table('perusahaans', function (Blueprint $table): void {
            $table->index('deleted_at', 'perusahaans_deleted_at_idx');
            $table->index('updated_by', 'perusahaans_updated_by_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perusahaan_kegiatan', function (Blueprint $table): void {
            $table->dropIndex('pk_prsh_nominal_idx');
        });

        Schema::table('kontaks', function (Blueprint $table): void {
            $table->dropIndex('kontaks_deleted_at_idx');
            $table->dropIndex('kontaks_prsh_valid_idx');
        });

        Schema::table('perusahaans', function (Blueprint $table): void {
            $table->dropIndex('perusahaans_deleted_at_idx');
            $table->dropIndex('perusahaans_updated_by_idx');
        });
    }
};

