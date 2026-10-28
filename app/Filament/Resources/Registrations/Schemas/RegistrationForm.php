<?php

namespace App\Filament\Resources\Registrations\Schemas;

use App\Enums\RegistrationStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pendaftar')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')->label('Nama')->required()->maxLength(255),
                        TextInput::make('npm')->label('NPM')->required()->unique(ignoreRecord: true)->maxLength(20),
                        Select::make('faculty')
                            ->label('Fakultas')
                            ->options(array_combine(config('ukm.faculties'), config('ukm.faculties')))
                            ->required(),
                        TextInput::make('phone')->label('No. WhatsApp')->tel()->required()->maxLength(20),
                        Select::make('status')
                            ->options(RegistrationStatus::class)
                            ->disableOptionWhen(fn (string $value) => $value === RegistrationStatus::Diterima->value)
                            ->helperText('Gunakan tombol "Terima & buat akun" di daftar pendaftar untuk menerima.')
                            ->required(),
                        Textarea::make('notes')->label('Catatan')->rows(3)->columnSpanFull(),
                    ]),
            ]);
    }
}
