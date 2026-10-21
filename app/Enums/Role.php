<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Role: string implements HasColor, HasLabel
{
    case AdminUkm = 'admin_ukm';
    case AdminInventaris = 'admin_inventaris';
    case Kurator = 'kurator';
    case Member = 'member';
    case AnggotaLain = 'anggota_lain';

    public function getLabel(): string
    {
        return match ($this) {
            self::AdminUkm => 'Admin UKM',
            self::AdminInventaris => 'Admin Inventaris',
            self::Kurator => 'Kurator',
            self::Member => 'Member UKM Fotografi',
            self::AnggotaLain => 'Anggota UKM Lain',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::AdminUkm => 'danger',
            self::AdminInventaris => 'warning',
            self::Kurator => 'info',
            self::Member => 'success',
            self::AnggotaLain => 'gray',
        };
    }

    /** Role yang boleh mengunggah karya ke galeri. */
    public function canUploadArtwork(): bool
    {
        return in_array($this, [self::AdminUkm, self::Kurator, self::Member]);
    }
}
