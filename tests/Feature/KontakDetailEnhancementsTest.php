<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Kontaks\Pages\ListKontaks;
use App\Filament\Resources\Kontaks\Pages\ViewKontak;
use App\Filament\Resources\Kontaks\Schemas\KontakInfolist;
use App\Filament\Resources\Perusahaans\PerusahaanResource;
use App\Models\Kontak;
use App\Models\Perusahaan;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class KontakDetailEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function nama_perusahaan_pada_detail_kontak_memiliki_tautan_ke_profil_perusahaan(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $perusahaan = Perusahaan::factory()->create([
            'nama_standar' => 'PT Merck Tbk',
        ]);

        $kontak = Kontak::factory()->create([
            'perusahaan_id' => $perusahaan->id,
            'nama' => 'dr. Budi Santoso',
            'no_telepon' => '081234567890',
        ]);

        $expectedPerusahaanUrl = PerusahaanResource::getUrl('view', ['record' => $perusahaan->id]);

        Livewire::test(ViewKontak::class, ['record' => $kontak->id])
            ->assertSuccessful()
            ->assertSee('PT Merck Tbk')
            ->assertSee('Edit Kontak');

        $this->assertStringContainsString('/admin/perusahaans/' . $perusahaan->id, $expectedPerusahaanUrl);
    }

    #[Test]
    public function view_action_pada_tabel_memiliki_footer_action_edit_dan_whatsapp(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $livewire = Livewire::test(ListKontaks::class)->assertSuccessful();
        $table = $livewire->instance()->getTable();

        $actions = $table->getActions();
        $actionGroup = collect($actions)->first(fn ($a) => $a instanceof ActionGroup);
        $this->assertNotNull($actionGroup, 'ActionGroup harus tersedia pada tabel');

        $groupedActions = $actionGroup->getActions();
        $viewAction = collect($groupedActions)->first(fn ($a) => $a instanceof ViewAction);
        $this->assertNotNull($viewAction, 'ViewAction slide-over harus tersedia dalam aksi baris');

        // Verifikasi extra modal footer actions di dalam ViewAction
        $reflection = new \ReflectionClass($viewAction);
        $footerProp = null;
        if ($reflection->hasProperty('extraModalFooterActions')) {
            $footerProp = $reflection->getProperty('extraModalFooterActions');
            $footerProp->setAccessible(true);
        }

        $footerActions = $footerProp ? $footerProp->getValue($viewAction) : [];
        $actionNames = collect($footerActions)->map(fn ($act) => $act instanceof Action ? $act->getName() : null)->filter()->values()->all();

        $this->assertContains('slideover_whatsapp', $actionNames);
        $this->assertContains('slideover_edit', $actionNames);
    }

    #[Test]
    public function infolist_memiliki_tata_letak_simetris_tanpa_duplikasi_tombol_edit(): void
    {
        $schema = Schema::make();
        $configured = KontakInfolist::configure($schema);

        $components = $configured->getComponents();
        $this->assertNotEmpty($components);

        $identitasSection = $components[0];
        $this->assertInstanceOf(Section::class, $identitasSection);

        // Verifikasi tidak ada tombol edit di header section agar tidak duplikat dengan footer
        $headerActions = $identitasSection->getHeaderActions();
        $this->assertEmpty($headerActions, 'Header section harus bersih tanpa duplikasi tombol edit.');
    }
}
