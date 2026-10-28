<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EventType: string implements HasLabel
{
    case Kelas = 'kelas';
    case Hunting = 'hunting';
    case Pameran = 'pameran';
    case Workshop = 'workshop';
    case Lomba = 'lomba';
    case Lainnya = 'lainnya';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
