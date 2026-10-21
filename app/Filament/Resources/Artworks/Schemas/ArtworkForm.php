<?php

namespace App\Filament\Resources\Artworks\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ArtworkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Detail Karya')
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(150),
                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->preload()
                            ->required(),
                        Textarea::make('caption')
                            ->label('Caption / Deskripsi')
                            ->helperText('Ceritakan konteks foto, lokasi, atau setting kamera.')
                            ->rows(4)
                            ->maxLength(2000),
                        TextInput::make('instagram_url')
                            ->label('Link Instagram')
                            ->url()
                            ->prefixIcon('heroicon-o-link')
                            ->placeholder('https://www.instagram.com/p/...'),
                        TextInput::make('tiktok_url')
                            ->label('Link TikTok')
                            ->url()
                            ->prefixIcon('heroicon-o-link')
                            ->placeholder('https://www.tiktok.com/@.../video/...'),
                    ]),
                Section::make('Foto')
                    ->columnSpan(1)
                    ->schema([
                        FileUpload::make('image')
                            ->label('File Foto')
                            ->image()
                            ->disk('public')
                            ->directory('artworks')
                            ->maxSize(5120)
                            ->helperText('JPG/PNG, maksimal 5 MB.')
                            ->required(),
                    ]),
            ]);
    }
}
