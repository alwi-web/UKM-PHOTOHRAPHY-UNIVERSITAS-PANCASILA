<?php

namespace App\Filament\Resources\Registrations\Tables;

use App\Enums\RegistrationStatus;
use App\Models\Registration;
use DomainException;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;

class RegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('npm')->label('NPM')->searchable()->copyable(),
                TextColumn::make('faculty')->label('Fakultas')->sortable(),
                TextColumn::make('phone')
                    ->label('WhatsApp')
                    ->icon(Heroicon::OutlinedChatBubbleLeftEllipsis)
                    ->color('success')
                    ->url(fn (Registration $record) => $record->whatsappUrl(), shouldOpenInNewTab: true),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->label('Mendaftar')->since()->sortable()
                    ->tooltip(fn (Registration $record) => $record->created_at->translatedFormat('d M Y H:i')),
            ])
            ->filters([
                SelectFilter::make('status')->options(RegistrationStatus::class),
                SelectFilter::make('faculty')
                    ->label('Fakultas')
                    ->options(array_combine(config('ukm.faculties'), config('ukm.faculties'))),
            ])
            ->recordActions([
                Action::make('accept')
                    ->label('Terima & buat akun')
                    ->icon(Heroicon::OutlinedUserPlus)
                    ->color('success')
                    ->visible(fn (Registration $record) => in_array($record->status, [RegistrationStatus::Baru, RegistrationStatus::Dihubungi], true))
                    ->modalHeading(fn (Registration $record) => "Terima {$record->name} sebagai Member")
                    ->modalDescription('Akun Member UKM Fotografi dibuat dari data pendaftar. Sampaikan email dan password ini ke yang bersangkutan.')
                    ->schema([
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->rule(Rule::unique('users', 'email'))
                            ->validationMessages(['unique' => 'Email ini sudah dipakai akun lain.']),
                        TextInput::make('password')->password()->revealable()->required()->minLength(8),
                    ])
                    ->action(function (Registration $record, array $data) {
                        try {
                            $record->accept(auth()->user(), $data['email'], $data['password']);
                            Notification::make()->title("Akun member untuk {$record->name} sudah dibuat.")->success()->send();
                        } catch (DomainException $e) {
                            Notification::make()->title($e->getMessage())->danger()->send();
                        }
                    }),
                ActionGroup::make([
                    Action::make('contacted')
                        ->label('Sudah Dihubungi')
                        ->icon(Heroicon::OutlinedPhone)
                        ->color('info')
                        ->visible(fn (Registration $record) => $record->status === RegistrationStatus::Baru)
                        ->action(fn (Registration $record) => $record->markContacted(auth()->user())),
                    Action::make('reject')
                        ->label('Tolak')
                        ->icon(Heroicon::OutlinedXMark)
                        ->color('danger')
                        ->visible(fn (Registration $record) => $record->status !== RegistrationStatus::Diterima)
                        ->schema([Textarea::make('notes')->label('Alasan')])
                        ->action(fn (Registration $record, array $data) => $record->reject(auth()->user(), $data['notes'] ?? null)),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
