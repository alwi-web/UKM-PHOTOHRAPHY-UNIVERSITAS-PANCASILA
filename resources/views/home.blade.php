<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>UKM Fotografi Universitas Pancasila</title>
  <meta name="description" content="UKM Fotografi Universitas Pancasila — wadah mahasiswa untuk belajar, berkarya, dan berbagi cerita lewat lensa." />
  <link rel="icon" href="{{ asset('images/logo.svg') }}" type="image/svg+xml" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
</head>
<body>

  <!-- ===== NAVBAR ===== -->
  <header class="navbar" id="navbar">
    <div class="container nav-inner">
      <a href="#beranda" class="brand">
        <img src="{{ asset('images/logo.svg') }}" alt="Logo UKM Fotografi" class="brand-logo" />
        <span>UKM Fotografi<small>Universitas Pancasila</small></span>
      </a>
      <button class="nav-toggle" id="navToggle" aria-label="Buka menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
      <nav class="nav-menu" id="navMenu">
        <a href="#beranda" class="nav-link active">Beranda</a>
        <a href="#tentang" class="nav-link">Tentang</a>
        <a href="#galeri" class="nav-link">Galeri</a>
        <a href="#kegiatan" class="nav-link">Kegiatan</a>
        <a href="#pengurus" class="nav-link">Pengurus</a>
        <a href="#kontak" class="nav-link">Kontak</a>
        <a href="{{ url('/admin') }}" class="nav-link">Masuk</a>
        <a href="#daftar" class="btn btn-primary btn-sm">Daftar</a>
      </nav>
    </div>
  </header>

  <!-- ===== HERO ===== -->
  <section class="hero" id="beranda"
    @if ($hero = $artworks->first()) style="background-image: url('{{ $hero->imageUrl() }}')" @endif>
    <div class="hero-overlay"></div>
    <div class="container hero-content">
      <p class="eyebrow">Unit Kegiatan Mahasiswa</p>
      <h1>Abadikan Momen,<br /><span class="accent">Ceritakan Dunia</span></h1>
      <p class="hero-text">
        UKM Fotografi Universitas Pancasila adalah rumah bagi mahasiswa yang ingin belajar,
        berkarya, dan berbagi cerita lewat lensa.
      </p>
      <div class="hero-actions">
        <a href="#daftar" class="btn btn-primary">Gabung Sekarang</a>
        <a href="#galeri" class="btn btn-outline">Lihat Karya</a>
      </div>
      <div class="hero-stats">
        <div><strong data-count="{{ $stats['members'] }}">0</strong><span>Anggota Aktif</span></div>
        <div><strong data-count="{{ $stats['events'] }}">0</strong><span>Kegiatan Tahun Ini</span></div>
        <div><strong data-count="{{ $stats['artworks'] }}">0</strong><span>Karya Terkurasi</span></div>
      </div>
    </div>
    <a href="#tentang" class="scroll-down" aria-label="Gulir ke bawah"><span></span></a>
  </section>

  <!-- ===== TENTANG ===== -->
  <section class="section" id="tentang">
    <div class="container about">
      <div class="about-media reveal">
        @php($aboutImages = $artworks->skip(1)->take(2)->values())
        @if ($aboutImages->count() === 2)
          <img src="{{ $aboutImages[0]->imageUrl() }}" alt="{{ $aboutImages[0]->title }}" loading="lazy" />
          <img src="{{ $aboutImages[1]->imageUrl() }}" alt="{{ $aboutImages[1]->title }}" class="about-media-small" loading="lazy" />
        @else
          <div class="about-placeholder"><img src="{{ asset('images/logo.svg') }}" alt="" /></div>
        @endif
      </div>
      <div class="about-text reveal">
        <p class="eyebrow">Tentang Kami</p>
        <h2 class="section-title">Komunitas Kreatif di Balik Lensa</h2>
        <p>
          Berdiri sebagai wadah minat dan bakat mahasiswa di bidang fotografi, kami percaya setiap
          orang punya cara unik untuk melihat dunia. Melalui kelas, hunting bersama, pameran, dan
          kolaborasi, kami tumbuh menjadi komunitas yang solid dan terus berkembang.
        </p>
        <div class="vm-grid">
          <div class="vm-card">
            <h3>Visi</h3>
            <p>Menjadi komunitas fotografi mahasiswa yang unggul, kreatif, dan berkarakter Pancasila.</p>
          </div>
          <div class="vm-card">
            <h3>Misi</h3>
            <ul>
              <li>Mengembangkan keterampilan fotografi anggota.</li>
              <li>Menghasilkan karya yang bermanfaat bagi kampus dan masyarakat.</li>
              <li>Membangun jejaring dengan komunitas kreatif lain.</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== GALERI ===== -->
  <section class="section section-alt" id="galeri">
    <div class="container">
      <div class="section-head reveal">
        <p class="eyebrow">Galeri</p>
        <h2 class="section-title">Karya Anggota</h2>
        <p class="section-sub">Karya pilihan kurator dari anggota UKM Fotografi.</p>
      </div>

      @if ($artworks->isEmpty())
        <p class="empty-state">Belum ada karya yang ditampilkan. Karya akan muncul di sini setelah dipilih kurator.</p>
      @else
        <div class="filter-bar" id="filterBar">
          <button class="filter-btn active" data-filter="semua">Semua</button>
          @foreach ($categories as $category)
            <button class="filter-btn" data-filter="{{ $category->slug }}">{{ $category->name }}</button>
          @endforeach
        </div>
        <div class="gallery" id="gallery">
          @foreach ($artworks as $artwork)
            <figure class="gallery-item" tabindex="0"
              data-kategori="{{ $artwork->category?->slug }}"
              data-src="{{ $artwork->imageUrl() }}"
              data-title="{{ $artwork->title }}"
              data-author="{{ $artwork->user->name }}"
              data-caption="{{ $artwork->caption }}"
              data-instagram="{{ $artwork->instagram_url }}"
              data-tiktok="{{ $artwork->tiktok_url }}"
              data-view-url="{{ route('artworks.view', $artwork) }}">
              <img src="{{ $artwork->imageUrl() }}" alt="{{ $artwork->title }}" loading="lazy" />
              <figcaption class="gallery-caption">
                <h4>{{ $artwork->title }}</h4>
                <span>oleh {{ $artwork->user->name }} · {{ $artwork->category?->name }}</span>
              </figcaption>
            </figure>
          @endforeach
        </div>
      @endif
    </div>
  </section>

  <!-- ===== KEGIATAN ===== -->
  <section class="section" id="kegiatan">
    <div class="container">
      <div class="section-head reveal">
        <p class="eyebrow">Agenda</p>
        <h2 class="section-title">Kegiatan Mendatang</h2>
        <p class="section-sub">Ikuti berbagai program yang kami selenggarakan sepanjang tahun. Anggota bisa mendaftar lewat panel anggota.</p>
      </div>
      <div class="event-list">
        @forelse ($events as $event)
          <article class="event reveal">
            <div class="event-date">
              <strong>{{ $event->start_at->format('d') }}</strong>
              <span>{{ $event->start_at->translatedFormat('M Y') }}</span>
            </div>
            <div class="event-body">
              <h3>{{ $event->title }}</h3>
              <p>{{ \Illuminate\Support\Str::limit(strip_tags($event->description), 160) }}</p>
              <div class="event-meta">
                <span>📍 {{ $event->location }}</span>
                <span>🕘 {{ $event->start_at->format('H.i') }}@if ($event->end_at) – {{ $event->end_at->format('H.i') }}@endif</span>
                @if ($event->quota)<span>👥 Kuota {{ $event->quota }} orang</span>@endif
              </div>
            </div>
            <span class="tag">{{ $event->type->getLabel() }}</span>
          </article>
        @empty
          <p class="empty-state">Belum ada kegiatan terjadwal. Pantau terus halaman ini!</p>
        @endforelse
      </div>
    </div>
  </section>

  <!-- ===== PENGURUS ===== -->
  @if ($board->isNotEmpty())
  <section class="section section-alt" id="pengurus">
    <div class="container">
      <div class="section-head reveal">
        <p class="eyebrow">Struktur Organisasi</p>
        <h2 class="section-title">Pengurus</h2>
        <p class="section-sub">Orang-orang di balik berjalannya UKM Fotografi periode ini.</p>
      </div>
      <div class="team-grid">
        @foreach ($board as $person)
          <article class="member reveal">
            <div class="member-photo">
              <img src="{{ $person->getFilamentAvatarUrl() ?? 'https://ui-avatars.com/api/?size=400&background=f5b301&color=111&name='.urlencode($person->name) }}" alt="{{ $person->name }}" loading="lazy" />
            </div>
            <h3>{{ $person->name }}</h3>
            <p>{{ $person->role->getLabel() }}</p>
          </article>
        @endforeach
      </div>
    </div>
  </section>
  @endif

  <!-- ===== PENDAFTARAN ===== -->
  <section class="section section-dark" id="daftar">
    <div class="container register">
      <div class="register-info reveal">
        <p class="eyebrow">Open Recruitment</p>
        <h2 class="section-title">Siap Bergabung?</h2>
        <p>
          Tidak harus punya kamera mahal atau pengalaman. Cukup bawa semangat belajar — kami bantu
          sisanya, mulai dari dasar hingga mahir.
        </p>
        <ul class="check-list">
          <li>Kelas fotografi dasar hingga lanjutan</li>
          <li>Hunting foto rutin setiap bulan</li>
          <li>Unggah karya &amp; tampil di galeri website</li>
          <li>Pinjam peralatan inventaris UKM</li>
          <li>Ikut pameran, lomba, dan workshop</li>
        </ul>
      </div>

      <form class="register-form reveal" id="registerForm" method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
        @csrf
        <h3>Formulir Pendaftaran</h3>

        @if (session('registered'))
          <p class="form-success">{{ session('registered') }}</p>
        @endif
        @if ($errors->any())
          <p class="form-failure">Periksa kembali isian yang ditandai merah.</p>
        @endif

        <div class="form-group @error('role') invalid @enderror">
          <label for="role">Jenis Keanggotaan</label>
          <select id="role" name="role" required>
            <option value="member" @selected(old('role', 'member') === 'member')>Member UKM Fotografi</option>
            <option value="anggota_lain" @selected(old('role') === 'anggota_lain')>Anggota UKM lain (kolaborasi / pinjam alat)</option>
          </select>
          @error('role')<small class="error">{{ $message }}</small>@enderror
        </div>
        <div class="form-group @error('origin_ukm') invalid @enderror" id="originUkmGroup" @if (old('role') !== 'anggota_lain') hidden @endif>
          <label for="origin_ukm">Asal UKM</label>
          <input type="text" id="origin_ukm" name="origin_ukm" value="{{ old('origin_ukm') }}" placeholder="Contoh: UKM Jurnalistik" />
          @error('origin_ukm')<small class="error">{{ $message }}</small>@enderror
        </div>

        <div class="form-row">
          <div class="form-group @error('name') invalid @enderror">
            <label for="name">Nama Lengkap</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required />
            @error('name')<small class="error">{{ $message }}</small>@enderror
          </div>
          <div class="form-group @error('nim') invalid @enderror">
            <label for="nim">NIM</label>
            <input type="text" id="nim" name="nim" value="{{ old('nim') }}" inputmode="numeric" required />
            @error('nim')<small class="error">{{ $message }}</small>@enderror
          </div>
        </div>
        <div class="form-row">
          <div class="form-group @error('faculty') invalid @enderror">
            <label for="faculty">Fakultas</label>
            <select id="faculty" name="faculty" required>
              <option value="">Pilih fakultas</option>
              @foreach (config('ukm.faculties') as $faculty)
                <option @selected(old('faculty') === $faculty)>{{ $faculty }}</option>
              @endforeach
            </select>
            @error('faculty')<small class="error">{{ $message }}</small>@enderror
          </div>
          <div class="form-group @error('batch_year') invalid @enderror">
            <label for="batch_year">Angkatan</label>
            <input type="number" id="batch_year" name="batch_year" value="{{ old('batch_year') }}" placeholder="{{ now()->year }}" min="2000" max="{{ now()->year + 1 }}" required />
            @error('batch_year')<small class="error">{{ $message }}</small>@enderror
          </div>
        </div>
        <div class="form-row">
          <div class="form-group @error('email') invalid @enderror">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required />
            @error('email')<small class="error">{{ $message }}</small>@enderror
          </div>
          <div class="form-group @error('phone') invalid @enderror">
            <label for="phone">No. WhatsApp</label>
            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" required />
            @error('phone')<small class="error">{{ $message }}</small>@enderror
          </div>
        </div>
        <div class="form-group @error('photo') invalid @enderror">
          <label for="photo">Foto Diri (maks. 2 MB)</label>
          <input type="file" id="photo" name="photo" accept="image/*" required />
          @error('photo')<small class="error">{{ $message }}</small>@enderror
        </div>
        <div class="form-row">
          <div class="form-group @error('password') invalid @enderror">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" minlength="8" required />
            @error('password')<small class="error">{{ $message }}</small>@enderror
          </div>
          <div class="form-group">
            <label for="password_confirmation">Ulangi Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" minlength="8" required />
          </div>
        </div>
        <div class="form-group @error('join_reason') invalid @enderror">
          <label for="join_reason">Alasan Bergabung</label>
          <textarea id="join_reason" name="join_reason" rows="3" placeholder="Ceritakan singkat alasanmu...">{{ old('join_reason') }}</textarea>
          @error('join_reason')<small class="error">{{ $message }}</small>@enderror
        </div>
        <button type="submit" class="btn btn-primary btn-block">Kirim Pendaftaran</button>
      </form>
    </div>
  </section>

  <!-- ===== KONTAK ===== -->
  <section class="section" id="kontak">
    <div class="container contact">
      <div class="contact-info reveal">
        <p class="eyebrow">Kontak</p>
        <h2 class="section-title">Hubungi Kami</h2>
        <p>Punya pertanyaan, ingin kolaborasi, atau butuh dokumentasi acara? Jangan ragu untuk menghubungi kami.</p>
        <ul class="contact-list">
          <li><span class="ci">📍</span> {{ config('ukm.contact.address') }}</li>
          <li><span class="ci">✉️</span> <a href="mailto:{{ config('ukm.contact.email') }}">{{ config('ukm.contact.email') }}</a></li>
          <li><span class="ci">📷</span> <a href="https://instagram.com/{{ config('ukm.contact.instagram') }}" target="_blank" rel="noopener">{{ '@'.config('ukm.contact.instagram') }}</a></li>
          <li><span class="ci">🎵</span> <a href="https://www.tiktok.com/@{{ config('ukm.contact.tiktok') }}" target="_blank" rel="noopener">{{ '@'.config('ukm.contact.tiktok') }}</a></li>
          <li><span class="ci">📱</span> {{ config('ukm.contact.phone') }}</li>
        </ul>
      </div>
      <div class="map-wrap reveal">
        <iframe
          title="Lokasi Universitas Pancasila"
          src="https://www.google.com/maps?q=Universitas+Pancasila+Jakarta&output=embed"
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"></iframe>
      </div>
    </div>
  </section>

  <!-- ===== FOOTER ===== -->
  <footer class="footer">
    <div class="container footer-inner">
      <div class="brand">
        <img src="{{ asset('images/logo.svg') }}" alt="" class="brand-logo" />
        <span>UKM Fotografi<small>Universitas Pancasila</small></span>
      </div>
      <nav class="footer-links">
        <a href="#tentang">Tentang</a>
        <a href="#galeri">Galeri</a>
        <a href="#kegiatan">Kegiatan</a>
        <a href="#daftar">Daftar</a>
        <a href="{{ url('/admin') }}">Panel Anggota</a>
      </nav>
      <p class="copyright">&copy; {{ now()->year }} UKM Fotografi Universitas Pancasila. All rights reserved.</p>
    </div>
  </footer>

  <!-- ===== LIGHTBOX ===== -->
  <div class="lightbox" id="lightbox" aria-hidden="true">
    <button class="lb-close" id="lbClose" aria-label="Tutup">&times;</button>
    <button class="lb-nav lb-prev" id="lbPrev" aria-label="Sebelumnya">&#10094;</button>
    <figure>
      <img id="lbImg" src="" alt="" />
      <figcaption>
        <strong id="lbTitle"></strong>
        <span id="lbAuthor"></span>
        <p id="lbCaption"></p>
        <span class="lb-links">
          <a id="lbInstagram" target="_blank" rel="noopener" hidden>Instagram</a>
          <a id="lbTiktok" target="_blank" rel="noopener" hidden>TikTok</a>
        </span>
      </figcaption>
    </figure>
    <button class="lb-nav lb-next" id="lbNext" aria-label="Berikutnya">&#10095;</button>
  </div>

  <a href="#beranda" class="back-to-top" id="backToTop" aria-label="Kembali ke atas">&#8593;</a>

  <script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
