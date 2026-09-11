<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
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
            ->logOnly(['nama_standar', 'industri', 'catatan'])
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
            ->with(['kegiatan', 'kategoriKegiatan'])
            ->orderBy('nama')
            ->get();
    }

    public function kontaks(): HasMany
    {
        return $this->hasMany(Kontak::class);
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
            ->whereHas('kontaks', fn ($q) => $q->where('perusahaan_id', $this->id))
            ->with('kategoriKegiatan')
            ->orderByDesc('tanggal_mulai')
            ->get();
    }
}
