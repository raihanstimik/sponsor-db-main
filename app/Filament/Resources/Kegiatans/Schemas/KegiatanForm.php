<?php

namespace App\Filament\Resources\Kegiatans\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KegiatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('kategori_kegiatan_id')
                    ->label('Kategori Kegiatan')
                    ->label('Kategori Medis')
                    ->relationship('kategoriKegiatan', 'nama_kategori')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('nama_kategori')
                            ->label('Nama Kategori')
                            ->required()
                            ->unique('kategori_kegiatans', 'nama_kategori')
                            ->maxLength(255),
                        ColorPicker::make('warna')
                            ->label('Warna Indikator')
                            ->helperText('Warna khas untuk badge dan grafik.'),
                        Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(3),
                    ])
                    ->required(),
                TextInput::make('nama_event')
                    ->label('Nama Event')
                    ->required()
                    ->maxLength(255),
                ColorPicker::make('warna')
                    ->label('Warna')
                    ->helperText('Dipakai untuk badge & sortir menurut warna. Kosongkan untuk mewarisi warna kategori.'),
                DatePicker::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->helperText('Kosongkan bila tanggal event belum diketahui'),
                DatePicker::make('tanggal_selesai')
                    ->label('Tanggal Selesai')
                    ->afterOrEqual('tanggal_mulai'),
                TextInput::make('venue')
                    ->label('Venue')
                    ->maxLength(255),
                Textarea::make('catatan')
                    ->label('Catatan')
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
