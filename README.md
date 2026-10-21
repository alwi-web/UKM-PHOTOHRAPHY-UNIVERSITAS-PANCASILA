# UKM Fotografi Universitas Pancasila

Website profil UKM Fotografi Universitas Pancasila — dibuat dengan HTML, CSS, dan JavaScript murni (tanpa framework/build).

## Fitur
- Navbar responsif (menu hamburger di mobile) + highlight menu aktif saat scroll
- Hero dengan statistik animasi (counter)
- Tentang, Visi & Misi
- Divisi (Landscape, Portrait, Videografi, Jurnalistik Foto, Editing)
- Galeri dengan filter kategori + lightbox (navigasi tombol & keyboard)
- Agenda kegiatan
- Pengurus inti
- Formulir pendaftaran dengan validasi (NPM, email, WhatsApp, dll.)
- Kontak + Google Maps, footer, tombol kembali ke atas

## Struktur
```
index.html      halaman utama
css/style.css   seluruh styling
js/main.js      data konten + interaksi
assets/logo.svg logo UKM
```

## Menjalankan
Buka `index.html` langsung di browser, atau jalankan server lokal:
```
npx serve .        # atau: python -m http.server
```

## Mengubah konten
Semua data (divisi, galeri, kegiatan, pengurus) ada di bagian **DATA** di atas `js/main.js`.
Foto masih memakai placeholder dari picsum.photos — ganti dengan foto asli (misalnya simpan di `assets/img/`).

Formulir pendaftaran saat ini menyimpan data di `localStorage` browser. Untuk menyimpan ke server,
ganti bagian yang ditandai di fungsi `initForm()` dengan request ke backend / Google Apps Script / Formspree.
