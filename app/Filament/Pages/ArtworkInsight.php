<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ArtworksPerCategoryChart;
use App\Filament\Widgets\ArtworkStatsOverview;
use App\Filament\Widgets\UploadsPerMonthChart;
use App\Models\Artwork;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

class ArtworkInsight extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Insight Karya';

    protected static ?string $title = 'Insight Karya';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdminUkm() ?? false;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ArtworkStatsOverview::class,
            ArtworksPerCategoryChart::class,
            UploadsPerMonthChart::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 2;
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Karya Paling Banyak Dilihat')
                ->schema([EmbeddedTable::make()]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Artwork::query()->with(['user', 'category']))
            ->defaultSort('views', 'desc')
            ->paginated([10, 25])
            ->columns([
                ImageColumn::make('image')->label('')->disk('public')->imageHeight(48),
                TextColumn::make('title')->label('Judul')->searchable(),
                TextColumn::make('user.name')->label('Fotografer')->searchable(),
                TextColumn::make('category.name')->label('Kategori')->badge()->color('gray'),
                TextColumn::make('status')->badge(),
                TextColumn::make('views')->label('Dilihat')->numeric()->sortable(),
            ]);
    }
}
