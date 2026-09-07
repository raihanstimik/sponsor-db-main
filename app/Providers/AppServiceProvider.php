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
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
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
        };
        foreach ([Kontak::class, Perusahaan::class, Kegiatan::class, KategoriKegiatan::class] as $model) {
            $model::saved($forgetIcm());
            $model::deleted($forgetIcm());
        }
    }
}
