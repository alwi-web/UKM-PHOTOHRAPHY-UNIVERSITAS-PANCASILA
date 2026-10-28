<?php

namespace App\Filament\Resources\Items\RelationManagers;

use App\Enums\LoanStatus;
use App\Models\LoanItem;
use App\Policies\LoanPolicy;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Riwayat siapa saja yang pernah meminjam barang ini. */
class LoansRelationManager extends RelationManager
{
    protected static string $relationship = 'loanItems';

    protected static ?string $title = 'Riwayat Peminjaman';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return LoanPolicy::manages(auth()->user());
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('loan'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('loan.borrower_name')->label('Peminjam')
                    ->description(fn (LoanItem $record) => "{$record->loan->borrower_npm} · {$record->loan->borrower_unit}"),
                TextColumn::make('quantity')->label('Jml'),
                TextColumn::make('loan.borrowed_at')->label('Dari')->dateTime('d M Y H:i'),
                TextColumn::make('loan.due_at')->label('Sampai')->dateTime('d M Y H:i'),
                TextColumn::make('loan.returned_at')->label('Dikembalikan')->dateTime('d M Y H:i')->placeholder('-'),
                TextColumn::make('condition_before')->label('Kondisi Sebelum')->badge()->placeholder('-'),
                TextColumn::make('condition_after')->label('Kondisi Sesudah')->badge()->placeholder('-'),
                TextColumn::make('loan.status')->label('Status')->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(LoanStatus::class)
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['value'],
                        fn ($q, $v) => $q->whereHas('loan', fn ($l) => $l->where('status', $v)),
                    )),
            ]);
    }
}
