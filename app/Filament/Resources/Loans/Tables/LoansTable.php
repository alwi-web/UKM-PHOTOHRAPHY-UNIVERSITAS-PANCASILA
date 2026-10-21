<?php

namespace App\Filament\Resources\Loans\Tables;

use App\Enums\ItemCondition;
use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\LoanItem;
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
                TextColumn::make('borrower_name')->label('Peminjam')->searchable(['borrower_name', 'borrower_npm', 'borrower_unit'])
                    ->description(fn (Loan $record) => "{$record->borrower_npm} · {$record->borrower_unit}"),
                TextColumn::make('items')
                    ->label('Barang')
                    ->state(fn (Loan $record) => $record->loanItems->map(fn (LoanItem $li) => "{$li->item->name} ×{$li->quantity}")->all())
                    ->listWithLineBreaks()
                    ->bulleted(),
                TextColumn::make('borrowed_at')->label('Dari')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('due_at')->label('Sampai')->dateTime('d M Y H:i')->sortable()
                    ->color(fn (Loan $record) => $record->isOverdue() ? 'danger' : null)
                    ->description(fn (Loan $record) => $record->isOverdue() ? 'Terlambat '.$record->due_at->diffForHumans(syntax: true) : null),
                TextColumn::make('returned_at')->label('Dikembalikan')->dateTime('d M Y H:i')->sortable()->placeholder('-')
                    ->toggleable(),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('status')->options(LoanStatus::class),
                SelectFilter::make('item')
                    ->label('Barang')
                    ->relationship('loanItems.item', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('handOver')
                    ->label('Serahkan')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (Loan $record) => $record->status === LoanStatus::Diajukan && auth()->user()->can('process', $record))
                    ->modalHeading('Serahkan barang ke peminjam')
                    ->modalDescription('Catat kondisi tiap barang sebelum dipinjam.')
                    ->fillForm(fn (Loan $record) => [
                        'conditions' => $record->loanItems->mapWithKeys(fn (LoanItem $li) => [$li->id => $li->item->condition])->all(),
                    ])
                    ->schema(fn (Loan $record) => self::conditionFields($record, 'Kondisi sebelum dipinjam'))
                    ->action(fn (Loan $record, array $data) => self::run(
                        fn () => $record->handOver(auth()->user(), $data['conditions'] ?? []),
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
                    ->modalHeading('Terima pengembalian barang')
                    ->modalDescription('Catat kondisi tiap barang setelah dipinjam.')
                    ->fillForm(fn (Loan $record) => [
                        'conditions' => $record->loanItems->mapWithKeys(fn (LoanItem $li) => [$li->id => ItemCondition::Baik])->all(),
                    ])
                    ->schema(fn (Loan $record) => [
                        ...self::conditionFields($record, 'Kondisi setelah dipinjam'),
                        Textarea::make('notes')->label('Catatan'),
                    ])
                    ->action(fn (Loan $record, array $data) => self::run(
                        fn () => $record->markReturned(auth()->user(), $data['conditions'] ?? [], $data['notes'] ?? null),
                        'Pengembalian barang dicatat.',
                    )),
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()->label(fn (Loan $record) => $record->status === LoanStatus::Diajukan ? 'Batalkan' : 'Hapus'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /** Satu pilihan kondisi untuk setiap barang dalam peminjaman. */
    private static function conditionFields(Loan $record, string $label): array
    {
        return $record->loanItems->map(fn (LoanItem $li) => Select::make("conditions.{$li->id}")
            ->label("{$label}: {$li->item->name} (×{$li->quantity})")
            ->options(ItemCondition::class)
            ->required()
        )->all();
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
