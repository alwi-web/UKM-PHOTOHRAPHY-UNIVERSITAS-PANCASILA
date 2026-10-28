<?php

namespace App\Filament\Resources\Items\Pages;

use App\Filament\Resources\Items\ItemResource;
use App\Filament\Resources\Loans\LoanResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListItems extends ListRecords
{
    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('borrow')
                ->label('Ajukan Peminjaman')
                ->icon(Heroicon::OutlinedArrowsRightLeft)
                ->color('gray')
                ->url(LoanResource::getUrl('create'))
                ->visible(fn () => LoanResource::canCreate()),
            CreateAction::make()->label('Tambah Barang'),
        ];
    }
}
