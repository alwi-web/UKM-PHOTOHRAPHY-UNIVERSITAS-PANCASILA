# Rencana Pengujian

Pengujian dilakukan dalam tiga tahap sesuai spesifikasi: **Pest** (otomatis), **Blackbox**, dan **UAT**.

## 1. Pest (otomatis)

Jalankan `./vendor/bin/pest`. Cakupan:

| File | Yang diuji |
|---|---|
| `tests/Feature/RoleAccessTest.php` | Setiap role hanya bisa membuka menu miliknya (55 kombinasi role × menu) |
| `tests/Feature/RegistrationTest.php` | Formulir pendaftaran calon anggota (nama, NPM, fakultas, WhatsApp), pernyataan belum ikut kaderisasi, NPM ganda, terima & buat akun, tolak |
| `tests/Feature/HomePageTest.php` | Website hanya menampilkan karya terkurasi & acara terpublikasi, pencatatan jumlah dilihat |
| `tests/Feature/LoanScheduleTest.php` | Cek bentrok tanggal: rentang beririsan, bersambung, status yang diabaikan, barang terlambat, filter ketersediaan |
| `tests/Feature/InventoryTest.php` | Stok tersedia, keterlambatan, pengajuan multi-barang, tambah/hapus barang, serah terima, pengembalian & kondisi barang, ubah tanggal |
| `tests/Feature/GalleryTest.php` | Unggah karya dengan segitiga exposure, hasil kurasi untuk member, kurasi, kurator tidak bisa mengunggah/mengubah |
| `tests/Feature/EventTest.php` | CRUD acara, daftar peserta, kuota, kehadiran, laporan kegiatan |
| `tests/Feature/OrganizationProfileTest.php` | Admin UKM mengubah profil, sejarah, visi & misi, struktur organisasi; tampil di website; role lain ditolak |
| `tests/Feature/UserManagementTest.php` | Tambah user, atur role, tunjuk kurator |
| `tests/Unit/EnumTest.php` | Matriks kemampuan tiap role |

## 2. Blackbox

Isi kolom **Hasil** saat pengujian manual (✓ sesuai / ✗ tidak sesuai).

