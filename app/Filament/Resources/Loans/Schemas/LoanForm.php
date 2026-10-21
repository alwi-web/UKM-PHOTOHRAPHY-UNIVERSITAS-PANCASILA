<?php

namespace App\Filament\Resources\Loans\Schemas;

use App\Enums\UserStatus;
use App\Models\Item;
use App\Models\Loan;
use App\Policies\LoanPolicy;
use Closure;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class LoanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pengajuan Peminjaman')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('user_id')
                            ->label('Peminjam')
                            ->relationship('user', 'name', fn ($query) => $query->where('status', UserStatus::Active))
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->nim})")
                            ->searchable(['name', 'nim'])
                            ->preload()
                            ->required()
                            ->visible(fn () => LoanPolicy::manages(auth()->user())),
                        Select::make('item_id')
                            ->label('Barang')
                            ->relationship('item', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Item $record) => "{$record->code} · {$record->name} (tersedia {$record->availableQuantity()})")
                            ->searchable(['name', 'code'])
                            ->preload()
                            ->live()
                            ->required(),
                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->integer()
                            ->minValue(1)
                            ->default(1)
                            ->required()
                            ->rule(fn (Get $get, ?Loan $record) => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                                $item = Item::find($get('item_id'));
                                if ($item && $value > $item->availableQuantity($record?->id)) {
                                    $fail("Stok tersedia hanya {$item->availableQuantity($record?->id)} unit.");
                                }
                            }),
                        DateTimePicker::make('due_at')
                            ->label('Rencana Tanggal Kembali')
                            ->seconds(false)
                            ->minDate(now()->startOfDay())
                            ->required(),
                        Textarea::make('purpose')
                            ->label('Keperluan')
                            ->placeholder('Contoh: dokumentasi acara wisuda FT')
                            ->rows(3)
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
