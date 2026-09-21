<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Kontaks\Pages\CreateKontak;
use App\Models\Kontak;
use App\Models\Perusahaan;
use App\Models\User;
use App\Services\AppNotificationService;
use Filament\Notifications\Livewire\DatabaseNotifications;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function komponen_database_notifications_berfungsi_dan_dapat_dimuat(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        Livewire::test(DatabaseNotifications::class)
            ->assertSuccessful();
    }

    #[Test]
    public function notifikasi_import_selesai_tersimpan_di_database_dan_dapat_dibaca(): void
    {
        $user = User::factory()->admin()->create();
        $service = new AppNotificationService();

        $service->notifyImportSelesai($user, 5, 2, 1);

        $this->assertDatabaseCount('notifications', 1);

        $notification = $user->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertSame('Impor Kontak Sponsor Selesai', $notification->data['title']);
        $this->assertStringContainsString('5 kontak baru', $notification->data['body']);
        $this->assertNull($notification->read_at);

        // Uji tandai telah dibaca melalui Livewire database notifications
        $this->actingAs($user);
        Livewire::test(DatabaseNotifications::class)
            ->call('markNotificationAsRead', $notification->id);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    #[Test]
    public function notifikasi_backup_dan_restore_tersimpan_di_database(): void
    {
        $user = User::factory()->admin()->create();
        $service = new AppNotificationService();

        $service->notifyBackupBerhasil($user, 'backup-2026-09-21.zip', '1.5 MB');
        $service->notifyRestoreBerhasil($user, 'backup-2026-09-21.zip');

        $this->assertDatabaseCount('notifications', 2);

        $titles = $user->notifications()->pluck('data')->map(fn ($d) => $d['title'])->all();
        $this->assertContains('Cadangan Database Berhasil Dibuat', $titles);
        $this->assertContains('Pemulihan Database Selesai', $titles);
    }

    #[Test]
    public function pembuatan_kontak_baru_memicu_notifikasi_in_app_ke_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $perusahaan = Perusahaan::factory()->create(['nama_standar' => 'PT Novartis Indonesia']);

        $this->actingAs($admin);

        Livewire::test(CreateKontak::class)
            ->fillForm([
                'perusahaan_id' => $perusahaan->id,
                'nama' => 'dr. Sarah Amanda',
                'no_telepon' => '081298765432',
                'email' => 'sarah@novartis.com',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $admin->id,
        ]);

        $notification = $admin->notifications()->latest()->first();
        $this->assertNotNull($notification);
        $this->assertSame('Kontak PIC Baru Ditambahkan', $notification->data['title']);
        $this->assertStringContainsString('dr. Sarah Amanda', $notification->data['body']);
    }

    #[Test]
    public function tandai_semua_telah_dibaca_bekerja_dengan_benar(): void
    {
        $user = User::factory()->admin()->create();
        $service = new AppNotificationService();

        $service->notifyBackupBerhasil($user, 'file1.zip', '1 MB');
        $service->notifyBackupBerhasil($user, 'file2.zip', '2 MB');

        $this->assertEquals(2, $user->unreadNotifications()->count());

        $this->actingAs($user);
        Livewire::test(DatabaseNotifications::class)
            ->call('markAllNotificationsAsRead');

        $this->assertEquals(0, $user->fresh()->unreadNotifications()->count());
    }
}

