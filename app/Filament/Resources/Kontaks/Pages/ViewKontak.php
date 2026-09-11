<?php

declare(strict_types=1);

namespace App\Filament\Resources\Kontaks\Pages;

use App\Filament\Resources\Kontaks\KontakResource;
use App\Models\Kontak;
use App\Support\PhoneNormalizer;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewKontak extends ViewRecord
{
    protected static string $resource = KontakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('whatsapp')
                ->label('Kirim WhatsApp')
                ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                ->color('success')
                ->url(fn (Kontak $record): string => self::whatsappUrl($record))
                ->openUrlInNewTab()
                ->visible(fn (Kontak $record): bool => filled($record->no_telepon)),
            EditAction::make(),
        ];
    }

    protected static function whatsappUrl(Kontak $record): string
    {
        $nomor = PhoneNormalizer::normalize($record->no_telepon);
        if ($nomor === null) {
            return '#';
        }

        $namaPic = trim((string) $record->nama);
        $sapaan = $namaPic !== '' ? 'Halo Bapak/Ibu '.$namaPic : 'Halo Bapak/Ibu';
        $namaEvent = trim((string) $record->kegiatan?->nama_event);
        $pesan = $namaEvent !== ''
            ? "{$sapaan}, perkenalkan kami dari tim sponsorship {$namaEvent}. Ada yang bisa kami bantu terkait kerja sama sponsorship?"
            : "{$sapaan}, perkenalkan kami dari tim sponsorship. Ada yang bisa kami bantu terkait kerja sama sponsorship?";

        return 'https://wa.me/'.$nomor.'?text='.rawurlencode($pesan);
    }
}
