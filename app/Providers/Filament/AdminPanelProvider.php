<?php

namespace App\Providers\Filament;

use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\Widgets\AccountWidget;

/** Panel untuk Admin UKM, Admin Inventaris, dan Member UKM Fotografi. */
class AdminPanelProvider extends BasePanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $this->base($panel, 'admin', 'UKM Fotografi UP')
            ->default()
            ->navigationGroups(['Inventaris', 'Galeri', 'Kegiatan', 'Manajemen User', 'Laporan'])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ]);
    }
}
