<?php

namespace App\Filament\Widgets;

use App\Enums\ArtworkStatus;
use App\Enums\LoanStatus;
use App\Enums\RegistrationStatus;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Artwork;
use App\Models\Event;
use App\Models\Item;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Registration;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Ringkasan di dasbor, isinya menyesuaikan role user yang login. */
class DashboardStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();

        return match ($user->role) {
            Role::AdminUkm => [
                $this->members(),
                ...$this->inventory(),
                $this->curation(),
                $this->events(),
            ],
            Role::AdminInventaris => $this->inventory(),
            Role::Kurator => [$this->curation()],
            Role::Member => $this->myArtworks($user),
            Role::AnggotaLain => $this->myLoans($user),
        };
    }

    private function members(): Stat
    {
        return Stat::make('Anggota Aktif', User::where('status', UserStatus::Active)->count())
            ->description(Registration::where('status', RegistrationStatus::Baru)->count().' calon anggota baru mendaftar')
            ->color('warning');
    }

    /** @return array<Stat> */
    private function inventory(): array
    {
        $overdue = Loan::overdue()->count();

        return [
            Stat::make('Pengajuan Peminjaman', Loan::where('status', LoanStatus::Diajukan)->count())
                ->description('Menunggu diproses'),
            Stat::make('Unit Sedang Dipinjam', LoanItem::whereHas('loan', fn ($q) => $q->where('status', LoanStatus::Dipinjam))->sum('quantity'))
                ->description($overdue ? "{$overdue} peminjaman terlambat" : 'Tidak ada yang terlambat')
                ->color($overdue ? 'danger' : 'success'),
        ];
    }

    private function curation(): Stat
    {
        return Stat::make('Menunggu Kurasi', Artwork::where('status', ArtworkStatus::Pending)->count())
            ->description(Artwork::published()->count().' karya tampil di website');
    }

    private function events(): Stat
    {
        return Stat::make('Acara Mendatang', Event::upcoming()->count())
            ->description(($next = Event::upcoming()->first()) ? 'Terdekat: '.$next->title : 'Belum ada jadwal');
    }

    /** @return array<Stat> */
    private function myArtworks(User $user): array
    {
        return [
            Stat::make('Karya Saya', $user->artworks()->count())
                ->description($user->artworks()->where('status', ArtworkStatus::Pending)->count().' menunggu kurasi'),
            Stat::make('Lulus Kurasi', $user->artworks()->where('status', ArtworkStatus::Approved)->count())
                ->description($user->artworks()->published()->count().' tampil di website')
                ->color('success'),
        ];
    }

    /** @return array<Stat> */
    private function myLoans(User $user): array
    {
        $overdue = $user->loans()->overdue()->exists();

        return [
            Stat::make('Barang Tersedia', Item::all()->filter(fn (Item $item) => $item->availableQuantity() > 0)->count())
                ->description('jenis barang bisa dipinjam'),
            Stat::make('Pengajuan Saya', $user->loans()->where('status', LoanStatus::Diajukan)->count())
                ->description('Menunggu diproses admin inventaris'),
            Stat::make('Sedang Saya Pinjam', $user->loans()->where('status', LoanStatus::Dipinjam)->count())
                ->description($overdue ? 'Ada barang yang lewat batas kembali!' : 'Tidak ada yang terlambat')
                ->color($overdue ? 'danger' : 'success'),
        ];
    }
}
