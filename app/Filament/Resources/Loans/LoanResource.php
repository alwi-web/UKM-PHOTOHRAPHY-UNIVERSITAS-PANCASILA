<?php

namespace App\Filament\Resources\Loans;

use App\Enums\LoanStatus;
use App\Filament\Resources\Loans\Pages\CreateLoan;
use App\Filament\Resources\Loans\Pages\EditLoan;
use App\Filament\Resources\Loans\Pages\ListLoans;
use App\Filament\Resources\Loans\Schemas\LoanForm;
use App\Filament\Resources\Loans\Tables\LoansTable;
use App\Models\Loan;
use App\Policies\LoanPolicy;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class LoanResource extends Resource
{
    protected static ?string $model = Loan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Inventaris';

    protected static ?string $modelLabel = 'peminjaman';

    protected static ?string $pluralModelLabel = 'Peminjaman';

    protected static ?int $navigationSort = 2;

    /** Anggota biasa hanya melihat peminjamannya sendiri. */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['item', 'user']);

        return LoanPolicy::manages(auth()->user()) ? $query : $query->whereBelongsTo(auth()->user());
    }

    public static function getNavigationBadge(): ?string
    {
        if (! LoanPolicy::manages(auth()->user())) {
            return null;
        }

        $pending = Loan::where('status', LoanStatus::Diajukan)->count();

        return $pending ? (string) $pending : null;
    }

    public static function form(Schema $schema): Schema
    {
        return LoanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoansTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoans::route('/'),
            'create' => CreateLoan::route('/create'),
            'edit' => EditLoan::route('/{record}/edit'),
        ];
    }
}
