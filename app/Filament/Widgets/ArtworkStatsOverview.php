<?php

namespace App\Filament\Widgets;

use App\Enums\ArtworkStatus;
use App\Models\Artwork;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ArtworkStatsOverview extends StatsOverviewWidget
{
    /** Hanya tampil di halaman Insight Karya, bukan di dasbor. */
    protected static bool $isDiscovered = false;

    protected function getStats(): array
    {
        $total = Artwork::count();
        $approved = Artwork::where('status', ArtworkStatus::Approved)->count();
        $curated = Artwork::where('status', '!=', ArtworkStatus::Pending)->count();
        $topPhotographer = Artwork::query()
            ->selectRaw('user_id, count(*) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->with('user')
            ->first();

        return [
            Stat::make('Total Karya', $total)
                ->description(Artwork::where('status', ArtworkStatus::Pending)->count().' menunggu kurasi'),
            Stat::make('Tingkat Lolos Kurasi', $curated ? round($approved / $curated * 100).'%' : '-')
                ->description("{$approved} dari {$curated} karya yang dikurasi"),
            Stat::make('Tampil di Website', Artwork::published()->count()),
            Stat::make('Total Dilihat', number_format(Artwork::sum('views'), 0, ',', '.'))
                ->description($topPhotographer ? "Paling produktif: {$topPhotographer->user?->name} ({$topPhotographer->total} karya)" : null),
        ];
    }
}
