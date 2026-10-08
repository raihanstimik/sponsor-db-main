<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SessionKeepaliveTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function rute_keepalive_mengembalikan_respons_204_dan_memperbarui_sesi(): void
    {
        $response = $this->get('/livewire-keepalive');

        $response->assertNoContent();
    }

    #[Test]
    public function rute_keepalive_dapat_diakses_oleh_user_yang_telah_login(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/livewire-keepalive');

        $response->assertNoContent();
        $this->assertAuthenticatedAs($user);
    }
}
