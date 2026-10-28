<?php

namespace App\Filament\Resources\Artworks;

use App\Enums\ArtworkStatus;
use App\Filament\Resources\Artworks\Pages\CreateArtwork;
use App\Filament\Resources\Artworks\Pages\EditArtwork;
use App\Filament\Resources\Artworks\Pages\ListArtworks;
use App\Filament\Resources\Artworks\Schemas\ArtworkForm;
use App\Filament\Resources\Artworks\Tables\ArtworksTable;
use App\Models\Artwork;
use App\Policies\ArtworkPolicy;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ArtworkResource extends Resource
{
    protected static ?string $model = Artwork::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Galeri';

    protected static ?string $modelLabel = 'karya';

    protected static ?string $pluralModelLabel = 'Karya Foto';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 1;

    /** Kurator & admin melihat semua karya, anggota hanya karyanya sendiri. */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['user', 'category']);

        return ArtworkPolicy::curates(auth()->user()) ? $query : $query->whereBelongsTo(auth()->user());
    }

    public static function getNavigationBadge(): ?string
    {
        if (! ArtworkPolicy::curates(auth()->user())) {
            return null;
        }

        $pending = Artwork::where('status', ArtworkStatus::Pending)->count();

        return $pending ? (string) $pending : null;
    }

    public static function form(Schema $schema): Schema
    {
        return ArtworkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArtworksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArtworks::route('/'),
            'create' => CreateArtwork::route('/create'),
            'edit' => EditArtwork::route('/{record}/edit'),
        ];
    }
}
