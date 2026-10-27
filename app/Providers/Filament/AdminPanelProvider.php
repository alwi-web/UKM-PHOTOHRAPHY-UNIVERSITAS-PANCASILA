<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
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
            // login memakai halaman terpisah di /login (lihat LoginController)
            ->profile(isSimple: false)
            ->brandName('UKM Fotografi UP')
            ->brandLogo(asset('images/photoup-logo.svg'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('images/photoup-logo.svg'))
            ->colors([
                // aksen hitam, sama dengan website
                'primary' => [
                    50 => 'oklch(0.97 0 0)',
                    100 => 'oklch(0.93 0 0)',
                    200 => 'oklch(0.87 0 0)',
                    300 => 'oklch(0.75 0 0)',
                    400 => 'oklch(0.62 0 0)',
                    500 => 'oklch(0.32 0 0)',
                    600 => 'oklch(0.2 0 0)',
                    700 => 'oklch(0.16 0 0)',
                    800 => 'oklch(0.13 0 0)',
                    900 => 'oklch(0.11 0 0)',
                    950 => 'oklch(0.08 0 0)',
                ],
            ])
            // latar panel abu-abu, sama dengan website
            ->renderHook(PanelsRenderHook::HEAD_END, fn (): string => '<style>.fi-body{background-color:#e9e9e7}</style>')
            ->font('Plus Jakarta Sans')
            ->navigationGroups(['Inventaris', 'Galeri', 'Kegiatan', 'Manajemen User', 'Laporan'])
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
