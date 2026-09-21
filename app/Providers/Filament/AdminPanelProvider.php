<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\CustomLogin;
use App\Filament\Pages\Auth\CustomRegister;
use App\Filament\Pages\ProfilSaya;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(CustomLogin::class)
            ->registration(CustomRegister::class)
            ->brandName('ICM Sponsor')
            ->brandLogo(asset('images/logo-icm.png'))
            ->brandLogo(asset('images/logo-icm.webp'))
            ->brandLogoHeight('2.25rem')
            ->darkModeBrandLogo(asset('images/logo-icm.png'))
            ->darkModeBrandLogo(asset('images/logo-icm.webp'))
            ->favicon(asset('images/icon-icm.png'))
            ->favicon(fn (): string => asset('images/icon-icm.png') . '?v=' . (file_exists(public_path('images/icon-icm.png')) ? filemtime(public_path('images/icon-icm.png')) : 1))
            ->colors([
                'primary' => Color::hex('#18225E'),
                'primary' => [
                    50 => '#f0f4ff',
                    100 => '#e0e7ff',
                    200 => '#c7d2fe',
                    300 => '#a5b4fc',
                    400 => '#818cf8',
                    500 => '#3b49b8',
                    600 => '#18225e',
                    700 => '#131b4b',
                    800 => '#0e1438',
                    900 => '#0a0e28',
                    950 => '#050714',
                ],
                'secondary' => Color::hex('#EA7C1A'),
                'info' => Color::hex('#0284C7'),
                'success' => Color::hex('#10B981'),
                'warning' => Color::hex('#F59E0B'),
                'danger' => Color::hex('#F43F5E'),
                'gray' => Color::Slate,
            ])
            ->font('Fira Sans')
            ->spa()
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->globalSearchDebounce('400ms')
            ->simplePageMaxContentWidth(Width::Medium)
            ->viteTheme(['resources/css/filament/admin/theme.css', 'resources/js/app.js'])
            ->renderHook(
                PanelsRenderHook::HEAD_START,
                function (): string {
                    $v = file_exists(public_path('images/icon-icm.png')) ? (string) filemtime(public_path('images/icon-icm.png')) : '1';

                    return '<link rel="apple-touch-icon" sizes="180x180" href="' . asset('apple-touch-icon.png') . '?v=' . $v . '">' . PHP_EOL .
                        '<link rel="icon" type="image/png" sizes="32x32" href="' . asset('images/icon-icm-32x32.png') . '?v=' . $v . '">' . PHP_EOL .
                        '<link rel="icon" type="image/png" sizes="192x192" href="' . asset('images/icon-icm-192x192.png') . '?v=' . $v . '">' . PHP_EOL .
                        '<link rel="shortcut icon" href="' . asset('favicon.ico') . '?v=' . $v . '">';
                }
            )
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn (): string => view('filament.hooks.impersonation-banner')->render()
            )
            ->renderHook(
                PanelsRenderHook::SIMPLE_LAYOUT_START,
                fn (): string => view('filament.hooks.aurora-background')->render()
            )
            ->sidebarFullyCollapsibleOnDesktop()
            ->sidebarWidth('16.5rem')
            ->collapsedSidebarWidth('4rem')
            ->userMenuItems([
                MenuItem::make()
                    ->label('Profil Saya')
                    ->url(fn (): string => ProfilSaya::getUrl())
                    ->icon('heroicon-o-user-circle')
                    ->visible(fn (): bool => auth()->check()),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
                ProfilSaya::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                'throttle:panel-user',
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
