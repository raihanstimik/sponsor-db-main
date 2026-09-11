<?php

declare(strict_types=1);

namespace App\Filament\Resources\KategoriKegiatans\Schemas;

use App\Models\KategoriKegiatan;
use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class KategoriKegiatanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Kategori Medis')
                ->icon('heroicon-o-tag')
                ->schema([
                    TextEntry::make('nama_kategori')
                        ->label('Nama Kategori')
                        ->weight(FontWeight::Bold)
                        ->size(TextSize::Large)
                        ->color('primary'),

                    Grid::make(2)
                        ->schema([
                            ColorEntry::make('warna')
                                ->label('Warna Kategori')
                                ->placeholder('-'),

                            TextEntry::make('kegiatans_count')
                                ->label('Jumlah Kegiatan / Event Terhubung')
                                ->state(fn (KategoriKegiatan $record): string => $record->kegiatans()->count().' Event')
                                ->badge()
                                ->color('primary')
                                ->icon('heroicon-o-calendar-days'),
                        ]),
                ]),

            Section::make('Deskripsi Kategori')
                ->icon('heroicon-o-document-text')
                ->collapsible()
                ->schema([
                    TextEntry::make('deskripsi')
                        ->label('')
                        ->placeholder('Belum ada deskripsi untuk kategori ini.')
                        ->markdown(),
                ]),

            Section::make('Metadata Sistem')
                ->collapsible()
                ->collapsed()
                ->columns(2)
                ->schema([
                    TextEntry::make('created_at')
                        ->label('Dibuat Pada')
                        ->dateTime('d F Y, H:i'),
                    TextEntry::make('updated_at')
                        ->label('Terakhir Diperbarui')
                        ->dateTime('d F Y, H:i'),
                ]),
        ]);
    }
}
