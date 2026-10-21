<?php

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Enums\ParticipantStatus;
use App\Enums\Role;
use App\Filament\Pages\EventReport;
use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\EditEvent;
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
