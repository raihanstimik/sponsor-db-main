<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\KlasifikasiTabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Kegiatan extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'kategori_kegiatan_id',
        'nama_event',
        'warna',
        'tanggal_mulai',
        'tanggal_selesai',
        'venue',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['kategori_kegiatan_id', 'nama_event', 'warna', 'venue'])
            ->logOnlyDirty()
            ->useLogName('kegiatan')
            ->dontSubmitEmptyLogs();
    }

    public function kategoriKegiatan(): BelongsTo
    {
        return $this->belongsTo(KategoriKegiatan::class, 'kategori_kegiatan_id');
    }

    public function kontaks(): HasMany
    {
        return $this->hasMany(Kontak::class);
    }

    /**
     * Warna efektif kegiatan: warna event itu sendiri, atau mewarisi warna kategori bila kosong.
     */
    public function getWarnaEfektifAttribute(): string
    {
        return $this->warna
            ?? $this->kategoriKegiatan?->warna
            ?? KlasifikasiTabel::warnaKategori($this->kategoriKegiatan?->nama_kategori ?? $this->nama_event ?? '')
            ?? '#18225E';
    }

    /**
     * Status dinamika event kongres: Mendatang, Berlangsung, Selesai, Terjadwal.
     */
    public function getStatusEventAttribute(): string
    {
        $today = now()->toDateString();

        return match (true) {
            $this->tanggal_mulai && $today < $this->tanggal_mulai->toDateString() => 'Mendatang',
            $this->tanggal_mulai && $this->tanggal_selesai && now()->between($this->tanggal_mulai, $this->tanggal_selesai) => 'Berlangsung',
            $this->tanggal_selesai && $today > $this->tanggal_selesai->toDateString() => 'Selesai',
            default => 'Terjadwal',
        };
    }

    /**
     * Warna semantik badge status event.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status_event) {
            'Mendatang' => 'warning',
            'Berlangsung' => 'success',
            'Selesai' => 'gray',
            default => 'info',
        };
    }

    /**
     * Durasi pelaksanaan event dalam hari.
     */
    public function getDurasiHariAttribute(): ?int
    {
        if ($this->tanggal_mulai && $this->tanggal_selesai) {
            return (int) $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
        }

        return null;
    }

    /**
     * Label jadwal lengkap berserta durasi hari.
     */
    public function getJadwalDanDurasiAttribute(): string
    {
        if ($this->tanggal_mulai && $this->tanggal_selesai) {
            $rentang = $this->tanggal_mulai->format('d M Y').' - '.$this->tanggal_selesai->format('d M Y');

            return "{$rentang} (Durasi {$this->durasi_hari} Hari)";
        }

        return $this->tanggal_mulai ? $this->tanggal_mulai->format('d M Y') : 'Belum dijadwalkan';
    }

    /**
     * Jumlah perusahaan sponsor unik yang terafiliasi lewat kontak PIC.
     */
    public function jumlahSponsor(): int
    {
        return (int) $this->kontaks()
            ->whereNotNull('perusahaan_id')
            ->distinct('perusahaan_id')
            ->count('perusahaan_id');
    }
}
