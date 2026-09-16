<?php

declare(strict_types=1);

namespace App\Filament\Resources\Kegiatans\Schemas;

use App\Models\Kegiatan;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class KegiatanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                // 1. Identitas & Ringkasan Eksekutif Kegiatan
                Section::make()
                    ->compact()
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 12])
                            ->schema([
                                TextEntry::make('initial_badge')
                                    ->hiddenLabel()
                                    ->state(fn (Kegiatan $record): string => mb_strtoupper(mb_substr($record->nama_event, 0, 2)))
                                    ->extraAttributes(fn (Kegiatan $record): array => [
                                        'class' => 'w-12 h-12 rounded-xl text-white flex items-center justify-center font-bold text-lg shadow-sm tracking-wider',
                                        'style' => 'background-color: '.($record->warna_efektif ?: '#18225E').';',
                                    ])
                                    ->columnSpan(['default' => 12, 'sm' => 2]),

                                Group::make([
                                    TextEntry::make('nama_event')
                                        ->hiddenLabel()
                                        ->weight(FontWeight::Bold)
                                        ->size(TextSize::Large)
                                        ->color('primary'),

                                    Grid::make(['default' => 1, 'sm' => 3])
                                        ->schema([
                                            TextEntry::make('kategoriKegiatan.nama_kategori')
                                                ->hiddenLabel()
                                                ->badge()
                                                ->color('primary')
                                                ->icon('heroicon-o-tag')
                                                ->placeholder('Tanpa Kategori Medis'),

                                            TextEntry::make('status_kegiatan')
                                                ->hiddenLabel()
                                                ->state(fn (Kegiatan $record): string => match ($record->status_event) {
                                                    'Mendatang' => 'Mendatang (Open Sponsor)',
                                                    'Berlangsung' => 'Sedang Berlangsung',
                                                    default => $record->status_event,
                                                })
                                                ->badge()
                                                ->color(fn (Kegiatan $record): string => $record->status_color)
                                                ->icon(fn (Kegiatan $record): string => match ($record->status_event) {
                                                    'Berlangsung' => 'heroicon-m-signal',
                                                    'Mendatang' => 'heroicon-m-clock',
                                                    'Selesai' => 'heroicon-m-check-circle',
                                                    default => 'heroicon-m-calendar',
                                                }),

                                            TextEntry::make('durasi_badge')
                                                ->hiddenLabel()
                                                ->state(fn (Kegiatan $record): string => $record->durasi_hari ? "{$record->durasi_hari} Hari Kongres" : ($record->tanggal_mulai ? $record->tanggal_mulai->format('Y') : 'Kongres Medis'))
                                                ->badge()
                                                ->color('gray')
                                                ->icon('heroicon-o-calendar-days'),
                                        ]),
                                ])->columnSpan(['default' => 12, 'sm' => 10]),
                            ]),
                    ])
                    ->extraAttributes([
                        'class' => '!p-4 bg-slate-50/70 dark:bg-slate-900/40 rounded-xl border border-slate-200/70 dark:border-slate-800',
                    ]),

                // 2. Jadwal & Lokasi Pelaksanaan
                Section::make('Jadwal & Lokasi Kongres')
                    ->icon('heroicon-o-map-pin')
                    ->compact()
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'sm' => 2])
                    ->schema([
                        TextEntry::make('jadwal')
                            ->label('Jadwal Pelaksanaan')
                            ->state(fn (Kegiatan $record): string => $record->jadwal_dan_durasi)
                            ->icon('heroicon-o-calendar'),

                        TextEntry::make('venue')
                            ->label('Lokasi / Venue Acara')
                            ->icon('heroicon-o-building-office-2')
                            ->placeholder('Lokasi belum ditentukan'),
                    ]),

                // 3. Metrik Afiliasi PIC & Sponsor
                Section::make('Kemitraan & Sponsor Terafiliasi')
                    ->icon('heroicon-o-chart-bar')
                    ->compact()
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'sm' => 2])
                    ->schema([
                        TextEntry::make('kontaks_count')
                            ->label('PIC Kontak Terdaftar')
                            ->state(fn (Kegiatan $record): string => $record->kontaks()->count().' Kontak PIC')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-o-user-group'),

                        TextEntry::make('sponsors_count')
                            ->label('Perusahaan Sponsor Terhubung')
                            ->state(fn (Kegiatan $record): string => $record->jumlahSponsor().' Perusahaan')
                            ->badge()
                            ->color('warning')
                            ->icon('heroicon-o-building-office-2'),
                    ]),

                // 4. Deskripsi & Catatan Kegiatan
                Section::make('Deskripsi & Catatan Kegiatan')
                    ->icon('heroicon-o-document-text')
                    ->compact()
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('catatan')
                            ->hiddenLabel()
                            ->placeholder('Belum ada catatan khusus atau agenda untuk kegiatan ini.')
                            ->markdown()
                            ->extraAttributes(fn (Kegiatan $record): array => filled($record->catatan)
                                ? ['class' => 'text-sm p-3 rounded-lg bg-slate-50 dark:bg-slate-800/50 border-l-4 border-[#18225E] text-slate-800 dark:text-slate-200']
                                : ['class' => 'text-sm text-slate-400 dark:text-slate-500 italic py-1']
                            ),
                    ]),

                // 5. Daftar PIC & Sponsor Terhubung
                Section::make('Daftar PIC & Sponsor Terhubung')
                    ->icon('heroicon-o-user-group')
                    ->compact()
                    ->columnSpanFull()
                    ->schema([
                        ViewEntry::make('pic_list')
                            ->hiddenLabel()
                            ->view('filament.infolists.entries.kegiatan-pic-list'),
                    ]),
            ]);
    }
}
