<?php

namespace Tests\Feature;

use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ActivityLogPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_log_page_renders_cleanly_for_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $act = activity()->causedBy($admin)->log('Tes aktivitas biasa');
        $importAct = activity('import')->causedBy($admin)->withProperties(['perusahaan_dibuat' => 2, 'kontak_dibuat' => 5, 'dilewati' => 0])->log('Mengimpor 5 kontak');

        $response = $this->actingAs($admin)->get('/admin/activity-logs');
        $response->assertSuccessful();

        Livewire::actingAs($admin)
            ->test(ListActivityLogs::class)
            ->assertSuccessful()
            ->assertTableActionVisible('view', $act)
            ->assertActionVisible('cleanLogs');
    }

    public function test_activity_log_page_karyawan_cannot_see_clean_logs_action(): void
    {
        $karyawan = User::factory()->karyawan()->create();
        $this->actingAs($karyawan);

        $response = $this->get('/admin/activity-logs');
        $response->assertSuccessful();

        Livewire::actingAs($karyawan)
            ->test(ListActivityLogs::class)
            ->assertActionHidden('cleanLogs');
    }

    public function test_admin_dapat_membersihkan_log_dengan_opsi_hari_dan_minggu(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        // Buat log aktivitas lama (10 hari lalu)
        $oldActivity = activity()->causedBy($admin)->log('Log lama 10 hari');
        $oldActivity->created_at = now()->subDays(10);
        $oldActivity->save();

        // Buat log aktivitas baru (1 jam lalu)
        $recentActivity = activity()->causedBy($admin)->log('Log baru 1 jam');

        // Jalankan aksi bersihkan log dengan opsi 7 hari (1 minggu)
        Livewire::actingAs($admin)
            ->test(ListActivityLogs::class)
            ->callAction('cleanLogs', ['days' => 7])
            ->assertHasNoActionErrors();

        // Log 10 hari lalu harus sudah terhapus, log 1 jam lalu tetap ada
        $this->assertDatabaseMissing('activity_log', ['id' => $oldActivity->id]);
        $this->assertDatabaseHas('activity_log', ['id' => $recentActivity->id]);
    }
}
