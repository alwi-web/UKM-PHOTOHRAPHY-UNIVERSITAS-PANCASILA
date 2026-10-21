<?php

use App\Enums\LoanStatus;
use App\Enums\Role;
use App\Filament\Resources\Items\Pages\ListItems;
use App\Filament\Resources\Loans\Pages\CreateLoan;
use App\Filament\Resources\Loans\Pages\EditLoan;
use App\Models\Item;
use App\Models\Loan;
use Illuminate\Support\Carbon;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Carbon::setTestNow('2026-11-02 08:00');
    $this->kamera = Item::factory()->create(['name' => 'Canon EOS 90D', 'quantity' => 1]);
});

afterEach(fn () => Carbon::setTestNow());

/** Pesan kamera pada rentang tertentu. */
function bookKamera(string $from, string $to, LoanStatus $status = LoanStatus::Diajukan): Loan
{
    return Loan::factory()->withItem(test()->kamera)->create([
        'borrowed_at' => $from,
        'due_at' => $to,
        'status' => $status,
    ]);
}

describe('perhitungan bentrok', function () {
    it('menghitung pemesanan yang tanggalnya beririsan', function (string $from, string $to, int $available) {
        bookKamera('2026-11-10 08:00', '2026-11-12 17:00');

        expect($this->kamera->availableQuantityBetween(Carbon::parse($from), Carbon::parse($to)))->toBe($available);
    })->with([
        'beririsan di awal' => ['2026-11-09 08:00', '2026-11-10 12:00', 0],
        'beririsan di akhir' => ['2026-11-12 08:00', '2026-11-13 08:00', 0],
        'berada di dalam' => ['2026-11-11 08:00', '2026-11-11 17:00', 0],
        'mencakup seluruhnya' => ['2026-11-01 08:00', '2026-11-30 08:00', 0],
        'sebelumnya' => ['2026-11-05 08:00', '2026-11-07 08:00', 1],
        'sesudahnya' => ['2026-11-13 08:00', '2026-11-14 08:00', 1],
        'bersambung pas di jam kembali' => ['2026-11-12 17:00', '2026-11-13 17:00', 1],
        'bersambung pas di jam pinjam' => ['2026-11-09 08:00', '2026-11-10 08:00', 1],
    ]);

    it('mengabaikan peminjaman yang ditolak atau sudah dikembalikan', function (LoanStatus $status) {
        bookKamera('2026-11-10 08:00', '2026-11-12 17:00', $status);

        expect($this->kamera->availableQuantityBetween(Carbon::parse('2026-11-11'), Carbon::parse('2026-11-11 12:00')))->toBe(1);
    })->with([LoanStatus::Ditolak, LoanStatus::Dikembalikan]);

    it('barang yang terlambat dikembalikan tetap dianggap terpakai', function () {
        bookKamera('2026-10-28 08:00', '2026-10-30 17:00', LoanStatus::Dipinjam); // seharusnya kembali 30 Okt

        expect($this->kamera->availableQuantityBetween(Carbon::parse('2026-11-20'), Carbon::parse('2026-11-21')))->toBe(0);
    });

    it('memperhitungkan jumlah unit', function () {
        $tripod = Item::factory()->create(['quantity' => 3]);
        Loan::factory()->withItem($tripod, 2)->create(['borrowed_at' => '2026-11-10 08:00', 'due_at' => '2026-11-12 08:00']);

        expect($tripod->availableQuantityBetween(Carbon::parse('2026-11-11'), Carbon::parse('2026-11-11 12:00')))->toBe(1)
            ->and($tripod->conflictingLoans(Carbon::parse('2026-11-11'), Carbon::parse('2026-11-11 12:00')))->toHaveCount(1);
    });
});

describe('form pengajuan', function () {
    beforeEach(fn () => loginAs(Role::AnggotaLain));

    it('menolak pengajuan yang bentrok dengan pemesanan lain', function () {
        bookKamera('2026-11-10 08:00', '2026-11-12 17:00');

        livewire(CreateLoan::class)
            ->fillForm([
                'borrowed_at' => '2026-11-11 08:00',
                'due_at' => '2026-11-13 17:00',
                'loanItems' => [['item_id' => $this->kamera->id, 'quantity' => 1]],
            ])
            ->call('create')
            ->assertHasFormErrors(['loanItems.0.quantity' => 'Canon EOS 90D sudah habis dipesan pada tanggal tersebut.']);

        expect(Loan::count())->toBe(1);
    });

    it('menerima pengajuan di tanggal yang tidak bentrok', function () {
        bookKamera('2026-11-10 08:00', '2026-11-12 17:00');

        livewire(CreateLoan::class)
            ->fillForm([
                'borrowed_at' => '2026-11-12 17:00',
                'due_at' => '2026-11-14 17:00',
                'loanItems' => [['item_id' => $this->kamera->id, 'quantity' => 1]],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(Loan::count())->toBe(2);
    });

    it('tanggal kembali harus setelah tanggal pinjam', function () {
        livewire(CreateLoan::class)
            ->fillForm([
                'borrowed_at' => '2026-11-12 08:00',
                'due_at' => '2026-11-12 08:00',
                'loanItems' => [['item_id' => $this->kamera->id, 'quantity' => 1]],
            ])
            ->call('create')
            ->assertHasFormErrors(['due_at']);
    });

    it('mengubah pengajuan sendiri tidak dianggap bentrok dengan dirinya', function () {
        $loan = bookKamera('2026-11-10 08:00', '2026-11-12 17:00');
        $loan->update(['user_id' => auth()->id()]);

        livewire(EditLoan::class, ['record' => $loan->getRouteKey()])
            ->fillForm(['due_at' => '2026-11-13 17:00'])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($loan->fresh()->due_at->format('Y-m-d H:i'))->toBe('2026-11-13 17:00');
    });
});

it('daftar barang bisa difilter berdasarkan ketersediaan di tanggal tertentu', function () {
    loginAs(Role::AnggotaLain);
    $lensa = Item::factory()->create(['quantity' => 1]);
    bookKamera('2026-11-10 08:00', '2026-11-12 17:00');

    livewire(ListItems::class)
        ->filterTable('period', ['from' => '2026-11-11 08:00', 'until' => '2026-11-11 17:00'])
        ->assertCanSeeTableRecords([$lensa])
        ->assertCanNotSeeTableRecords([$this->kamera])
        ->filterTable('period', ['from' => '2026-11-20 08:00', 'until' => '2026-11-21 17:00'])
        ->assertCanSeeTableRecords([$lensa, $this->kamera]);
});
