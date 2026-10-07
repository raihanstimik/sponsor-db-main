<?php

namespace App\Filament\Resources\Perusahaans\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PerusahaanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Perusahaan Baku')
                    ->description('Data primer entitas korporasi sponsor')
                    ->icon('heroicon-o-building-office-2')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nama_standar')
                            ->label('Nama Perusahaan Baku')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('Contoh: Kalbe Farma, Bio Farma')
                            ->helperText('Gunakan nama standar/baku tanpa singkatan tidak resmi.'),
                        TextInput::make('industri')
                            ->label('Bidang Industri / Sektor')
                            ->maxLength(255)
                            ->placeholder('Contoh: Farmasi & Suplemen, Alat Medis, Nutrisi')
                            ->datalist([
                                'Farmasi & Suplemen',
                                'Alat Kesehatan & Diagnostik',
                                'Nutrisi & Gizi Klinis',
                                'Estetika & Laser Medis',
                                'Rumah Sakit & Layanan Kesehatan',
                                'Teknologi Informasi Medis',
                            ]),
                    ]),

                Section::make('Informasi Kantor & Kontak Resmi')
                    ->description('Alamat kantor pusat dan website resmi korporasi')
                    ->icon('heroicon-o-globe-alt')
                    ->columns(2)
                    ->schema([
                        TextInput::make('alamat')
                            ->label('Alamat Kantor Pusat')
                            ->maxLength(255)
                            ->placeholder('Kota / Kawasan Industri'),
                        TextInput::make('website')
                            ->label('Website Resmi Korporasi')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://...'),
                    ]),

                Section::make('Catatan Kerjasama Internal')
                    ->description('Riwayat perjanjian, paket sponsorship, atau informasi penting')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->schema([
                        Textarea::make('catatan')
                            ->label('Catatan Kerjasama')
                            ->rows(3)
                            ->placeholder('Misal: Mitra sponsor rutin kategori simposium satelit dan pameran.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Riwayat Sponsorship & Keterlibatan Event')
                    ->description('Kelola seluruh event yang disponsori perusahaan secara langsung di form ini')
                    ->icon('heroicon-o-trophy')
                    ->collapsible()
                    ->schema([
                        Repeater::make('riwayatSponsorships')
                            ->relationship('riwayatSponsorships')
                            ->label('Daftar Partisipasi Event')
                            ->itemLabel(function (array $state): ?string {
                                $tahun = $state['tahun'] ?? '-';
                                $paket = filled($state['paket'] ?? null) ? "💎 {$state['paket']}" : 'Event';
                                $nominal = ! empty($state['nominal']) && (float) $state['nominal'] > 0
                                    ? ' (Rp '.number_format((float) $state['nominal'], 0, ',', '.').')'
                                    : '';

                                return "{$tahun} — {$paket}{$nominal}";
                            })
                            ->collapsible()
                            ->collapsed(fn (string $operation): bool => $operation === 'edit')
                            ->cloneable()
                            ->addActionLabel('+ Catat Riwayat Sponsorship Baru')
                            ->columns(2)
                            ->schema([
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
                            ]),
                    ]),
            ]);
    }
}
