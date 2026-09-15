<?php

declare(strict_types=1);

namespace App\Filament\Resources\KategoriKegiatans\Tables;

use App\Filament\Resources\KategoriKegiatans\Schemas\KategoriKegiatanForm;
use App\Filament\Resources\KategoriKegiatans\Schemas\KategoriKegiatanInfolist;
use App\Models\KategoriKegiatan;
use App\Support\FilamentTableHelper;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class KategoriKegiatansTable
{
    public static function configure(Table $table): Table
    {
        FilamentTableHelper::applyDefaultPresets($table, [10, 25, 50], 25);

        return $table
            ->columns([
                TextColumn::make('nama_kategori')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->weight('semibold'),
                ColorColumn::make('warna')
                    ->label('Warna')
                    ->copyable()
                    ->placeholder('-'),
                TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('kegiatans_count')
                    ->label('Jumlah Kegiatan')
                    ->counts('kegiatans')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat')
                    ->icon('heroicon-m-eye')
                    ->slideOver()
                    ->modalWidth('md')
                    ->modalHeading(fn (KategoriKegiatan $record): string => 'Detail Kategori: ' . $record->nama_kategori)
                    ->modalCancelAction(fn (Action $action) => $action->label('Tutup'))
                    ->schema(fn (Schema $schema): Schema => KategoriKegiatanInfolist::configure($schema)),

                EditAction::make()
                    ->label('Ubah')
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Ubah Kategori Spesialisasi')
                    ->modalDescription('Perbarui data kategori spesialisasi medis dan palet warnanya.')
                    ->modalWidth('md')
                    ->slideOver()
                    ->modalSubmitActionLabel('Simpan Perubahan')
                    ->modalCancelActionLabel('Batal')
                    ->successNotificationTitle('Kategori medis berhasil diperbarui')
                    ->schema(fn (Schema $schema): Schema => KategoriKegiatanForm::configure($schema))
                    ->visible(fn (KategoriKegiatan $record) => auth()->user()?->can('update', $record) ?? false),

                DeleteAction::make()
                    ->label('Hapus')
                    ->icon('heroicon-m-trash')
                    ->modalHeading('Hapus Kategori Spesialisasi')
                    ->modalDescription('Apakah Anda yakin ingin menghapus kategori medis ini? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus Kategori')
                    ->modalCancelActionLabel('Batal')
                    ->successNotificationTitle('Kategori medis berhasil dihapus')
                    ->visible(fn (KategoriKegiatan $record) => auth()->user()?->can('delete', $record) ?? false)
                    ->before(function (DeleteAction $action, KategoriKegiatan $record) {
                        $kegiatanCount = $record->kegiatans()->count();
                        if ($kegiatanCount > 0) {
                            Notification::make()
                                ->title('Gagal Menghapus Kategori')
                                ->body("Kategori \"{$record->nama_kategori}\" masih digunakan oleh {$kegiatanCount} kegiatan/event. Lepaskan atau ubah kategori pada kegiatan terkait terlebih dahulu.")
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('deleteAny', KategoriKegiatan::class) ?? false)
                        ->before(function (DeleteBulkAction $action, Collection $records) {
                            $usedCategories = $records->filter(fn (KategoriKegiatan $record) => $record->kegiatans()->count() > 0);
                            if ($usedCategories->isNotEmpty()) {
                                $names = $usedCategories->pluck('nama_kategori')->join(', ');
                                Notification::make()
                                    ->title('Gagal Menghapus Sebagian Kategori')
                                    ->body("Kategori berikut masih digunakan oleh kegiatan dan tidak dapat dihapus: {$names}")
                                    ->danger()
                                    ->send();

                                $action->cancel();
                            }
                        }),
                ]),
            ])
            ->defaultSort('nama_kategori');
    }
}
