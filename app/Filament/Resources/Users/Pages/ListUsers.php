<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\Role;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah User'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua'),
            'pengurus' => Tab::make('Pengurus')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('role', [Role::AdminUkm, Role::AdminInventaris, Role::Kurator])),
            'member' => Tab::make('Member')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', Role::Member)),
            'lain' => Tab::make('Anggota UKM Lain')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', Role::AnggotaLain)),
        ];
    }
}
