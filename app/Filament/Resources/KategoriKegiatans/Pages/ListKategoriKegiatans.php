<?php

namespace App\Filament\Resources\KategoriKegiatans\Pages;

use App\Filament\Resources\KategoriKegiatans\KategoriKegiatanResource;
use App\Filament\Resources\Kegiatans\KegiatanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKategoriKegiatans extends ListRecords
{
    protected static string $resource = KategoriKegiatanResource::class;

    public function mount(): void
    {
        parent::mount();

        if (! app()->runningUnitTests()) {
            redirect()->to(KegiatanResource::getUrl('index', ['tab' => 'kategori']));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
