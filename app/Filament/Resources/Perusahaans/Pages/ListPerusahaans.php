<?php

namespace App\Filament\Resources\Perusahaans\Pages;

use App\Filament\Resources\Perusahaans\PerusahaanResource;
use App\Filament\Resources\Perusahaans\Widgets\PerusahaanStatsOverview;
use App\Models\Perusahaan;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListPerusahaans extends ListRecords
{
    protected static string $resource = PerusahaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('exportMaster')
                ->label('Ekspor Master')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->action(function () {
                    return response()->streamDownload(function () {
                        $handle = fopen('php://output', 'w');
                        fwrite($handle, "\xEF\xBB\xBF");
                        fputcsv($handle, ['Nama Perusahaan Baku', 'Bidang Industri', 'Alamat', 'Website', 'Jumlah PIC', 'Catatan Kerjasama']);
                        Perusahaan::withCount('kontaks')->orderBy('nama_standar')->chunk(500, function ($rows) use ($handle) {
                            foreach ($rows as $row) {
                                fputcsv($handle, [
                                    $row->nama_standar,
                                    $row->industri,
                                    $row->alamat,
                                    $row->website,
                                    $row->kontaks_count,
                                    $row->catatan,
                                ]);
                            }
                        });
                        fclose($handle);
                    }, 'master-perusahaan-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
                }),
            CreateAction::make()
                ->label('Tambah Perusahaan Baku')
                ->icon(Heroicon::OutlinedBuildingOffice),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PerusahaanStatsOverview::class,
        ];
    }
}
