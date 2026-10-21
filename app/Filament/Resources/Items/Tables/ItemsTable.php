<?php

namespace App\Filament\Resources\Items\Tables;

use App\Enums\ItemCondition;
use App\Enums\LoanStatus;
use App\Models\Item;
use App\Models\LoanItem;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withSum(
                ['loanItems as borrowed_quantity' => fn (Builder $q) => $q->whereHas('loan', fn ($l) => $l->where('status', LoanStatus::Dipinjam))],
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
                    ->label(fn (HasTable $livewire) => self::period($livewire) ? 'Tersedia di Tanggal Itu' : 'Tersedia')
                    ->state(fn (Item $record, HasTable $livewire) => ($period = self::period($livewire))
                        ? $record->availableQuantityBetween(...$period)
                        : max(0, $record->quantity - (int) $record->borrowed_quantity))
                    ->badge()
                    ->color(fn (int $state) => $state > 0 ? 'success' : 'danger'),
                TextColumn::make('condition')->label('Kondisi')->badge(),
            ])
            ->filters([
                SelectFilter::make('condition')->label('Kondisi')->options(ItemCondition::class),
                SelectFilter::make('category')->label('Kategori')
                    ->options(fn () => Item::query()->distinct()->pluck('category', 'category')->filter()->all()),
                Filter::make('period')
                    ->label('Cek ketersediaan')
                    ->schema([
                        DateTimePicker::make('from')->label('Pinjam dari')->seconds(false),
                        DateTimePicker::make('until')->label('Sampai')->seconds(false)->after('from'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (! $period = self::parsePeriod($data)) {
                            return;
                        }

                        $query->where('quantity', '>', LoanItem::query()
                            ->selectRaw('coalesce(sum(quantity), 0)')
                            ->whereColumn('loan_items.item_id', 'items.id')
                            ->overlapping(...$period));
                    })
                    ->indicateUsing(fn (array $data) => ($period = self::parsePeriod($data))
                        ? 'Tersedia '.$period[0]->translatedFormat('d M Y H.i').' – '.$period[1]->translatedFormat('d M Y H.i')
                        : null),
                Filter::make('available')
                    ->label('Hanya yang tersedia saat ini')
                    ->query(fn (Builder $query) => $query->whereRaw(
                        'quantity > coalesce((select sum(loan_items.quantity) from loan_items join loans on loans.id = loan_items.loan_id where loan_items.item_id = items.id and loans.status = ?), 0)',
                        [LoanStatus::Dipinjam->value],
                    )),
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

    /** @return array{0: Carbon, 1: Carbon}|null */
    private static function period(HasTable $livewire): ?array
    {
        return self::parsePeriod($livewire->getTableFilterState('period') ?? []);
    }

    /** @return array{0: Carbon, 1: Carbon}|null */
    private static function parsePeriod(array $data): ?array
    {
        if (blank($data['from'] ?? null) || blank($data['until'] ?? null)) {
            return null;
        }

        [$from, $until] = [Carbon::parse($data['from']), Carbon::parse($data['until'])];

        return $until->gt($from) ? [$from, $until] : null;
    }
}
