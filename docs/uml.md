# UML — Sistem Informasi UKM Fotografi Universitas Pancasila (PhotoUP 94)

Diagram dibuat dari kode aplikasi (model, policy, controller, dan halaman panel Filament).
Versi gambar ada di folder [`uml/`](uml/) dan semuanya digabung di [`uml.pdf`](uml.pdf).

| No | Diagram | Gambar |
|---|---|---|
| 1 | Use Case Diagram | `uml/01-use-case.svg`, `uml/01-use-case.png` |
| 2 | Activity Diagram — Pendaftaran Calon Anggota | `uml/02-activity-pendaftaran.png` |
| 3 | Activity Diagram — Login | `uml/03-activity-login.png` |
| 4 | Activity Diagram — Peminjaman Barang | `uml/04-activity-peminjaman.png` |
| 5 | Activity Diagram — Unggah & Kurasi Karya | `uml/05-activity-kurasi.png` |
| 6 | Sequence Diagram — Login | `uml/06-sequence-login.png` |
| 7 | Sequence Diagram — Pendaftaran Calon Anggota | `uml/07-sequence-pendaftaran.png` |
| 8 | Sequence Diagram — Pengajuan & Proses Peminjaman | `uml/08-sequence-peminjaman.png` |
| 9 | Sequence Diagram — Unggah & Kurasi Karya | `uml/09-sequence-kurasi.png` |
| 10 | Class Diagram | `uml/10-class.png` |

## 1. Use Case Diagram

Aktor: **Pengunjung** (termasuk calon anggota), **Member UKM Fotografi**, **Anggota UKM Lain**, **Kurator**,
**Admin Inventaris**, dan **Admin UKM**. Admin UKM dapat melakukan semua use case aktor lain
(digambar sebagai generalisasi ke Admin Inventaris dan Kurator, dan ditulis pada label aktornya). Semua use case di panel mensyaratkan Login.

![Use Case Diagram](uml/01-use-case.svg)

Ringkasan use case per aktor:

| Aktor | Use case |
|---|---|
| Pengunjung / Calon Anggota | Melihat website; mendaftar sebagai calon anggota |
| Member UKM Fotografi | Login/Logout; mengunggah karya; melihat hasil kurasi; melihat barang tersedia; mengajukan, mengubah, atau membatalkan peminjaman |
| Anggota UKM Lain | Login/Logout; melihat barang tersedia; mengajukan, mengubah, atau membatalkan peminjaman |
| Kurator | Login/Logout; mengkurasi karya |
| Admin Inventaris | Login/Logout; melihat barang tersedia; mengelola barang; memproses peminjaman |
| Admin UKM | Semua use case di atas (generalisasi), ditambah mengelola user & kurator, memproses pendaftar, mengelola acara, kategori, profil & struktur BPH, serta melihat laporan |

Mengajukan dan mengubah peminjaman selalu menyertakan (*include*) pengecekan bentrok tanggal.

## 2. Activity Diagram — Pendaftaran Calon Anggota

<!-- uml:02-activity-pendaftaran -->
```mermaid
flowchart TB
    subgraph L1["Calon Anggota"]
        S((●)) --> A1[Membuka halaman /daftar]
        A1 --> A2[Mengisi nama, NPM, fakultas,<br/>nomor WhatsApp, centang pernyataan]
        A2 --> A3[Menekan Kirim Pendaftaran]
        A5[Memperbaiki isian yang ditandai merah] --> A3
        A7[Melihat konfirmasi<br/>'Pendaftaran terkirim!']
    end
    subgraph L2["Sistem"]
        A3 --> B1{Data valid &<br/>NPM belum terdaftar?}
        B1 -- Tidak --> A5
        B1 -- Ya --> B2[Menyimpan pendaftar<br/>status Baru]
        B2 --> A7
        B5[Membuat akun Member aktif<br/>dari data pendaftar] --> B6[Status pendaftar: Diterima]
        B7[Status pendaftar: Ditolak<br/>+ catatan]
    end
    subgraph L3["Admin UKM"]
        B2 --> C1[Melihat pendaftar baru di panel]
        C1 --> C2[Menghubungi lewat WhatsApp,<br/>tandai Dihubungi]
        C2 --> C3{Lulus kaderisasi?}
        C3 -- Ya --> C4[Terima & isi email + password] --> B5
        C3 -- Tidak --> C5[Tolak] --> B7
    end
    B6 --> E((◉))
    B7 --> E
    A7 --> E2((◉))
```

## 3. Activity Diagram — Login

