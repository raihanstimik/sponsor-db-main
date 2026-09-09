<?php

namespace App\Filament\Resources\Kontaks\Schemas;

use App\Models\Kegiatan;
use App\Models\Kontak;
use App\Support\PhoneNormalizer;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class KontakForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('perusahaan_id')
                    ->label('Perusahaan')
                    ->relationship('perusahaan', 'nama_standar')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('nama_standar')
                            ->label('Nama Standar')
                            ->required()
                            ->unique()
                            ->maxLength(255),
                        TextInput::make('industri')
                            ->label('Industri')
                            ->maxLength(255),
                    ]),
                Select::make('kegiatan_id')
                    ->label('Kegiatan')
                    ->relationship('kegiatan', 'nama_event')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (?string $state, Set $set): void {
                        $kategoriId = $state ? Kegiatan::find($state)?->kategori_kegiatan_id : null;
                        $set('kategori_kegiatan_id', $kategoriId);
                    }),
                Select::make('kategori_kegiatan_id')
                    ->label('Kategori')
                    ->relationship('kategoriKegiatan', 'nama_kategori')
                    ->searchable()
                    ->preload(),
                TextInput::make('nama')
                    ->label('Nama PIC')
                    ->required()
                    ->maxLength(255),
                TextInput::make('no_telepon')
                    ->label('No. Telepon')
                    ->tel()
                    ->placeholder('contoh: 0811-1465-133')
                    ->helperText('Otomatis disimpan sebagai 628xxxxxxxxxx (tanpa + / 0 di depan).')
                    ->live(onBlur: true)
                    ->hint(function (?string $state, ?Kontak $record): ?string {
                        if (! filled($state)) {
                            return null;
                        }
                        $norm = PhoneNormalizer::normalize($state);
                        if (! filled($norm) || strlen($norm) < 7) {
                            return null;
                        }
                        $duplikat = Kontak::query()
                            ->with('perusahaan')
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
                    ->hintIcon('heroicon-o-exclamation-triangle')
                    ->helperText('Otomatis disimpan sebagai 628xxxxxxxxxx (tanpa + / 0 di depan). Tetap dapat disimpan jika nomor bersama.')
                    ->maxLength(50),
                TextInput::make('email')
                    ->label('Email PIC')
                    ->email()
                    ->placeholder('contoh: pic@perusahaan.com')
                    ->maxLength(255),
                Toggle::make('status_format_valid')
                    ->label('Format Nomor Valid')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Dihitung otomatis dari format nomor.'),
                Select::make('status_verifikasi')
                    ->label('Status Verifikasi')
                    ->options([
                        'terverifikasi' => 'Terverifikasi',
                        'perlu_dicek' => 'Perlu dicek',
                        'tidak_aktif' => 'Tidak aktif',
                    ])
                    ->default('terverifikasi')
                    ->required(),
                Textarea::make('catatan')
                    ->label('Catatan Follow-up / Negosiasi')
                    ->placeholder('Catatan internal hasil pembicaraan, follow-up, atau preferensi sponsor...')
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
