<?php

declare(strict_types=1);

namespace App\Filament\Resources\Perusahaans\Pages;

use App\Filament\Resources\Perusahaans\PerusahaanResource;
use App\Models\Perusahaan;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPerusahaan extends ViewRecord
{
    protected static string $resource = PerusahaanResource::class;

    public function getTitle(): string
    {
        /** @var Perusahaan $record */
        $record = $this->getRecord();

        return 'Profil Perusahaan: '.$record->nama_standar;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
