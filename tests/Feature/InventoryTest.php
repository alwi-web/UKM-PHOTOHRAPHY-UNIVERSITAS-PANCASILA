<?php

use App\Enums\ItemCondition;
use App\Enums\LoanStatus;
use App\Enums\Role;
use App\Filament\Resources\Items\Pages\CreateItem;
use App\Filament\Resources\Items\Pages\ListItems;
use App\Filament\Resources\Loans\Pages\CreateLoan;
use App\Filament\Resources\Loans\Pages\ListLoans;
use App\Models\Item;
use App\Models\Loan;
use App\Models\User;
use Filament\Actions\Testing\TestAction;

use function Pest\Livewire\livewire;

describe('stok barang', function () {
    it('menghitung unit tersedia dari peminjaman aktif', function () {
        $item = Item::factory()->create(['quantity' => 3]);
        Loan::factory()->borrowed()->for($item)->create(['quantity' => 2]);
        Loan::factory()->for($item)->create(['quantity' => 1]); // masih diajukan, belum mengurangi stok

        expect($item->borrowedQuantity())->toBe(2)
            ->and($item->availableQuantity())->toBe(1);
    });

    it('menandai peminjaman yang lewat batas kembali', function () {
        $late = Loan::factory()->borrowed()->create(['due_at' => now()->subDay()]);
        Loan::factory()->borrowed()->create(['due_at' => now()->addDay()]);

        expect($late->isOverdue())->toBeTrue()
            ->and(Loan::overdue()->pluck('id')->all())->toBe([$late->id]);
    });
});

describe('alur peminjaman', function () {
    it('member mengajukan peminjaman atas namanya sendiri', function () {
        $member = loginAs(Role::Member);
        $other = User::factory()->create();
        $item = Item::factory()->create(['quantity' => 2]);

        livewire(CreateLoan::class)
            ->fillForm([
                'user_id' => $other->id, // diabaikan untuk member
                'item_id' => $item->id,
                'quantity' => 1,
                'due_at' => now()->addDays(2),
                'purpose' => 'Dokumentasi acara',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $loan = Loan::sole();
        expect($loan->user_id)->toBe($member->id)
            ->and($loan->status)->toBe(LoanStatus::Diajukan);
    });

    it('menolak jumlah pinjam melebihi stok tersedia', function () {
        loginAs(Role::Member);
        $item = Item::factory()->create(['quantity' => 1]);

        livewire(CreateLoan::class)
            ->fillForm([
                'item_id' => $item->id,
                'quantity' => 2,
                'due_at' => now()->addDays(2),
                'purpose' => 'Hunting',
            ])
            ->call('create')
            ->assertHasFormErrors(['quantity']);
    });

    it('member hanya melihat peminjamannya sendiri', function () {
        $member = loginAs(Role::Member);
        $mine = Loan::factory()->for($member)->create();
        $others = Loan::factory()->create();

        livewire(ListLoans::class)
            ->assertCanSeeTableRecords([$mine])
            ->assertCanNotSeeTableRecords([$others]);
    });

    it('admin inventaris menyerahkan barang dan mencatat kondisi awal', function () {
        $admin = loginAs(Role::AdminInventaris);
        $loan = Loan::factory()->create();

        livewire(ListLoans::class)
            ->callAction(TestAction::make('handOver')->table($loan), data: ['condition_before' => ItemCondition::Baik->value])
            ->assertHasNoFormErrors();

        $loan->refresh();
        expect($loan->status)->toBe(LoanStatus::Dipinjam)
            ->and($loan->borrowed_at)->not->toBeNull()
            ->and($loan->condition_before)->toBe(ItemCondition::Baik)
            ->and($loan->handled_by)->toBe($admin->id);
    });

    it('pengembalian mencatat kondisi akhir dan memperbarui kondisi barang', function () {
        loginAs(Role::AdminInventaris);
        $loan = Loan::factory()->borrowed()->create();

        livewire(ListLoans::class)
            ->callAction(TestAction::make('return')->table($loan), data: [
                'condition_after' => ItemCondition::RusakRingan->value,
                'notes' => 'Lensa tergores',
            ]);

        $loan->refresh();
        expect($loan->status)->toBe(LoanStatus::Dikembalikan)
            ->and($loan->returned_at)->not->toBeNull()
            ->and($loan->condition_after)->toBe(ItemCondition::RusakRingan)
            ->and($loan->item->condition)->toBe(ItemCondition::RusakRingan);
    });

    it('member tidak bisa memproses peminjaman', function () {
        loginAs(Role::Member);
        $loan = Loan::factory()->for(auth()->user())->create();

        livewire(ListLoans::class)->assertActionHidden(TestAction::make('handOver')->table($loan));
    });

    it('tidak bisa menyerahkan barang jika stok habis', function () {
        $admin = User::factory()->role(Role::AdminInventaris)->create();
        $item = Item::factory()->create(['quantity' => 1]);
        Loan::factory()->borrowed()->for($item)->create();
        $loan = Loan::factory()->for($item)->create();

        expect(fn () => $loan->handOver($admin, ItemCondition::Baik))->toThrow(DomainException::class);
    });
});

describe('data barang', function () {
    it('admin inventaris bisa menambah barang', function () {
        loginAs(Role::AdminInventaris);

        livewire(CreateItem::class)
            ->fillForm([
                'code' => 'CAM-099',
                'name' => 'Nikon Z50',
                'category' => 'Kamera',
                'quantity' => 1,
                'condition' => ItemCondition::Baik->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(Item::where('code', 'CAM-099')->exists())->toBeTrue();
    });

    it('member bisa melihat daftar barang tapi tidak bisa menambah', function () {
        loginAs(Role::Member);
        $item = Item::factory()->create();

        livewire(ListItems::class)->assertCanSeeTableRecords([$item]);
        $this->get(CreateItem::getUrl())->assertForbidden();
    });
});
