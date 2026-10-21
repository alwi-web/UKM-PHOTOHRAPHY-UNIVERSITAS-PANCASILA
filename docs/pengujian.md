# Rencana Pengujian

Pengujian dilakukan dalam tiga tahap sesuai spesifikasi: **Pest** (otomatis), **Blackbox**, dan **UAT**.

## 1. Pest (otomatis)

Jalankan `./vendor/bin/pest`. Cakupan:

| File | Yang diuji |
|---|---|
| `tests/Feature/RegistrationTest.php` | Pendaftaran anggota, validasi NIM/role/asal UKM, akses panel sebelum & sesudah verifikasi |
| `tests/Feature/HomePageTest.php` | Website hanya menampilkan karya terkurasi & acara terpublikasi, pencatatan jumlah dilihat |
| `tests/Feature/InventoryTest.php` | Stok tersedia, keterlambatan, pengajuan, serah terima, pengembalian & kondisi barang, batasan role |
| `tests/Feature/GalleryTest.php` | Unggah karya, kurasi, karya tampil di website, batasan role, halaman insight |
| `tests/Feature/EventTest.php` | CRUD acara, daftar peserta, kuota, kehadiran, laporan kegiatan |
| `tests/Feature/UserManagementTest.php` | Tambah user, atur role, verifikasi pendaftar, tunjuk kurator, batasan akses |
| `tests/Unit/EnumTest.php` | Aturan role yang boleh mengunggah karya |

## 2. Blackbox

Isi kolom **Hasil** saat pengujian manual (✓ sesuai / ✗ tidak sesuai).

| No | Fitur | Skenario | Data uji | Hasil yang diharapkan | Hasil |
|---|---|---|---|---|---|
| B01 | Pendaftaran | Isi semua data valid lalu kirim | NIM 10 digit, foto JPG < 2 MB | Muncul pesan terima kasih, user berstatus "Menunggu Verifikasi" | |
| B02 | Pendaftaran | NIM sudah terdaftar | NIM milik user lain | Muncul pesan "NIM ini sudah terdaftar." | |
| B03 | Pendaftaran | Pilih "Anggota UKM lain" tanpa asal UKM | Asal UKM kosong | Muncul pesan wajib isi asal UKM | |
| B04 | Login | Pendaftar belum diverifikasi mencoba login | Akun pending | Akses panel ditolak (403) | |
| B05 | Manajemen user | Admin menyetujui pendaftar | Tombol "Setujui" | Status jadi Aktif, user bisa login | |
| B06 | Manajemen user | Admin menunjuk kurator | Menu "Tunjuk sebagai Kurator" | Role berubah jadi Kurator | |
| B07 | Inventaris | Admin inventaris menambah barang | Kode CAM-010, jumlah 2 | Barang muncul di daftar, tersedia 2 | |
| B08 | Peminjaman | Member mengajukan pinjam melebihi stok | Jumlah 5, stok 2 | Muncul pesan "Stok tersedia hanya 2 unit." | |
| B09 | Peminjaman | Admin menyerahkan barang | Kondisi awal "Baik" | Status "Sedang Dipinjam", stok tersedia berkurang | |
| B10 | Peminjaman | Barang lewat batas kembali | Batas kembali kemarin | Tampil di tab "Terlambat", tanggal berwarna merah | |
| B11 | Peminjaman | Admin menerima pengembalian | Kondisi akhir "Rusak Ringan" | Status "Dikembalikan", kondisi barang ikut berubah | |
| B12 | Peminjaman | Member membuka daftar peminjaman | Login member | Hanya melihat peminjamannya sendiri | |
| B13 | Galeri | Member mengunggah karya | Foto + kategori + link IG | Karya berstatus "Menunggu Kurasi" | |
| B14 | Galeri | Anggota UKM lain membuka menu unggah | Login anggota UKM lain | Menu Karya Foto tidak tersedia (403) | |
| B15 | Kurasi | Kurator meloloskan & menampilkan karya | Lolos + tampilkan di website | Karya muncul di galeri website | |
| B16 | Kurasi | Kurator menolak karya | Tolak + catatan | Karya tidak muncul di website | |
| B17 | Acara | Admin membuat acara | Kuota 20, status Dipublikasikan | Acara tampil di agenda website | |
| B18 | Acara | Member ikut acara | Tombol "Ikut" | Member tercatat sebagai peserta | |
| B19 | Acara | Kuota penuh | Kuota 1, sudah 1 peserta | Tombol "Ikut" tidak muncul | |
| B20 | Acara | Admin menandai hadir | Pilih peserta → "Tandai Hadir" | Status kehadiran "Hadir" | |
| B21 | Laporan | Filter laporan kegiatan per periode | Dari–sampai bulan ini | Hanya kegiatan pada periode itu | |
| B22 | Laporan | Unduh CSV laporan kegiatan | Tombol "Unduh CSV" | File CSV terunduh sesuai filter | |
| B23 | Laporan | Kurator membuka insight karya | Login kurator | Statistik & grafik tampil | |

## 3. UAT (User Acceptance Test)

Responden dari tiap aktor (mis. 1 Admin UKM, 1 Admin Inventaris, 1 Kurator, 5 Member, 2 Anggota UKM lain)
mencoba skenario sesuai perannya, lalu mengisi kuesioner skala Likert 1–5
(1 = sangat tidak setuju, 5 = sangat setuju).

| No | Pernyataan | 1 | 2 | 3 | 4 | 5 |
|---|---|---|---|---|---|---|
| U01 | Tampilan website mudah dipahami | | | | | |
| U02 | Proses pendaftaran anggota mudah dilakukan | | | | | |
| U03 | Saya mudah menemukan barang yang tersedia untuk dipinjam | | | | | |
| U04 | Proses pengajuan dan pengembalian barang jelas | | | | | |
| U05 | Status dan riwayat peminjaman membantu melacak barang | | | | | |
| U06 | Mengunggah karya ke galeri mudah dilakukan | | | | | |
| U07 | Proses kurasi karya membantu menjaga kualitas galeri | | | | | |
| U08 | Informasi acara dan pendaftaran peserta mudah diikuti | | | | | |
| U09 | Laporan kegiatan dan insight karya bermanfaat bagi pengurus | | | | | |
| U10 | Secara keseluruhan sistem ini membantu kegiatan UKM | | | | | |

Persentase kelayakan = (total skor ÷ skor maksimum) × 100%.
Interpretasi: 0–20% sangat kurang, 21–40% kurang, 41–60% cukup, 61–80% baik, 81–100% sangat baik.
