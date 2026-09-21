<?php

declare(strict_types=1);

namespace App\Filament\Resources\Kontaks\Schemas;

use App\Models\Kegiatan;
use App\Models\Kontak;
use App\Support\KlasifikasiTabel;
use App\Support\PhoneNormalizer;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class KontakForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 2])
            ->components([
                Section::make('Afiliasi Perusahaan & Kegiatan')
                    ->icon('heroicon-o-building-office-2')
                    ->compact()
                    ->schema([
                        Select::make('perusahaan_id')
                            ->label('Perusahaan Sponsor')
                            ->placeholder('Pilih perusahaan sponsor...')
                            ->prefixIcon('heroicon-m-building-office-2')
                            ->relationship('perusahaan', 'nama_standar', fn ($query) => $query->orderBy('nama_standar'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('nama_standar')
                                    ->label('Nama Perusahaan Baku')
                                    ->placeholder('Contoh: Kalbe Farma, Bio Farma')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->helperText('Gunakan nama standar/baku tanpa singkatan tidak resmi.'),
                                TextInput::make('industri')
                                    ->label('Bidang Industri / Sektor')
                                    ->placeholder('Contoh: Farmasi & Suplemen, Alat Medis')
                                    ->datalist([
                                        'Farmasi & Suplemen',
                                        'Alat Kesehatan & Diagnostik',
                                        'Nutrisi & Gizi Klinis',
                                        'Estetika & Laser Medis',
                                        'Rumah Sakit & Layanan Kesehatan',
                                        'Teknologi Informasi Medis',
                                    ])
                                    ->maxLength(255),
                            ])
                            ->createOptionModalHeading('Tambah Perusahaan Sponsor Baru'),

                        Hidden::make('kegiatan_id')->dehydrated(),

                        Select::make('kegiatans')
                            ->label('Kegiatan / Event yang Diikuti')
                            ->placeholder('Pilih satu atau beberapa kegiatan / event...')
                            ->prefixIcon('heroicon-m-calendar')
                            ->relationship('kegiatans', 'nama_event', fn ($query) => $query->orderBy('nama_event'))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set): void {
                                if (empty($state)) {
                                    return;
                                }
                                $ids = is_array($state) ? $state : [$state];
                                $firstId = reset($ids);
                                if ($firstId) {
                                    $kategoriId = Kegiatan::where('id', $firstId)->value('kategori_kegiatan_id');
                                    if ($kategoriId) {
                                        $set('kategori_kegiatan_id', $kategoriId);
                                    }
                                }
                            })
                            ->createOptionForm([
                                TextInput::make('nama_event')
                                    ->label('Nama Kegiatan / Event')
                                    ->placeholder('Contoh: PIT PERDAMI 2026 / INDAAC 2026')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('kategori_kegiatan_id')
                                    ->label('Kategori Medis / Spesialisasi')
                                    ->placeholder('Pilih kategori medis (opsional)...')
                                    ->relationship('kategoriKegiatan', 'nama_kategori', fn ($query) => $query->orderBy('nama_kategori'))
                                    ->searchable()
                                    ->preload(),
                                ColorPicker::make('warna')
                                    ->label('Warna Indikator')
                                    ->helperText('Opsional, mewarisi warna kategori bila kosong.'),
                                DatePicker::make('tanggal_mulai')
                                    ->label('Tanggal Mulai')
                                    ->placeholder('Pilih tanggal mulai...'),
                                TextInput::make('venue')
                                    ->label('Lokasi / Venue')
                                    ->placeholder('Contoh: Hotel Mulia Senayan, Jakarta')
                                    ->maxLength(255),
                            ])
                            ->createOptionModalHeading('Tambah Kegiatan / Event Baru'),

                        Select::make('kategori_kegiatan_id')
                            ->label('Kategori Medis / Spesialisasi')
                            ->placeholder('Pilih spesialisasi (opsional)...')
                            ->prefixIcon('heroicon-m-tag')
                            ->relationship('kategoriKegiatan', 'nama_kategori', fn ($query) => $query->orderBy('nama_kategori'))
                            ->searchable()
                            ->preload()
                            ->helperText('Otomatis terisi jika kegiatan dipilih, atau dapat ditentukan manual.')
                            ->createOptionForm([
                                TextInput::make('nama_kategori')
                                    ->label('Nama Kategori Medis')
                                    ->placeholder('Contoh: Onkologi & Ginekologi, Estetika')
                                    ->required()
                                    ->unique('kategori_kegiatans', 'nama_kategori')
                                    ->maxLength(255),
                                ColorPicker::make('warna')
                                    ->label('Warna Indikator')
                                    ->helperText('Warna khas untuk badge dan grafik distribusi.')
                                    ->default(fn () => KlasifikasiTabel::warnaKategori('')),
                                Textarea::make('deskripsi')
                                    ->label('Deskripsi Ringkas')
                                    ->placeholder('Penjelasan ringkas spesialisasi medis ini...')
                                    ->rows(2),
                            ])
                            ->createOptionModalHeading('Tambah Kategori Medis Baru'),
                    ]),

                Section::make('Informasi Kontak PIC')
                    ->icon('heroicon-o-user')
                    ->compact()
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama Lengkap PIC')
                            ->placeholder('Contoh: dr. Budi Santoso / Hendra')
                            ->prefixIcon('heroicon-m-user')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('no_telepon')
                            ->label('No. Telepon / WhatsApp')
                            ->tel()
                            ->placeholder('Contoh: 0811-1465-133 atau 628111465133')
                            ->prefixIcon('heroicon-m-phone')
                            ->helperText('Disimpan otomatis format 628xxx (siap WhatsApp).')
                            ->live(onBlur: true)
                            ->hint(function (?string $state, ?Kontak $record): ?string {
                                if (! filled($state)) {
                                    return null;
                                }
                                $norm = PhoneNormalizer::normalize($state);
                                if (! filled($norm) || strlen($norm) < 8) {
                                    return null;
                                }
                                $duplikat = Kontak::query()
                                    ->select(['id', 'nama', 'perusahaan_id'])
                                    ->with(['perusahaan:id,nama_standar'])
                                    ->where('no_telepon', $norm)
                                    ->when($record?->exists, fn ($q) => $q->where('id', '!=', $record->id))
                                    ->first();

                                if ($duplikat) {
                                    $namaPerusahaan = $duplikat->perusahaan?->nama_standar ?? 'perusahaan lain';

                                    return "⚠️ Sudah terdaftar: {$duplikat->nama} ({$namaPerusahaan})";
                                }

                                return null;
                            })
                            ->hintColor('warning')
                            ->hintIcon(function (?string $state, ?Kontak $record): ?string {
                                if (! filled($state)) {
                                    return null;
                                }
                                $norm = PhoneNormalizer::normalize($state);
                                if (! filled($norm) || strlen($norm) < 8) {
                                    return null;
                                }

                                $adaDuplikat = Kontak::query()
                                    ->where('no_telepon', $norm)
                                    ->when($record?->exists, fn ($q) => $q->where('id', '!=', $record->id))
                                    ->exists();

                                return $adaDuplikat ? 'heroicon-o-exclamation-triangle' : null;
                            })
                            ->maxLength(50),

                        TextInput::make('email')
                            ->label('Email PIC')
                            ->email()
                            ->placeholder('Contoh: pic@perusahaan.com')
                            ->prefixIcon('heroicon-m-envelope')
                            ->maxLength(255),
                    ]),

                Section::make('Catatan Follow-up & Negosiasi')
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->compact()
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('catatan')
                            ->label('Catatan Negosiasi / Follow-up')
                            ->placeholder('Catatan internal hasil pembicaraan, follow-up, preferensi paket sponsorship (Platinum/Gold), hasil pertemuan...')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}