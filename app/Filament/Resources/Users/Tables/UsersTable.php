<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('photo')->label('')->disk('public')->circular()
                    ->defaultImageUrl(fn (User $record) => 'https://ui-avatars.com/api/?background=f5b301&color=111&name='.urlencode($record->name)),
                TextColumn::make('name')->label('Nama')->searchable()->sortable()
                    ->description(fn (User $record) => $record->npm),
                TextColumn::make('email')->searchable()->toggleable(),
                TextColumn::make('faculty')->label('Fakultas')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('role')->badge()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('created_at')->label('Terdaftar')->date('d M Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')->options(Role::class),
                SelectFilter::make('status')->options(UserStatus::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('makeCurator')
                        ->label('Tunjuk sebagai Kurator')
                        ->icon(Heroicon::OutlinedSparkles)
                        ->requiresConfirmation()
                        ->visible(fn (User $record) => $record->status === UserStatus::Active && $record->role !== Role::Kurator && ! $record->isAdminUkm())
                        ->action(function (User $record) {
                            $record->update(['role' => Role::Kurator]);
                            Notification::make()->title("{$record->name} ditunjuk sebagai kurator.")->success()->send();
                        }),
                    Action::make('removeCurator')
                        ->label('Cabut Kurator')
                        ->icon(Heroicon::OutlinedNoSymbol)
                        ->requiresConfirmation()
                        ->visible(fn (User $record) => $record->role === Role::Kurator)
                        ->action(fn (User $record) => $record->update(['role' => Role::Member])),
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
