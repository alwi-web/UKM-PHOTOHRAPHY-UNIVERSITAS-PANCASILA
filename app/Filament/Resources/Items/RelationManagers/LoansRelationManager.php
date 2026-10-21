<?php

namespace App\Filament\Resources\Items\RelationManagers;

use App\Enums\LoanStatus;
use App\Policies\LoanPolicy;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/** Riwayat siapa saja yang pernah meminjam barang ini. */
class LoansRelationManager extends RelationManager
{
    protected static string $relationship = 'loans';

    protected static ?string $title = 'Riwayat Peminjaman';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return LoanPolicy::manages(auth()->user());
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')->label('Peminjam')->description(fn ($record) => $record->user->nim),
                TextColumn::make('quantity')->label('Jml'),
                TextColumn::make('borrowed_at')->label('Mulai Dipinjam')->dateTime('d M Y H:i')->placeholder('-'),
                TextColumn::make('due_at')->label('Batas Kembali')->dateTime('d M Y H:i'),
                TextColumn::make('returned_at')->label('Dikembalikan')->dateTime('d M Y H:i')->placeholder('-'),
                TextColumn::make('condition_before')->label('Kondisi Awal')->badge()->placeholder('-'),
                TextColumn::make('condition_after')->label('Kondisi Akhir')->badge()->placeholder('-'),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('status')->options(LoanStatus::class),
            ]);
    }
}
