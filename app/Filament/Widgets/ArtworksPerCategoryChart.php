<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use Filament\Widgets\ChartWidget;

class ArtworksPerCategoryChart extends ChartWidget
{
    /** Hanya tampil di halaman Insight Karya, bukan di dasbor. */
    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Karya per Kategori';

    protected function getData(): array
    {
        $categories = Category::withCount('artworks')->withSum('artworks', 'views')->orderBy('name')->get();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah karya',
                    'data' => $categories->pluck('artworks_count')->all(),
                    'backgroundColor' => '#111111',
                ],
            ],
            'labels' => $categories->pluck('name')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
