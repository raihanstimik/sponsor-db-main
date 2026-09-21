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
        Schema::create('kegiatan_kontak', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kontak_id')->constrained('kontaks')->cascadeOnDelete();
            $table->foreignId('kegiatan_id')->constrained('kegiatans')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['kontak_id', 'kegiatan_id']);
        });

        // Backfill relasi kegiatan yang sudah ada di tabel kontaks ke tabel pivot kegiatan_kontak
        DB::table('kontaks')
            ->whereNotNull('kegiatan_id')
            ->select('id as kontak_id', 'kegiatan_id')
            ->orderBy('id')
            ->chunk(500, function ($rows): void {
                $now = now();
                $payload = [];
                foreach ($rows as $row) {
                    $payload[] = [
                        'kontak_id' => $row->kontak_id,
                        'kegiatan_id' => $row->kegiatan_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                if (! empty($payload)) {
                    DB::table('kegiatan_kontak')->insertOrIgnore($payload);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan_kontak');
    }
};