| No | Fitur | Skenario | Data uji | Hasil yang diharapkan | Hasil |
|---|---|---|---|---|---|
| B01 | Pendaftaran | Isi formulir pendaftaran calon anggota dengan data valid | Nama, NPM 10 digit, fakultas, 0812-3456-7890, centang pernyataan | Muncul pesan terima kasih; pendaftar tampil di menu Pendaftar Calon Anggota berstatus "Baru" | |
| B02 | Pendaftaran | Tidak mencentang pernyataan "Saya belum pernah mendaftar di PhotoUP" | Centang kosong | Muncul pesan pendaftaran khusus mahasiswa yang belum pernah mendaftar di PhotoUP | |
| B03 | Pendaftaran | NPM sudah menjadi anggota atau sudah mendaftar | NPM yang sama dikirim dua kali | Muncul pesan "NPM ini sudah terdaftar" | |
| B04 | Pendaftar calon anggota | Admin menghubungi pendaftar | Klik nomor WhatsApp, lalu "Sudah Dihubungi" | Chat WhatsApp terbuka; status jadi "Sudah Dihubungi" | |
| B05 | Pendaftar calon anggota | Admin menerima pendaftar | "Terima & buat akun", isi email & password | Akun Member terbentuk dan bisa login ke panel | |
| B06 | Manajemen user | Admin menunjuk kurator | Menu "Tunjuk sebagai Kurator" | Role berubah jadi Kurator | |
| B07 | Profil UKM | Admin UKM mengubah sejarah dan struktur organisasi | Isi sejarah 2 paragraf, nama Ketua, koordinator divisi | Setelah Simpan, perubahan tampil di bagian Tentang Kami website | |
| B08 | Profil UKM | Role selain Admin UKM membuka /admin/profil-ukm | Login admin inventaris | Menu tidak tampil dan akses ditolak (403) | |
| B09 | Inventaris | Admin inventaris menambah barang | Kode CAM-010, jumlah 2 | Barang muncul di daftar, tersedia 2 | |
| B10 | Peminjaman | Anggota UKM lain membuka form pengajuan | Login anggota UKM lain | Nama, NPM, dan Fakultas/UKM terisi otomatis | |
| B11 | Peminjaman | Mengajukan 2 barang sekaligus | Kamera ×1, Tripod ×2, tanggal dari–sampai | Pengajuan tersimpan berstatus "Diajukan" dengan 2 barang | |
| B12 | Peminjaman | Jumlah melebihi stok | Jumlah 5, stok 2 | Muncul pesan stok tersedia hanya 2 unit | |
| B13 | Peminjaman | Tanggal kembali sebelum tanggal pinjam | Dari 10 Nov, sampai 8 Nov | Muncul pesan validasi tanggal | |
| B14 | Peminjaman | Tanggal bentrok dengan pemesanan lain | Kamera (stok 1) sudah dipesan 10–12 Nov, ajukan 11–13 Nov | Muncul keterangan jadwal bentrok dan pesan "sudah habis dipesan pada tanggal tersebut" | |
| B15 | Peminjaman | Tanggal tidak bentrok | Kamera sudah dipesan 10–12 Nov 17.00, ajukan 12 Nov 17.00–14 Nov | Pengajuan diterima | |
| B16 | Inventaris | Cek ketersediaan di daftar barang | Filter tanggal 11 Nov | Kamera yang sudah dipesan tidak muncul | |
| B17 | Peminjaman | Menghapus satu barang dari pengajuan yang belum diproses | Hapus Tripod | Pengajuan tinggal berisi Kamera | |
| B18 | Peminjaman | Admin menyerahkan barang | Kondisi sebelum "Baik" per barang | Status "Sedang Dipinjam", stok tersedia berkurang | |
| B19 | Peminjaman | Admin mengubah tanggal kembali | Mundur 3 hari | Tanggal kembali berubah | |
| B20 | Peminjaman | Admin menerima pengembalian | Kondisi sesudah "Rusak Ringan" | Status "Dikembalikan", kondisi barang ikut berubah | |
| B21 | Peminjaman | Barang lewat batas kembali | Batas kembali kemarin | Tampil di tab "Terlambat", tanggal berwarna merah | |
| B22 | Galeri | Member mengunggah foto tanpa data exposure | Aperture/shutter/ISO kosong | Muncul pesan wajib diisi | |
| B23 | Galeri | Member mengunggah foto lengkap | Kategori, caption, f/8 · 1/250 · ISO 100, deskripsi, link IG | Karya berstatus "Menunggu Kurasi" | |
| B24 | Galeri | Member membuka tab "Lolos Kurasi" | Login member | Hanya karya miliknya yang lolos kurasi | |
| B25 | Kurasi | Kurator meloloskan & menampilkan karya | Lolos + tampilkan di website | Karya muncul di galeri website beserta data exposure | |
| B26 | Kurasi | Kurator mencoba mengunggah atau mengubah karya | Login kurator | Tidak ada tombol unggah/ubah (403) | |
| B27 | Hak akses | Setiap role membuka menu yang bukan miliknya | Mis. member membuka /admin/loans | Akses ditolak (403) | |
| B28 | Acara | Admin UKM membuat acara | Kuota 20, status Dipublikasikan | Acara tampil di agenda website | |
| B29 | Acara | Admin menandai hadir | Pilih peserta → "Tandai Hadir" | Status kehadiran "Hadir" | |
| B30 | Laporan | Filter laporan kegiatan per periode | Dari–sampai bulan ini | Hanya kegiatan pada periode itu | |
| B31 | Laporan | Unduh CSV laporan kegiatan | Tombol "Unduh CSV" | File CSV terunduh sesuai filter | |
| B32 | Laporan | Admin UKM membuka insight karya | Login admin UKM | Statistik & grafik tampil | |

## 3. UAT (User Acceptance Test)

Responden dari tiap aktor (mis. 1 Admin UKM, 1 Admin Inventaris, 1 Kurator, 5 Member, 2 Anggota UKM lain)
mencoba skenario sesuai perannya, lalu mengisi kuesioner skala Likert 1–5
(1 = sangat tidak setuju, 5 = sangat setuju).

| No | Pernyataan | 1 | 2 | 3 | 4 | 5 |
|---|---|---|---|---|---|---|
| U01 | Tampilan website mudah dipahami | | | | | |
| U02 | Formulir pendaftaran calon anggota mudah diisi | | | | | |
| U03 | Saya mudah menemukan barang yang tersedia untuk dipinjam (anggota UKM lain) | | | | | |
| U04 | Proses pengajuan dan pengembalian barang jelas | | | | | |
| U05 | Status dan riwayat peminjaman membantu melacak barang | | | | | |
| U06 | Mengunggah karya ke galeri mudah dilakukan | | | | | |
| U07 | Proses kurasi karya membantu menjaga kualitas galeri | | | | | |
| U08 | Pengelolaan acara dan peserta mudah dilakukan (admin UKM) | | | | | |
| U09 | Laporan kegiatan dan insight karya bermanfaat bagi pengurus | | | | | |
| U10 | Secara keseluruhan sistem ini membantu kegiatan UKM | | | | | |

Persentase kelayakan = (total skor ÷ skor maksimum) × 100%.
Interpretasi: 0–20% sangat kurang, 21–40% kurang, 41–60% cukup, 61–80% baik, 81–100% sangat baik.