<!-- uml:03-activity-login -->
```mermaid
flowchart TB
    subgraph L1["Pengguna"]
        S((●)) --> A1[Membuka /login<br/>atau tombol Masuk]
        A1 --> A2[Mengisi email & password]
        A2 --> A3[Menekan Masuk]
        A6[Membaca pesan kesalahan] --> A2
    end
    subgraph L2["Sistem"]
        A3 --> B0{Lebih dari 5 percobaan<br/>dalam 1 menit?}
        B0 -- Ya --> B0a[Tolak sementara] --> A6
        B0 -- Tidak --> B1{Email & password cocok?}
        B1 -- Tidak --> B1a[Pesan: Email atau password salah] --> A6
        B1 -- Ya --> B2{Status akun Aktif?}
        B2 -- Tidak --> B2a[Logout otomatis +<br/>pesan akun belum aktif] --> A6
        B2 -- Ya --> B3[Membuat sesi baru]
        B3 --> B4[Menampilkan dasbor & menu<br/>sesuai role]
    end
    B4 --> E((◉))
```

## 4. Activity Diagram — Peminjaman Barang

<!-- uml:04-activity-peminjaman -->
```mermaid
flowchart TB
    subgraph L1["Peminjam (Member / Anggota UKM Lain)"]
        S((●)) --> A1[Membuka Daftar Barang /<br/>Cek ketersediaan per tanggal]
        A1 --> A2[Ajukan Peminjaman: data diri,<br/>tanggal dari–sampai, keperluan]
        A2 --> A3[Menambah / menghapus barang<br/>beserta jumlahnya]
        A3 --> A4[Simpan pengajuan]
        A6[Mengubah jumlah / tanggal] --> A4
        A9[Mengembalikan barang]
    end
    subgraph L2["Sistem"]
        A4 --> B1{Jumlah tersedia pada<br/>tanggal tersebut cukup?}
        B1 -- Tidak --> B1a[Tampilkan bentrok:<br/>peminjaman lain di tanggal itu] --> A6
        B1 -- Ya --> B2[Simpan peminjaman<br/>status Diajukan]
        B4[Status: Dipinjam,<br/>catat kondisi sebelum]
        B5[Status: Ditolak]
        B6[Status: Dikembalikan,<br/>catat kondisi sesudah & tanggal kembali]
    end
    subgraph L3["Admin Inventaris"]
        B2 --> C1[Memeriksa pengajuan]
        C1 --> C2{Disetujui?}
        C2 -- Tidak --> C3[Tolak + catatan] --> B5
        C2 -- Ya --> C4[Serahkan barang,<br/>isi kondisi tiap barang] --> B4
        B4 --> A9
        A9 --> C5[Terima kembali,<br/>isi kondisi tiap barang] --> B6
    end
    B5 --> E((◉))
    B6 --> E
```

## 5. Activity Diagram — Unggah & Kurasi Karya

<!-- uml:05-activity-kurasi -->
```mermaid
flowchart TB
    subgraph L1["Member UKM"]
        S((●)) --> A1[Unggah Karya: foto, judul, kategori,<br/>caption, aperture, shutter, ISO,<br/>penjelasan exposure, link IG/TikTok]
        A1 --> A2[Simpan]
        A4[Melengkapi data] --> A2
        A8[Melihat hasil kurasi & catatan kurator]
    end
    subgraph L2["Sistem"]
        A2 --> B1{Data wajib lengkap?}
        B1 -- Tidak --> A4
        B1 -- Ya --> B2[Simpan karya<br/>status Menunggu Kurasi]
        B4[Status Lolos Kurasi]
        B5[Status Ditolak + catatan]
        B6[Karya tampil di galeri website]
    end
    subgraph L3["Kurator"]
        B2 --> C1[Membuka tab Menunggu Kurasi,<br/>melihat pratinjau karya]
        C1 --> C2{Layak?}
        C2 -- Tidak --> C3[Tolak + catatan] --> B5
        C2 -- Ya --> C4[Loloskan] --> B4
        B4 --> C5{Tampilkan di website?}
        C5 -- Ya --> B6
    end
    B5 --> A8
    B4 --> A8
    B6 --> A8
    A8 --> E((◉))
```

## 6. Sequence Diagram — Login

