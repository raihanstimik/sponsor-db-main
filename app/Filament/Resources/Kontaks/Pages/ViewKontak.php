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
                ->visible(fn (Kontak $record): bool => filled($record->no_telepon) && PhoneNormalizer::isWhatsappSupported($record->no_telepon)),
            EditAction::make(),
        ];
    }

    protected static function whatsappUrl(Kontak $record): string
    {
        return PhoneNormalizer::whatsappUrl($record->no_telepon);
    }
}
