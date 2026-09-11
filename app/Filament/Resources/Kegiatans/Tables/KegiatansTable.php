<?php

declare(strict_types=1);

namespace App\Filament\Resources\Kegiatans\Tables;

use App\Filament\Resources\Kontaks\KontakResource;
use App\Models\Kegiatan;
use App\Support\FilamentTableHelper;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KegiatansTable
{
    public static function configure(Table $table): Table
    {
        FilamentTableHelper::applyDefaultPresets($table);

        return $table
            ->columns([
                TextColumn::make('nama_event')
                    ->label('Kegiatan & Venue')
                    ->searchable()
                    ->weight(FontWeight::Bold)
                    ->color('primary')
                    ->description(fn (Kegiatan $record): ?string => $record->venue ? '📍 '.$record->venue : null)
                    ->wrap()
                    ->grow(),

                TextColumn::make('kategoriKegiatan.nama_kategori')
                    ->label('Kategori Medis')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (Kegiatan $record): string => $record->warna_efektif),

                TextColumn::make('tanggal_mulai')
                    ->label('Jadwal & Status')
                    ->sortable()
                    ->formatStateUsing(fn (Kegiatan $record): string => $record->tanggal_mulai && $record->tanggal_selesai
                        ? $record->tanggal_mulai->format('d M Y').' - '.$record->tanggal_selesai->format('d M Y')
                        : ($record->tanggal_mulai ? $record->tanggal_mulai->format('d M Y') : 'Belum Dijadwalkan'))
                    ->description(function (Kegiatan $record): string {
                        $durasi = $record->durasi_hari ? "Durasi {$record->durasi_hari} Hari" : null;
                        $status = $record->status_event;

                        return $durasi ? "{$durasi} • {$status}" : $status;
                    })
                    ->icon(Heroicon::OutlinedCalendarDays),

                TextColumn::make('kontaks_count')
                    ->label('Sponsor & PIC')
                    ->counts('kontaks')
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => (int) $state.' PIC')
                    ->description(fn (Kegiatan $record): string => $record->jumlahSponsor().' Sponsor')
                    ->icon(Heroicon::OutlinedUserGroup)
                    ->color('info'),

                ColorColumn::make('warna')
                    ->label('Hex Warna')
                    ->copyable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kategori_kegiatan_id')
                    ->label('Kategori Medis')
                    ->relationship('kategoriKegiatan', 'nama_kategori')
                    ->preload()
                    ->searchable(),

                SelectFilter::make('status')
                    ->label('Status Event')
                    ->options([
                        'mendatang' => 'Mendatang (Open Sponsor)',
                        'berlangsung' => 'Sedang Berlangsung',
                        'selesai' => 'Selesai',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $val = $data['value'] ?? null;
                        $today = now()->toDateString();
                        if ($val === 'mendatang') {
                            return $query->whereDate('tanggal_mulai', '>', $today);
                        }
                        if ($val === 'berlangsung') {
                            return $query->whereDate('tanggal_mulai', '<=', $today)
                                ->whereDate('tanggal_selesai', '>=', $today);
                        }
                        if ($val === 'selesai') {
                            return $query->whereDate('tanggal_selesai', '<', $today);
                        }

                        return $query;
                    }),

                SelectFilter::make('tahun')
                    ->label('Tahun Event')
                    ->options([
                        '2026' => 'Tahun 2026',
                        '2025' => 'Tahun 2025',
                        '2024' => 'Tahun 2024',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $year = $data['value'] ?? null;
                        if (filled($year)) {
                            return $query->whereYear('tanggal_mulai', (int) $year);
                        }

                        return $query;
                    }),
            ])
            ->recordActions([
                Action::make('lihat_kontak')
                    ->label('')
                    ->tooltip(fn (Kegiatan $record): string => "Buka daftar PIC untuk {$record->nama_event}")
                    ->icon(Heroicon::OutlinedUsers)
                    ->color('primary')
                    ->url(fn (Kegiatan $record): string => KontakResource::getUrl('index', [
                        'tableFilters' => [
                            'kegiatan_id' => [
                                'values' => [$record->id],
                            ],
                        ],
                    ])),

                ViewAction::make()
                    ->label('')
                    ->tooltip('Overview Detail Kegiatan')
                    ->slideOver()
                    ->color('info'),

                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Kegiatan'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => (bool) auth()->user()?->isAdmin()),
                ]),
            ])
            ->defaultSort('tanggal_mulai');
    }
}