<!-- uml:06-sequence-login -->
```mermaid
sequenceDiagram
    autonumber
    actor U as Pengguna
    participant V as Halaman /login
    participant C as LoginController
    participant RL as RateLimiter
    participant A as Auth (sesi)
    participant DB as Database (users)
    participant P as Panel /admin

    U->>V: Buka halaman login
    U->>V: Isi email & password, tekan Masuk
    V->>C: POST /login (email, password, remember)
    C->>C: Validasi format isian
    C->>RL: tooManyAttempts(email|IP, 5)?
    alt Terlalu banyak percobaan
        RL-->>C: ya
        C-->>V: Pesan "Terlalu banyak percobaan"
    else Masih boleh
        C->>A: attempt(email, password)
        A->>DB: Cari user & cocokkan hash password
        alt Salah
            DB-->>A: tidak cocok
            C->>RL: hit()
            C-->>V: Pesan "Email atau password salah."
        else Cocok
            A-->>C: user
            C->>C: user.canAccessPanel() (status Aktif?)
            alt Belum aktif
                C->>A: logout()
                C-->>V: Pesan "Akun kamu belum aktif"
            else Aktif
                C->>A: regenerate session
                C-->>U: Redirect ke /admin
                U->>P: Buka dasbor (menu sesuai role)
            end
        end
    end
```

## 7. Sequence Diagram — Pendaftaran Calon Anggota

<!-- uml:07-sequence-pendaftaran -->
```mermaid
sequenceDiagram
    autonumber
    actor CA as Calon Anggota
    participant V as Halaman /daftar
    participant RC as RegistrationController
    participant R as Registration
    participant DB as Database
    actor AD as Admin UKM
    participant PR as Panel: Pendaftar Calon Anggota
    participant U as User

    CA->>V: Isi nama, NPM, fakultas, WhatsApp, centang pernyataan
    V->>RC: POST /daftar
    RC->>RC: Normalisasi nomor & validasi (NPM unik, fakultas terdaftar)
    alt Tidak valid
        RC-->>V: Kembali dengan pesan kesalahan
    else Valid
        RC->>R: create(data)
        R->>DB: INSERT registrations (status baru)
        RC-->>V: Tampilkan "Pendaftaran terkirim!"
    end
    AD->>PR: Buka daftar pendaftar (tab Baru)
    AD->>PR: Klik nomor WhatsApp → Tandai Dihubungi
    PR->>R: markContacted(admin)
    alt Diterima setelah kaderisasi
        AD->>PR: Terima (isi email & password)
        PR->>R: accept(admin, email, password)
        R->>U: create(role Member, status Aktif)
        U->>DB: INSERT users
        R->>DB: UPDATE registrations (diterima, user_id)
    else Ditolak
        AD->>PR: Tolak + catatan
        PR->>R: reject(admin, catatan)
        R->>DB: UPDATE registrations (ditolak)
    end
```

## 8. Sequence Diagram — Pengajuan & Proses Peminjaman

<!-- uml:08-sequence-peminjaman -->
```mermaid
sequenceDiagram
    autonumber
    actor PJ as Peminjam
    participant F as Panel: Ajukan Peminjaman
    participant LP as LoanPolicy
    participant I as Item
    participant L as Loan / LoanItem
    participant DB as Database
    actor AI as Admin Inventaris

    PJ->>F: Buka form (data diri terisi dari akun)
    F->>LP: create(user)? (role boleh meminjam)
    LP-->>F: diizinkan
    PJ->>F: Isi tanggal dari–sampai, tambah barang & jumlah
    F->>I: availableQuantityBetween(dari, sampai)
    I->>DB: Hitung unit Diajukan/Dipinjam yang tumpang tindih
    DB-->>I: jumlah terpakai
    alt Jumlah tidak cukup
        I-->>F: sisa unit + conflictingLoans()
        F-->>PJ: Pesan bentrok tanggal
    else Cukup
        F->>L: create(loan + loanItems)
        L->>DB: INSERT loans (diajukan), loan_items
        F-->>PJ: Pengajuan tersimpan
    end
    AI->>F: Buka tab Diajukan
    alt Disetujui
        AI->>L: handOver(admin, kondisi sebelum)
        L->>DB: UPDATE status dipinjam, condition_before
        Note over PJ,AI: Barang dipakai sampai batas kembali
        AI->>L: markReturned(admin, kondisi sesudah)
        L->>DB: UPDATE status dikembalikan, returned_at, condition_after
    else Ditolak
        AI->>L: reject(admin, catatan)
        L->>DB: UPDATE status ditolak
    end
```

## 9. Sequence Diagram — Unggah & Kurasi Karya

