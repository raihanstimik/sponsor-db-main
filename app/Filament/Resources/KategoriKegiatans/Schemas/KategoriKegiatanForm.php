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
                    ->placeholder('contoh: Onkologi & Ginekologi')
                    ->prefixIcon('heroicon-m-tag')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                ColorPicker::make('warna')
                    ->label('Warna Indikator')
                    ->helperText('Dipakai untuk badge & grafik distribusi. Otomatis diisi jika kosong.')
                    ->default(fn () => KlasifikasiTabel::warnaKategori('')),
                Textarea::make('deskripsi')
                    ->label('Deskripsi')
                    ->placeholder('Penjelasan ringkas spesialisasi medis ini...')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }
}
