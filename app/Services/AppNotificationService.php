<?php

declare(strict_types=1);

namespace App\Services;

use App\Filament\Pages\CadanganData;
use App\Filament\Resources\Kontaks\KontakResource;
use App\Models\Kontak;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class AppNotificationService
{
    /**
     * Kirim notifikasi database in-app dasar ke satu atau banyak penerima.
     *
     * @param  User|Collection<int, User>|array<int, User>  $recipients
     * @param  array<int, Action>  $actions
     */
    public function send(
        User|Collection|array $recipients,
        string $title,
        ?string $body = null,
        string $color = 'info',
        ?string $icon = null,
        array $actions = []
    ): void {
        $notification = Notification::make()
            ->title($title)
            ->color($color);

        if (filled($body)) {
            $notification->body($body);
        }

        if (filled($icon)) {
            $notification->icon($icon);
        }

        if ($actions !== []) {
            $notification->actions($actions);
        }

        $notification->sendToDatabase($recipients);
    }

    /**
     * Notifikasi saat impor file Excel/CSV selesai dieksekusi.
     */
    public function notifyImportSelesai(User $user, int $kontakDibuat, int $perusahaanDibuat, int $dilewati): void
    {
        $bodyParts = [];
        if ($kontakDibuat > 0) {
            $bodyParts[] = "{$kontakDibuat} kontak baru";
        }
        if ($perusahaanDibuat > 0) {
            $bodyParts[] = "{$perusahaanDibuat} perusahaan baru";
        }
        if ($dilewati > 0) {
            $bodyParts[] = "{$dilewati} baris dilewati/tertaut";
        }

        $body = $bodyParts !== []
            ? 'Ringkasan: ' . implode(' · ', $bodyParts) . '.'
            : 'Tidak ada data kontak baru yang ditambahkan.';

        $this->send(
            recipients: $user,
            title: 'Impor Kontak Sponsor Selesai',
            body: $body,
            color: $kontakDibuat > 0 ? 'success' : 'info',
            icon: 'heroicon-o-arrow-up-tray',
            actions: [
                Action::make('view')
                    ->label('Buka Daftar Kontak')
                    ->button()
                    ->url(KontakResource::getUrl('index')),
            ]
        );
    }

    /**
     * Notifikasi saat pembuatan berkas cadangan database selesai.
     */
    public function notifyBackupBerhasil(User $user, string $filename, string|int $ukuran): void
    {
        if (is_numeric($ukuran)) {
            $bytes = (int) $ukuran;
            if ($bytes >= 1048576) {
                $ukuranStr = number_format($bytes / 1048576, 2) . ' MB';
            } elseif ($bytes >= 1024) {
                $ukuranStr = number_format($bytes / 1024, 2) . ' KB';
            } else {
                $ukuranStr = $bytes . ' B';
            }
        } else {
            $ukuranStr = (string) $ukuran;
        }

        $this->send(
            recipients: $user,
            title: 'Cadangan Database Berhasil Dibuat',
            body: "Berkas cadangan '{$filename}' ({$ukuranStr}) telah siap.",
            color: 'success',
            icon: 'heroicon-o-circle-stack',
            actions: [
                Action::make('view_backup')
                    ->label('Buka Cadangan Data')
                    ->button()
                    ->url(CadanganData::getUrl()),
            ]
        );
    }

    /**
     * Notifikasi saat pemulihan database (restore) selesai dieksekusi.
     */
    public function notifyRestoreBerhasil(User $user, string $filename): void
    {
        $this->send(
            recipients: $user,
            title: 'Pemulihan Database Selesai',
            body: "Database berhasil dipulihkan dari berkas '{$filename}'.",
            color: 'warning',
            icon: 'heroicon-o-arrow-path',
            actions: [
                Action::make('view_kontak')
                    ->label('Periksa Data Kontak')
                    ->button()
                    ->url(KontakResource::getUrl('index')),
            ]
        );
    }

    /**
     * Notifikasi saat kontak PIC baru ditambahkan ke sistem.
     */
    public function notifyKontakBaru(User $causer, Kontak $kontak): void
    {
        $perusahaan = $kontak->perusahaan?->nama_standar ?? 'perusahaan sponsor';
        $namaPic = filled($kontak->nama) ? $kontak->nama : 'PIC Baru';

        // Kirim notifikasi ke semua admin (kecuali yang membuat jika ada banyak user)
        $admins = User::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('role', 'admin')
                    ->orWhereHas('roles', fn ($rq) => $rq->where('name', 'admin'));
            })
            ->get();

        if ($admins->isEmpty()) {
            $admins = collect([$causer]);
        }

        $this->send(
            recipients: $admins,
            title: 'Kontak PIC Baru Ditambahkan',
            body: "{$namaPic} ({$perusahaan}) telah didaftarkan oleh {$causer->name}.",
            color: 'primary',
            icon: 'heroicon-o-user-plus',
            actions: [
                Action::make('view_detail')
                    ->label('Lihat Kontak')
                    ->button()
                    ->url(KontakResource::getUrl('index', [
                        'tableFilters' => [
                            'cari' => ['q' => $kontak->no_telepon ?: $kontak->nama],
                        ],
                    ])),
            ]
        );
    }

    /**
     * Mengirim notifikasi ke seluruh admin aktif.
     *
     * @param  array<int, Action>  $actions
     */
    public function notifyAdmins(
        string $title,
        ?string $body = null,
        string $color = 'info',
        ?string $icon = null,
        array $actions = []
    ): void {
        $admins = User::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('role', 'admin')
                    ->orWhereHas('roles', fn ($rq) => $rq->where('name', 'admin'));
            })
            ->get();

        if ($admins->isNotEmpty()) {
            $this->send($admins, $title, $body, $color, $icon, $actions);
        }
    }
}

