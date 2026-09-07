<?php

namespace App\Filament\Widgets;

use App\Models\KategoriKegiatan;
use App\Support\KlasifikasiTabel;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DistribusiKategoriWidget extends Widget
{
    protected string $view = 'filament.widgets.distribusi-kategori';

    protected static ?int $sort = -98;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array{total: int, rows: array<int, array{nama: string, count: int, share: float, width: float, hex: string}>}
     */
    public static function data(): array
    {
        // Cache 60 detik untuk 50 user baca bersamaan (kunci sama dengan chart lama agar invalidasi tetap jalan)
        return Cache::remember('icm:chart_kategori', 60, function (): array {
            // Eager-free agregat: hitung event per kategori via LEFT JOIN agar kategori tanpa event tetap muncul
            $rows = KategoriKegiatan::query()
                ->leftJoin('kegiatans', 'kegiatans.kategori_kegiatan_id', '=', 'kategori_kegiatans.id')
                ->select('kategori_kegiatans.id', 'kategori_kegiatans.nama_kategori', DB::raw('count(kegiatans.id) as event_count'))
                ->groupBy('kategori_kegiatans.id', 'kategori_kegiatans.nama_kategori')
                ->orderByDesc('event_count')
                ->orderBy('kategori_kegiatans.nama_kategori')
                ->get();

            if ($rows->isEmpty()) {
                return ['total' => 0, 'rows' => []];
            }

            $counts = $rows->pluck('event_count')->map(fn ($v): int => (int) $v);
            $total = $counts->sum();
            $max = $counts->max();

            $items = [];
            foreach ($rows as $row) {
                $count = (int) $row->event_count;
                $hex = KlasifikasiTabel::warnaKategori($row->nama_kategori);
                if ($hex === null || preg_match('/^#[0-9A-Fa-f]{6}$/', $hex) !== 1) {
                    // fallback palet Tailwind bila warna kategori tidak terdefinisi
                    $hex = ['#1E2A4A', '#0EA5E9', '#8B5CF6', '#EC4899', '#10B981', '#F59E0B', '#EF4444', '#14B8A6'][$row->id % 8];
                }
                $items[] = [
                    'nama' => $row->nama_kategori,
                    'count' => $count,
                    'share' => $total > 0 ? round($count / $total * 100, 1) : 0.0,
                    'width' => $max > 0 ? round($count / $max * 100, 1) : 0.0,
                    'hex' => $hex,
                ];
            }

            return ['total' => $total, 'rows' => $items];
        });
    }
}
