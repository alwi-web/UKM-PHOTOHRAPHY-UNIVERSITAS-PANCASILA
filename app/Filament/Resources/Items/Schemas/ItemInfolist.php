<?php

namespace App\Filament\Resources\Items\Schemas;

use App\Models\Item;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ItemInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        ImageEntry::make('photo')->label('Foto')->disk('public')->placeholder('Belum ada foto'),
                        TextEntry::make('code')->label('Kode'),
                        TextEntry::make('name')->label('Nama Barang'),
                        TextEntry::make('category')->label('Kategori'),
                        TextEntry::make('brand')->label('Merek / Tipe')->placeholder('-'),
                        TextEntry::make('condition')->label('Kondisi')->badge(),
                        TextEntry::make('quantity')->label('Jumlah Unit'),
                        TextEntry::make('borrowed')
                            ->label('Sedang Dipinjam')
                            ->state(fn (Item $record) => $record->borrowedQuantity()),
                        TextEntry::make('available')
                            ->label('Tersedia')
                            ->state(fn (Item $record) => $record->availableQuantity())
                            ->badge()
                            ->color(fn (int $state) => $state > 0 ? 'success' : 'danger'),
                        TextEntry::make('description')->label('Keterangan')->placeholder('-')->columnSpanFull(),
                    ]),
            ]);
    }
}
