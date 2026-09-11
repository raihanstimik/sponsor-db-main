<?php

namespace App\Providers;

use App\Filament\Pages\Auth\CustomLogin;
use App\Filament\Pages\Auth\CustomRegister;
use App\Models\KategoriKegiatan;
use App\Models\Kegiatan;
use App\Models\Kontak;
use App\Models\Perusahaan;
use App\Models\User;
use App\Policies\ActivityPolicy;
use App\Policies\KategoriKegiatanPolicy;
use App\Policies\KegiatanPolicy;
use App\Policies\KontakPolicy;
use App\Policies\PerusahaanPolicy;
use App\Policies\UserPolicy;
use App\Support\Hooks\SanitizeUtf8State;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Daftarkan SEBELUM LivewireServiceProvider::boot() memanggil
        // ComponentHookRegistry::boot(). Hook yang didaftarkan di boot()
        // justru muncul SETELAH library membuat listener mount/hydrate/dehydrate
        // sehingga hook TIDAK PERNAH terpasang pada komponen saat runtime.
        Livewire::componentHook(new SanitizeUtf8State);
    }

    public function boot(): void
    {
        if (! app()->environment('testing') && (str_starts_with((string) config('app.url'), 'https://') || request()->header('x-forwarded-proto') === 'https')) {
            URL::forceScheme('https');
        }

        FilamentColor::register([
            'success' => Color::hex('#1F8A70'),
            'warning' => Color::hex('#D98E04'),
            'danger' => Color::hex('#C0392B'),
        ]);

        // Footer hak cipta di halaman auth (login & register) ala Stitch
        FilamentView::registerRenderHook(
            PanelsRenderHook::FOOTER,
            fn (): View => view('filament.auth.footer'),
            scopes: [CustomLogin::class, CustomRegister::class],
        );

        // Preload logo LCP di header HTML agar langsung dimuat dengan prioritas tinggi
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            fn (): HtmlString => new HtmlString('<link rel="preload" as="image" href="'.asset('images/logo-icm.webp').'" type="image/webp" fetchpriority="high">'."\n"),
        );
        Gate::policy(Activity::class, ActivityPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(KategoriKegiatan::class, KategoriKegiatanPolicy::class);
        Gate::policy(Kegiatan::class, KegiatanPolicy::class);
        Gate::policy(Kontak::class, KontakPolicy::class);
        Gate::policy(Perusahaan::class, PerusahaanPolicy::class);

        // Admin super-bypass: kalau sudah isAdmin, lewati permission check granular
        Gate::before(fn (User $user, string $ability) => $user->isAdmin() ? true : null);

        // Invalidate cache 60 detik untuk 50 reader — stale max 60s masih aman
        $forgetIcm = fn (): \Closure => function (): void {
            Cache::forget('icm:stats_overview');
            Cache::forget('icm:chart_kategori');
            Cache::forget('icm:chart_top_event');
            Cache::forget('icm:peta_nomor_perusahaan');
            Cache::forget('icm:perusahaan_stats');
        };
        foreach ([Kontak::class, Perusahaan::class, Kegiatan::class, KategoriKegiatan::class] as $model) {
            $model::saved($forgetIcm());
            $model::deleted($forgetIcm());
        }

        // --------------------------------------------------------------------------
        // Rate Limiter: Skalabilitas 50+ Pegawai & Perlindungan Resource
        // --------------------------------------------------------------------------
        RateLimiter::for('panel-user', function (Request $request) {
            if (app()->environment('testing') && ! $request->header('X-Test-Rate-Limit')) {
                return Limit::none();
            }

            $user = $request->user();

            if ($user) {
                return Limit::perMinute(240)
                    ->by($user->id)
                    ->response(function (Request $request, array $headers) {
                        if ($request->expectsJson() || $request->header('X-Livewire')) {
                            return response()->json([
                                'message' => 'Terlalu banyak permintaan dalam waktu singkat. Mohon tunggu beberapa detik.',
                            ], 429, $headers);
                        }

                        return response(
                            'Terlalu banyak permintaan dalam waktu singkat. Mohon tunggu beberapa saat sebelum mencoba kembali.',
                            429,
                            $headers
                        );
                    });
            }

            return Limit::perMinute(60)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response(
                        'Terlalu banyak permintaan dari alamat IP ini. Silakan coba kembali dalam 1 menit.',
                        429,
                        $headers
                    );
                });
        });

        RateLimiter::for('exports', function (Request $request) {
            if (app()->environment('testing') && ! $request->header('X-Test-Rate-Limit')) {
                return Limit::none();
            }

            $key = $request->user()?->id ?: $request->ip();

            return Limit::perMinute(6)
                ->by($key)
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Permintaan export terlalu sering. Mohon tunggu beberapa saat sebelum mengunduh kembali.',
                    ], 429, $headers);
                });
        });
    }
}
