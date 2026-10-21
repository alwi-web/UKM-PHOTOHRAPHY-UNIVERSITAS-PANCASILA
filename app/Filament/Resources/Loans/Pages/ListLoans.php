<?php

namespace App\Filament\Resources\Loans\Pages;

use App\Enums\LoanStatus;
use App\Filament\Resources\Loans\LoanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListLoans extends ListRecords
{
    protected static string $resource = LoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Ajukan Peminjaman'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua'),
            'diajukan' => Tab::make('Diajukan')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', LoanStatus::Diajukan)),
            'dipinjam' => Tab::make('Sedang Dipinjam')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', LoanStatus::Dipinjam)),
            'terlambat' => Tab::make('Terlambat')
                ->modifyQueryUsing(fn (Builder $query) => $query->overdue()),
            'selesai' => Tab::make('Selesai')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [LoanStatus::Dikembalikan, LoanStatus::Ditolak])),
        ];
    }
}
