<?php

namespace App\Providers\Filament;

use App\Filament\Resources\Items\ItemResource;
use App\Filament\Resources\Loans\LoanResource;
use App\Filament\Widgets\DashboardStats;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\Widgets\AccountWidget;

/** Panel Anggota UKM Lain: melihat barang dan meminjam barang UKM Fotografi. */
class AnggotaPanelProvider extends BasePanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $this->withLabel($this->base($panel, 'anggota', 'Panel Anggota UKM Lain'))
            ->navigationGroups(['Inventaris'])
            ->resources([
                ItemResource::class,
                LoanResource::class,
            ])
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                AccountWidget::class,
                DashboardStats::class,
            ]);
    }
}
