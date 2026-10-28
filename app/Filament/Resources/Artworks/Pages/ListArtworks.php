<?php

namespace App\Filament\Resources\Artworks\Pages;

use App\Enums\ArtworkStatus;
use App\Enums\Role;
use App\Filament\Resources\Artworks\ArtworkResource;
use App\Models\Artwork;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListArtworks extends ListRecords
{
    protected static string $resource = ArtworkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Unggah Karya'),
        ];
    }

    /** Kurator langsung melihat antrean karya yang perlu dikurasi. */
    public function getDefaultActiveTab(): string|int|null
    {
        return auth()->user()->role === Role::Kurator ? 'pending' : 'semua';
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua'),
            'pending' => Tab::make('Menunggu Kurasi')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ArtworkStatus::Pending)),
            'approved' => Tab::make('Lolos Kurasi')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ArtworkStatus::Approved)),
            'featured' => Tab::make('Tampil di Website')
                ->modifyQueryUsing(fn (Builder $query) => $query->published()),
            'nominasi' => Tab::make('Nominasi Minggu Ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('nominated_week', Artwork::weekOf()->toDateString())),
            'rejected' => Tab::make('Ditolak')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ArtworkStatus::Rejected)),
        ];
    }
}
