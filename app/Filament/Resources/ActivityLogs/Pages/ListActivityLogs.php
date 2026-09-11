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

// app/Filament/Resources/ActivityLogs/Pages/ListActivityLogs.php
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

                    Notification::make()
                        ->title('Pembersihan Log Berhasil')
                        ->body("Sebanyak {$deleted} data log lama (> {$days} hari) telah dibersihkan.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
