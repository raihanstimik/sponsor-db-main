<?php

declare(strict_types=1);

namespace App\Filament\Resources\Perusahaans\Widgets;

use App\Models\Perusahaan;
use App\Support\KlasifikasiTabel;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class PerusahaanStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Ringkasan Master Perusahaan';

    protected function getStats(): array
    {
        $data = Cache::remember('icm:perusahaan_stats', 60, function (): array {
            $total = Perusahaan::count();

            $farmasi = Perusahaan::where('industri', 'like', '%farmasi%')->count();
            $alkes = Perusahaan::where(function ($q) {
                $q->where('industri', 'like', '%alat%')
                    ->orWhere('industri', 'like', '%alkes%')
                    ->orWhere('industri', 'like', '%diagnostik%')
                    ->orWhere('industri', 'like', '%laser%')
                    ->orWhere('industri', 'like', '%estetik%');
            })->count();

            // Mitra utama: memiliki >= 3 PIC terdaftar
            $mitraUtama = Perusahaan::has('kontaks', '>=', 3)->count();

            return [
                'total' => $total,
                'farmasi' => $farmasi,
                'alkes' => $alkes,
                'mitraUtama' => $mitraUtama,
            ];
        });

        $total = (int) $data['total'];
        $farmasi = (int) $data['farmasi'];
        $alkes = (int) $data['alkes'];
        $mitraUtama = (int) $data['mitraUtama'];

        $pct = fn (int $v): int => $total > 0 ? (int) round($v / $total * 100) : 0;

        return [
            Stat::make('Total Korporasi', number_format($total, 0, ',', '.'))
                ->description('100% Entitas Baku Terdaftar')
                ->descriptionIcon(Heroicon::CheckCircle)
                ->icon(Heroicon::OutlinedBuildingOffice2)
                ->color('primary')
                ->chart([3, 5, 4, 6, 7, 8, 9])
                ->extraAttributes(KlasifikasiTabel::statBorderExtraAttributes('primary')),

            Stat::make('Farmasi & Suplemen', number_format($farmasi, 0, ',', '.'))
                ->description($pct($farmasi).'% pangsa sektor industri')
                ->descriptionIcon(Heroicon::ChartPie)
                ->icon(Heroicon::OutlinedSparkles)
                ->color('warning')
                ->chart([2, 4, 5, 4, 6, 5, 7])
                ->extraAttributes(KlasifikasiTabel::statBorderExtraAttributes('warning')),

            Stat::make('Alat Medis & Diagnostik', number_format($alkes, 0, ',', '.'))
                ->description($pct($alkes).'% kontribusi alkes & laser')
                ->descriptionIcon(Heroicon::CpuChip)
                ->icon(Heroicon::OutlinedWrenchScrewdriver)
                ->color('info')
                ->chart([1, 3, 2, 4, 5, 4, 6])
                ->extraAttributes(KlasifikasiTabel::statBorderExtraAttributes('info')),

            Stat::make('Mitra Utama (≥3 PIC)', number_format($mitraUtama, 0, ',', '.'))
                ->description($pct($mitraUtama).'% jaringan PIC luas')
                ->descriptionIcon(Heroicon::UserGroup)
                ->icon(Heroicon::OutlinedUserGroup)
                ->color('success')
                ->chart([2, 3, 4, 5, 6, 7, 8])
                ->extraAttributes(KlasifikasiTabel::statBorderExtraAttributes('success')),
        ];
    }
}
