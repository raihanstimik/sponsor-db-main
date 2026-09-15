<?php

declare(strict_types=1);

namespace App\Filament\Resources\Divisis\Tables;

use App\Models\Divisi;
use App\Support\FilamentTableHelper;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DivisisTable
{
    public static function configure(Table $table): Table
    {
        FilamentTableHelper::applyDefaultPresets($table);

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Divisi')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->icon('heroicon-m-building-office-2')
                    ->iconColor('primary')
                    ->copyable(),

                TextColumn::make('slug')
                    ->label('Slug / Kode')
                    ->searchable()
                    ->fontFamily('mono')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('users_count')
                    ->label('Anggota')
                    ->counts('users')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'primary' : 'gray')
                    ->formatStateUsing(fn (int $state): string => $state.' Karyawan')
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Deskripsi & Tugas')
                    ->limit(45)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->placeholder('-')
                    ->color('gray'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('')
                    ->icon('heroicon-m-eye')
                    ->tooltip('Lihat Detail Divisi')
                    ->slideOver()
                    ->modalWidth('md')
                    ->modalHeading(fn (Divisi $record): string => 'Detail Divisi: '.$record->name)
                    ->modalCancelAction(fn (Action $action) => $action->label('Tutup'))
                    ->modalSubmitAction(false)
                    ->color('gray'),

                EditAction::make()
                    ->label('')
                    ->icon('heroicon-m-pencil-square')
                    ->tooltip('Ubah Divisi')
                    ->modalHeading('Ubah Data Divisi')
                    ->modalWidth('md')
                    ->color('primary')
                    ->successNotificationTitle('Divisi berhasil diperbarui'),

                DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-m-trash')
                    ->tooltip('Hapus Divisi')
                    ->color('danger')
                    ->modalHeading('Hapus Divisi')
                    ->modalDescription('Apakah Anda yakin ingin menghapus divisi ini? Tindakan ini tidak dapat dibatalkan.')
                    ->before(function (DeleteAction $action, Divisi $record) {
                        if (strtolower($record->name) === 'umum' || strtolower($record->slug) === 'umum') {
                            Notification::make()
                                ->title('Gagal Menghapus Divisi')
                                ->body('Divisi "Umum" adalah divisi dasar sistem dan tidak dapat dihapus.')
                                ->danger()
                                ->send();

                            $action->cancel();
                        }

                        $userCount = $record->users()->count();
                        if ($userCount > 0) {
                            Notification::make()
                                ->title('Gagal Menghapus Divisi')
                                ->body("Divisi ini masih memiliki {$userCount} karyawan terdaftar. Pindahkan karyawan ke divisi lain terlebih dahulu.")
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    })
                    ->successNotificationTitle('Divisi berhasil dihapus'),
            ])
            ->emptyStateHeading('Belum Ada Divisi')
            ->emptyStateDescription('Buat divisi kerja baru untuk mulai memetakan penugasan akun karyawan ICM.')
            ->emptyStateIcon('heroicon-o-building-office-2')
            ->defaultSort('name');
    }
}

