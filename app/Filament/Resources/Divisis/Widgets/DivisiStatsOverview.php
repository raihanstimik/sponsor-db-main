<?php

declare(strict_types=1);

namespace App\Filament\Resources\Divisis\Widgets;

use App\Models\Divisi;
use App\Models\User;
use App\Support\KlasifikasiTabel;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DivisiStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = null;

    protected function getStats(): array
    {
        $totalDivisi = Divisi::count();
        $totalKaryawan = User::whereNotNull('divisi_id')->count();
        $avgKaryawan = $totalDivisi > 0 ? round($totalKaryawan / $totalDivisi, 1) : 0;

        return [
            Stat::make('Total Divisi Kerja', number_format($totalDivisi, 0, ',', '.'))
                ->description('Unit kerja terdaftar')
                ->descriptionIcon(Heroicon::BuildingOffice2)
                ->icon(Heroicon::OutlinedBuildingOffice2)
                ->color('primary')
                ->extraAttributes(KlasifikasiTabel::statBorderExtraAttributes('primary')),

            Stat::make('Karyawan Terpetakan', number_format($totalKaryawan, 0, ',', '.'))
                ->description('Memiliki penugasan divisi')
                ->descriptionIcon(Heroicon::UserGroup)
                ->icon(Heroicon::OutlinedUserGroup)
                ->color('success')
                ->extraAttributes(KlasifikasiTabel::statBorderExtraAttributes('success')),

            Stat::make('Rata-rata Anggota', $avgKaryawan.' / divisi')
                ->description('Distribusi beban divisi')
                ->descriptionIcon(Heroicon::ChartBar)
                ->icon(Heroicon::OutlinedChartBar)
                ->color('info')
                ->extraAttributes(KlasifikasiTabel::statBorderExtraAttributes('info')),
        ];
    }
}

