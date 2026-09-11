<?php

namespace App\Filament\Resources\Perusahaans\Schemas;

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
            ]);
    }
}
