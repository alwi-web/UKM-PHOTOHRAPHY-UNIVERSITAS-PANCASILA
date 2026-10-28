<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>@hasSection('title')@yield('title') — @endif{{ $profile->name }} · UKM Fotografi Universitas Pancasila</title>
  <meta name="description" content="UKM Fotografi Universitas Pancasila — wadah mahasiswa untuk belajar, berkarya, dan berbagi cerita lewat lensa." />
  <link rel="icon" href="{{ asset('images/photoup-logo.svg') }}" type="image/svg+xml" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
  <link rel="stylesheet" href="{{ asset('css/site.css') }}" />
  <script>document.documentElement.classList.add('js')</script>
</head>
<body>

  <header class="site-header">
    <div class="site-header-inner">
      <a href="{{ route('home') }}" class="brand" aria-label="{{ $profile->name }} — beranda">
        <img src="{{ asset('images/photoup-logo.svg') }}" alt="" width="34" height="34" />
        <span>PHOTO <b>UP</b></span>
      </a>
      <nav class="site-nav" aria-label="Menu utama">
        <a href="{{ route('profile') }}" @class(['active' => request()->routeIs('profile')])>Profil</a>
        <a href="{{ route('gallery') }}" @class(['active' => request()->routeIs('gallery')])>Galeri</a>
        <a href="{{ route('home') }}#kegiatan">Kegiatan</a>
        <a href="{{ route('home') }}#kontak">Kontak</a>
      </nav>
      <div class="site-actions">
        <button type="button" class="icon-btn" id="searchToggle" aria-expanded="false" aria-controls="searchBar" aria-label="Cari karya">
          <x-glyph name="search" :size="18" />
        </button>
        <a href="{{ route('login') }}" class="site-login">Masuk</a>
        <a href="{{ route('register.show') }}" class="btn btn-sm">Daftar</a>
      </div>
    </div>
    {{-- pencarian karya: dibuka dari ikon kaca pembesar, hasilnya di halaman Galeri --}}
    <form class="search-bar" id="searchBar" action="{{ route('gallery') }}" method="GET" role="search" hidden>
      <label for="searchInput" class="sr-only">Cari karya atau fotografer</label>
      <x-glyph name="search" :size="18" />
      <input type="search" id="searchInput" name="q" value="{{ $search ?? '' }}" placeholder="Cari judul karya atau nama fotografer" autocomplete="off" />
      <button type="submit" class="btn btn-sm">Cari</button>
    </form>
  </header>

  <main>
    @yield('content')
  </main>

  <footer class="site-footer">
    <div class="container site-footer-inner">
      <div>
        <a href="{{ route('home') }}" class="brand">
          <img src="{{ asset('images/photoup-logo.svg') }}" alt="" width="30" height="30" />
          <span>PHOTO <b>UP</b></span>
        </a>
        <p>UKM Fotografi Universitas Pancasila</p>
      </div>
      <nav aria-label="Menu bawah">
        <a href="{{ route('profile') }}">Profil</a>
        <a href="{{ route('gallery') }}">Galeri</a>
        <a href="{{ route('home') }}#kegiatan">Kegiatan</a>
        <a href="{{ route('home') }}#kontak">Kontak</a>
        <a href="{{ route('register.show') }}">Daftar Calon Anggota</a>
      </nav>
      <p class="copy">&copy; {{ now()->year }} {{ $profile->name }}</p>
    </div>
  </footer>

  <div class="lightbox" id="lightbox" aria-hidden="true">
    <button type="button" class="lb-btn lb-close" id="lbClose" aria-label="Tutup">&times;</button>
    <button type="button" class="lb-btn lb-prev" id="lbPrev" aria-label="Sebelumnya">&#8249;</button>
    <figure>
      <img id="lbImg" src="" alt="" />
      <figcaption>
        <strong id="lbTitle"></strong>
        <span id="lbAuthor"></span>
        <p id="lbCaption"></p>
        <span class="lb-exposure" id="lbExposure"></span>
        <p id="lbExposureNotes"></p>
        <span class="lb-links">
          <a id="lbInstagram" target="_blank" rel="noopener" hidden>Instagram</a>
          <a id="lbTiktok" target="_blank" rel="noopener" hidden>TikTok</a>
        </span>
      </figcaption>
    </figure>
    <button type="button" class="lb-btn lb-next" id="lbNext" aria-label="Berikutnya">&#8250;</button>
  </div>

  <script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
