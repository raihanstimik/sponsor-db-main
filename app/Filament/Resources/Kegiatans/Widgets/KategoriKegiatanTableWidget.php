<?php

declare(strict_types=1);

namespace App\Filament\Resources\Kegiatans\Widgets;

use App\Filament\Resources\KategoriKegiatans\Schemas\KategoriKegiatanForm;
use App\Filament\Resources\KategoriKegiatans\Tables\KategoriKegiatansTable;
use App\Models\KategoriKegiatan;
use Filament\Actions\CreateAction;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class KategoriKegiatanTableWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $table = KategoriKegiatansTable::configure($table);

        return $table
            ->query(fn (): Builder => KategoriKegiatan::query()->withCount('kegiatans'))
            ->heading('Kelola Kategori Spesialisasi')
            ->description('Daftar spesialisasi medis dan palet warna indikator untuk pengelompokan sponsor')
            ->headerActions([
                CreateAction::make('createKategori')
                    ->label('Tambah Kategori')
                    ->icon(Heroicon::OutlinedPlusCircle)
                    ->model(KategoriKegiatan::class)
                    ->modalHeading('Tambah Kategori Spesialisasi')
                    ->modalDescription('Buat kategori spesialisasi medis baru beserta palet warna indikatornya.')
                    ->modalWidth('md')
                    ->slideOver()
                    ->modalSubmitActionLabel('Simpan Kategori')
                    ->modalCancelActionLabel('Batal')
                    ->successNotificationTitle('Kategori medis berhasil ditambahkan')
                    ->schema(fn (Schema $schema): Schema => KategoriKegiatanForm::configure($schema))
                    ->visible(fn (): bool => (bool) auth()->user()?->isAdmin()),
            ]);
    }
}
