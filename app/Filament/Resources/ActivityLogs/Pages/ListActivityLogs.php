<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogs\Pages;

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;
use Spatie\Activitylog\Models\Activity;

class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cleanLogs')
                ->label('Bersihkan Log Lama')
                ->icon(Heroicon::OutlinedTrash)
                ->color('gray')
                ->visible(fn (): bool => (bool) auth()->user()?->isAdmin())
                ->form([
                    Select::make('days')
                        ->label('Rentang Usia Log yang Dihapus')
                        ->options([
                            '30' => 'Lebih dari 30 hari yang lalu',
                            '60' => 'Lebih dari 60 hari yang lalu (Direkomendasikan)',
                            '90' => 'Lebih dari 90 hari yang lalu',
                            '1' => 'Lebih dari 1 hari yang lalu (Kemarin)',
                            '3' => 'Lebih dari 3 hari yang lalu',
                            '7' => 'Lebih dari 7 hari (1 minggu yang lalu)',
                            '14' => 'Lebih dari 14 hari (2 minggu yang lalu)',
                            '30' => 'Lebih dari 30 hari (1 bulan yang lalu)',
                            '60' => 'Lebih dari 60 hari (2 bulan yang lalu - Direkomendasikan)',
                            '90' => 'Lebih dari 90 hari (3 bulan yang lalu)',
                            '0' => 'Semua catatan log (Hapus seluruhnya)',
                        ])
                        ->default('60')
                        ->required()
                        ->helperText('Log histori sebelum rentang waktu ini akan dihapus permanen untuk merampingkan database.'),
                ])
                ->modalHeading('Bersihkan Log Histori')
                ->modalDescription('Tindakan ini akan menghapus catatan audit lama sesuai rentang yang dipilih agar ukuran database tetap optimal.')
                ->modalSubmitActionLabel('Bersihkan Sekarang')
                ->action(function (array $data): void {
                    $days = (int) ($data['days'] ?? 60);
                    $countBefore = Activity::count();
                    Artisan::call('activitylog:clean', ['--days' => $days]);
                    $countAfter = Activity::count();
                    $deleted = max(0, $countBefore - $countAfter);

                    $labelRentang = match ($days) {
                        0 => 'seluruh waktu',
                        1 => '> 1 hari',
                        3 => '> 3 hari',
                        7 => '> 7 hari (1 minggu)',
                        14 => '> 14 hari (2 minggu)',
                        30 => '> 30 hari (1 bulan)',
                        60 => '> 60 hari (2 bulan)',
                        90 => '> 90 hari (3 bulan)',
                        default => "> {$days} hari",
                    };

                    Notification::make()
                        ->title('Pembersihan Log Berhasil')
                        ->body("Sebanyak {$deleted} data log lama (> {$days} hari) telah dibersihkan.")
                        ->body("Sebanyak {$deleted} data log histori ({$labelRentang}) telah dibersihkan.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
