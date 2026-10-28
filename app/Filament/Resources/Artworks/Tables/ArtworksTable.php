<?php

namespace App\Filament\Resources\Artworks\Tables;

use App\Enums\ArtworkStatus;
use App\Models\Artwork;
use App\Policies\ArtworkPolicy;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ArtworksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            // kurator cukup klik baris untuk membuka form kurasi
            ->recordAction(fn () => ArtworkPolicy::curates(auth()->user()) ? 'curate' : null)
            ->columns([
                ImageColumn::make('image')->label('')->disk('public')->imageHeight(64),
                TextColumn::make('title')->label('Judul')->searchable()->sortable()->limit(40)
                    ->tooltip(fn (Artwork $record) => $record->caption),
                TextColumn::make('user.name')->label('Fotografer')->searchable()
                    ->visible(fn () => ArtworkPolicy::curates(auth()->user())),
                TextColumn::make('category.name')->label('Kategori')->badge()->color('gray'),
                TextColumn::make('exposure')
                    ->label('Exposure')
                    ->state(fn (Artwork $record) => $record->exposureSummary())
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('status')->badge(),
                IconColumn::make('is_featured')->label('Di Website')->boolean(),
                TextColumn::make('nominated_week')->label('Nominasi Mingguan')
                    ->state(fn (Artwork $record) => $record->isNominatedThisWeek() ? 'Minggu ini' : null)
                    ->badge()->color('primary')->icon(Heroicon::Star)->placeholder('-'),
                TextColumn::make('views')->label('Dilihat')->numeric()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('curator_note')->label('Catatan Kurator')->placeholder('-')->limit(50)
                    ->tooltip(fn (Artwork $record) => $record->curator_note)
                    ->visible(fn () => ! ArtworkPolicy::curates(auth()->user()) || auth()->user()->isAdminUkm()),
                TextColumn::make('curator.name')->label('Kurator')->placeholder('-')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Diunggah')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(ArtworkStatus::class),
                SelectFilter::make('category')->label('Kategori')->relationship('category', 'name'),
                TernaryFilter::make('is_featured')->label('Ditampilkan di website'),
            ])
            ->recordActions([
                Action::make('curate')
                    ->label('Kurasi')
                    ->icon(Heroicon::OutlinedSparkles)
                    ->color('primary')
                    ->authorize('curate')
                    ->modalHeading(fn (Artwork $record) => "Kurasi: {$record->title}")
                    ->modalDescription(fn (Artwork $record) => "Oleh {$record->user->name}")
                    ->modalContent(fn (Artwork $record) => view('filament.artworks.curate-preview', ['artwork' => $record]))
                    ->fillForm(fn (Artwork $record) => [
                        'status' => $record->status === ArtworkStatus::Pending ? ArtworkStatus::Approved : $record->status,
                        'is_featured' => $record->is_featured,
                        'curator_note' => $record->curator_note,
                    ])
                    ->schema([
                        Radio::make('status')
                            ->label('Hasil Kurasi')
                            ->options([
                                ArtworkStatus::Approved->value => 'Lolos kurasi',
                                ArtworkStatus::Rejected->value => 'Tolak',
                            ])
                            ->live()
                            ->required(),
                        Toggle::make('is_featured')
                            ->label('Tampilkan di galeri website')
                            ->visible(fn (Get $get) => in_array($get('status'), [ArtworkStatus::Approved, ArtworkStatus::Approved->value], true)),
                        Textarea::make('curator_note')->label('Catatan untuk fotografer')->rows(3),
                    ])
                    ->action(function (Artwork $record, array $data) {
                        $status = $data['status'] instanceof ArtworkStatus ? $data['status'] : ArtworkStatus::from($data['status']);
                        $record->curate(auth()->user(), $status, (bool) ($data['is_featured'] ?? false), $data['curator_note'] ?? null);
                        Notification::make()->title('Hasil kurasi disimpan.')->success()->send();
                    }),
                Action::make('nominate')
                    ->label('Nominasikan')
                    ->tooltip('Jadikan nominasi foto mingguan di galeri website')
                    ->icon(Heroicon::OutlinedStar)
                    ->color('gray')
                    ->authorize('curate')
                    ->visible(fn (Artwork $record) => $record->status === ArtworkStatus::Approved && ! $record->isNominatedThisWeek())
                    ->requiresConfirmation()
                    ->modalHeading(fn (Artwork $record) => "Nominasikan \"{$record->title}\"?")
                    ->modalDescription('Karya tampil di carousel Nominasi Foto Mingguan pada galeri website selama minggu ini.')
                    ->modalSubmitActionLabel('Nominasikan')
                    ->action(function (Artwork $record) {
                        $record->nominate();
                        Notification::make()->title('Karya dinominasikan sebagai foto minggu ini.')->success()->send();
                    }),
                Action::make('cancelNomination')
                    ->label('Batalkan nominasi')
                    ->icon(Heroicon::Star)
                    ->color('warning')
                    ->authorize('curate')
                    ->visible(fn (Artwork $record) => $record->isNominatedThisWeek())
                    ->action(function (Artwork $record) {
                        $record->cancelNomination();
                        Notification::make()->title('Nominasi dibatalkan.')->success()->send();
                    }),
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('feature')
                        ->label('Tampilkan di website')
                        ->icon(Heroicon::OutlinedEye)
                        ->visible(fn () => ArtworkPolicy::curates(auth()->user()))
                        ->action(fn (Collection $records) => $records->each(
                            fn (Artwork $a) => $a->curate(auth()->user(), ArtworkStatus::Approved, true, $a->curator_note)
                        ))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('unfeature')
                        ->label('Sembunyikan dari website')
                        ->icon(Heroicon::OutlinedEyeSlash)
                        ->visible(fn () => ArtworkPolicy::curates(auth()->user()))
                        ->action(fn (Collection $records) => $records->each->update(['is_featured' => false]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
