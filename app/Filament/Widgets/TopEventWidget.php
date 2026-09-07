<?php

namespace App\Filament\Widgets;

use App\Models\Kegiatan;
use App\Models\Kontak;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TopEventWidget extends Widget
{
    protected string $view = 'filament.widgets.top-event';

    protected static ?int $sort = -97;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array{totalTop: int, dominasi: float, rows: array<int, array{rank: int, nama: string, nama_pendek: string, sub: string, count: int, width: float, hex: string}>}
     */
    public static function data(): array
    {
        // Cache 60 detik untuk 50 user baca bersamaan (kunci sama dengan chart lama agar invalidasi tetap jalan)
        return Cache::remember('icm:chart_top_event', 60, function (): array {
            // Satu agregat: Top 5 event dengan kontak terbanyak + nama kategori & tanggal mulai
            $rows = Kegiatan::query()
                ->leftJoin('kontaks', 'kontaks.kegiatan_id', '=', 'kegiatans.id')
                ->leftJoin('kategori_kegiatans', 'kategori_kegiatans.id', '=', 'kegiatans.kategori_kegiatan_id')
                ->select(
                    'kegiatans.id',
                    'kegiatans.nama_event',
                    'kegiatans.warna',
                    'kegiatans.tanggal_mulai',
                    'kategori_kegiatans.nama_kategori',
                    DB::raw('count(kontaks.id) as kontak_count')
                )
                ->groupBy('kegiatans.id', 'kegiatans.nama_event', 'kegiatans.warna', 'kegiatans.tanggal_mulai', 'kategori_kegiatans.nama_kategori')
                ->orderByDesc('kontak_count')
                ->orderBy('kegiatans.nama_event')
                ->limit(5)
                ->get();

            if ($rows->isEmpty()) {
                return ['totalTop' => 0, 'dominasi' => 0.0, 'rows' => []];
            }

            $counts = $rows->pluck('kontak_count')->map(fn ($v): int => (int) $v);
            $totalTop = $counts->sum();
            $max = $counts->max();
            $totalKontak = Kontak::count();

            $palette = ['#1E2A4A', '#0EA5E9', '#8B5CF6', '#EC4899', '#10B981'];
            $items = [];
            foreach ($rows->values() as $idx => $row) {
                $count = (int) $row->kontak_count;
                $hex = $row->warna ?? $palette[$idx % count($palette)];
                if (preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $hex) !== 1) {
                    $hex = $palette[$idx % count($palette)];
                }
                $sub = trim(implode(' • ', array_filter([
                    $row->nama_kategori,
                    $row->tanggal_mulai ? Carbon::parse($row->tanggal_mulai)->format('Y') : null,
                ])));
                $items[] = [
                    'rank' => $idx + 1,
                    'nama' => $row->nama_event,
                    'nama_pendek' => Str::limit($row->nama_event, 12),
                    'sub' => $sub !== '' ? $sub : '-',
                    'count' => $count,
                    'width' => $max > 0 ? round($count / $max * 100, 1) : 0.0,
                    'hex' => $hex,
                ];
            }

            return [
                'totalTop' => $totalTop,
                'dominasi' => $totalKontak > 0 ? round($totalTop / $totalKontak * 100, 1) : 0.0,
                'rows' => $items,
            ];
        });
    }
}
