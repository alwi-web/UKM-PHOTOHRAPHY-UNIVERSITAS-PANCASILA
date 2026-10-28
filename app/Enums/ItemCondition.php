<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ItemCondition: string implements HasColor, HasLabel
{
    case Baik = 'baik';
    case RusakRingan = 'rusak_ringan';
    case RusakBerat = 'rusak_berat';
    case Hilang = 'hilang';

    public function getLabel(): string
    {
        return match ($this) {
            self::Baik => 'Baik',
            self::RusakRingan => 'Rusak Ringan',
            self::RusakBerat => 'Rusak Berat',
            self::Hilang => 'Hilang',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Baik => 'success',
            self::RusakRingan => 'warning',
            self::RusakBerat, self::Hilang => 'danger',
        };
    }
}
