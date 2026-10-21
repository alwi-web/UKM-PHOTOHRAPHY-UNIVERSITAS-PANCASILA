<?php

namespace App\Filament\Resources\Items\Tables;

use App\Enums\ItemCondition;
use App\Enums\LoanStatus;
use App\Models\Item;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withSum(
                ['loans as borrowed_quantity' => fn (Builder $q) => $q->where('status', LoanStatus::Dipinjam)],
                'quantity',
            ))
            ->columns([
                ImageColumn::make('photo')->label('')->disk('public')->square(),
                TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                TextColumn::make('name')->label('Nama Barang')->searchable()->sortable()
                    ->description(fn ($record) => $record->brand),
                TextColumn::make('category')->label('Kategori')->badge()->color('gray')->sortable(),
                TextColumn::make('quantity')->label('Jumlah')->numeric()->sortable(),
                TextColumn::make('borrowed_quantity')->label('Dipinjam')->numeric()->default(0),
                TextColumn::make('available')
                    ->label('Tersedia')
                    ->state(fn ($record) => max(0, $record->quantity - (int) $record->borrowed_quantity))
                    ->badge()
                    ->color(fn (int $state) => $state > 0 ? 'success' : 'danger'),
                TextColumn::make('condition')->label('Kondisi')->badge(),
            ])
            ->filters([
                SelectFilter::make('condition')->label('Kondisi')->options(ItemCondition::class),
                SelectFilter::make('category')->label('Kategori')
                    ->options(fn () => Item::query()->distinct()->pluck('category', 'category')->filter()->all()),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
