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
}
