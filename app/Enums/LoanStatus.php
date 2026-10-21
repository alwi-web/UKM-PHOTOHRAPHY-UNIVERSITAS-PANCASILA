<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LoanStatus: string implements HasColor, HasLabel
{
    case Diajukan = 'diajukan';
    case Dipinjam = 'dipinjam';
    case Dikembalikan = 'dikembalikan';
    case Ditolak = 'ditolak';

    public function getLabel(): string
    {
        return match ($this) {
            self::Diajukan => 'Diajukan',
            self::Dipinjam => 'Sedang Dipinjam',
            self::Dikembalikan => 'Dikembalikan',
            self::Ditolak => 'Ditolak',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Diajukan => 'warning',
            self::Dipinjam => 'info',
            self::Dikembalikan => 'success',
            self::Ditolak => 'danger',
        };
    }
}
