<?php

declare(strict_types=1);

namespace App\Filament\Resources\Kontaks\Schemas;

use App\Models\Kontak;
use App\Support\PhoneNormalizer;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

class KontakInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                // 1. Identitas Perusahaan & PIC (Satu Kolom Penuh)
                Section::make('Identitas Sponsor')
                    ->compact()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('perusahaan.nama_standar')
                            ->label('Nama Perusahaan')
                            ->weight(FontWeight::Bold)
                            ->size(TextSize::Large)
                            ->color('primary')
                            ->placeholder('-'),

                        TextEntry::make('perusahaan.industri')
                            ->label('Sektor / Industri')
                            ->badge()
                            ->color('gray')
                            ->placeholder('Sektor Umum'),

                        TextEntry::make('nama')
                            ->label('Nama PIC')
                            ->icon(Heroicon::OutlinedUser)
                            ->weight(FontWeight::SemiBold)
                            ->placeholder('(Tanpa Nama PIC)'),

                        TextEntry::make('updatedBy.name')
                            ->label('PIC Pengelola / Diperbarui Oleh')
                            ->placeholder('-'),
                    ]),

                // 2. Data Komunikasi & Event Sponsor (2 Kolom Kompak)
                Section::make('Komunikasi & Kegiatan')
                    ->compact()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('no_telepon')
                            ->label('No. Telepon')
                            ->copyable()
                            ->copyMessage('Nomor telepon disalin')
                            ->formatStateUsing(fn (?string $state): ?string => PhoneNormalizer::formatDisplay($state))
                            ->hint(fn (Kontak $record): ?string => PhoneNormalizer::isLandline($record->no_telepon) ? 'Telepon Kantor' : null)
                            ->icon(Heroicon::OutlinedPhone)
                            ->suffixAction(
                                Action::make('infolist_whatsapp')
                                    ->label('Chat WA')
                                    ->tooltip('Kirim pesan via WhatsApp Web')
                                    ->icon(PhoneNormalizer::whatsappIconHtml('w-3.5 h-3.5'))
                                    ->color('success')
                                    ->extraAttributes([
                                        'class' => 'badge-whatsapp-inline',
                                    ])
                                    ->url(fn (Kontak $record): string => PhoneNormalizer::whatsappUrl($record->no_telepon))
                                    ->openUrlInNewTab()
                                    ->visible(fn (Kontak $record): bool => filled($record->no_telepon) && PhoneNormalizer::isWhatsappSupported($record->no_telepon))
                            )
                            ->placeholder('-'),

                        TextEntry::make('email')
                            ->label('Email PIC')
                            ->copyable()
                            ->icon(Heroicon::OutlinedEnvelope)
                            ->url(fn ($record) => filled($record->email) ? 'mailto:'.$record->email : null)
                            ->placeholder('-'),

                        TextEntry::make('kegiatans.nama_event')
                            ->label('Kegiatan / Event yang Diikuti')
                            ->badge()
                            ->color(fn (Kontak $record): ?string => $record->kegiatan?->warna ?? $record->kategoriKegiatan?->warna ?? 'primary')
                            ->placeholder('-'),

                        TextEntry::make('kategoriKegiatan.nama_kategori')
                            ->label('Kategori Medis')
                            ->badge()
                            ->color(fn (Kontak $record): ?string => $record->kategoriKegiatan?->warna ?? 'primary')
                            ->placeholder('-'),
                    ]),

                // 3. Catatan Follow-up (Tanpa Dropdown / Selalu Tampil Rapi)
                Section::make('Catatan Follow-up')
                    ->compact()
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('catatan')
                            ->hiddenLabel()
                            ->placeholder('Belum ada catatan follow-up untuk kontak ini.')
                            ->extraAttributes(fn (Kontak $record): array => filled($record->catatan)
                                ? ['class' => 'text-sm p-3 rounded-lg bg-amber-500/10 border-l-4 border-amber-500 text-slate-800 dark:text-slate-200']
                                : ['class' => 'text-sm text-slate-400 italic']
                            )
                            ->columnSpanFull(),
                    ]),

                // 4. Informasi Sistem (Tanpa Dropdown / Selalu Tampil Rapi)
                Section::make('Informasi Sistem')
                    ->compact()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('status_format_valid')
                            ->label('Format Nomor')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Format Valid' : 'Format Tidak Standar')
                            ->color(fn (bool $state): string => $state ? 'success' : 'warning'),

                        TextEntry::make('tahun_event')
                            ->label('Tahun Event')
                            ->state(function (Kontak $record): string {
                                if ($record->kegiatans->isNotEmpty()) {
                                    $years = $record->kegiatans->pluck('tanggal_mulai')->filter()->map(fn ($d) => $d->format('Y'))->unique()->implode(', ');
                                    if ($years !== '') {
                                        return $years;
                                    }
                                }
                                return $record->kegiatan?->tanggal_mulai?->format('Y') ?? '-';
                            })
                            ->placeholder('-'),

                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d M Y H:i')
                            ->size(TextSize::Small),

                        TextEntry::make('updated_at')
                            ->label('Terakhir Diperbarui')
                            ->dateTime('d M Y H:i')
                            ->size(TextSize::Small),
                    ]),
            ]);
    }
}
