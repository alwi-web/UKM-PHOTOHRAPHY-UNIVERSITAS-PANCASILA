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
                            ->label('Caption')
                            ->helperText('Cerita di balik foto: lokasi, momen, atau pesan yang ingin disampaikan.')
                            ->rows(3)
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
                Section::make('Segitiga Exposure')
                    ->description('Pengaturan kamera saat memotret.')
                    ->columnSpan(2)
                    ->columns(3)
                    ->schema([
                        TextInput::make('aperture')
                            ->label('Aperture')
                            ->placeholder('f/1.8')
                            ->required()
                            ->maxLength(20),
                        TextInput::make('shutter_speed')
                            ->label('Shutter Speed')
                            ->placeholder('1/250')
                            ->suffix('detik')
                            ->required()
                            ->maxLength(20),
                        TextInput::make('iso')
                            ->label('ISO')
                            ->placeholder('100')
                            ->integer()
                            ->minValue(25)
                            ->maxValue(409600)
                            ->required(),
                        Textarea::make('exposure_notes')
                            ->label('Deskripsi Penggunaan Segitiga Exposure')
                            ->helperText('Jelaskan alasan memilih aperture, shutter speed, dan ISO tersebut.')
                            ->placeholder('Contoh: aperture lebar f/1.8 untuk bokeh, shutter 1/250 agar subjek tidak blur, ISO 100 karena cahaya matahari cukup.')
                            ->rows(3)
                            ->required()
                            ->columnSpanFull(),
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
