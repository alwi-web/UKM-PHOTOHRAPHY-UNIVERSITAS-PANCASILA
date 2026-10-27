<?php

namespace App\Providers\Filament;

use App\Http\Middleware\RedirectToOwnPanel;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/** Tampilan dan middleware yang sama untuk semua panel (admin, kurator, anggota UKM lain). */
abstract class BasePanelProvider extends PanelProvider
{
    protected function base(Panel $panel, string $id, string $brandName): Panel
    {
        return $panel
            ->id($id)
            ->path($id)
            // login memakai halaman terpisah di /login (lihat LoginController)
            ->profile(isSimple: false)
            ->brandName($brandName)
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
            ->sidebarCollapsibleOnDesktop()
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                // harus jalan sebelum Authenticate supaya user diarahkan, bukan ditolak 403
                RedirectToOwnPanel::class,
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

    /** Nama panel ditampilkan di samping logo, supaya user tahu sedang di panel mana. */
    protected function withLabel(Panel $panel): Panel
    {
        $label = $panel->getBrandName();

        return $panel->renderHook(PanelsRenderHook::TOPBAR_LOGO_AFTER, fn (): string => '<span style="margin-inline-start:.75rem;padding:.3rem .75rem;border-radius:999px;background:#111;color:#fff;font-size:.8rem;font-weight:600;white-space:nowrap">'.e($label).'</span>');
    }
}
