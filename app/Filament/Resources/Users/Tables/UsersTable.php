<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
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
use Illuminate\Database\Eloquent\Collection;

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
                    ->description(fn (User $record) => $record->nim),
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
                Action::make('approve')
                    ->label('Setujui')
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (User $record) => $record->status === UserStatus::Pending)
                    ->action(function (User $record) {
                        $record->update(['status' => UserStatus::Active]);
                        Notification::make()->title("{$record->name} sekarang anggota aktif.")->success()->send();
                    }),
                Action::make('reject')
                    ->label('Tolak')
                    ->icon(Heroicon::OutlinedXMark)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (User $record) => $record->status === UserStatus::Pending)
                    ->action(fn (User $record) => $record->update(['status' => UserStatus::Rejected])),
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
                    BulkAction::make('approveSelected')
                        ->label('Setujui terpilih')
                        ->icon(Heroicon::OutlinedCheck)
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['status' => UserStatus::Active]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
