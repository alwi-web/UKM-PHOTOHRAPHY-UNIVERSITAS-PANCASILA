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

    /*
     * Ringkasan hak akses:
     * - Admin UKM        : semua fitur.
     * - Admin Inventaris : daftar barang & peminjaman.
     * - Kurator          : mengkurasi foto untuk website.
     * - Member UKM       : mengunggah foto & melihat hasil kurasi.
     * - Anggota UKM lain : meminjam barang.
     */

    /** Mengelola data barang dan memproses semua peminjaman. */
    public function canManageInventory(): bool
    {
        return in_array($this, [self::AdminUkm, self::AdminInventaris]);
    }

    /** Melihat barang yang tersedia dan mengajukan peminjaman. */
    public function canBorrow(): bool
    {
        return in_array($this, [self::AdminUkm, self::AdminInventaris, self::AnggotaLain]);
    }

    /** Mengunggah karya ke galeri. */
    public function canUploadArtwork(): bool
    {
        return in_array($this, [self::AdminUkm, self::Member]);
    }

    /** Mengkurasi karya dan memilih yang tampil di website. */
    public function canCurate(): bool
    {
        return in_array($this, [self::AdminUkm, self::Kurator]);
    }
}
