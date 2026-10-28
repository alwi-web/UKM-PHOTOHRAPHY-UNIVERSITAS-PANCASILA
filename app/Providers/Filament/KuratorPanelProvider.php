<?php

namespace App\Providers\Filament;

use App\Filament\Resources\Artworks\ArtworkResource;
use App\Filament\Widgets\ArtworksPerCategoryChart;
use App\Filament\Widgets\DashboardStats;
use App\Filament\Widgets\PendingArtworks;
use App\Filament\Widgets\UploadsPerMonthChart;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\Widgets\AccountWidget;

/** Panel Kurator: hanya untuk mengkurasi karya yang tampil di website. */
class KuratorPanelProvider extends BasePanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $this->withLabel($this->base($panel, 'kurator', 'Panel Kurator'))
            ->resources([
                ArtworkResource::class,
            ])
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                AccountWidget::class,
                DashboardStats::class,
                PendingArtworks::class,
                ArtworksPerCategoryChart::class,
                UploadsPerMonthChart::class,
            ]);
    }
}
