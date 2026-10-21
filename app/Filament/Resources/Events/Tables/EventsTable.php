<?php

namespace App\Filament\Resources\Events\Tables;

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Enums\ParticipantStatus;
use App\Models\Event;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('start_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->withCount('participants'))
            ->columns([
                TextColumn::make('title')->label('Acara')->searchable()->sortable()->wrap()
                    ->description(fn (Event $record) => $record->location),
                TextColumn::make('type')->label('Jenis')->badge()->color('gray'),
                TextColumn::make('start_at')->label('Waktu')->dateTime('d M Y, H:i')->sortable(),
                TextColumn::make('participants_count')
                    ->label('Peserta')
                    ->formatStateUsing(fn (Event $record, $state) => $record->quota ? "{$state} / {$record->quota}" : (string) $state),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('status')->options(EventStatus::class),
                SelectFilter::make('type')->label('Jenis')->options(EventType::class),
            ])
            ->recordActions([
                Action::make('join')
                    ->label('Ikut')
                    ->icon(Heroicon::OutlinedUserPlus)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Event $record) => "Daftar ke {$record->title}?")
                    ->authorize('join')
                    ->action(function (Event $record) {
                        $record->participants()->attach(auth()->id(), ['status' => ParticipantStatus::Terdaftar]);
                        Notification::make()->title('Kamu terdaftar sebagai peserta.')->success()->send();
                    }),
                Action::make('leave')
                    ->label('Batal Ikut')
                    ->icon(Heroicon::OutlinedUserMinus)
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Event $record) => $record->start_at->isFuture()
                        && $record->participants()->whereKey(auth()->id())->exists())
                    ->action(function (Event $record) {
                        $record->participants()->detach(auth()->id());
                        Notification::make()->title('Pendaftaran dibatalkan.')->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
