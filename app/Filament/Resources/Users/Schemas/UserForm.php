<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Role;
use App\Enums\UserStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Biodata')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label('Nama Lengkap')->required()->maxLength(255),
                        TextInput::make('nim')->label('NIM')->unique(ignoreRecord: true)->maxLength(20),
                        TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                        TextInput::make('phone')->label('No. WhatsApp')->tel()->maxLength(20),
                        TextInput::make('faculty')->label('Fakultas')->maxLength(255),
                        TextInput::make('batch_year')->label('Angkatan')->integer()->minValue(2000)->maxValue(2100),
                        TextInput::make('origin_ukm')
                            ->label('Asal UKM')
                            ->visible(fn (Get $get) => in_array($get('role'), [Role::AnggotaLain, Role::AnggotaLain->value], true))
                            ->columnSpanFull(),
                        Textarea::make('join_reason')->label('Alasan Bergabung')->rows(3)->columnSpanFull(),
                    ]),
                Section::make('Akun')
                    ->columnSpan(1)
                    ->schema([
                        FileUpload::make('photo')
                            ->label('Foto')
                            ->image()
                            ->avatar()
                            ->disk('public')
                            ->directory('users')
                            ->maxSize(2048),
                        Select::make('role')
                            ->options(Role::class)
                            ->default(Role::Member)
                            ->live()
                            ->required(),
                        Select::make('status')
                            ->options(UserStatus::class)
                            ->default(UserStatus::Active)
                            ->required(),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->required(fn (string $operation) => $operation === 'create')
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->helperText(fn (string $operation) => $operation === 'edit' ? 'Kosongkan jika tidak diubah.' : null),
                    ]),
            ]);
    }
}
