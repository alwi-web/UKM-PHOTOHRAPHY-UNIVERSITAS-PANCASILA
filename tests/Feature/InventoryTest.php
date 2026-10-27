<?php

use App\Enums\ItemCondition;
use App\Enums\LoanStatus;
use App\Enums\Role;
use App\Filament\Resources\Items\Pages\CreateItem;
use App\Filament\Resources\Items\Pages\ListItems;
use App\Filament\Resources\Loans\Pages\CreateLoan;
use App\Filament\Resources\Loans\Pages\EditLoan;
use App\Filament\Resources\Loans\Pages\ListLoans;
use App\Models\Item;
use App\Models\Loan;
use App\Models\User;
use Filament\Actions\Testing\TestAction;

use function Pest\Livewire\livewire;

describe('stok barang', function () {
    it('menghitung unit tersedia dari barang yang sedang dipinjam', function () {
        $item = Item::factory()->create(['quantity' => 3]);
        Loan::factory()->borrowed()->withItem($item, 2)->create();
        Loan::factory()->withItem($item, 1)->create(); // masih diajukan, belum mengurangi stok

        expect($item->borrowedQuantity())->toBe(2)
            ->and($item->availableQuantity())->toBe(1);
    });

    it('menandai peminjaman yang lewat batas kembali', function () {
        $late = Loan::factory()->borrowed()->create(['due_at' => now()->subDay()]);
        Loan::factory()->borrowed()->create(['due_at' => now()->addDay()]);

        expect($late->isOverdue())->toBeTrue()
            ->and(Loan::overdue()->pluck('id')->all())->toBe([$late->id]);
    });

    it('anggota UKM lain bisa melihat barang yang tersedia', function () {
        loginAs(Role::AnggotaLain);
        $available = Item::factory()->create(['quantity' => 1]);
        $habis = Item::factory()->create(['quantity' => 1]);
        Loan::factory()->borrowed()->withItem($habis)->create();

        livewire(ListItems::class)
            ->filterTable('available')
            ->assertCanSeeTableRecords([$available])
            ->assertCanNotSeeTableRecords([$habis]);
    });
});

