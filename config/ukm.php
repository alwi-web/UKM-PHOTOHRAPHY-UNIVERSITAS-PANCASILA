<?php

return [
    'faculties' => [
        'Fakultas Ekonomi & Bisnis',
        'Fakultas Farmasi',
        'Fakultas Hukum',
        'Fakultas Teknik',
        'Fakultas Pariwisata',
        'Fakultas Psikologi',
        'Fakultas Ilmu Komunikasi',
    ],

    /*
     * Isi awal profil UKM. Setelah aplikasi berjalan, profil diubah oleh Admin UKM
     * lewat menu "Profil UKM" di panel admin (tersimpan di tabel organization_profiles).
     */
    'profile_defaults' => [
        'name' => 'PhotoUP 94',
        'tagline' => 'PhotoUP adalah UKM Fotografi mahasiswa Universitas Pancasila yang menjadi ruang bagi kreativitas, eksplorasi, dan kolaborasi melalui lensa.',
        'about' => 'UKM Fotografi adalah wadah minat dan bakat mahasiswa di bidang fotografi. Kami belajar bersama lewat kelas, hunting foto, pameran, dan kolaborasi dengan komunitas kreatif lain.',
        'history' => 'PhotoUP 94 adalah Unit Kegiatan Mahasiswa fotografi di Universitas Pancasila.',
        'vision' => 'Menjadi komunitas fotografi mahasiswa yang unggul, kreatif, dan berkarakter Pancasila.',
        'missions' => [
            'Mengembangkan keterampilan fotografi anggota.',
            'Menghasilkan karya yang bermanfaat bagi kampus dan masyarakat.',
            'Membangun jejaring dengan komunitas kreatif lain.',
        ],
        'core_members' => [
            ['position' => 'Ketua Umum', 'user_id' => null],
            ['position' => 'Wakil Ketua', 'user_id' => null],
            ['position' => 'Sekretaris', 'user_id' => null],
            ['position' => 'Bendahara', 'user_id' => null],
        ],
        'divisions' => [
            ['name' => 'Penelitian dan Pengembangan', 'description' => null, 'member_ids' => []],
            ['name' => 'Hunting', 'description' => null, 'member_ids' => []],
            ['name' => 'Pameran', 'description' => null, 'member_ids' => []],
            ['name' => 'Inventaris', 'description' => null, 'member_ids' => []],
            ['name' => 'Humas Internal dan Eksternal', 'description' => null, 'member_ids' => []],
        ],
        'address' => 'Sekretariat UKM, Universitas Pancasila, Jl. Srengseng Sawah, Jagakarsa, Jakarta Selatan 12640',
        'email' => 'ukmfotografi@univpancasila.ac.id',
        'instagram' => 'ukmfotografi.up',
        'tiktok' => 'ukmfotografi.up',
        'phone' => '0812-0000-0000 (Humas)',
    ],
];
