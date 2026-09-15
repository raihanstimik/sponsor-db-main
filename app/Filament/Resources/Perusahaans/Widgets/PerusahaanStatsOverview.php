<?php

declare(strict_types=1);

namespace App\Filament\Resources\Perusahaans\Widgets;

use App\Models\Kontak;
use App\Models\Perusahaan;
use App\Support\KlasifikasiTabel;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class PerusahaanStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = null;

    protected function getStats(): array
    {
        $data = Cache::remember('icm:perusahaan_stats', 60, function (): array {
            $total = Perusahaan::count();

            // Total PIC kontak yang terafiliasi dengan perusahaan baku
            $totalPic = Kontak::whereNotNull('perusahaan_id')->count();

            // Mitra multi-event: perusahaan yang berpartisipasi di >= 2 kegiatan kongres
            $multiEvent = Perusahaan::whereHas('kontaks', function ($q) {
                $q->select('perusahaan_id')
                    ->groupBy('perusahaan_id')
                    ->havingRaw('COUNT(DISTINCT kegiatan_id) >= 2');
            })->count();

            // Mitra utama: perusahaan dengan relasi kuat (>= 3 PIC terdaftar)
            $mitraUtama = Perusahaan::has('kontaks', '>=', 3)->count();

            return [
                'total' => $total,
                'totalPic' => $totalPic,
                'multiEvent' => $multiEvent,
                'mitraUtama' => $mitraUtama,
            ];
        });

        $total = (int) $data['total'];
        $totalPic = (int) $data['totalPic'];
        $multiEvent = (int) $data['multiEvent'];
        $mitraUtama = (int) $data['mitraUtama'];

        $pct = fn (int $v): int => $total > 0 ? (int) round($v / $total * 100) : 0;
        $avgPic = $total > 0 ? number_format($totalPic / $total, 1, ',', '.') : '0';

        return [
            Stat::make('Total Korporasi', number_format($total, 0, ',', '.'))
                ->description('100% Entitas Baku Terdaftar')
                ->descriptionIcon(Heroicon::CheckCircle)
                ->icon(Heroicon::OutlinedBuildingOffice2)
                ->color('primary')
                ->extraAttributes(KlasifikasiTabel::statBorderExtraAttributes('primary')),

            Stat::make('Total PIC Terhubung', number_format($totalPic, 0, ',', '.'))
                ->description("Rata-rata {$avgPic} PIC per entitas")
                ->descriptionIcon(Heroicon::Identification)
                ->icon(Heroicon::OutlinedIdentification)
                ->color('info')
                ->extraAttributes(KlasifikasiTabel::statBorderExtraAttributes('info')),

            Stat::make('Mitra Multi-Event', number_format($multiEvent, 0, ',', '.'))
                ->description('Sponsor aktif di ≥ 2 kegiatan')
                ->descriptionIcon(Heroicon::CalendarDays)
                ->icon(Heroicon::OutlinedCalendarDays)
                ->color('warning')
                ->extraAttributes(KlasifikasiTabel::statBorderExtraAttributes('warning')),

            Stat::make('Mitra Utama (≥3 PIC)', number_format($mitraUtama, 0, ',', '.'))
                ->description($pct($mitraUtama).'% relasi korporasi luas')
                ->descriptionIcon(Heroicon::UserGroup)
                ->icon(Heroicon::OutlinedUserGroup)
                ->color('success')
                ->extraAttributes(KlasifikasiTabel::statBorderExtraAttributes('success')),
        ];
    }
}