<!-- uml:09-sequence-kurasi -->
```mermaid
sequenceDiagram
    autonumber
    actor M as Member UKM
    participant F as Panel: Karya Foto
    participant AP as ArtworkPolicy
    participant A as Artwork
    participant DB as Database
    actor K as Kurator
    participant W as Website (galeri)

    M->>F: Unggah Karya (foto, kategori, caption, exposure, link IG/TikTok)
    F->>AP: create(user)? (role boleh mengunggah)
    AP-->>F: diizinkan
    F->>A: create(data, user_id)
    A->>DB: INSERT artworks (status pending)
    K->>F: Buka tab Menunggu Kurasi
    F->>AP: curate(user)? (role kurator / admin UKM)
    AP-->>F: diizinkan
    K->>F: Pilih karya → jendela kurasi (pratinjau)
    alt Lolos
        K->>A: curate(kurator, approved, tampil?, catatan)
        A->>DB: UPDATE status approved, is_featured, curated_by, curated_at
    else Ditolak
        K->>A: curate(kurator, rejected, false, catatan)
        A->>DB: UPDATE status rejected, curator_note
    end
    M->>F: Melihat status & catatan kurator
    W->>A: published() (approved + is_featured)
    A-->>W: Daftar karya untuk galeri
```

## 10. Class Diagram

<!-- uml:10-class -->
```mermaid
classDiagram
    direction LR

    class User {
        +int id
        +string name
        +string npm
        +string email
        +string phone
        +string faculty
        +int batch_year
        +string origin_ukm
        +string photo
        +Role role
        +UserStatus status
        +canAccessPanel(panel) bool
        +hasRole(roles) bool
        +isAdminUkm() bool
        +unit() string
        +loans() HasMany
        +artworks() HasMany
        +events() BelongsToMany
    }

    class Item {
        +int id
        +string code
        +string name
        +string category
        +string brand
        +int quantity
        +ItemCondition condition
        +borrowedQuantity() int
        +availableQuantity() int
        +availableQuantityBetween(from, to) int
        +conflictingLoans(from, to) Collection
    }

    class Loan {
        +int id
        +string borrower_name
        +string borrower_npm
        +string borrower_unit
        +string purpose
        +LoanStatus status
        +datetime borrowed_at
        +datetime due_at
        +datetime returned_at
        +isOverdue() bool
        +handOver(admin, conditions) void
        +reject(admin, notes) void
        +markReturned(admin, conditions, notes) void
    }

    class LoanItem {
        +int id
        +int quantity
        +ItemCondition condition_before
        +ItemCondition condition_after
        +scopeOverlapping(from, to)
    }

    class Category {
        +int id
        +string name
        +string slug
        +artworks() HasMany
    }

    class Artwork {
        +int id
        +string title
        +string caption
        +string aperture
        +string shutter_speed
        +int iso
        +string exposure_notes
        +string image
        +string instagram_url
        +string tiktok_url
        +ArtworkStatus status
        +bool is_featured
        +string curator_note
        +int views
        +exposureSummary() string
        +imageUrl() string
        +curate(curator, status, featured, note) void
        +scopePublished()
    }

    class Event {
        +int id
        +string title
        +EventType type
        +string location
        +datetime start_at
        +datetime end_at
        +int quota
        +EventStatus status
        +participants() BelongsToMany
        +scopeUpcoming()
    }

    class EventParticipant {
        <<pivot event_user>>
        +ParticipantStatus status
    }

    class Registration {
        +int id
        +string name
        +string npm
        +string faculty
        +string phone
        +RegistrationStatus status
        +string notes
        +whatsappUrl() string
        +markContacted(admin) void
        +reject(admin, notes) void
        +accept(admin, email, password) User
    }

    class OrganizationProfile {
        +int id
        +string name
        +string tagline
        +string history
        +string vision
        +array missions
        +array core_members
        +array divisions
        +current()$ OrganizationProfile
        +eligibleMembers()$ Builder
        +historyParagraphs() array
        +chartLevels() array
    }

    class Role {
        <<enumeration>>
        AdminUkm
        AdminInventaris
        Kurator
        Member
        AnggotaLain
        +canManageInventory() bool
        +canBorrow() bool
        +canUploadArtwork() bool
        +canCurate() bool
    }

    User "1" --> "0..*" Loan : mengajukan
    User "0..1" --> "0..*" Loan : memproses
    Loan "1" *-- "1..*" LoanItem : berisi
    Item "1" --> "0..*" LoanItem : dipinjam dalam
    User "1" --> "0..*" Artwork : mengunggah
    User "0..1" --> "0..*" Artwork : mengkurasi
    Category "0..1" --> "0..*" Artwork : mengelompokkan
    User "0..1" --> "0..*" Event : membuat
    Event "1" --> "0..*" EventParticipant
    User "1" --> "0..*" EventParticipant : mengikuti
    Registration "0..1" --> "0..1" User : menjadi akun
    User "0..1" --> "0..*" Registration : memproses
    OrganizationProfile ..> User : struktur BPH (JSON)
    User ..> Role : memiliki
```
