<?php

namespace App\Filament\Pages;

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Enums\ParticipantStatus;
use App\Models\Event;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class EventReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Daftar Kegiatan';

    protected static ?string $title = 'Laporan Daftar Kegiatan';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdminUkm() ?? false;
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([EmbeddedTable::make()]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Event::query()
                    ->withCount([
                        'participants',
                        'participants as attended_count' => fn (Builder $q) => $q->where('event_user.status', ParticipantStatus::Hadir),
                    ])
            )
            ->defaultSort('start_at', 'desc')
            ->columns([
                TextColumn::make('start_at')->label('Tanggal')->date('d M Y')->sortable(),
                TextColumn::make('title')->label('Kegiatan')->searchable()->wrap(),
                TextColumn::make('type')->label('Jenis')->badge()->color('gray'),
                TextColumn::make('location')->label('Lokasi')->toggleable(),
                TextColumn::make('participants_count')->label('Peserta')->sortable(),
                TextColumn::make('attended_count')->label('Hadir')->sortable(),
                TextColumn::make('attendance_rate')
                    ->label('Kehadiran')
                    ->state(fn (Event $record) => $record->participants_count
                        ? round($record->attended_count / $record->participants_count * 100).'%'
                        : '-'),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                Filter::make('period')
                    ->schema([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('start_at', '>=', $d))
                        ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('start_at', '<=', $d))),
                SelectFilter::make('type')->label('Jenis')->options(EventType::class),
                SelectFilter::make('status')->options(EventStatus::class),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Unduh CSV')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->action(fn () => $this->exportCsv()),
        ];
    }

    public function exportCsv(): StreamedResponse
    {
        $events = $this->getFilteredSortedTableQuery()->get();

        return response()->streamDownload(function () use ($events) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tanggal', 'Kegiatan', 'Jenis', 'Lokasi', 'Peserta', 'Hadir', 'Status']);
            foreach ($events as $e) {
                fputcsv($out, [
                    $e->start_at->format('Y-m-d H:i'),
                    $e->title,
                    $e->type->getLabel(),
                    $e->location,
                    $e->participants_count,
                    $e->attended_count,
                    $e->status->getLabel(),
                ]);
            }
            fclose($out);
        }, 'laporan-kegiatan-'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv']);
    }
}
