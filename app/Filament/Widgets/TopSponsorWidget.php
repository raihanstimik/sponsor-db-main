<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Kegiatan;
use App\Models\Perusahaan;
use App\Models\PerusahaanKegiatan;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class TopSponsorWidget extends Widget
{
    protected string $view = 'filament.widgets.top-sponsor';

    protected static ?int $sort = -96;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'xl' => 1,
    ];

    public ?int $selectedPerusahaanId = null;

    public ?int $selectedRiwayatId = null;

    public ?int $selectedKegiatanPivotId = null;

    /**
     * @return array{totalSponsor: int, rows: array<int, array{id: int, rank: int, inisial: string, nama: string, industri: string, event_count: int, pic_count: int, total_nominal: float, updated_text: string}>}
     */
    public function getDataProperty(): array
    {
        return Cache::remember('icm:widget_top_10_sponsors', 60, function (): array {
            $sponsors = Perusahaan::query()
                ->withCount(['kegiatans', 'kontaks'])
                ->with('updatedBy')
                ->orderByDesc('kegiatans_count')
                ->orderByDesc('kontaks_count')
                ->limit(10)
                ->get();

            $totalSponsor = Perusahaan::count();

            $rows = [];
            foreach ($sponsors as $idx => $sp) {
                $totalNominal = (float) $sp->totalNominalSponsorship();
                $updatedText = $sp->updated_at
                    ? $sp->updated_at->diffForHumans().($sp->updatedBy ? ' ('.$sp->updatedBy->name.')' : '')
                    : '-';

                $rows[] = [
                    'id' => $sp->id,
                    'rank' => $idx + 1,
                    'inisial' => $sp->inisial,
                    'nama' => $sp->nama_standar,
                    'industri' => $sp->industri ?? 'Sektor Umum',
                    'event_count' => (int) $sp->kegiatans_count,
                    'pic_count' => (int) $sp->kontaks_count,
                    'total_nominal' => $totalNominal,
                    'updated_text' => $updatedText,
                ];
            }

            return [
                'totalSponsor' => $totalSponsor,
                'rows' => $rows,
            ];
        });
    }

    public function selectPerusahaan(int $id): void
    {
        $this->selectedPerusahaanId = $id;
        $this->selectedRiwayatId = null;
        $this->selectedKegiatanPivotId = null;
    }

    public function selectRiwayat(int $id): void
    {
        $this->selectedRiwayatId = $id;
        $this->selectedKegiatanPivotId = null;
    }

    public function selectKegiatanPivot(int $id): void
    {
        $this->selectedKegiatanPivotId = $id;
        $this->selectedRiwayatId = null;
    }

    public function closeRiwayatDetail(): void
    {
        $this->selectedRiwayatId = null;
        $this->selectedKegiatanPivotId = null;
    }

    public function closeDetail(): void
    {
        $this->selectedPerusahaanId = null;
        $this->selectedRiwayatId = null;
        $this->selectedKegiatanPivotId = null;
    }

    public function getSelectedPerusahaanProperty(): ?Perusahaan
    {
        if (! $this->selectedPerusahaanId) {
            return null;
        }

        return Perusahaan::with([
            'updatedBy',
            'kontaks',
            'riwayatSponsorships.kegiatan.kategoriKegiatan',
            'kegiatans.kategoriKegiatan',
        ])->find($this->selectedPerusahaanId);
    }

    public function getSelectedRiwayatProperty(): ?PerusahaanKegiatan
    {
        if (! $this->selectedRiwayatId) {
            return null;
        }

        return PerusahaanKegiatan::with([
            'perusahaan',
            'kegiatan.kategoriKegiatan',
        ])->find($this->selectedRiwayatId);
    }

    public function getSelectedKegiatanPivotProperty(): ?Kegiatan
    {
        if (! $this->selectedKegiatanPivotId || ! $this->selectedPerusahaanId) {
            return null;
        }

        return Kegiatan::with('kategoriKegiatan')
            ->whereHas('perusahaans', fn ($q) => $q->where('perusahaans.id', $this->selectedPerusahaanId))
            ->find($this->selectedKegiatanPivotId);
    }
}
