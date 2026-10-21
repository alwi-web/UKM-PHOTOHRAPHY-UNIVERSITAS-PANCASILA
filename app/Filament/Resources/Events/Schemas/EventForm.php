<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Enums\EventStatus;
use App\Enums\EventType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Informasi Acara')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Nama Acara')
                            ->required()
                            ->maxLength(150)
                            ->columnSpanFull(),
                        Select::make('type')
                            ->label('Jenis')
                            ->options(EventType::class)
                            ->required(),
                        TextInput::make('location')
                            ->label('Lokasi')
                            ->required()
                            ->maxLength(255),
                        DateTimePicker::make('start_at')
                            ->label('Mulai')
                            ->seconds(false)
                            ->required(),
                        DateTimePicker::make('end_at')
                            ->label('Selesai')
                            ->seconds(false)
                            ->after('start_at'),
                        RichEditor::make('description')
                            ->label('Deskripsi')
                            ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link'])
                            ->columnSpanFull(),
                    ]),
                Section::make('Pengaturan')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('status')
                            ->options(EventStatus::class)
                            ->default(EventStatus::Draft)
                            ->required(),
                        TextInput::make('quota')
                            ->label('Kuota Peserta')
                            ->integer()
                            ->minValue(1)
                            ->helperText('Kosongkan jika tanpa batas.'),
                        Toggle::make('registration_open')
                            ->label('Pendaftaran dibuka')
                            ->default(true),
                        FileUpload::make('poster')
                            ->image()
                            ->disk('public')
                            ->directory('events')
                            ->maxSize(3072),
                    ]),
            ]);
    }
}
