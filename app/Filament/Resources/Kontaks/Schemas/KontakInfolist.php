<?php

namespace App\Filament\Resources\Kontaks\Schemas;

use App\Filament\Resources\Kontaks\Tables\KontaksTable;
use App\Models\Kontak;
use Filament\Actions\Action;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class KontakInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Utama')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('perusahaan.nama_standar')
                            ->label('Perusahaan'),
                        TextEntry::make('perusahaan.industri')
                            ->label('Industri')
                            ->placeholder('-'),
                        TextEntry::make('nama')
                            ->label('Nama PIC')
                            ->weight('semibold'),
                        TextEntry::make('no_telepon')
                            ->label('No. Telepon')
                            ->copyable()
                            ->icon(Heroicon::OutlinedPhone)
                            ->extraAttributes(['class' => 'fi-in-text-phone', 'style' => 'white-space: nowrap;'])
                            ->url(fn ($record) => filled($record->no_telepon) ? 'tel:'.$record->no_telepon : null)
                            ->suffixAction(
                                Action::make('chat_whatsapp')
                                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                                    ->color('success')
                                    ->tooltip('Kirim WhatsApp')
                                    ->url(fn (Kontak $record): string => KontaksTable::whatsappUrl($record))
                                    ->openUrlInNewTab()
                                    ->visible(fn ($record) => filled($record?->no_telepon))
                            ),
                        TextEntry::make('email')
                            ->label('Email PIC')
                            ->copyable()
                            ->icon('heroicon-o-envelope')
                            ->extraAttributes(['style' => 'white-space: nowrap;'])
                            ->url(fn ($record) => filled($record->email) ? 'mailto:'.$record->email : null)
                            ->placeholder('-'),
                    ]),
                Section::make('Catatan Follow-up')
                    ->columnSpanFull()
                    ->collapsible()
                    ->schema([
                        TextEntry::make('catatan')
                            ->label('')
                            ->placeholder('Belum ada catatan follow-up untuk kontak ini.')
                            ->columnSpanFull(),
                    ]),
                Section::make('Kegiatan')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('kegiatan.nama_event')
                            ->label('Kegiatan')
                            ->placeholder('-'),
                        TextEntry::make('kegiatan.tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->date('d M Y')
                            ->placeholder('-'),
                        TextEntry::make('kategoriKegiatan.nama_kategori')
                            ->label('Kategori')
                            ->placeholder('-'),
                    ]),
                Section::make('Informasi Sistem')
                    ->columns(2)
                    ->schema([
                        IconEntry::make('status_format_valid')
                            ->label('Format Nomor Valid')
                            ->boolean(),
                        TextEntry::make('updatedBy.name')
                            ->label('Diperbarui oleh')
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->label('Dibuat pada')
                            ->dateTime('d M Y H:i'),
                        TextEntry::make('updated_at')
                            ->label('Terakhir diperbarui')
                            ->dateTime('d M Y H:i'),
                    ]),
            ]);
    }
}
