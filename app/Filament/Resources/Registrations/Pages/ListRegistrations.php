<?php

namespace App\Filament\Resources\Registrations\Pages;

use App\Enums\RegistrationStatus;
use App\Filament\Resources\Registrations\RegistrationResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListRegistrations extends ListRecords
{
    protected static string $resource = RegistrationResource::class;

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua'),
            ...collect(RegistrationStatus::cases())->mapWithKeys(fn (RegistrationStatus $status) => [
                $status->value => Tab::make($status->getLabel())
                    ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $status)),
            ])->all(),
        ];
    }
}
