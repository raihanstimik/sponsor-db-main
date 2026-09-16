<?php

declare(strict_types=1);

namespace App\Filament\Resources\Kegiatans\Pages;

use App\Filament\Resources\Kegiatans\KegiatanResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewKegiatan extends ViewRecord
{
    protected static string $resource = KegiatanResource::class;

    public function getTitle(): string
    {
        /** @var \App\Models\Kegiatan $record */
        $record = $this->getRecord();

        return 'Rincian Kegiatan: '.$record->nama_event;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            EditAction::make()
                ->label('Edit Kegiatan'),
        ];
    }
}
