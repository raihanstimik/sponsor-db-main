<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $perusahaan_id
 * @property int|null $kegiatan_id
 * @property string|null $nama_event_manual
 * @property float $nominal
 * @property string|null $paket
 * @property string|null $bentuk_partisipasi
 * @property int|null $tahun
 * @property Carbon|null $tanggal_partisipasi
 * @property string|null $catatan
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Perusahaan $perusahaan
 * @property-read Kegiatan|null $kegiatan
 * @property-read string $nama_event
 * @property-read int|null $tahun_efektif
 * @property-read string $formatted_nominal
 */
class PerusahaanKegiatan extends Model
{
    use HasFactory;

    protected $table = 'perusahaan_kegiatan';

    protected $fillable = [
        'perusahaan_id',
        'kegiatan_id',
        'nama_event_manual',
        'nominal',
        'paket',
        'bentuk_partisipasi',
        'tahun',
        'tanggal_partisipasi',
        'catatan',
    ];

    protected $attributes = [
        'nominal' => 0,
    ];

    protected static function booted(): void
    {
        static::saving(function (PerusahaanKegiatan $model): void {
            if ($model->nominal === null) {
                $model->nominal = 0;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'nominal' => 'float',
            'tahun' => 'integer',
            'tanggal_partisipasi' => 'date',
        ];
    }

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan_id');
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id');
    }

    /**
     * Nama event yang diikuti, baik dari master Kegiatan atau input manual.
     */
    public function getNamaEventAttribute(): string
    {
        if ($this->kegiatan && filled($this->kegiatan->nama_event)) {
            return $this->kegiatan->nama_event;
        }

        if (filled($this->nama_event_manual)) {
            return $this->nama_event_manual;
        }

        return 'Event Tanpa Judul';
    }

    /**
     * Tahun efektif pelaksanaan sponsorship.
     */
    public function getTahunEfektifAttribute(): ?int
    {
        if ($this->tahun) {
            return (int) $this->tahun;
        }

        if ($this->tanggal_partisipasi) {
            return (int) $this->tanggal_partisipasi->format('Y');
        }

        if ($this->kegiatan?->tanggal_mulai) {
            return (int) $this->kegiatan->tanggal_mulai->format('Y');
        }

        if (preg_match('/\b(20\d{2})\b/', $this->nama_event, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Format nominal dalam Rupiah atau strip jika kosong.
     */
    public function getFormattedNominalAttribute(): string
    {
        if ($this->nominal > 0) {
            return 'Rp '.number_format($this->nominal, 0, ',', '.');
        }

        return '-';
    }

    /**
     * Kelas warna badge untuk paket sponsorship.
     */
    public function getPaketColorAttribute(): string
    {
        return match (mb_strtolower((string) $this->paket)) {
            'platinum' => 'gray',
            'gold' => 'warning',
            'silver' => 'slate',
            'bronze' => 'danger',
            'reguler' => 'info',
            'custom' => 'primary',
            default => 'gray',
        };
    }

    /**
     * Scope pengurutan kronologis dari tahun dan tanggal paling akhir ke yang paling lama.
     */
    public function scopeTerbaru(Builder $query): Builder
    {
        $driver = $query->getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return $query
                ->orderByRaw("COALESCE(tahun, CAST(strftime('%Y', tanggal_partisipasi) AS INTEGER), CAST(strftime('%Y', created_at) AS INTEGER)) DESC")
                ->orderByRaw('COALESCE(tanggal_partisipasi, created_at) DESC')
                ->orderByDesc('id');
        }

        return $query
            ->orderByRaw('COALESCE(tahun, YEAR(tanggal_partisipasi), YEAR(created_at)) DESC')
            ->orderByRaw('COALESCE(tanggal_partisipasi, created_at) DESC')
            ->orderByDesc('id');
    }
}
