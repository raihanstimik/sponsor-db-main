<?php

declare(strict_types=1);

namespace App\Filament\Resources\KategoriKegiatans\Schemas;

use App\Support\KlasifikasiTabel;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KategoriKegiatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_kategori')
                    ->label('Nama Kategori')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                ColorPicker::make('warna')
                    ->label('Warna')
                    ->helperText('Dipakai untuk badge & sortir menurut warna. Otomatis diisi jika dikosongkan.')
                    ->default(fn () => KlasifikasiTabel::warnaKategori('')),
                Textarea::make('deskripsi')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