describe('pengajuan oleh anggota UKM lain', function () {
    it('mengisi data diri, tanggal pinjam, dan beberapa barang sekaligus', function () {
        $tamu = loginAs(Role::AnggotaLain, ['name' => 'Rani', 'npm' => '4123000111', 'origin_ukm' => 'UKM Musik']);
        $kamera = Item::factory()->create(['quantity' => 2]);
        $tripod = Item::factory()->create(['quantity' => 3]);

        livewire(CreateLoan::class)
            ->assertSchemaStateSet([
                'borrower_name' => 'Rani',
                'borrower_npm' => '4123000111',
                'borrower_unit' => 'UKM Musik',
            ])
            ->fillForm([
                'borrowed_at' => now()->addDay(),
                'due_at' => now()->addDays(3),
                'purpose' => 'Video klip',
                'loanItems' => [
                    ['item_id' => $kamera->id, 'quantity' => 1],
                    ['item_id' => $tripod->id, 'quantity' => 2],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $loan = Loan::sole();
        expect($loan->user_id)->toBe($tamu->id)
            ->and($loan->status)->toBe(LoanStatus::Diajukan)
            ->and($loan->borrower_unit)->toBe('UKM Musik')
            ->and($loan->loanItems->pluck('quantity', 'item_id')->all())->toBe([$kamera->id => 1, $tripod->id => 2]);
    });

    it('member UKM juga bisa mengajukan peminjaman dengan data dirinya', function () {
        $member = loginAs(Role::Member, ['name' => 'Dimas', 'npm' => '4122000222', 'faculty' => 'Fakultas Teknik']);
        $lensa = Item::factory()->create(['quantity' => 1]);

        livewire(CreateLoan::class)
            ->assertSchemaStateSet([
                'borrower_name' => 'Dimas',
                'borrower_npm' => '4122000222',
                'borrower_unit' => 'Fakultas Teknik',
            ])
            ->fillForm([
                'borrowed_at' => now()->addDay(),
                'due_at' => now()->addDays(2),
                'loanItems' => [['item_id' => $lensa->id, 'quantity' => 1]],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(Loan::sole()->user_id)->toBe($member->id);
    });

    it('member hanya melihat peminjaman miliknya dan tidak bisa memprosesnya', function () {
        $member = loginAs(Role::Member);
        $milikku = Loan::factory()->create(['user_id' => $member->id]);
        $orangLain = Loan::factory()->create();

        livewire(ListLoans::class)
            ->assertCanSeeTableRecords([$milikku])
            ->assertCanNotSeeTableRecords([$orangLain])
            ->assertActionHidden(TestAction::make('handOver')->table($milikku));
    });

    it('menolak jumlah melebihi stok tersedia', function () {
        loginAs(Role::AnggotaLain);
        $item = Item::factory()->create(['quantity' => 1]);

        livewire(CreateLoan::class)
            ->fillForm([
                'borrowed_at' => now()->addDay(),
                'due_at' => now()->addDays(2),
                'loanItems' => [['item_id' => $item->id, 'quantity' => 2]],
            ])
            ->call('create')
            ->assertHasFormErrors(['loanItems.0.quantity']);

        expect(Loan::count())->toBe(0);
    });

    it('menolak tanggal kembali sebelum tanggal pinjam', function () {
        loginAs(Role::AnggotaLain);
        $item = Item::factory()->create();

        livewire(CreateLoan::class)
            ->fillForm([
                'borrowed_at' => now()->addDays(3),
                'due_at' => now()->addDay(),
                'loanItems' => [['item_id' => $item->id, 'quantity' => 1]],
            ])
            ->call('create')
            ->assertHasFormErrors(['due_at']);
    });

    it('bisa menambah dan menghapus barang selama pengajuan belum diproses', function () {
        $tamu = loginAs(Role::AnggotaLain);
        $kamera = Item::factory()->create();
        $lensa = Item::factory()->create();
        $loan = Loan::factory()->for($tamu)->withItem($kamera)->create();

        livewire(EditLoan::class, ['record' => $loan->getRouteKey()])
            ->fillForm(['loanItems' => [['item_id' => $lensa->id, 'quantity' => 1]]])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($loan->loanItems()->pluck('item_id')->all())->toBe([$lensa->id]);
    });

    it('tidak bisa mengubah pengajuan yang sudah diproses', function () {
        $tamu = loginAs(Role::AnggotaLain);
        $loan = Loan::factory()->borrowed()->for($tamu)->withItem()->create();

        $this->get(EditLoan::getUrl(['record' => $loan], panel: 'anggota'))->assertForbidden();
    });

    it('hanya melihat peminjamannya sendiri', function () {
        $tamu = loginAs(Role::AnggotaLain);
        $mine = Loan::factory()->for($tamu)->withItem()->create();
        $others = Loan::factory()->withItem()->create();

        livewire(ListLoans::class)
            ->assertCanSeeTableRecords([$mine])
            ->assertCanNotSeeTableRecords([$others])
            ->assertActionHidden(TestAction::make('handOver')->table($mine));
    });
});

describe('pemrosesan oleh admin inventaris', function () {
    it('menyerahkan barang dan mencatat kondisi sebelum dipinjam', function () {
        $admin = loginAs(Role::AdminInventaris);
        $loan = Loan::factory()->withItem()->create();
        $line = $loan->loanItems->first();

        livewire(ListLoans::class)
            ->callAction(TestAction::make('handOver')->table($loan), data: [
                'conditions' => [$line->id => ItemCondition::RusakRingan->value],
            ])
            ->assertHasNoFormErrors();

        $loan->refresh();
        expect($loan->status)->toBe(LoanStatus::Dipinjam)
            ->and($loan->handled_by)->toBe($admin->id)
            ->and($line->fresh()->condition_before)->toBe(ItemCondition::RusakRingan);
    });

    it('menerima pengembalian, mencatat kondisi sesudah, dan memperbarui kondisi barang', function () {
        loginAs(Role::AdminInventaris);
        $loan = Loan::factory()->borrowed()->withItem()->create();
        $line = $loan->loanItems->first();

        livewire(ListLoans::class)
            ->callAction(TestAction::make('return')->table($loan), data: [
                'conditions' => [$line->id => ItemCondition::RusakBerat->value],
                'notes' => 'Lensa retak',
            ]);

        $loan->refresh();
        expect($loan->status)->toBe(LoanStatus::Dikembalikan)
            ->and($loan->returned_at)->not->toBeNull()
            ->and($line->fresh()->condition_after)->toBe(ItemCondition::RusakBerat)
            ->and($line->item->fresh()->condition)->toBe(ItemCondition::RusakBerat);
    });

    it('mengatur ulang tanggal pinjam dan tanggal kembali', function () {
        loginAs(Role::AdminInventaris);
        $loan = Loan::factory()->borrowed()->withItem()->create();
        $newDue = now()->addWeek()->startOfMinute();

        livewire(EditLoan::class, ['record' => $loan->getRouteKey()])
            ->fillForm(['due_at' => $newDue])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($loan->fresh()->due_at->equalTo($newDue))->toBeTrue();
    });

    it('melihat semua peminjaman beserta peminjamnya', function () {
        loginAs(Role::AdminInventaris);
        $loans = Loan::factory(3)->withItem()->create();

        livewire(ListLoans::class)->assertCanSeeTableRecords($loans);
    });

    it('tidak bisa menyerahkan barang jika stok habis', function () {
        $admin = User::factory()->role(Role::AdminInventaris)->create();
        $item = Item::factory()->create(['quantity' => 1]);
        Loan::factory()->borrowed()->withItem($item)->create();
        $loan = Loan::factory()->withItem($item)->create();

        expect(fn () => $loan->handOver($admin))->toThrow(DomainException::class);
    });

    it('menambah data barang', function () {
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
});
