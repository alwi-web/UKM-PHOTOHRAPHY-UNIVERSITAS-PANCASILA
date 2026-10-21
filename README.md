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

## Aktor & hak akses

| Fitur | Admin UKM | Admin Inventaris | Kurator | Member UKM Fotografi | Anggota UKM Lain |
|---|:-:|:-:|:-:|:-:|:-:|
| Lihat daftar barang & stok | ✓ | ✓ | ✓ | ✓ | ✓ |
| Kelola data barang | ✓ | ✓ | | | |
| Ajukan peminjaman (milik sendiri) | ✓ | ✓ | ✓ | ✓ | ✓ |
| Serahkan / tolak / terima kembali barang | ✓ | ✓ | | | |
| Lihat semua peminjaman & riwayat per barang | ✓ | ✓ | | | |
| Unggah karya | ✓ | | ✓ | ✓ | |
| Kurasi & pilih karya untuk website | ✓ | | ✓ | | |
| Kelola kategori galeri | ✓ | | ✓ | | |
| CRUD acara, kelola peserta & kehadiran | ✓ | | | | |
| Ikut / batal ikut acara | ✓ | ✓ | ✓ | ✓ | ✓ |
| Manajemen user, role, verifikasi pendaftar, tunjuk kurator | ✓ | | | | |
| Laporan daftar kegiatan (+ unduh CSV) | ✓ | | | | |
| Laporan insight karya | ✓ | | ✓ | | |

Hak akses diatur di `app/Policies`.

## Fitur

**Peminjaman inventaris** (`/admin/items`, `/admin/loans`)
- Daftar barang dengan jumlah total, jumlah dipinjam, dan jumlah tersedia.
- Tab peminjaman: Diajukan, Sedang Dipinjam, Terlambat, Selesai.
- Riwayat peminjam per barang, jadi bisa dilacak siapa yang meminjam.
- Tanggal mulai dipinjam dan batas pengembalian; yang lewat batas ditandai merah.
- Kondisi barang dicatat sebelum dipinjam dan sesudah dikembalikan. Kondisi barang di inventaris ikut diperbarui jika rusak.
- Jumlah pinjam divalidasi terhadap stok yang tersedia.

**Galeri karya online** (`/admin/artworks`)
- Yang boleh mengunggah: Member UKM Fotografi, Kurator, dan Admin UKM.
- Setiap karya punya kategori, caption, link Instagram, dan link TikTok.
- Kurasi: kurator meloloskan atau menolak karya dengan catatan, lalu memilih karya yang tampil di website.
- Jumlah dilihat tercatat saat pengunjung membuka foto di galeri website.

**Acara & kegiatan** (`/admin/events`)
- CRUD acara: nama, jenis, lokasi, waktu mulai/selesai, deskripsi, kuota, poster, status, dan buka/tutup pendaftaran.
- Anggota event: anggota mendaftar sendiri. Admin bisa menambah atau mengeluarkan peserta dan menandai kehadiran.

**Manajemen user** (`/admin/users`)
- Admin bisa menambah user dan mengatur role.
- Pendaftaran anggota baru dari website: nama, NIM, foto, fakultas, angkatan, WhatsApp, dan password. Pendaftar berstatus *Menunggu Verifikasi* sampai disetujui admin.
- Admin bisa menunjuk atau mencabut kurator.

**Laporan** (`/admin/event-report`, `/admin/artwork-insight`)
- Daftar kegiatan dengan jumlah peserta dan persentase kehadiran, bisa difilter per periode, dan bisa diunduh sebagai CSV.
- Insight karya: total karya, tingkat lolos kurasi, total dilihat, fotografer paling produktif, grafik per kategori, unggahan per bulan, dan karya terpopuler.

**Website publik** (`/`): profil UKM, galeri karya pilihan kurator, agenda mendatang, pengurus, formulir pendaftaran, dan kontak.

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

Buka http://localhost:8000 untuk website dan http://localhost:8000/admin untuk panel.

### Akun demo (dari seeder, password semua: `password`)

| Role | Email |
|---|---|
| Admin UKM | admin@ukmfoto.test |
| Admin Inventaris | inventaris@ukmfoto.test |
| Kurator | kurator@ukmfoto.test |
| Member UKM Fotografi | member@ukmfoto.test |
| Anggota UKM Lain | tamu@ukmfoto.test |

Foto karya di data demo memakai placeholder dari picsum.photos. Ganti dengan unggahan asli lewat panel.

## Pengujian otomatis

```bash
./vendor/bin/pest
```

Tes ada di `tests/Feature` (pendaftaran, halaman depan, inventaris, galeri & kurasi, acara & laporan, manajemen user) dan `tests/Unit`.

## Struktur penting

```
app/Enums/                 role & status (label Indonesia, warna badge)
app/Models/                User, Item, Loan, Category, Artwork, Event
app/Policies/              hak akses per role
app/Filament/Resources/    halaman CRUD panel admin
app/Filament/Pages/        laporan kegiatan & insight karya
app/Filament/Widgets/      ringkasan dasbor & grafik
app/Http/Controllers/      website publik & pendaftaran
resources/views/home.blade.php   halaman depan website
public/css/style.css, public/js/main.js   aset website publik
database/seeders/          data demo
```

> `index.html`, `css/`, `js/`, dan `assets/` di root adalah versi statis awal (sebelum Laravel). Isinya sudah dipindah ke
> `resources/views/home.blade.php` dan `public/`, jadi folder lama tersebut boleh dihapus.
