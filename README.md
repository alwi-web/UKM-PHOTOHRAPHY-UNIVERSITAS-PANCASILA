# Sistem Informasi UKM Fotografi Universitas Pancasila

Website profil + panel manajemen UKM Fotografi: peminjaman inventaris, galeri karya online dengan kurasi,
acara & kegiatan, manajemen anggota, dan laporan.

| Teknologi | Versi |
|---|---|
| Laravel | 12 |
| Database | MySQL (tes memakai SQLite in-memory) |
| Panel admin | Filament 4 |
| Testing | Pest 4 |
| Metode | Waterfall |
| Pengujian | Blackbox + UAT + Pest (lihat [`docs/pengujian.md`](docs/pengujian.md)) |
| Basis data | MySQL 8, rancangan tabel di [`docs/erd.md`](docs/erd.md) (gambar: `docs/erd.png`, `docs/erd.pdf`) |
| Pemodelan | UML: use case, activity, sequence, class di [`docs/uml.md`](docs/uml.md) (gambar: `docs/uml/`, `docs/uml.pdf`) |

## Aktor & hak akses

| Role | Yang bisa dilakukan | Panel & menu |
|---|---|---|
| **Admin UKM** | Mengatur semuanya, termasuk memproses pendaftar calon anggota serta mengubah profil, sejarah, visi & misi, dan struktur organisasi | `/admin`, semua menu |
| **Admin Inventaris** | Mengelola daftar barang & jumlahnya, melihat barang yang sedang dipinjam dan siapa peminjamnya, mengatur tanggal pinjam & tanggal kembali, mencatat kondisi barang sebelum & sesudah dipinjam | `/admin`: Daftar Barang, Peminjaman |
| **Kurator** | Mengkurasi foto dan memilih yang tampil di website | **Panel Kurator** `/kurator`: dasbor antrean kurasi & grafik karya, Karya Foto (kurasi) |
| **Member UKM Fotografi** | Mengunggah foto beserta kategori, caption, segitiga exposure (aperture, shutter speed, ISO, dan deskripsinya), link Instagram; melihat foto mana yang lulus kurasi; meminjam barang seperti anggota UKM lain (data diri terisi dari akun) | `/admin`: Karya Foto, Daftar Barang, Peminjaman |
| **Anggota UKM Lain** | Melihat barang yang tersedia, mengajukan peminjaman (nama lengkap, NPM, fakultas/UKM, tanggal dari–sampai), menambah & menghapus barang dalam pengajuan selama belum diproses | **Panel Anggota UKM Lain** `/anggota`: dasbor peminjaman, Daftar Barang, Peminjaman |

Kurator dan Anggota UKM Lain punya panel sendiri; membuka panel role lain otomatis diarahkan ke panelnya. Aturan ini ada di `app/Enums/Role.php` (`panel()`), `app/Policies`, dan `app/Providers/Filament`, dan diuji di `tests/Feature/RoleAccessTest.php`.

## Fitur

**Peminjaman inventaris** (`/admin/items`, `/admin/loans`)
- Daftar barang dengan jumlah total, jumlah dipinjam, dan jumlah tersedia. Filter "Cek ketersediaan" menampilkan barang yang masih bisa dipinjam pada tanggal dari–sampai tertentu.
- **Cek bentrok tanggal**: jumlah pinjam dihitung terhadap peminjaman lain yang tanggalnya beririsan (berstatus Diajukan atau Sedang Dipinjam). Form menampilkan jadwal yang bentrok dan sisa unit; pengajuan yang melebihi sisa ditolak. Barang yang terlambat dikembalikan dianggap tetap terpakai sampai kembali. Tanggal yang bersambung (kembali 10.00, dipinjam lagi 10.00) tidak dianggap bentrok.
- Satu pengajuan bisa berisi beberapa barang. Peminjam bisa menambah dan menghapus barang selama pengajuan belum diproses.
- Data peminjam (nama, NPM, fakultas/UKM) terisi otomatis dari profil dan bisa diubah.
- Admin inventaris menyerahkan barang (mencatat kondisi sebelum), menolak, atau menerima pengembalian (mencatat kondisi sesudah). Kondisi barang di inventaris ikut diperbarui jika rusak.
- Admin inventaris bisa mengubah tanggal pinjam, tanggal kembali, dan tanggal dikembalikan.
- Tab peminjaman: Diajukan, Sedang Dipinjam, Terlambat, Selesai. Yang lewat batas kembali ditandai merah.
- Riwayat peminjam per barang di halaman detail barang.

**Galeri karya online** (`/admin/artworks`)
- Member mengunggah foto dengan kategori, caption, segitiga exposure, link Instagram, dan link TikTok (opsional).
- Member melihat status kurasi dan catatan kurator untuk setiap karyanya. Karya yang sudah dikurasi tidak bisa diubah lagi.
- Kurator meloloskan atau menolak karya, lalu memilih yang tampil di website.
- Di website, pengunjung melihat data exposure saat membuka foto. Jumlah dilihat tercatat untuk laporan.
- **Nominasi foto mingguan:** kurator atau Admin UKM menekan **Nominasikan** pada karya yang lolos kurasi (tab *Nominasi Minggu Ini* untuk melihat daftarnya). Di bagian galeri website, nominasi minggu ini tampil sebagai carousel yang bergeser otomatis (bisa digeser, berhenti saat disentuh/diarahkan kursor). Jika minggu ini belum ada nominasi, yang tampil nominasi minggu terakhir.

**Acara & kegiatan** (`/admin/events`, Admin UKM)
- CRUD acara: nama, jenis, lokasi, waktu, deskripsi, kuota, poster, dan status.
- Anggota event: tambah/keluarkan peserta dan tandai kehadiran.

