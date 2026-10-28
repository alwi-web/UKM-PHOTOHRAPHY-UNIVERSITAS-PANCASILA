<?php

namespace App\Filament\Widgets;

use App\Enums\ArtworkStatus;
use App\Filament\Resources\Artworks\ArtworkResource;
use App\Models\Artwork;
use Filament\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/** Antrean karya yang belum dikurasi, tampil di dasbor Panel Kurator. */
class PendingArtworks extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Antrean Kurasi';

    public function table(Table $table): Table
    {
        return $table
            ->query(Artwork::query()->with(['user', 'category'])->where('status', ArtworkStatus::Pending)->oldest())
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Semua karya sudah dikurasi')
            ->columns([
                ImageColumn::make('image')->label('')->disk('public')->imageHeight(48),
                TextColumn::make('title')->label('Judul')->limit(40),
                TextColumn::make('user.name')->label('Fotografer'),
                TextColumn::make('category.name')->label('Kategori')->badge()->color('gray'),
                TextColumn::make('created_at')->label('Diunggah')->since(),
            ])
            ->headerActions([
                Action::make('all')->label('Buka halaman kurasi')
                    ->url(ArtworkResource::getUrl('index', ['tableFilters' => ['status' => ['value' => ArtworkStatus::Pending->value]]])),
            ]);
    }
}
