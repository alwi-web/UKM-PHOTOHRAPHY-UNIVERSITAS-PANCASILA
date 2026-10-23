<?php

return [
    'faculties' => [
        'Fakultas Teknik',
        'Fakultas Ekonomi dan Bisnis',
        'Fakultas Hukum',
        'Fakultas Psikologi',
        'Fakultas Farmasi',
        'Fakultas Teknologi Industri',
        'Fakultas Ilmu Komunikasi',
        'Sekolah Pascasarjana',
    ],

    /*
     * Isi awal profil UKM. Setelah aplikasi berjalan, profil diubah oleh Admin UKM
     * lewat menu "Profil UKM" di panel admin (tersimpan di tabel organization_profiles).
     */
    'profile_defaults' => [
        'name' => 'PhotoUP 94',
        'tagline' => 'Tempat mahasiswa Universitas Pancasila belajar, berkarya, dan berbagi cerita lewat foto.',
        'about' => 'UKM Fotografi adalah wadah minat dan bakat mahasiswa di bidang fotografi. Kami belajar bersama lewat kelas, hunting foto, pameran, dan kolaborasi dengan komunitas kreatif lain.',
        'history' => 'PhotoUP 94 adalah Unit Kegiatan Mahasiswa fotografi di Universitas Pancasila.',
        'vision' => 'Menjadi komunitas fotografi mahasiswa yang unggul, kreatif, dan berkarakter Pancasila.',
        'missions' => [
            'Mengembangkan keterampilan fotografi anggota.',
            'Menghasilkan karya yang bermanfaat bagi kampus dan masyarakat.',
            'Membangun jejaring dengan komunitas kreatif lain.',
        ],
        'core_members' => [
            ['position' => 'Ketua', 'name' => null, 'photo' => null],
            ['position' => 'Wakil Ketua', 'name' => null, 'photo' => null],
            ['position' => 'Sekretaris', 'name' => null, 'photo' => null],
            ['position' => 'Bendahara', 'name' => null, 'photo' => null],
        ],
        'divisions' => [
            ['name' => 'Penelitian dan Pengembangan', 'coordinator' => null, 'description' => null],
            ['name' => 'Hunting', 'coordinator' => null, 'description' => null],
            ['name' => 'Pameran', 'coordinator' => null, 'description' => null],
            ['name' => 'Inventaris', 'coordinator' => null, 'description' => null],
            ['name' => 'Humas Internal dan Eksternal', 'coordinator' => null, 'description' => null],
        ],
        'address' => 'Sekretariat UKM, Universitas Pancasila, Jl. Srengseng Sawah, Jagakarsa, Jakarta Selatan 12640',
        'email' => 'ukmfotografi@univpancasila.ac.id',
        'instagram' => 'ukmfotografi.up',
        'tiktok' => 'ukmfotografi.up',
        'phone' => '0812-0000-0000 (Humas)',
    ],
];
