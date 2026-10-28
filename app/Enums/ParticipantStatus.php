<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ParticipantStatus: string implements HasColor, HasLabel
{
    case Terdaftar = 'terdaftar';
    case Hadir = 'hadir';
    case TidakHadir = 'tidak_hadir';

    public function getLabel(): string
    {
        return match ($this) {
            self::Terdaftar => 'Terdaftar',
            self::Hadir => 'Hadir',
            self::TidakHadir => 'Tidak Hadir',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Terdaftar => 'gray',
            self::Hadir => 'success',
            self::TidakHadir => 'danger',
        };
    }
}
