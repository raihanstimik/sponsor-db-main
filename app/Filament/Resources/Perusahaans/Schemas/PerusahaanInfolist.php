<?php

declare(strict_types=1);

namespace App\Filament\Resources\Perusahaans\Schemas;

use App\Models\Perusahaan;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class PerusahaanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                // 1. Identitas & Ringkasan Eksekutif
                Section::make()
                    ->compact()
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 12])
                            ->schema([
                                TextEntry::make('inisial')
                                    ->hiddenLabel()
                                    ->formatStateUsing(fn (Perusahaan $record): string => $record->inisial)
                                    ->extraAttributes([
                                        'class' => 'w-12 h-12 rounded-xl bg-[#18225E] text-white flex items-center justify-center font-bold text-lg shadow-sm tracking-wider',
                                    ])
                                    ->columnSpan(['default' => 12, 'sm' => 2]),

                                Group::make([
                                    TextEntry::make('nama_standar')
                                        ->hiddenLabel()
                                        ->weight(FontWeight::Bold)
                                        ->size(TextSize::Large)
                                        ->color('primary'),

                                    Grid::make(['default' => 2, 'sm' => 3])
                                        ->schema([
                                            TextEntry::make('industri')
                                                ->hiddenLabel()
                                                ->badge()
                                                ->color('gray')
                                                ->placeholder('Sektor Umum'),

                                            TextEntry::make('kontaks_count')
                                                ->hiddenLabel()
                                                ->state(fn (Perusahaan $record): string => $record->kontaks()->count().' PIC Terhubung')
                                                ->badge()
                                                ->icon('heroicon-o-user-group')
                                                ->color('success'),

                                            TextEntry::make('kegiatans_count')
                                                ->hiddenLabel()
                                                ->state(fn (Perusahaan $record): string => $record->kegiatanPernahDiikuti()->count().' Event Diikuti')
                                                ->badge()
                                                ->icon('heroicon-o-calendar-days')
                                                ->color('primary'),
                                        ]),
                                ])->columnSpan(['default' => 12, 'sm' => 10]),
                            ]),
                    ])
                    ->extraAttributes([
                        'class' => '!p-4 bg-slate-50/70 dark:bg-slate-900/40 rounded-xl border border-slate-200/70 dark:border-slate-800',
                    ]),

                // 2. Informasi Kantor & Saluran Resmi
                Section::make('Informasi Kantor & Kontak Resmi')
                    ->icon('heroicon-o-building-office')
                    ->compact()
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'sm' => 2])
                    ->schema([
                        TextEntry::make('alamat')
                            ->label('Alamat Kantor Pusat')
                            ->icon('heroicon-o-map-pin')
                            ->placeholder('Belum tercatat'),

                        TextEntry::make('website')
                            ->label('Website Resmi')
                            ->icon('heroicon-o-globe-alt')
                            ->url(fn (?string $state): ?string => filled($state) ? (str_starts_with($state, 'http') ? $state : 'https://'.$state) : null)
                            ->openUrlInNewTab()
                            ->placeholder('Belum tercatat'),
                    ]),

                // 3. Catatan Kerjasama Internal
                Section::make('Catatan Kerjasama Internal')
                    ->icon('heroicon-o-document-text')
                    ->compact()
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('catatan')
                            ->hiddenLabel()
                            ->placeholder('Belum ada catatan khusus kerjasama untuk perusahaan ini.')
                            ->markdown()
                            ->extraAttributes(fn (Perusahaan $record): array => filled($record->catatan)
                                ? ['class' => 'text-sm p-3 rounded-lg bg-slate-50 dark:bg-slate-800/50 border-l-4 border-[#18225E] text-slate-800 dark:text-slate-200']
                                : ['class' => 'text-sm text-slate-400 dark:text-slate-500 italic py-1']
                            ),
                    ]),

                // 4. PIC Kontak Terhubung
                Section::make('Daftar PIC Terhubung')
                    ->icon('heroicon-o-user-group')
                    ->compact()
                    ->columnSpanFull()
                    ->schema([
                        ViewEntry::make('pic_list')
                            ->hiddenLabel()
                            ->view('filament.infolists.entries.perusahaan-pic-list'),
                    ]),

                // 5. Histori Partisipasi Kongres Medis
                Section::make('Histori Partisipasi Kongres Medis')
                    ->icon('heroicon-o-clock')
                    ->description('Event kongres yang diikuti melalui PIC kontak perusahaan ini')
                    ->compact()
                    ->columnSpanFull()
                    ->schema([
                        ViewEntry::make('histori_event')
                            ->hiddenLabel()
                            ->view('filament.infolists.entries.perusahaan-event-timeline'),
                    ]),
            ]);
    }
}