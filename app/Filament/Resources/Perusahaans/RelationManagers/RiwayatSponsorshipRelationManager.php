<?php

declare(strict_types=1);

namespace App\Filament\Resources\Perusahaans\RelationManagers;

use App\Models\PerusahaanKegiatan;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use BackedEnum;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RiwayatSponsorshipRelationManager extends RelationManager
{
    protected static string $relationship = 'riwayatSponsorships';

    protected static ?string $title = 'Riwayat Sponsorship & Partisipasi Event';

    protected static string|BackedEnum|null $icon = 'heroicon-o-trophy';

    protected static ?string $modelLabel = 'Riwayat Sponsorship';

    protected static ?string $pluralModelLabel = 'Riwayat Sponsorship';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $count = $ownerRecord->riwayatSponsorships()->count();

        return $count > 0 ? (string) $count : null;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'sm' => 2])
                    ->schema([
                        Select::make('kegiatan_id')
                            ->label('Pilih dari Master Event')
                            ->relationship('kegiatan', 'nama_event')
                            ->searchable()
                            ->preload()
                            ->placeholder('-- Pilih dari master kegiatan resmi atau kosongkan jika mandiri --')
                            ->helperText('Pilih jika event ini sudah terdaftar di master data kegiatan.')
                            ->reactive(),

                        TextInput::make('nama_event_manual')
                            ->label('Nama Event Mandiri / Bebas')
                            ->placeholder('Contoh: Kongres Nasional 2021, Simposium Regional...')
                            ->helperText('Diisi apabila event tidak terdaftar di master kegiatan.')
                            ->maxLength(255)
                            ->visible(fn ($get): bool => blank($get('kegiatan_id'))),

                        TextInput::make('tahun')
                            ->label('Tahun Partisipasi')
                            ->numeric()
                            ->minValue(1990)
                            ->maxValue((int) date('Y') + 5)
                            ->default((int) date('Y'))
                            ->required()
                            ->helperText('Tahun pelaksanaan event sponsorship (wajib)'),

                        DatePicker::make('tanggal_partisipasi')
                            ->label('Tanggal Pelaksanaan (Opsional)')
                            ->helperText('Tanggal spesifik kegiatan sponsorship'),

                        TextInput::make('nominal')
                            ->label('Nominal Dana Sponsorship (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('0')
                            ->helperText('Kosongkan jika nominal tidak diketahui atau berupa in-kind'),

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
                            ->placeholder('-- Kosongkan jika tanpa paket khusus --'),

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
                            ->label('Keterangan / Catatan Negosiasi')
                            ->placeholder('Catatan PIC, nomor invoice, fasilitas khusus, atau histori kesepakatan...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_event')
            ->defaultSort('tahun', 'desc')
            ->columns([
                TextColumn::make('tahun')
                    ->label('Tahun')
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->badge()
                    ->color(fn (PerusahaanKegiatan $record): string => match (true) {
                        $record->tahun >= (int) date('Y') - 1 => 'success',
                        $record->tahun >= (int) date('Y') - 3 => 'primary',
                        default => 'gray',
                    }),

                TextColumn::make('nama_event')
                    ->label('Nama Event')
                    ->searchable(['nama_event_manual'])
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->description(function (PerusahaanKegiatan $record): ?string {
                        if ($record->tanggal_partisipasi) {
                            return '📅 '.$record->tanggal_partisipasi->format('d M Y');
                        }
                        if ($record->kegiatan?->venue) {
                            return '📍 '.$record->kegiatan->venue;
                        }

                        return null;
                    })
                    ->wrap(),

                TextColumn::make('paket')
                    ->label('Paket')
                    ->badge()
                    ->color(fn (PerusahaanKegiatan $record): string => $record->paket_color)
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('nominal')
                    ->label('Nominal Dana')
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => $state > 0 ? 'Rp '.number_format((float) $state, 0, ',', '.') : '-')
                    ->weight(FontWeight::Bold)
                    ->color(fn ($state): string => $state > 0 ? 'success' : 'gray'),

                TextColumn::make('bentuk_partisipasi')
                    ->label('Bentuk Partisipasi')
                    ->placeholder('-')
                    ->limit(25),

                TextColumn::make('catatan')
                    ->label('Keterangan')
                    ->placeholder('-')
                    ->limit(35)
                    ->tooltip(fn (?string $state): ?string => $state),
            ])
            ->filters([
                SelectFilter::make('tahun')
                    ->label('Filter Tahun')
                    ->options(function (): array {
                        $years = PerusahaanKegiatan::query()
                            ->whereNotNull('tahun')
                            ->distinct()
                            ->orderByDesc('tahun')
                            ->pluck('tahun', 'tahun')
                            ->toArray();

                        return array_map('strval', $years);
                    }),

                SelectFilter::make('paket')
                    ->label('Filter Paket')
                    ->options([
                        'Platinum' => 'Platinum',
                        'Gold' => 'Gold',
                        'Silver' => 'Silver',
                        'Bronze' => 'Bronze',
                        'Simposium' => 'Simposium',
                        'Booth' => 'Booth',
                        'Reguler' => 'Reguler',
                        'Custom' => 'Custom',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Catat Riwayat Sponsorship')
                    ->icon('heroicon-m-plus-circle')
                    ->modalHeading('Catat Riwayat Sponsorship Baru')
                    ->slideOver()
                    ->modalWidth('xl')
                    ->successNotificationTitle('Riwayat sponsorship berhasil ditambahkan'),
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver()
                    ->modalHeading('Ubah Riwayat Sponsorship')
                    ->modalWidth('xl')
                    ->successNotificationTitle('Riwayat sponsorship berhasil diperbarui'),
                DeleteAction::make()
                    ->modalHeading('Hapus Riwayat Sponsorship')
                    ->successNotificationTitle('Riwayat sponsorship berhasil dihapus'),
            ])
            ->emptyStateHeading('Belum Ada Riwayat Sponsorship')
            ->emptyStateDescription('Klik tombol di atas untuk mencatat riwayat event kongres atau simposium yang pernah disponsori oleh perusahaan ini.')
            ->emptyStateIcon('heroicon-o-chart-bar');
    }
}
