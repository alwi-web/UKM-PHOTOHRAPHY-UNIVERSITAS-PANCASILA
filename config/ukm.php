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
     * Ditampilkan di bagian "Tentang Kami" pada halaman depan.
     * Setiap elemen 'history' adalah satu paragraf.
     */
    'name' => 'PhotoUP 94',

    'history' => [
        'PhotoUP 94 adalah Unit Kegiatan Mahasiswa fotografi di Universitas Pancasila.',
    ],

    'structure' => [
        'core' => ['Ketua', 'Wakil Ketua', 'Sekretaris', 'Bendahara'],
        'divisions' => [
            'Penelitian dan Pengembangan',
            'Hunting',
            'Pameran',
            'Inventaris',
            'Humas Internal dan Eksternal',
        ],
    ],

    'contact' => [
        'address' => env('UKM_ADDRESS', 'Sekretariat UKM, Universitas Pancasila, Jl. Srengseng Sawah, Jagakarsa, Jakarta Selatan 12640'),
        'email' => env('UKM_EMAIL', 'ukmfotografi@univpancasila.ac.id'),
        'instagram' => env('UKM_INSTAGRAM', 'ukmfotografi.up'),
        'tiktok' => env('UKM_TIKTOK', 'ukmfotografi.up'),
        'phone' => env('UKM_PHONE', '0812-0000-0000 (Humas)'),
    ],
];
