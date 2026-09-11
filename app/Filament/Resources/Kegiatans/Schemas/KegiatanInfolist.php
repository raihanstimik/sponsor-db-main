<?php

declare(strict_types=1);

namespace App\Filament\Resources\Kegiatans\Schemas;

use App\Models\Kegiatan;
use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class KegiatanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Kegiatan & Kongres Medis')
                ->icon('heroicon-o-calendar-days')
                ->schema([
                    TextEntry::make('nama_event')
                        ->label('Nama Kegiatan / Event')
                        ->weight(FontWeight::Bold)
                        ->size(TextSize::Large)
                        ->color('primary'),

                    Grid::make(2)
                        ->schema([
                            TextEntry::make('kategoriKegiatan.nama_kategori')
                                ->label('Kategori Medis')
                                ->badge()
                                ->color(fn (Kegiatan $record): ?string => $record->warna ?? $record->kategoriKegiatan?->warna ?? 'primary')
                                ->placeholder('-'),

                            ColorEntry::make('warna')
                                ->label('Warna Indikator')
                                ->placeholder('-'),

                            TextEntry::make('status_kegiatan')
                                ->label('Status Kongres')
                                ->state(fn (Kegiatan $record): string => match ($record->status_event) {
                                    'Mendatang' => 'Mendatang (Open Sponsor)',
                                    'Berlangsung' => 'Sedang Berlangsung',
                                    default => $record->status_event,
                                })
                                ->badge()
                                ->color(fn (Kegiatan $record): string => $record->status_color),

                            TextEntry::make('durasi')
                                ->label('Jadwal & Durasi')
                                ->state(fn (Kegiatan $record): string => $record->jadwal_dan_durasi)
                                ->icon('heroicon-o-clock'),

                            TextEntry::make('venue')
                                ->label('Lokasi / Venue')
                                ->icon('heroicon-o-map-pin')
                                ->placeholder('Belum diatur')
                                ->columnSpanFull(),

                            TextEntry::make('kontaks_count')
                                ->label('Afiliasi PIC')
                                ->state(fn (Kegiatan $record): string => $record->kontaks()->count().' Kontak PIC')
                                ->badge()
                                ->color('info')
                                ->icon('heroicon-o-user-group'),

                            TextEntry::make('sponsors_count')
                                ->label('Komitmen Sponsor')
                                ->state(fn (Kegiatan $record): string => (string) $record->kontaks()->whereNotNull('perusahaan_id')->distinct('perusahaan_id')->count('perusahaan_id').' Perusahaan')
                                ->state(fn (Kegiatan $record): string => $record->jumlahSponsor().' Perusahaan')
                                ->badge()
                                ->color('warning')
                                ->icon('heroicon-o-building-office-2'),
                        ]),
                ]),

            Section::make('Deskripsi & Catatan Kegiatan')
                ->icon('heroicon-o-document-text')
                ->collapsible()
                ->schema([
                    TextEntry::make('catatan')
                        ->label('')
                        ->placeholder('Belum ada catatan untuk kegiatan ini.')
                        ->markdown(),
                ]),
        ]);
    }
}
