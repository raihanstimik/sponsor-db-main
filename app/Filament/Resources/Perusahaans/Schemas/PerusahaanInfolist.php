<?php

declare(strict_types=1);

namespace App\Filament\Resources\Perusahaans\Schemas;

use App\Filament\Resources\Kontaks\KontakResource;
use App\Models\Perusahaan;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Actions;
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
        return $schema->components([
            // Header Card Identitas Perusahaan
            Section::make()
                ->schema([
                    Grid::make(['default' => 1, 'sm' => 12])
                        ->schema([
                            TextEntry::make('inisial')
                                ->label('')
                                ->formatStateUsing(fn (Perusahaan $record): string => $record->inisial)
                                ->extraAttributes([
                                    'class' => 'w-14 h-14 rounded-2xl bg-gradient-to-br from-[#18225E] to-[#1E294B] text-white flex items-center justify-center font-bold text-xl shadow-md tracking-wider ring-2 ring-orange-500/30',
                                ])
                                ->columnSpan(['default' => 12, 'sm' => 3]),

                            Group::make([
                                TextEntry::make('nama_standar')
                                    ->label('')
                                    ->weight(FontWeight::Bold)
                                    ->size(TextSize::Large)
                                    ->color('primary')
                                    ->suffixAction(
                                        Action::make('verified')
                                            ->icon('heroicon-m-check-badge')
                                            ->color('warning')
                                            ->tooltip('Entitas Terverifikasi ICM')
                                    ),
                                TextEntry::make('industri')
                                    ->label('')
                                    ->badge()
                                    ->color('warning')
                                    ->placeholder('Sektor Umum'),
                            ])->columnSpan(['default' => 12, 'sm' => 9]),
                        ]),
                ])
                ->extraAttributes(['class' => '!p-4 bg-slate-50/50 dark:bg-slate-900/30 rounded-2xl border border-slate-200/60 dark:border-slate-800']),

            // Metrik Ringkas Partner
            Grid::make(2)
                ->schema([
                    TextEntry::make('kontaks_count')
                        ->label('PIC Terhubung')
                        ->state(fn (Perusahaan $record): string => $record->kontaks()->count().' Kontak')
                        ->icon('heroicon-o-user-group')
                        ->badge()
                        ->color('success')
                        ->size(TextSize::Medium)
                        ->weight(FontWeight::SemiBold),

                    TextEntry::make('kegiatans_count')
                        ->label('Partisipasi Kongres')
                        ->state(fn (Perusahaan $record): string => $record->kegiatanPernahDiikuti()->count().' Event')
                        ->icon('heroicon-o-calendar-days')
                        ->badge()
                        ->color('primary')
                        ->size(TextSize::Medium)
                        ->weight(FontWeight::SemiBold),
                ]),

            // Informasi Alamat & Website
            Section::make('Informasi Kantor & Kontak Resmi')
                ->icon('heroicon-o-building-office')
                ->collapsible()
                ->collapsed(fn (Perusahaan $record): bool => blank($record->alamat) && blank($record->website))
                ->schema([
                    TextEntry::make('alamat')
                        ->label('Alamat Kantor Pusat')
                        ->icon('heroicon-o-map-pin')
                        ->placeholder('Belum diatur'),
                    TextEntry::make('website')
                        ->label('Website Resmi')
                        ->icon('heroicon-o-globe-alt')
                        ->url(fn (?string $state): ?string => filled($state) ? (str_starts_with($state, 'http') ? $state : 'https://'.$state) : null)
                        ->openUrlInNewTab()
                        ->placeholder('Belum diatur'),
                ])->columns(2),

            // Catatan Strategis Kerjasama
            Section::make('Catatan Kerjasama Internal')
                ->icon('heroicon-o-document-text')
                ->schema([
                    TextEntry::make('catatan')
                        ->label('')
                        ->placeholder('Belum ada catatan khusus kerjasama untuk perusahaan ini.')
                        ->markdown()
                        ->extraAttributes(['class' => 'italic text-slate-700 dark:text-slate-300 text-sm']),
                ]),

            // Riwayat Kegiatan Kongres Medis yang Pernah Diikuti
            Section::make('Histori Partisipasi Kongres Medis')
                ->icon('heroicon-o-clock')
                ->description('Event kongres yang diikuti melalui PIC kontak perusahaan ini')
                ->schema([
                    ViewEntry::make('histori_event')
                        ->label('')
                        ->view('filament.infolists.entries.perusahaan-event-timeline'),
                ]),

            // Tombol Tindakan
            Actions::make([
                Action::make('keKontak')
                    ->label('Lihat Semua PIC Terkait di Modul Kontak')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('primary')
                    ->url(fn (Perusahaan $record): string => KontakResource::getUrl().'?tableFilters[perusahaan][value]='.$record->id)
                    ->openUrlInNewTab(),
            ]),
        ]);
    }
}
