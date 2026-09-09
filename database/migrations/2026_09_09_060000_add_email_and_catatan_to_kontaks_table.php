<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kontaks', function (Blueprint $table) {
            $table->string('email')->nullable()->after('no_telepon');
            $table->text('catatan')->nullable()->after('status_verifikasi');
        });
    }

    public function down(): void
    {
        Schema::table('kontaks', function (Blueprint $table) {
            $table->dropColumn(['email', 'catatan']);
        });
    }
};
