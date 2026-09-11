<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\PetaNomorPerusahaan;
use App\Support\PhoneNormalizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Kontak extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'perusahaan_id',
        'kegiatan_id',
        'kategori_kegiatan_id',
        'nama',
        'no_telepon',
        'email',
        'catatan',
        'status_format_valid',
        'status_verifikasi',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status_format_valid' => 'boolean',
        ];
    }

    /**
     * Ringkasan agregat statistik kontak: total dan nomor valid.
     *
     * @return object{total: int|string, valid: int|string|null}
     */
    public static function agregatStatistik(?Builder $query = null): object
    {
        $targetQuery = $query ?? static::query();

        return $targetQuery
            ->selectRaw('count(*) as total, sum(case when status_format_valid = 1 then 1 else 0 end) as valid')
            ->first() ?? (object) [
                'total' => 0,
                'valid' => 0,
            ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['perusahaan_id', 'kegiatan_id', 'kategori_kegiatan_id', 'nama', 'no_telepon', 'email', 'catatan'])
            ->logOnlyDirty()
            ->useLogName('kontak')
            ->dontSubmitEmptyLogs();
    }

    public function setNoTeleponAttribute($value): void
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            $this->attributes['no_telepon'] = null;
            $this->attributes['status_format_valid'] = false;

            return;
        }

        $normalized = PhoneNormalizer::normalize($raw);
        $this->attributes['no_telepon'] = PhoneNormalizer::isValid($normalized)
            ? $normalized
            : preg_replace('/\s+/', ' ', $raw);
        $this->attributes['status_format_valid'] = PhoneNormalizer::isValid($normalized);
    }

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(Perusahaan::class);
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function kategoriKegiatan(): BelongsTo
    {
        return $this->belongsTo(KategoriKegiatan::class, 'kategori_kegiatan_id');
    }

    /**
     * @deprecated pakai PetaNomorPerusahaan::untukKontak() — cache 60 detik anti N+1
     *
     * @return array<int, string>
     */
    public function perusahaanLainDenganNomorSama(): array
    {
        // ponytail: single cached map, not per-row query
        return PetaNomorPerusahaan::untukKontak($this, app(PetaNomorPerusahaan::class)->ambil());
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
