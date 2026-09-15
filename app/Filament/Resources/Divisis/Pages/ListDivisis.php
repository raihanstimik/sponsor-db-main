<?php

declare(strict_types=1);

namespace App\Filament\Resources\Divisis\Pages;

use App\Filament\Resources\Divisis\DivisiResource;
use App\Filament\Resources\Divisis\Widgets\DivisiStatsOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListDivisis extends ListRecords
{
    protected static string $resource = DivisiResource::class;

    protected static ?string $title = 'Manajemen Divisi';

    public function getSubheading(): ?string
    {
        return 'Kelola struktur divisi kerja untuk memetakan penugasan dan akses akun karyawan ICM.';
    }

    public function getBreadcrumbs(): array
    {
        return [
            DivisiResource::getUrl() => 'Manajemen Divisi',
            'Daftar',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Divisi')
                ->icon(Heroicon::OutlinedPlusCircle)
                ->modalHeading('Tambah Divisi Kerja Baru')
                ->modalDescription('Buat unit kerja baru untuk mengelompokkan penugasan karyawan ICM.')
                ->modalWidth('md')
                ->successNotificationTitle('Divisi baru berhasil ditambahkan'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DivisiStatsOverview::class,
        ];
    }
}

