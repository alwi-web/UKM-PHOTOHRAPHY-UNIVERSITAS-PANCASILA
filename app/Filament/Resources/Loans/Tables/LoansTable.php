<?php

namespace App\Filament\Resources\Loans\Tables;

use App\Enums\ItemCondition;
use App\Enums\LoanStatus;
use App\Models\Loan;
use DomainException;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LoansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('item.name')->label('Barang')->searchable()
                    ->description(fn (Loan $record) => $record->item->code),
                TextColumn::make('user.name')->label('Peminjam')->searchable()
                    ->description(fn (Loan $record) => $record->user->nim),
                TextColumn::make('quantity')->label('Jml'),
                TextColumn::make('borrowed_at')->label('Mulai Dipinjam')->dateTime('d M Y H:i')->sortable()->placeholder('-'),
                TextColumn::make('due_at')->label('Batas Kembali')->dateTime('d M Y H:i')->sortable()
                    ->color(fn (Loan $record) => $record->isOverdue() ? 'danger' : null)
                    ->description(fn (Loan $record) => $record->isOverdue() ? 'Terlambat '.$record->due_at->diffForHumans(syntax: true) : null),
                TextColumn::make('returned_at')->label('Dikembalikan')->dateTime('d M Y H:i')->sortable()->placeholder('-')
                    ->toggleable(),
                TextColumn::make('condition_before')->label('Kondisi Awal')->badge()->placeholder('-')->toggleable(),
                TextColumn::make('condition_after')->label('Kondisi Akhir')->badge()->placeholder('-')->toggleable(),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('status')->options(LoanStatus::class),
                SelectFilter::make('item')->label('Barang')->relationship('item', 'name')->searchable()->preload(),
            ])
            ->recordActions([
                Action::make('handOver')
                    ->label('Serahkan')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (Loan $record) => $record->status === LoanStatus::Diajukan && auth()->user()->can('process', $record))
                    ->schema([
                        Select::make('condition_before')
                            ->label('Kondisi barang saat diserahkan')
                            ->options(ItemCondition::class)
                            ->default(fn (Loan $record) => $record->item->condition)
                            ->required(),
                    ])
                    ->action(fn (Loan $record, array $data) => self::run(
                        fn () => $record->handOver(auth()->user(), $data['condition_before'] instanceof ItemCondition ? $data['condition_before'] : ItemCondition::from($data['condition_before'])),
                        'Barang diserahkan ke peminjam.',
                    )),
                Action::make('reject')
                    ->label('Tolak')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->visible(fn (Loan $record) => $record->status === LoanStatus::Diajukan && auth()->user()->can('process', $record))
                    ->schema([
                        Textarea::make('notes')->label('Alasan penolakan'),
                    ])
                    ->action(fn (Loan $record, array $data) => self::run(
                        fn () => $record->reject(auth()->user(), $data['notes'] ?? null),
                        'Peminjaman ditolak.',
                    )),
                Action::make('return')
                    ->label('Terima Kembali')
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->color('info')
                    ->visible(fn (Loan $record) => $record->status === LoanStatus::Dipinjam && auth()->user()->can('process', $record))
                    ->schema([
                        Select::make('condition_after')
                            ->label('Kondisi barang saat dikembalikan')
                            ->options(ItemCondition::class)
                            ->default(ItemCondition::Baik)
                            ->required(),
                        Textarea::make('notes')->label('Catatan kondisi'),
                    ])
                    ->action(fn (Loan $record, array $data) => self::run(
                        fn () => $record->markReturned(
                            auth()->user(),
                            $data['condition_after'] instanceof ItemCondition ? $data['condition_after'] : ItemCondition::from($data['condition_after']),
                            $data['notes'] ?? null,
                        ),
                        'Pengembalian barang dicatat.',
                    )),
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function run(callable $callback, string $success): void
    {
        try {
            $callback();
            Notification::make()->title($success)->success()->send();
        } catch (DomainException $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
        }
    }
}
