<?php

declare(strict_types=1);

namespace App\Filament\Resources\Kegiatans\Widgets;

use App\Models\KategoriKegiatan;
use App\Models\Kegiatan;
use App\Models\Kontak;
use App\Support\KlasifikasiTabel;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class KegiatanStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Statistik Kongres & Kategori Medis';

    protected function getStats(): array
    {
        $data = Cache::remember('icm:kegiatan_stats', 60, function (): array {
            $totalKegiatan = Kegiatan::count();
            $totalKategori = KategoriKegiatan::count();
            $today = now()->toDateString();
            $eventMendatang = Kegiatan::where(function ($query) use ($today) {
                $query->whereDate('tanggal_mulai', '>=', $today)
                    ->orWhere(function ($q) use ($today) {
                        $q->whereNull('tanggal_mulai')
                            ->orWhereDate('tanggal_selesai', '>=', $today);
                    });
            })->count();

            $totalKontakTerafiliasi = Kontak::whereNotNull('kegiatan_id')->count();

            return [
                'totalKegiatan' => $totalKegiatan,
                'totalKategori' => $totalKategori,
                'eventMendatang' => $eventMendatang,
                'totalKontakTerafiliasi' => $totalKontakTerafiliasi,
            ];
        });

        $totalKegiatan = (int) $data['totalKegiatan'];
        $totalKategori = (int) $data['totalKategori'];
        $eventMendatang = (int) $data['eventMendatang'];
        $totalKontakTerafiliasi = (int) $data['totalKontakTerafiliasi'];

        return [
            Stat::make('Total Event Terdaftar', number_format($totalKegiatan, 0, ',', '.'))
                ->description('Acara medis ICM aktif')
                ->descriptionIcon(Heroicon::CheckCircle)
                ->icon(Heroicon::OutlinedCalendarDays)
                ->color('primary')
                ->chart([3, 5, 4, 6, 7, 8, 9])
                ->extraAttributes(KlasifikasiTabel::statBorderExtraAttributes('primary')),

            Stat::make('Kategori Spesialisasi', number_format($totalKategori, 0, ',', '.'))
                ->description('Bidang ilmu kedokteran')
                ->descriptionIcon(Heroicon::Tag)
                ->icon(Heroicon::OutlinedFolder)
                ->color('warning')
                ->chart([2, 4, 5, 4, 6, 5, 7])
                ->extraAttributes(KlasifikasiTabel::statBorderExtraAttributes('warning')),

            Stat::make('Event Mendatang', number_format($eventMendatang, 0, ',', '.'))
                ->description('Siap penawaran sponsor')
                ->descriptionIcon(Heroicon::Clock)
                ->icon(Heroicon::OutlinedClock)
                ->color('info')
                ->chart([2, 3, 4, 5, 6, 7, 8])
                ->extraAttributes(KlasifikasiTabel::statBorderExtraAttributes('info')),

            Stat::make('Total Kontak Terafiliasi', number_format($totalKontakTerafiliasi, 0, ',', '.'))
                ->description('PIC terhubung ke kongres')
                ->descriptionIcon(Heroicon::UserGroup)
                ->icon(Heroicon::OutlinedUserGroup)
                ->color('success')
                ->chart([3, 4, 6, 7, 8, 9, 10])
                ->extraAttributes(KlasifikasiTabel::statBorderExtraAttributes('success')),
        ];
    }
}
