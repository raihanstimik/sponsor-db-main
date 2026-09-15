<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Kontak;
use App\Models\Perusahaan;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Livewire\GlobalSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GlobalSearchHighlightTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    #[Test]
    public function global_search_input_tidak_menampilkan_suffix_meta_k(): void
    {
        $this->assertNull(Filament::getGlobalSearchFieldSuffix());

        Livewire::test(GlobalSearch::class)
            ->assertDontSee('META+K')
            ->assertDontSee('meta+k')
            ->assertDontSee('inline-suffix');
    }

    #[Test]
    public function global_search_menandai_kata_yang_cocok_pada_judul_dengan_warna_stabilo(): void
    {
        $perusahaan = Perusahaan::factory()->create([
            'nama_standar' => 'PT Mitra Sejahtera',
        ]);

        Kontak::factory()->create([
            'nama' => 'Ibu Sarah Amelia',
            'perusahaan_id' => $perusahaan->id,
        ]);

        Livewire::test(GlobalSearch::class)
            ->set('search', 'ibu')
            ->assertSeeHtml('<mark class="fi-stabilo-highlight">Ibu</mark> Sarah Amelia');
    }

    #[Test]
    public function global_search_menandai_kata_yang_cocok_pada_detail_dengan_warna_stabilo(): void
    {
        $perusahaan = Perusahaan::factory()->create([
            'nama_standar' => 'PT Bio Farma Persero',
        ]);

        Kontak::factory()->create([
            'nama' => 'Rahmat Hidayat',
            'perusahaan_id' => $perusahaan->id,
        ]);

        Livewire::test(GlobalSearch::class)
            ->set('search', 'Bio')
            ->assertSeeHtml('PT <mark class="fi-stabilo-highlight">Bio</mark> Farma Persero');
    }
}
