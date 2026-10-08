<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Perusahaan extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'nama_standar',
        'industri',
        'alamat',
        'website',
        'catatan',
        'updated_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama_standar', 'industri', 'alamat', 'website', 'catatan'])
            ->logOnlyDirty()
            ->useLogName('perusahaan')
            ->dontSubmitEmptyLogs();
    }

    /**
     * Mengambil daftar PIC kontak terhubung dengan eager-loading untuk tampilan modal.
     *
     * @return Collection<int, Kontak>
     */
    public function kontaksForDetailModal(): Collection
    {
        return $this->kontaks()
            ->with(['kegiatan', 'kegiatans', 'kategoriKegiatan'])
            ->orderBy('nama')
            ->get();
    }

    public function kontaks(): HasMany
    {
        return $this->hasMany(Kontak::class);
    }

    public function kegiatans(): BelongsToMany
    {
        return $this->belongsToMany(Kegiatan::class, 'perusahaan_kegiatan')
            ->withPivot(['id', 'nominal', 'paket', 'bentuk_partisipasi', 'tahun', 'tanggal_partisipasi', 'nama_event_manual', 'catatan'])
            ->withTimestamps();
    }

    public function riwayatSponsorships(): HasMany
    {
        return $this->hasMany(PerusahaanKegiatan::class, 'perusahaan_id')->terbaru();
    }

    public function latestRiwayatSponsorship(): HasOne
    {
        return $this->hasOne(PerusahaanKegiatan::class, 'perusahaan_id')->terbaru();
    }

    /**
     * Mengambil riwayat sponsorship perusahaan (opsional filter N tahun ke belakang).
     *
     * @return Collection<int, PerusahaanKegiatan>
     */
    public function riwayatSponsorship(?int $years = null): Collection
    {
        $query = $this->riwayatSponsorships()->with('kegiatan.kategoriKegiatan');

        if ($years !== null) {
            $sinceYear = (int) now()->subYears($years)->year;
            $query->where(function ($q) use ($sinceYear) {
                $q->where('tahun', '>=', $sinceYear)
                    ->orWhereYear('tanggal_partisipasi', '>=', $sinceYear);
            });
        }

        return $query->get();
    }

    /**
     * Akumulasi total nominal dana yang pernah diberikan perusahaan.
     * Jika relasi sudah di-eager-load, gunakan koleksi — hindari SUM query baru (anti N+1).
     */
    public function totalNominalSponsorship(): float
    {
        if ($this->relationLoaded('riwayatSponsorships')) {
            return (float) $this->riwayatSponsorships->sum('nominal');
        }

        return (float) $this->riwayatSponsorships()->sum('nominal');
    }

    protected ?array $memoKeaktifanSponsor = null;

    /**
     * Analisis Keaktifan Sponsor berdasarkan riwayat event terbaru.
     *
     * @return array{key: string, label: string, color: string, badge_class: string, tahun_terakhir: int|null, selisih_tahun: int|null}
     */
    public function getKeaktifanSponsorAttribute(): array
    {
        if ($this->memoKeaktifanSponsor !== null) {
            return $this->memoKeaktifanSponsor;
        }

        $currentYear = (int) date('Y');

        $latestRecord = $this->relationLoaded('latestRiwayatSponsorship')
            ? $this->latestRiwayatSponsorship
            : ($this->relationLoaded('riwayatSponsorships') ? $this->riwayatSponsorships->first() : $this->latestRiwayatSponsorship()->first());

        if (! $latestRecord) {
            $fallbackKegiatan = $this->relationLoaded('latestKontakWithKegiatan')
                ? $this->latestKontakWithKegiatan?->kegiatan
                : null;

            if ($fallbackKegiatan) {
                $yr = $fallbackKegiatan->tanggal_mulai ? (int) $fallbackKegiatan->tanggal_mulai->format('Y') : null;
                if (! $yr && preg_match('/\b(20\d{2})\b/', $fallbackKegiatan->nama_event, $m)) {
                    $yr = (int) $m[1];
                }
                if ($yr) {
                    $selisih = $currentYear - $yr;

                    return $this->memoKeaktifanSponsor = $this->formatKeaktifanResult($yr, $selisih);
                }
            }

            $fallbackEvents = $this->kegiatanPernahDiikuti();
            if ($fallbackEvents->isNotEmpty()) {
                $firstEv = $fallbackEvents->first();
                $yr = $firstEv->tanggal_mulai ? (int) $firstEv->tanggal_mulai->format('Y') : null;
                if (! $yr && preg_match('/\b(20\d{2})\b/', $firstEv->nama_event, $m)) {
                    $yr = (int) $m[1];
                }
                if ($yr) {
                    $selisih = $currentYear - $yr;

                    return $this->memoKeaktifanSponsor = $this->formatKeaktifanResult($yr, $selisih);
                }
            }

            return $this->memoKeaktifanSponsor = [
                'key' => 'baru',
                'label' => 'Prospek Baru',
                'color' => 'gray',
                'badge_class' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-300',
                'tahun_terakhir' => null,
                'selisih_tahun' => null,
            ];
        }

        $latestYear = $latestRecord->tahun_efektif;
        $selisih = $latestYear ? ($currentYear - $latestYear) : 0;

        return $this->memoKeaktifanSponsor = $this->formatKeaktifanResult($latestYear, $selisih);
    }

    protected function formatKeaktifanResult(?int $latestYear, ?int $selisih): array
    {
        if ($latestYear === null) {
            return [
                'key' => 'baru',
                'label' => 'Prospek Baru',
                'color' => 'gray',
                'badge_class' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-300',
                'tahun_terakhir' => null,
                'selisih_tahun' => null,
            ];
        }

        return match (true) {
            $selisih <= 1 => [
                'key' => 'sangat_aktif',
                'label' => 'Sangat Aktif (VIP Partner)',
                'color' => 'success',
                'badge_class' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-500/30',
                'tahun_terakhir' => $latestYear,
                'selisih_tahun' => $selisih,
            ],
            $selisih <= 3 => [
                'key' => 'aktif',
                'label' => 'Aktif Reguler',
                'color' => 'primary',
                'badge_class' => 'bg-blue-500/10 text-blue-700 dark:text-blue-400 border-blue-500/30',
                'tahun_terakhir' => $latestYear,
                'selisih_tahun' => $selisih,
            ],
            $selisih <= 4 => [
                'key' => 'perlu_reaktivasi',
                'label' => 'Perlu Re-engagement',
                'color' => 'warning',
                'badge_class' => 'bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-500/30',
                'tahun_terakhir' => $latestYear,
                'selisih_tahun' => $selisih,
            ],
            default => [
                'key' => 'dorman',
                'label' => 'Dorman (>4 Thn Pasif)',
                'color' => 'danger',
                'badge_class' => 'bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-500/30',
                'tahun_terakhir' => $latestYear,
                'selisih_tahun' => $selisih,
            ],
        };
    }

    /**
     * Analitik dan penentu strategi sponsor untuk pengambilan keputusan Pimpro.
     *
     * @return array{
     *   total_investasi: float,
     *   total_event: int,
     *   rata_rata_nominal: float,
     *   paket_favorit: string|null,
     *   kategori_potensi: string,
     *   rekomendasi_strategi: string,
     *   trend_tahunan: array<int, array{nominal: float, count: int, pakets: list<string>}>
     * }
     */
    public function getAnalitikSponsorAttribute(): array
    {
        // Cache 2 menit per perusahaan — query berat ke riwayat, paket, dan trend tidak perlu ulang tiap akses
        return Cache::remember("icm:analitik_sponsor:{$this->id}", 120, function (): array {
            $riwayats = $this->relationLoaded('riwayatSponsorships')
                ? $this->riwayatSponsorships->load('kegiatan')
                : $this->riwayatSponsorships()->with('kegiatan')->get();

            $totalNominal = (float) $riwayats->sum('nominal');
            $totalEvent = $riwayats->count();
            $avgNominal = $totalEvent > 0 ? ($totalNominal / $totalEvent) : 0;

            $packageCounts = [];
            $trendTahunan = [];

            foreach ($riwayats as $rw) {
                $yr = $rw->tahun_efektif ?? (int) date('Y');
                $pkt = $rw->paket;

                if ($pkt) {
                    $packageCounts[$pkt] = ($packageCounts[$pkt] ?? 0) + 1;
                }

                if (! isset($trendTahunan[$yr])) {
                    $trendTahunan[$yr] = ['nominal' => 0.0, 'count' => 0, 'pakets' => []];
                }

                $trendTahunan[$yr]['nominal'] += (float) $rw->nominal;
                $trendTahunan[$yr]['count']++;
                if ($pkt && ! in_array($pkt, $trendTahunan[$yr]['pakets'], true)) {
                    $trendTahunan[$yr]['pakets'][] = $pkt;
                }
            }

            ksort($trendTahunan);
            arsort($packageCounts);
            $paketFavorit = ! empty($packageCounts) ? array_key_first($packageCounts) : null;

            $hasPlatinum = in_array('Platinum', array_keys($packageCounts), true);
            $hasGold = in_array('Gold', array_keys($packageCounts), true);
            $hasSilver = in_array('Silver', array_keys($packageCounts), true);

            $kategoriPotensi = match (true) {
                $totalNominal >= 150000000 || $hasPlatinum => 'Diamond Whale (Tier 1)',
                $totalNominal >= 50000000 || $hasGold => 'Core Partner (Tier 2)',
                $totalNominal >= 15000000 || $hasSilver => 'Growth Partner (Tier 3)',
                $totalEvent > 0 => 'Entry Partner (Tier 4)',
                default => 'Prospek Baru',
            };

            $keaktifan = $this->keaktifan_sponsor;
            $rekomendasi = match (true) {
                $keaktifan['key'] === 'baru' => 'Sponsor belum memiliki histori event. Rekomendasi: Tawarkan proposal pengenalan dengan paket fleksibel (Silver/Booth) dan jadwalkan audiensi dengan PIC.',
                $keaktifan['key'] === 'dorman' => "Perusahaan tidak aktif sejak {$keaktifan['tahun_terakhir']}. Rekomendasi: Lakukan pendekatan re-engagement dengan penawaran diskon early-bird atau paket kolaborasi khusus.",
                $keaktifan['key'] === 'perlu_reaktivasi' => "Terakhir berpartisipasi pada {$keaktifan['tahun_terakhir']}. Rekomendasi: Segera kirimkan proposal event mendatang dan jadwalkan sesi koordinasi dengan PIC sebelum budget sponsor dialokasikan ke event lain.",
                $hasPlatinum => 'Sponsor Platinum bernilai sangat tinggi. Rekomendasi: Tawarkan slot simposium eksklusif atau sponsorship utama dengan proposal prioritas VVIP.',
                $hasGold => 'Sponsor konsisten di tier Gold. Rekomendasi: Ajukan paket Gold sebagai standar, dan berikan opsi upgrade ke Platinum dengan benefit penempatan booth premium.',
                $hasSilver => 'Sponsor rutin di paket Silver. Rekomendasi: Dorong upgrade nilai sponsorship dengan menawarkan bundling simposium satelit atau branding kit.',
                default => 'Sponsor aktif berpartisipasi. Rekomendasi: Pertahankan hubungan kemitraan dengan memberikan laporan apresiasi keterlibatan event sebelumnya.'
            };

            return [
                'total_investasi' => $totalNominal,
                'total_event' => $totalEvent,
                'rata_rata_nominal' => $avgNominal,
                'paket_favorit' => $paketFavorit,
                'kategori_potensi' => $kategoriPotensi,
                'rekomendasi_strategi' => $rekomendasi,
                'trend_tahunan' => $trendTahunan,
            ];
        });
    }

    public function latestKontakWithKegiatan(): HasOne
    {
        return $this->hasOne(Kontak::class)->whereNotNull('kegiatan_id')->latestOfMany('id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Inisial 2 huruf korporat untuk avatar identitas (misal: "Kalbe Farma" -> "KF").
     */
    public function getInisialAttribute(): string
    {
        $clean = preg_replace('/\b(pt|cv|tbk|ltd|inc|persero|co)\b/iu', '', $this->nama_standar ?? '') ?? '';
        $words = preg_split('/\s+/u', trim($clean), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($words) >= 2) {
            return strtoupper(mb_substr($words[0], 0, 1).mb_substr($words[1], 0, 1));
        }

        if (count($words) === 1) {
            return strtoupper(mb_substr($words[0], 0, 2));
        }

        return strtoupper(mb_substr($this->nama_standar ?? 'CO', 0, 2));
    }

    /**
     * Kegiatan kongres medis yang pernah diikuti melalui PIC kontak perusahaan.
     *
     * @return Collection<int, Kegiatan>
     */
    public function kegiatanPernahDiikuti(): Collection
    {
        return Kegiatan::query()
            ->where(function ($query) {
                $query->whereHas('kontaks', fn ($q) => $q->where('perusahaan_id', $this->id))
                    ->orWhereHas('directKontaks', fn ($q) => $q->where('perusahaan_id', $this->id));
            })
            ->with('kategoriKegiatan')
            ->get()
            ->sortByDesc(function (Kegiatan $k) {
                if ($k->tanggal_mulai) {
                    return $k->tanggal_mulai->format('Y-m-d');
                }
                if (preg_match('/\b(20\d{2})\b/', $k->nama_event, $matches)) {
                    return $matches[1].'-01-01';
                }

                return '1970-01-01';
            })
            ->values();
    }
}