**Manajemen user** (`/admin/users`, Admin UKM)
- Tambah user, atur role, tunjuk atau cabut kurator.

**Pendaftaran calon anggota** (`/admin/registrations`, menu Pendaftar Calon Anggota, Admin UKM)
- Formulir ada di halaman terpisah `/daftar` (tombol **Daftar** di website), khusus mahasiswa yang belum pernah mengikuti kaderisasi. Isiannya: nama, NPM, fakultas, nomor WhatsApp, dan centang pernyataan "Saya belum pernah mendaftar di PhotoUP".
- NPM yang sudah menjadi anggota atau sudah pernah mendaftar ditolak.
- Admin melihat pendaftar per status (Baru, Sudah Dihubungi, Diterima, Ditolak) dan bisa membuka chat WhatsApp langsung dari tabel.
- Setelah kaderisasi, "Terima & buat akun" membuat akun Member dari data pendaftar. Admin mengisi email dan password untuk diserahkan ke anggota baru.

**Laporan** (Admin UKM)
- Daftar kegiatan dengan jumlah peserta dan persentase kehadiran, bisa difilter per periode dan diunduh sebagai CSV.
- Insight karya: total karya, tingkat lolos kurasi, total dilihat, grafik per kategori, unggahan per bulan, dan karya terpopuler.

**Website publik**
- **Beranda** (`/`): slider foto besar (Nominasi Foto Mingguan, atau karya terbaru bila belum ada nominasi), profil singkat dengan ikon mengambang Profil · Visi · Misi · Struktural, 5 karya terbaru, agenda kegiatan, dan kontak. Di HP ada menu ringkas berupa tombol ke setiap halaman.
- **Profil** (`/profil`): sejarah, visi & misi, dan bagan struktur BPH.
- **Galeri** (`/galeri`): semua karya pilihan kurator, filter kategori, dan pencarian judul karya / nama fotografer (juga dari ikon kaca pembesar di header).
- Halaman **Daftar** (`/daftar`) dan **Login** (`/login`) terpisah dari website.

## Instalasi

Kebutuhan: PHP 8.2+ (ekstensi `pdo_mysql`, `intl`, `gd`/`imagick`, `zip`), Composer, MySQL 8.

```bash
git clone <repo> && cd UKM-PHOTOHRAPHY-UNIVERSITAS-PANCASILA
composer install
cp .env.example .env
php artisan key:generate

# buat database kosong "ukm_fotografi" di MySQL, sesuaikan DB_* di .env, lalu:
php artisan migrate --seed
php artisan storage:link

php artisan serve
```

Buka http://localhost:8000 untuk website. Login lewat halaman terpisah http://localhost:8000/login (tombol **Masuk** di website); setelah berhasil masuk kamu diarahkan ke panel sesuai role: http://localhost:8000/admin (Admin UKM, Admin Inventaris, Member), http://localhost:8000/kurator (Kurator), atau http://localhost:8000/anggota (Anggota UKM Lain). Membuka `/admin` tanpa login juga diarahkan ke `/login`, dan keluar dari panel kembali ke halaman login.

### Akun demo (dari seeder, password semua: `password`)

| Role | Email |
|---|---|
| Admin UKM | admin@ukmfoto.test |
| Admin Inventaris | inventaris@ukmfoto.test |
| Kurator | kurator@ukmfoto.test |
| Member UKM Fotografi | member@ukmfoto.test |
| Anggota UKM Lain | tamu@ukmfoto.test |

Foto karya di data demo memakai placeholder dari picsum.photos. Ganti dengan unggahan asli lewat panel.

### Mengubah profil UKM

Profil (nama, slogan, deskripsi, kontak), sejarah, visi & misi, dan struktur organisasi diubah oleh
**Admin UKM** lewat menu **Profil UKM** di panel (`/admin/profil-ukm`). Role lain tidak bisa membukanya.
Isi awalnya diambil dari `profile_defaults` di `config/ukm.php` saat profil pertama kali dibuka.

Struktur organisasi tampil sebagai bagan: pengurus inti pertama (Ketua Umum) di paling atas, kedua di
bawahnya, sisanya bercabang, lalu semua divisi di baris terbawah. Admin UKM **memilih orangnya dari akun
Member UKM Fotografi yang aktif**; foto, nama, dan NPM di bagan diambil dari akun tersebut, sehingga
perubahan data di menu Anggota & User langsung terbawa. Akun yang dinonaktifkan, dihapus, atau role-nya
bukan Member lagi otomatis hilang dari bagan.

## Pengujian otomatis

```bash
./vendor/bin/pest
```

Tes ada di `tests/Feature` (hak akses per role, pendaftaran, halaman depan, inventaris, galeri & kurasi, acara & laporan, manajemen user) dan `tests/Unit`.

## Struktur penting

```
app/Enums/                 role & status (label Indonesia, warna badge)
app/Models/                User, OrganizationProfile, Registration, Item, Loan, LoanItem, Category, Artwork, Event
app/Policies/              hak akses per role
app/Filament/Resources/    halaman CRUD panel admin
app/Filament/Pages/        laporan kegiatan & insight karya
app/Filament/Widgets/      ringkasan dasbor & grafik
app/Providers/Filament/    panel admin, panel kurator, panel anggota UKM lain
app/Http/Controllers/      website publik & pendaftaran
resources/views/layouts/site.blade.php   header, footer, lightbox website
resources/views/home.blade.php, profile.blade.php, gallery.blade.php   halaman website
public/css/style.css, public/css/site.css, public/js/main.js   aset website publik
database/seeders/          data demo
```
