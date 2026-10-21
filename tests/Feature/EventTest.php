<?php

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Enums\ParticipantStatus;
use App\Enums\Role;
use App\Filament\Pages\EventReport;
use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\Pages\ListEvents;
use App\Filament\Resources\Events\RelationManagers\ParticipantsRelationManager;
use App\Models\Event;
use App\Models\User;
use Filament\Actions\Testing\TestAction;

use function Pest\Livewire\livewire;

it('admin UKM membuat acara baru', function () {
    $admin = loginAs(Role::AdminUkm);

    livewire(CreateEvent::class)
        ->fillForm([
            'title' => 'Hunting Foto Monas',
            'type' => EventType::Hunting->value,
            'location' => 'Monas, Jakarta Pusat',
            'start_at' => now()->addWeek(),
            'status' => EventStatus::Published->value,
            'quota' => 20,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $event = Event::sole();
    expect($event->created_by)->toBe($admin->id)
        ->and($event->slug)->toStartWith('hunting-foto-monas');
});

it('member tidak bisa membuat acara', function () {
    loginAs(Role::Member);

    $this->get(CreateEvent::getUrl())->assertForbidden();
});

it('member mendaftar sebagai peserta acara', function () {
    $member = loginAs(Role::Member);
    $event = Event::factory()->create();

    livewire(ListEvents::class)->callAction(TestAction::make('join')->table($event));

    expect($event->participants()->whereKey($member->id)->exists())->toBeTrue();
});

it('tidak bisa mendaftar jika kuota penuh', function () {
    $event = Event::factory()->create(['quota' => 1]);
    $event->participants()->attach(User::factory()->create(), ['status' => ParticipantStatus::Terdaftar]);

    $member = loginAs(Role::Member);

    expect($event->canRegister($member))->toBeFalse();
    livewire(ListEvents::class)->assertActionHidden(TestAction::make('join')->table($event));
});

it('admin menandai kehadiran peserta', function () {
    loginAs(Role::AdminUkm);
    $event = Event::factory()->create();
    $peserta = User::factory()->create();
    $event->participants()->attach($peserta, ['status' => ParticipantStatus::Terdaftar]);

    livewire(ParticipantsRelationManager::class, ['ownerRecord' => $event, 'pageClass' => EditEvent::class])
        ->assertCanSeeTableRecords([$peserta])
        ->selectTableRecords([$peserta])
        ->callAction(TestAction::make('markPresent')->table()->bulk());

    expect($event->participants()->first()->pivot->status)->toBe(ParticipantStatus::Hadir->value);
});

it('laporan kegiatan menampilkan jumlah peserta dan kehadiran', function () {
    loginAs(Role::AdminUkm);
    $event = Event::factory()->create(['title' => 'Kelas Dasar']);
    $event->participants()->attach(User::factory(2)->create(), ['status' => ParticipantStatus::Hadir]);

    livewire(EventReport::class)
        ->assertCanSeeTableRecords([$event])
        ->assertTableColumnStateSet('participants_count', 2, $event)
        ->assertTableColumnStateSet('attended_count', 2, $event);
});

it('laporan kegiatan hanya untuk admin UKM', function () {
    loginAs(Role::Kurator);

    $this->get(EventReport::getUrl())->assertForbidden();
});
