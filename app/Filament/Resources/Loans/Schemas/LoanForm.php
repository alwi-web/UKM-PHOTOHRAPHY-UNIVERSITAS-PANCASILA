<?php

namespace App\Filament\Resources\Loans\Schemas;

use App\Enums\ItemCondition;
use App\Enums\UserStatus;
use App\Models\Item;
use App\Models\User;
use App\Policies\LoanPolicy;
use Closure;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class LoanForm
{
    public static function configure(Schema $schema): Schema
    {
        $manages = fn () => LoanPolicy::manages(auth()->user());

        return $schema
            ->components([
                Section::make('Data Peminjam')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('user_id')
                            ->label('Akun Peminjam')
                            ->relationship('user', 'name', fn ($query) => $query->where('status', UserStatus::Active))
                            ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->name} ({$record->npm})")
                            ->searchable(['name', 'npm'])
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if ($user = User::find($state)) {
                                    $set('borrower_name', $user->name);
                                    $set('borrower_npm', $user->npm);
                                    $set('borrower_unit', $user->unit());
                                }
                            })
                            ->default(fn () => auth()->id())
                            ->required()
                            ->visible($manages)
                            ->columnSpanFull(),
                        TextInput::make('borrower_name')
                            ->label('Nama Lengkap')
                            ->default(fn () => auth()->user()->name)
                            ->required()
                            ->maxLength(255),
                        TextInput::make('borrower_npm')
                            ->label('NPM')
                            ->default(fn () => auth()->user()->npm)
                            ->required()
                            ->maxLength(20),
                        TextInput::make('borrower_unit')
                            ->label('Fakultas / UKM')
                            ->default(fn () => auth()->user()->unit())
                            ->required()
                            ->maxLength(255),
                    ]),
                Section::make('Waktu Peminjaman')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        DateTimePicker::make('borrowed_at')
                            ->label('Dipinjam dari tanggal')
                            ->seconds(false)
                            ->minDate(fn (string $operation) => $operation === 'create' ? now()->startOfDay() : null)
                            ->required(),
                        DateTimePicker::make('due_at')
                            ->label('Sampai tanggal')
                            ->seconds(false)
                            ->afterOrEqual('borrowed_at')
                            ->required(),
                        DateTimePicker::make('returned_at')
                            ->label('Dikembalikan pada')
                            ->seconds(false)
                            ->visible(fn (string $operation) => $operation === 'edit' && LoanPolicy::manages(auth()->user())),
                        Textarea::make('purpose')
                            ->label('Keperluan')
                            ->placeholder('Contoh: liputan pentas seni UKM Musik')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
                Section::make('Barang yang Dipinjam')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('loanItems')
                            ->hiddenLabel()
                            ->relationship()
                            ->columns(4)
                            ->minItems(1)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah barang')
                            ->deleteAction(fn ($action) => $action->tooltip('Hapus barang'))
                            ->itemLabel(fn (array $state) => Item::find($state['item_id'] ?? null)?->name)
                            ->schema([
                                Select::make('item_id')
                                    ->label('Barang')
                                    ->options(fn () => Item::orderBy('name')->get()
                                        ->mapWithKeys(fn (Item $item) => [$item->id => "{$item->code} · {$item->name} (tersedia {$item->availableQuantity()})"]))
                                    ->searchable()
                                    ->live()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->required()
                                    ->columnSpan(2),
                                TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->integer()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required()
                                    ->rule(fn (Get $get, $livewire) => function (string $attribute, $value, Closure $fail) use ($get, $livewire) {
                                        $item = Item::find($get('item_id'));
                                        $loanId = $livewire->record?->getKey();
                                        if ($item && $value > $item->availableQuantity($loanId)) {
                                            $fail("Stok {$item->name} tersedia hanya {$item->availableQuantity($loanId)} unit.");
                                        }
                                    }),
                                Select::make('condition_before')
                                    ->label('Kondisi Sebelum')
                                    ->options(ItemCondition::class)
                                    ->visible($manages),
                                Select::make('condition_after')
                                    ->label('Kondisi Sesudah')
                                    ->options(ItemCondition::class)
                                    ->visible(fn (string $operation) => $operation === 'edit' && LoanPolicy::manages(auth()->user())),
                            ]),
                    ]),
            ]);
    }
}
