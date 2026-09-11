<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimiterTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_user_rate_limiter_terdaftar_dan_mengisolasi_per_user_id(): void
    {
        $limiter = RateLimiter::limiter('panel-user');
        $this->assertNotNull($limiter, 'Rate limiter panel-user harus terdaftar di AppServiceProvider');

        $user1 = User::factory()->create(['id' => 101]);
        $user2 = User::factory()->create(['id' => 102]);

        $requestUser1 = Request::create('/admin', 'GET', [], [], [], [
            'HTTP_X_TEST_RATE_LIMIT' => 'true',
        ]);
        $requestUser1->setUserResolver(fn () => $user1);

        $requestUser2 = Request::create('/admin', 'GET', [], [], [], [
            'HTTP_X_TEST_RATE_LIMIT' => 'true',
        ]);
        $requestUser2->setUserResolver(fn () => $user2);

        /** @var Limit $limit1 */
        $limit1 = $limiter($requestUser1);
        /** @var Limit $limit2 */
        $limit2 = $limiter($requestUser2);

        $this->assertInstanceOf(Limit::class, $limit1);
        $this->assertInstanceOf(Limit::class, $limit2);

        $this->assertSame(240, $limit1->maxAttempts);
        $this->assertSame(240, $limit2->maxAttempts);

        $this->assertSame(101, $limit1->key);
        $this->assertSame(102, $limit2->key);
    }

    public function test_panel_user_rate_limiter_menggunakan_ip_untuk_tamu(): void
    {
        $limiter = RateLimiter::limiter('panel-user');
        $requestGuest = Request::create('/admin/login', 'GET', [], [], [], [
            'REMOTE_ADDR' => '192.168.1.50',
            'HTTP_X_TEST_RATE_LIMIT' => 'true',
        ]);
        $requestGuest->setUserResolver(fn () => null);

        /** @var Limit $limit */
        $limit = $limiter($requestGuest);

        $this->assertInstanceOf(Limit::class, $limit);
        $this->assertSame(60, $limit->maxAttempts);
        $this->assertSame('192.168.1.50', $limit->key);
    }

    public function test_exports_rate_limiter_terdaftar_dengan_batas_6_per_menit(): void
    {
        $limiter = RateLimiter::limiter('exports');
        $this->assertNotNull($limiter, 'Rate limiter exports harus terdaftar');

        $user = User::factory()->create(['id' => 201]);
        $request = Request::create('/admin/kontak/export', 'GET', [], [], [], [
            'HTTP_X_TEST_RATE_LIMIT' => 'true',
        ]);
        $request->setUserResolver(fn () => $user);

        /** @var Limit $limit */
        $limit = $limiter($request);

        $this->assertInstanceOf(Limit::class, $limit);
        $this->assertSame(6, $limit->maxAttempts);
        $this->assertSame(201, $limit->key);
    }

    public function test_custom_response_429_memberikan_pesan_ramah(): void
    {
        $limiter = RateLimiter::limiter('panel-user');

        $user = User::factory()->create(['id' => 301]);
        $requestJson = Request::create('/admin', 'GET', [], [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_TEST_RATE_LIMIT' => 'true',
        ]);
        $requestJson->setUserResolver(fn () => $user);

        /** @var Limit $limit */
        $limit = $limiter($requestJson);
        $responseCallback = $limit->responseCallback;
        $this->assertNotNull($responseCallback);

        $response = $responseCallback($requestJson, ['Retry-After' => '30']);
        $this->assertSame(429, $response->getStatusCode());
        $this->assertStringContainsString('Terlalu banyak permintaan', (string) $response->getContent());
    }
}
