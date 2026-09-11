<?php

namespace App\Filament\Widgets;

use App\Models\KategoriKegiatan;
use App\Models\Kegiatan;
use App\Models\Kontak;
use App\Models\Perusahaan;
use App\Support\KlasifikasiTabel;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class KontakStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Ringkasan Database';

    protected static ?int $sort = -100;

    protected string|int|array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Cache 60 detik untuk 50 user baca bersamaan — hindari query agregat tiap F5
        $cached = Cache::remember('icm:stats_overview', 60, function (): array {
            $totalPerusahaan = Perusahaan::count();
            $totalKegiatan = Kegiatan::count();
            $totalKategori = KategoriKegiatan::count();
            $aggr = Kontak::agregatStatistik();

            return [
                'totalPerusahaan' => $totalPerusahaan,
                'totalKegiatan' => $totalKegiatan,
                'totalKategori' => $totalKategori,
                'aggr' => $aggr,
            ];
        });

        $totalPerusahaan = $cached['totalPerusahaan'];
        $totalKegiatan = $cached['totalKegiatan'];
        $totalKategori = $cached['totalKategori'];
        $aggr = $cached['aggr'];

        $totalKontak = (int) ($aggr->total ?? 0);
        $validHp = (int) ($aggr->valid ?? 0);

        $pct = fn (int $v): int => $totalKontak > 0 ? (int) round($v / $totalKontak * 100) : 0;

        return [
            // app/Filament/Widgets/KontakStatsOverview.php — Balok ringkasan KPI utama
            Stat::make('Total Perusahaan', number_format($totalPerusahaan, 0, ',', '.'))
                ->description($totalKategori.' kategori · '.$totalKegiatan.' kegiatan')
                ->descriptionIcon(Heroicon::BuildingOffice)
                ->icon(Heroicon::OutlinedBuildingOffice2)
                ->color('primary')
                ->chart([2, 4, 3, 6, 5, 7, 6])
                ->extraAttributes(KlasifikasiTabel::statBorderExtraAttributes('orange')),

            Stat::make('Total Kontak', number_format($totalKontak, 0, ',', '.'))
                ->description($validHp.' nomor valid ('.$pct($validHp).'%)')
                ->descriptionIcon(Heroicon::UserGroup)
                ->icon(Heroicon::OutlinedUsers)
                ->color('primary')
                ->chart([3, 5, 4, 7, 6, 9, 8])
                ->extraAttributes(KlasifikasiTabel::statBorderExtraAttributes('primary')),

            Stat::make('Nomor HP Valid', number_format($validHp, 0, ',', '.'))
                ->description($pct($validHp).'% siap dihubungi WhatsApp')
                ->descriptionIcon(Heroicon::CheckBadge)
                ->icon(Heroicon::OutlinedPhone)
                ->color('success')
                ->chart([2, 5, 4, 6, 7, 8, 9])
                ->extraAttributes(KlasifikasiTabel::statBorderExtraAttributes('success', 'dark:!bg-success-950/20')),

            Stat::make('Total Kegiatan', number_format($totalKegiatan, 0, ',', '.'))
                ->description($totalKategori.' kategori kegiatan')
                ->descriptionIcon(Heroicon::CalendarDays)
                ->icon(Heroicon::OutlinedCalendarDays)
                ->color('primary')
                ->chart([1, 3, 2, 4, 3, 5, 4])
                ->extraAttributes(KlasifikasiTabel::statBorderExtraAttributes('sky')),
        ];
    }
}
