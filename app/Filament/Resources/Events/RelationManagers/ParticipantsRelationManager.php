<?php

namespace App\Filament\Resources\Events\RelationManagers;

use App\Enums\ParticipantStatus;
use App\Enums\UserStatus;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ParticipantsRelationManager extends RelationManager
{
    protected static string $relationship = 'participants';

    protected static ?string $title = 'Anggota Event';

    protected static ?string $modelLabel = 'peserta';

    public function isReadOnly(): bool
    {
        return ! auth()->user()->isAdminUkm();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('status')
                    ->label('Kehadiran')
                    ->options(ParticipantStatus::class)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable(),
                TextColumn::make('npm')->label('NPM')->searchable(),
                TextColumn::make('role')->label('Keanggotaan')->badge(),
                TextColumn::make('pivot.created_at')->label('Mendaftar')->dateTime('d M Y H:i'),
                TextColumn::make('pivot.status')
                    ->label('Kehadiran')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ParticipantStatus::from($state)->getLabel())
                    ->color(fn ($state) => ParticipantStatus::from($state)->getColor()),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Kehadiran')
                    ->options(ParticipantStatus::class)
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['value'],
                        fn ($q, $v) => $q->wherePivot('status', $v),
                    )),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Tambah Peserta')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'npm'])
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->where('status', UserStatus::Active))
                    ->schema(fn (AttachAction $action) => [
                        $action->getRecordSelect(),
                        Select::make('status')
                            ->label('Kehadiran')
                            ->options(ParticipantStatus::class)
                            ->default(ParticipantStatus::Terdaftar)
                            ->required(),
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('Ubah Kehadiran'),
                DetachAction::make()->label('Keluarkan'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markPresent')
                        ->label('Tandai Hadir')
                        ->action(fn (Collection $records) => $records->each(
                            fn ($user) => $this->getOwnerRecord()->participants()->updateExistingPivot($user->id, ['status' => ParticipantStatus::Hadir])
                        ))
                        ->deselectRecordsAfterCompletion(),
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
