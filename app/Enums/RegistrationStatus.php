<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RegistrationStatus: string implements HasColor, HasLabel
{
    case Baru = 'baru';
    case Dihubungi = 'dihubungi';
    case Diterima = 'diterima';
    case Ditolak = 'ditolak';

    public function getLabel(): string
    {
        return match ($this) {
            self::Baru => 'Baru',
            self::Dihubungi => 'Sudah Dihubungi',
            self::Diterima => 'Diterima',
            self::Ditolak => 'Ditolak',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Baru => 'warning',
            self::Dihubungi => 'info',
            self::Diterima => 'success',
            self::Ditolak => 'danger',
        };
    }
}
