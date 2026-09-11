<?php

declare(strict_types=1);

namespace App\Support;

use Filament\Actions\ViewAction;
use Filament\Tables\Table;

class FilamentTableHelper
{
    /**
     * Konfigurasi standar untuk seluruh tabel Filament di aplikasi ICM:
     * - Menonaktifkan URL langsung pada baris tabel (mencegah klik baris membuka halaman edit).
     * - Mengarahkan aksi klik baris ke ViewAction (slideOver / modal detail).
     * - Menerapkan preset opsi paginasi dan default page option yang konsisten.
     *
     * @param  array<int>|null  $paginationOptions
     */
    public static function applyDefaultPresets(
        Table $table,
        ?array $paginationOptions = [15, 25, 50, 100],
        ?int $defaultPageOption = 25
    ): Table {
        $table
            ->recordUrl(null)
            ->recordAction(ViewAction::class);

        if ($paginationOptions !== null) {
            $table->paginated($paginationOptions);
        }

        if ($defaultPageOption !== null) {
            $table->defaultPaginationPageOption($defaultPageOption);
        }

        return $table;
    }
}
