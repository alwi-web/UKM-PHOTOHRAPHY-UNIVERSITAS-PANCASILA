<?php

namespace Database\Seeders;

use App\Enums\ArtworkStatus;
use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Enums\ItemCondition;
use App\Enums\LoanStatus;
use App\Enums\ParticipantStatus;
use App\Enums\Role;
use App\Models\Artwork;
use App\Models\Category;
use App\Models\Event;
use App\Models\Item;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Data awal untuk demo. Semua akun memakai password: password
     */
    public function run(): void
    {
        $admin = User::factory()->role(Role::AdminUkm)->create(['name' => 'Admin UKM', 'email' => 'admin@ukmfoto.test']);
        $inventaris = User::factory()->role(Role::AdminInventaris)->create(['name' => 'Admin Inventaris', 'email' => 'inventaris@ukmfoto.test']);
        $kurator = User::factory()->role(Role::Kurator)->create(['name' => 'Kurator Galeri', 'email' => 'kurator@ukmfoto.test']);
        $member = User::factory()->create(['name' => 'Member Fotografi', 'email' => 'member@ukmfoto.test']);
        $tamu = User::factory()->role(Role::AnggotaLain)->create([
            'name' => 'Anggota UKM Jurnalistik', 'email' => 'tamu@ukmfoto.test', 'origin_ukm' => 'UKM Jurnalistik',
        ]);
        $members = User::factory(10)->create()->push($member);
        User::factory(3)->pending()->create();

        // ---- Inventaris ----
        $items = collect([
            ['CAM-001', 'Canon EOS 90D', 'Kamera', 'Canon', 2],
            ['CAM-002', 'Sony Alpha A6400', 'Kamera', 'Sony', 1],
            ['LEN-001', 'Canon EF 50mm f/1.8 STM', 'Lensa', 'Canon', 2],
            ['LEN-002', 'Sigma 18-35mm f/1.8 Art', 'Lensa', 'Sigma', 1],
            ['TRP-001', 'Tripod Manfrotto Compact', 'Tripod', 'Manfrotto', 3],
            ['LGT-001', 'Flash Godox TT600', 'Lighting', 'Godox', 2],
            ['LGT-002', 'Softbox 60x90', 'Lighting', 'Godox', 2],
        ])->map(fn ($i) => Item::create([
            'code' => $i[0], 'name' => $i[1], 'category' => $i[2], 'brand' => $i[3], 'quantity' => $i[4], 'condition' => ItemCondition::Baik,
        ]));

        $loan = fn (User $user, array $attrs, array $lines) => tap(
            Loan::create([
                'user_id' => $user->id,
                'borrower_name' => $user->name,
                'borrower_npm' => $user->npm,
                'borrower_unit' => $user->unit(),
                ...$attrs,
            ]),
            fn (Loan $l) => collect($lines)->each(fn ($line) => $l->loanItems()->create($line)),
        );

        $loan($tamu, [
            'purpose' => 'Liputan pentas seni', 'status' => LoanStatus::Dipinjam, 'handled_by' => $inventaris->id,
            'borrowed_at' => now()->subDays(2), 'due_at' => now()->addDay(),
        ], [
            ['item_id' => $items[0]->id, 'quantity' => 1, 'condition_before' => ItemCondition::Baik],
            ['item_id' => $items[4]->id, 'quantity' => 1, 'condition_before' => ItemCondition::Baik],
        ]);
        $loan($tamu, [
            'purpose' => 'Dokumentasi rapat kerja', 'status' => LoanStatus::Diajukan,
            'borrowed_at' => now()->addDays(3), 'due_at' => now()->addDays(5),
        ], [
            ['item_id' => $items[2]->id, 'quantity' => 1],
            ['item_id' => $items[5]->id, 'quantity' => 2],
        ]);
        $tamu2 = User::factory()->role(Role::AnggotaLain)->create(['name' => 'Rani (UKM Musik)', 'origin_ukm' => 'UKM Musik']);
        $loan($tamu2, [
            'purpose' => 'Video klip band', 'status' => LoanStatus::Dipinjam, 'handled_by' => $inventaris->id,
            'borrowed_at' => now()->subDays(6), 'due_at' => now()->subDay(),
        ], [
            ['item_id' => $items[1]->id, 'quantity' => 1, 'condition_before' => ItemCondition::Baik],
        ]);
        $loan($tamu2, [
            'purpose' => 'Foto profil anggota', 'status' => LoanStatus::Dikembalikan, 'handled_by' => $inventaris->id,
            'borrowed_at' => now()->subDays(12), 'due_at' => now()->subDays(10), 'returned_at' => now()->subDays(10),
        ], [
            ['item_id' => $items[6]->id, 'quantity' => 1, 'condition_before' => ItemCondition::Baik, 'condition_after' => ItemCondition::RusakRingan],
        ]);
        $items[6]->update(['condition' => ItemCondition::RusakRingan]);

        // ---- Galeri ----
        $categories = collect(['Landscape', 'Portrait', 'Human Interest', 'Street'])
            ->map(fn ($name) => Category::create(['name' => $name]));

        foreach (range(1, 12) as $i) {
            Artwork::create([
                // dua karya pertama milik akun demo member, supaya hasil kurasi bisa dilihat
                'user_id' => $i <= 2 ? $member->id : $members->random()->id,
                'category_id' => $categories->random()->id,
                'title' => fake()->randomElement(['Senja di Danau Kampus', 'Tatapan', 'Pedagang Pagi', 'Ritme Kota', 'Kabut Puncak', 'Bayangan Halte'])." #{$i}",
                'caption' => fake()->sentence(14),
                'aperture' => ['f/1.8', 'f/4', 'f/8', 'f/11'][$i % 4],
                'shutter_speed' => ['1/250', '1/60', '1/500', '2'][$i % 4],
                'iso' => [100, 400, 200, 100][$i % 4],
                'exposure_notes' => 'Aperture dipilih sesuai kebutuhan ruang tajam, shutter speed menyesuaikan gerak subjek, dan ISO dijaga rendah agar minim noise.',
                'image' => "https://picsum.photos/seed/ukm-up-{$i}/800/".[600, 900, 1000][$i % 3],
                'instagram_url' => $i % 2 ? 'https://www.instagram.com/ukmfotografi.up/' : null,
                'status' => ArtworkStatus::Approved,
                'is_featured' => true,
                'curator_note' => $i === 1 ? 'Komposisi rule of thirds-nya kuat, warna senja terjaga.' : null,
                'curated_by' => $kurator->id,
                'curated_at' => now()->subDays($i),
                'views' => fake()->numberBetween(5, 300),
                'created_at' => now()->subDays($i * 10),
            ]);
        }
        Artwork::factory(3)->recycle($members)->recycle($categories)->create();
        Artwork::factory()->for($member)->recycle($categories)->create(['title' => 'Hujan di Halte Kampus']);
        Artwork::factory()->for($member)->recycle($categories)->create([
            'title' => 'Siluet Menara Air',
            'status' => ArtworkStatus::Rejected,
            'curator_note' => 'Foto under-exposed dan subjek kurang tajam. Coba shutter lebih cepat atau naikkan ISO.',
            'curated_by' => $kurator->id,
            'curated_at' => now()->subDay(),
        ]);

        // ---- Kegiatan ----
        $events = collect([
            ['Kelas Fotografi Dasar', EventType::Kelas, 'Aula Gedung Rektorat', 10],
            ['Hunting Foto Kota Tua', EventType::Hunting, 'Kota Tua, Jakarta Barat', 24],
            ['Pameran Foto "Lensa Pancasila"', EventType::Pameran, 'Lobby Fakultas Teknik', 45],
            ['Workshop Editing Lightroom', EventType::Workshop, 'Lab Komputer FTI', 60],
        ])->map(fn ($e) => Event::create([
            'title' => $e[0], 'type' => $e[1], 'location' => $e[2],
            'description' => 'Kegiatan rutin UKM Fotografi Universitas Pancasila untuk seluruh anggota.',
            'start_at' => now()->addDays($e[3])->setTime(9, 0), 'end_at' => now()->addDays($e[3])->setTime(15, 0),
            'quota' => 40, 'status' => EventStatus::Published, 'registration_open' => true, 'created_by' => $admin->id,
        ]));
        $events[0]->participants()->attach($members->take(6)->pluck('id'), ['status' => ParticipantStatus::Terdaftar]);

        $past = Event::create([
            'title' => 'Hunting Sunrise Ancol', 'type' => EventType::Hunting, 'location' => 'Pantai Ancol',
            'start_at' => now()->subMonth()->setTime(5, 0), 'status' => EventStatus::Selesai, 'created_by' => $admin->id,
        ]);
        $members->take(8)->each(fn ($m, $i) => $past->participants()->attach($m->id, [
            'status' => $i < 6 ? ParticipantStatus::Hadir : ParticipantStatus::TidakHadir,
        ]));
    }
}
