<?php

namespace App\Filament\Widgets;

use App\Models\Artwork;
use Filament\Widgets\ChartWidget;

class UploadsPerMonthChart extends ChartWidget
{
    /** Hanya tampil di halaman Insight Karya, bukan di dasbor. */
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 4;

    protected ?string $heading = 'Unggahan 6 Bulan Terakhir';

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => now()->startOfMonth()->subMonths($i));

        $counts = $months->map(fn ($month) => Artwork::whereBetween('created_at', [$month, $month->copy()->endOfMonth()])->count());

        return [
            'datasets' => [
                [
                    'label' => 'Karya diunggah',
                    'data' => $counts->all(),
                    'borderColor' => '#111111',
                    'backgroundColor' => 'rgba(17, 17, 17, 0.12)',
                    'fill' => true,
                ],
            ],
            'labels' => $months->map(fn ($m) => $m->translatedFormat('M Y'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
