<?php

namespace App\Filament\Resources\Items\Schemas;

use App\Enums\ItemCondition;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Barang')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('code')
                            ->label('Kode Inventaris')
                            ->placeholder('CAM-001')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(30),
                        TextInput::make('name')
                            ->label('Nama Barang')
                            ->required()
                            ->maxLength(255),
                        Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'Kamera' => 'Kamera',
                                'Lensa' => 'Lensa',
                                'Lighting' => 'Lighting',
                                'Tripod' => 'Tripod',
                                'Aksesoris' => 'Aksesoris',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->required(),
                        TextInput::make('brand')
                            ->label('Merek / Tipe')
                            ->maxLength(255),
                        TextInput::make('quantity')
                            ->label('Jumlah Unit')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->default(1),
                        Select::make('condition')
                            ->label('Kondisi')
                            ->options(ItemCondition::class)
                            ->default(ItemCondition::Baik)
                            ->required(),
                        Textarea::make('description')
                            ->label('Keterangan')
                            ->rows(3)
                            ->columnSpanFull(),
                        FileUpload::make('photo')
                            ->label('Foto Barang')
                            ->image()
                            ->disk('public')
                            ->directory('items')
                            ->maxSize(2048)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
