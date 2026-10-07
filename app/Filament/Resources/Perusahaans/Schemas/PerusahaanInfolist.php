<?php

declare(strict_types=1);

namespace App\Filament\Resources\Perusahaans\Schemas;

use App\Models\Perusahaan;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Notifications\Notification;
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
                // 1. Identitas & Ringkasan Eksekutif Minimalis (Anti-Clipped)
                ViewEntry::make('header_card')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->view('filament.infolists.entries.perusahaan-header-card'),

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
                    ->description('Rekam jejak sponsorship, nominal dana, paket, dan analisis keaktifan perusahaan')
                    ->compact()
                    ->columnSpanFull()
                    ->headerActions([
                        Action::make('catat_riwayat_cepat')
                            ->label('Catat Sponsorship')
                            ->icon('heroicon-o-plus-circle')
                            ->color('primary')
                            ->modalHeading('Catat Riwayat Sponsorship Baru')
                            ->modalWidth('xl')
                            ->slideOver()
                            ->form([
                                Select::make('kegiatan_id')
                                    ->label('Pilih dari Master Event')
                                    ->relationship('kegiatan', 'nama_event')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('-- Pilih dari master kegiatan resmi atau isi manual di bawah --')
                                    ->reactive(),

                                TextInput::make('nama_event_manual')
                                    ->label('Nama Event Bebas / Mandiri')
                                    ->placeholder('Contoh: Kongres Nasional 2021...')
                                    ->visible(fn ($get): bool => blank($get('kegiatan_id'))),

                                TextInput::make('tahun')
                                    ->label('Tahun Pelaksanaan')
                                    ->numeric()
                                    ->minValue(1990)
                                    ->maxValue((int) date('Y') + 5)
                                    ->default((int) date('Y'))
                                    ->required(),

                                DatePicker::make('tanggal_partisipasi')
                                    ->label('Tanggal Pelaksanaan (Opsional)'),

                                Select::make('paket')
                                    ->label('Paket Sponsorship')
                                    ->options([
                                        'Platinum' => '💎 Platinum (Tier Utama)',
                                        'Gold' => '🥇 Gold (Tier Menengah Atas)',
                                        'Silver' => '🥈 Silver (Tier Standar)',
                                        'Bronze' => '🥉 Bronze (Tier Dasar)',
                                        'Simposium' => '🎤 Simposium Satelit',
                                        'Booth' => '🎪 Sewa Booth Pameran',
                                        'Reguler' => '📦 Reguler',
                                        'Custom' => '⚙️ Custom / Kemitraan Khusus',
                                    ])
                                    ->searchable()
                                    ->placeholder('-- Pilih paket sponsorship --'),

                                TextInput::make('nominal')
                                    ->label('Nominal Dana Sponsorship (Rp)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->placeholder('0'),

                                TextInput::make('bentuk_partisipasi')
                                    ->label('Bentuk Partisipasi')
                                    ->placeholder('Contoh: Sewa Booth 3x3, Simposium, Kit Seminar...')
                                    ->datalist([
                                        'Sewa Booth 3x3',
                                        'Sewa Booth 2x2',
                                        'Simposium Satelit',
                                        'Workshop Klinis',
                                        'Goodie Bag & Kit Seminar',
                                        'Branding Lanyard & ID Card',
                                        'Donasi Pendidikan',
                                        'Iklan Buku Program',
                                    ])
                                    ->columnSpanFull(),

                                Textarea::make('catatan')
                                    ->label('Keterangan / Benefit Tambahan')
                                    ->placeholder('Catatan PIC, nomor invoice, fasilitas khusus...')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->action(function (Perusahaan $record, array $data): void {
                                $record->riwayatSponsorships()->create($data);
                                Notification::make()
                                    ->title('Riwayat sponsorship berhasil dicatat')
                                    ->success()
                                    ->send();
                            }),
                    ])
                    ->schema([
                        ViewEntry::make('histori_event')
                            ->hiddenLabel()
                            ->view('filament.infolists.entries.perusahaan-event-timeline'),
                    ]),
            ]);
    }
}
