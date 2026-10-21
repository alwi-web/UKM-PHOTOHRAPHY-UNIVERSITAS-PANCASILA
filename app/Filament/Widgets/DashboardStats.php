<?php

namespace App\Filament\Widgets;

use App\Enums\ArtworkStatus;
use App\Enums\LoanStatus;
use App\Enums\UserStatus;
use App\Models\Artwork;
use App\Models\Event;
use App\Models\Loan;
use App\Models\User;
use App\Policies\ArtworkPolicy;
use App\Policies\LoanPolicy;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Ringkasan di dashboard, isinya menyesuaikan role user yang login. */
class DashboardStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();
        $stats = [];

        if ($user->isAdminUkm()) {
            $stats[] = Stat::make('Anggota Aktif', User::where('status', UserStatus::Active)->count())
                ->description(User::where('status', UserStatus::Pending)->count().' pendaftar menunggu verifikasi')
                ->color('warning');
        }

        if (LoanPolicy::manages($user)) {
            $stats[] = Stat::make('Barang Sedang Dipinjam', Loan::where('status', LoanStatus::Dipinjam)->sum('quantity'))
                ->description(Loan::overdue()->count().' peminjaman terlambat')
                ->color(Loan::overdue()->exists() ? 'danger' : 'success');
        } else {
            $stats[] = Stat::make('Peminjaman Saya', $user->loans()->where('status', LoanStatus::Dipinjam)->count())
                ->description($user->loans()->overdue()->exists() ? 'Ada barang yang lewat batas kembali!' : 'Tidak ada yang terlambat')
                ->color($user->loans()->overdue()->exists() ? 'danger' : 'success');
        }

        if (ArtworkPolicy::curates($user)) {
            $stats[] = Stat::make('Menunggu Kurasi', Artwork::where('status', ArtworkStatus::Pending)->count())
                ->description(Artwork::published()->count().' karya tampil di website');
        } elseif ($user->role->canUploadArtwork()) {
            $stats[] = Stat::make('Karya Saya', $user->artworks()->count())
                ->description($user->artworks()->published()->count().' tampil di website');
        }

        $stats[] = Stat::make('Acara Mendatang', Event::upcoming()->count())
            ->description(($next = Event::upcoming()->first()) ? 'Terdekat: '.$next->title : 'Belum ada jadwal');

        return $stats;
    }
}
