<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>{{ $profile->name }} — UKM Fotografi Universitas Pancasila</title>
  <meta name="description" content="UKM Fotografi Universitas Pancasila — wadah mahasiswa untuk belajar, berkarya, dan berbagi cerita lewat lensa." />
  <link rel="icon" href="{{ asset('images/photoup-logo.svg') }}" type="image/svg+xml" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Instrument+Serif:ital@1&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
  <script>document.documentElement.classList.add('js')</script>
</head>
<body>

  <header class="topbar">
    <div class="topbar-inner">
      <a href="#beranda" class="brand">
        <img src="{{ asset('images/photoup-logo.svg') }}" alt="" width="36" height="36" />
        <span>PHOTO <b>UP</b></span>
      </a>
      <nav class="nav" aria-label="Menu utama">
        <a href="#profil">Profil</a>
        <a href="#galeri">Galeri</a>
        <a href="#kegiatan">Kegiatan</a>
        <a href="#kontak">Kontak</a>
      </nav>
      <div class="nav-actions">
        <a href="{{ url('/admin') }}" class="nav-login"><x-glyph name="login" :size="16" /> Masuk</a>
        <a href="#daftar" class="btn btn-sm">Daftar</a>
      </div>
    </div>
  </header>

  <main>
    {{-- ================= HERO ================= --}}
    <section class="hero" id="beranda">
      <div class="hero-grid-bg" aria-hidden="true"></div>
      <div class="container hero-split">
        <div class="hero-copy">
          <p class="hello reveal"><span class="wave" aria-hidden="true">👋</span> Hai!</p>
          <h1 class="reveal">Selamat Datang <span>di UKM <span class="mark">PHOTO UP</span> KMUP</span></h1>
          @if ($profile->tagline)
            <p class="lead reveal">{{ $profile->tagline }}</p>
          @endif
          <div class="actions reveal">
            <a href="#galeri" class="btn">Lihat Karya <x-glyph name="arrow-right" :size="18" /></a>
          </div>
        </div>

        {{-- mockup HP di atas bentuk-bentuk warna --}}
        <div class="hero-phone reveal">
          <div class="blobs" aria-hidden="true">
            <span class="blob blob-1"></span>
            <span class="blob blob-2"></span>
            <span class="blob blob-3"></span>
            <span class="blob blob-4"></span>
            <span class="blob blob-5"></span>
          </div>
          <div class="phone">
            <span class="phone-notch" aria-hidden="true"></span>
            <p class="phone-text">Yuk berkenalan dengan kami, belajar fotografi dan organisasi bersama!</p>
            <a href="#profil" class="phone-btn" aria-label="Kenalan dengan kami"><x-glyph name="chevron-right" :size="20" /></a>
          </div>
        </div>
      </div>

    </section>

    {{-- ================= PROFIL ================= --}}
    <section class="section" id="profil">
      {{-- di HP: tiap bagian tampil sebagai tombol, isinya dibuka saat diketuk --}}
      <button type="button" class="section-toggle" aria-expanded="false" aria-controls="profil-body">
        <span class="toggle-icon"><x-glyph name="users" /></span>
        <span class="toggle-text"><strong>Profil</strong><small>Sejarah, visi &amp; misi, struktur BPH PhotoUP</small></span>
        <x-glyph name="chevron-right" class="toggle-chevron" />
      </button>
      <div id="profil-body" class="section-body container">
        <div class="section-head reveal">
          <span class="eyebrow"><x-glyph name="users" :size="14" /> Profil</span>
          <h2>Profil {{ $profile->name }}</h2>
          @if ($profile->about)
            <p>{{ $profile->about }}</p>
          @endif
        </div>

        @if ($profile->historyParagraphs())
          <article class="card card-history reveal">
            <span class="card-icon"><x-glyph name="history" /></span>
            <h3>Sejarah {{ $profile->name }}</h3>
            @foreach ($profile->historyParagraphs() as $paragraph)
              <p>{{ $paragraph }}</p>
            @endforeach
          </article>
        @endif

        @if ($profile->vision || $profile->missions)
          <div class="vm-grid">
            @if ($profile->vision)
              <article class="card card-vision reveal">
                <span class="card-icon"><x-glyph name="eye" /></span>
                <h3>Visi</h3>
                <p class="vision">{{ $profile->vision }}</p>
              </article>
            @endif

            @if ($profile->missions)
              <article class="card card-mission reveal">
                <span class="card-icon"><x-glyph name="target" /></span>
                <h3>Misi</h3>
                <ol class="missions">
                  @foreach ($profile->missions as $mission)
                    <li><x-glyph name="check" :size="16" /><span>{{ $mission }}</span></li>
                  @endforeach
                </ol>
              </article>
            @endif
          </div>
        @endif

        @if ($profile->core_members || $profile->divisions)
          <div class="card card-chart reveal">
            <h3>Struktur BPH PhotoUP</h3>
            <p class="card-sub">Badan Pengurus Harian dan anggota divisi {{ $profile->name }}.</p>
            <div class="chart">
              @php($levels = $profile->chartLevels())
              @foreach ($levels as $level => $nodes)
                @if ($level > 0)
                  <span class="chart-line" aria-hidden="true"></span>
                @endif
                <div @class([
                  'chart-level',
                  'chart-row' => count($nodes) > 1,
                  'chart-pair' => count($nodes) === 2,
                  'chart-top' => $level === 0,
                  // garis tengah menembus baris bercabang (mis. Sekretaris–Bendahara) menuju tingkat berikutnya
                  'chart-branch' => count($nodes) > 1 && count($nodes) % 2 === 0 && $level < count($levels) - 1,
                ])>
                  @foreach ($nodes as $node)
                    <div class="chart-cell">
                      <div class="node">
                        <strong>{{ $node['title'] }}</strong>
                        @if ($person = $node['person'])
                          {{-- pengurus inti: foto berbingkai, lalu nama dan NPM (dari akun anggota) --}}
                          <div class="node-person">
                            @if ($person['photo'])
                              <img src="{{ $person['photo'] }}" alt="Foto {{ $person['name'] }}" class="member-photo" width="64" height="64" loading="lazy" />
                            @else
                              <span class="member-photo member-initial" aria-hidden="true">{{ mb_substr($person['name'], 0, 1) }}</span>
                            @endif
                            <span class="person-name">{{ $person['name'] }}</span>
                            @if ($person['npm'])<small>{{ $person['npm'] }}</small>@endif
                          </div>
                        @endif
                        @if ($node['note'])<small class="node-note">{{ $node['note'] }}</small>@endif
                        @if ($node['members'])
                          <ul class="node-members">
                            @foreach ($node['members'] as $member)
                              <li>
                                @if ($member['photo'])
                                  <img src="{{ $member['photo'] }}" alt="Foto {{ $member['name'] }}" class="member-photo" width="64" height="64" loading="lazy" />
                                @else
                                  <span class="member-photo member-initial" aria-hidden="true">{{ mb_substr($member['name'], 0, 1) }}</span>
                                @endif
                                <strong>{{ $member['name'] }}</strong>
                                @if ($member['npm'])<small>{{ $member['npm'] }}</small>@endif
                              </li>
                            @endforeach
                          </ul>
                        @endif
                      </div>
                    </div>
                  @endforeach
                </div>
              @endforeach
            </div>
          </div>
        @endif
      </div>
    </section>

    {{-- ================= GALERI ================= --}}
    <section class="section" id="galeri">
      <button type="button" class="section-toggle" aria-expanded="false" aria-controls="galeri-body">
        <span class="toggle-icon"><x-glyph name="image" /></span>
        <span class="toggle-text"><strong>Karya Anggota</strong><small>Galeri foto pilihan kurator</small></span>
        <x-glyph name="chevron-right" class="toggle-chevron" />
      </button>
      <div id="galeri-body" class="section-body container">
        <div class="section-head reveal">
          <span class="eyebrow"><x-glyph name="image" :size="14" /> Galeri</span>
          <h2>Karya Anggota</h2>
          <p>Karya pilihan kurator. Klik foto untuk melihat cerita dan pengaturan kameranya.</p>
        </div>

        @if ($artworks->isEmpty())
          <p class="empty">Belum ada karya yang ditampilkan.</p>
        @else
          <div class="filters" id="filterBar">
            <button type="button" class="chip active" data-filter="semua">Semua</button>
            @foreach ($categories as $category)
              <button type="button" class="chip" data-filter="{{ $category->slug }}">{{ $category->name }}</button>
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
                data-exposure="{{ $artwork->exposureSummary() }}"
                data-exposure-notes="{{ $artwork->exposure_notes }}"
                data-instagram="{{ $artwork->instagram_url }}"
                data-tiktok="{{ $artwork->tiktok_url }}"
                data-view-url="{{ route('artworks.view', $artwork) }}">
                <img src="{{ $artwork->imageUrl() }}" alt="{{ $artwork->title }}" loading="lazy" />
                <figcaption>
                  <strong>{{ $artwork->title }}</strong>
                  <span>{{ $artwork->user->name }}@if ($artwork->category) · {{ $artwork->category->name }}@endif</span>
                </figcaption>
              </figure>
            @endforeach
          </div>
        @endif
      </div>
    </section>

    {{-- ================= KEGIATAN ================= --}}
    <section class="section" id="kegiatan">
      <button type="button" class="section-toggle" aria-expanded="false" aria-controls="kegiatan-body">
        <span class="toggle-icon"><x-glyph name="calendar" /></span>
        <span class="toggle-text"><strong>Kegiatan</strong><small>Agenda kegiatan mendatang</small></span>
        <x-glyph name="chevron-right" class="toggle-chevron" />
      </button>
      <div id="kegiatan-body" class="section-body container">
        <div class="section-head reveal">
          <span class="eyebrow"><x-glyph name="calendar" :size="14" /> Agenda</span>
          <h2>Kegiatan Mendatang</h2>
          <p>Hunting bareng, workshop, sampai pameran. Catat tanggalnya!</p>
        </div>
        @if ($events->isEmpty())
          <p class="empty">Belum ada kegiatan terjadwal.</p>
        @else
          <div class="events">
            @foreach ($events as $event)
              <article class="event reveal">
                <time class="event-date" datetime="{{ $event->start_at->toDateString() }}">
                  <b>{{ $event->start_at->translatedFormat('d') }}</b>
                  <span>{{ $event->start_at->translatedFormat('M Y') }}</span>
                </time>
                <div class="event-body">
                  <span class="tag">{{ $event->type->getLabel() }}</span>
                  <h3>{{ $event->title }}</h3>
                  <ul class="event-meta">
                    <li><x-glyph name="map-pin" :size="15" /> {{ $event->location }}</li>
                    <li><x-glyph name="clock" :size="15" /> {{ $event->start_at->format('H.i') }}@if ($event->end_at)–{{ $event->end_at->format('H.i') }}@endif</li>
                  </ul>
                </div>
              </article>
            @endforeach
          </div>
        @endif
      </div>
    </section>

    {{-- ================= DAFTAR ================= --}}
    <section id="daftar" @class(['section', 'is-open' => session('registered') || $errors->any()])>
      <button type="button" class="section-toggle" aria-expanded="false" aria-controls="daftar-body">
        <span class="toggle-icon"><x-glyph name="user-plus" /></span>
        <span class="toggle-text"><strong>Daftar Calon Anggota</strong><small>Formulir pendaftaran anggota baru</small></span>
        <x-glyph name="chevron-right" class="toggle-chevron" />
      </button>
      <div id="daftar-body" class="section-body container">
        <div class="join">
          <div class="join-info">
            <span class="eyebrow eyebrow-dark"><x-glyph name="user-plus" :size="14" /> Gabung</span>
            <h2>Daftar Calon Anggota</h2>
            <p>
              Khusus mahasiswa Universitas Pancasila yang belum pernah mengikuti kaderisasi UKM Fotografi.
              Tidak perlu punya kamera mahal — yang penting mau belajar.
            </p>
            <ol class="steps">
              <li><b>1</b><span><strong>Isi formulir</strong>Nama, NPM, fakultas, dan nomor WhatsApp.</span></li>
              <li><b>2</b><span><strong>Dihubungi pengurus</strong>Info selanjutnya dikirim lewat WhatsApp.</span></li>
              <li><b>3</b><span><strong>Ikut kaderisasi</strong>Mulai belajar dan berkarya bareng PhotoUP.</span></li>
            </ol>
          </div>

          <form class="form" id="registerForm" method="POST" action="{{ route('register') }}">
            @csrf
            <h3>Formulir Pendaftaran Calon Anggota</h3>

            @if (session('registered'))
              <p class="notice notice-success">{{ session('registered') }}</p>
            @endif
            @if ($errors->any())
              <p class="notice notice-error">Periksa kembali isian yang ditandai merah.</p>
            @endif

            <div class="field @error('name') invalid @enderror">
              <label for="name">Nama Lengkap</label>
              <input type="text" id="name" name="name" value="{{ old('name') }}" autocomplete="name" placeholder="Nama sesuai KTM" required />
              @error('name')<small class="error">{{ $message }}</small>@enderror
            </div>
            <div class="field-row">
              <div class="field @error('npm') invalid @enderror">
                <label for="npm">NPM</label>
                <input type="text" id="npm" name="npm" value="{{ old('npm') }}" inputmode="numeric" pattern="\d{6,20}" placeholder="Contoh: 4522210001" required />
                @error('npm')<small class="error">{{ $message }}</small>@enderror
              </div>
              <div class="field @error('faculty') invalid @enderror">
                <label for="faculty">Fakultas</label>
                <select id="faculty" name="faculty" required>
                  <option value="">Pilih fakultas</option>
                  @foreach (config('ukm.faculties') as $faculty)
                    <option @selected(old('faculty') === $faculty)>{{ $faculty }}</option>
                  @endforeach
                </select>
                @error('faculty')<small class="error">{{ $message }}</small>@enderror
              </div>
            </div>
            <div class="field @error('phone') invalid @enderror">
              <label for="phone">Nomor WhatsApp</label>
              <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" autocomplete="tel" required />
              @error('phone')<small class="error">{{ $message }}</small>@enderror
            </div>
            <div class="field field-check @error('not_cadre_yet') invalid @enderror">
              <label for="not_cadre_yet">
                <input type="checkbox" id="not_cadre_yet" name="not_cadre_yet" value="1" @checked(old('not_cadre_yet')) required />
                Saya belum pernah mendaftar di PhotoUP.
              </label>
              @error('not_cadre_yet')<small class="error">{{ $message }}</small>@enderror
            </div>
            <button type="submit" class="btn btn-block">Kirim Pendaftaran <x-glyph name="arrow-right" :size="18" /></button>
          </form>
        </div>
      </div>
    </section>

    {{-- ================= KONTAK ================= --}}
    <section class="section" id="kontak">
      <button type="button" class="section-toggle" aria-expanded="false" aria-controls="kontak-body">
        <span class="toggle-icon"><x-glyph name="mail" /></span>
        <span class="toggle-text"><strong>Kontak</strong><small>Alamat, email, dan media sosial</small></span>
        <x-glyph name="chevron-right" class="toggle-chevron" />
      </button>
      <div id="kontak-body" class="section-body container">
        <div class="section-head reveal">
          <span class="eyebrow"><x-glyph name="mail" :size="14" /> Kontak</span>
          <h2>Ngobrol bareng kami</h2>
          <p>Ada pertanyaan soal pendaftaran, kerja sama, atau liputan? Hubungi kami.</p>
        </div>
        <ul class="contact">
          @if ($profile->address)
            <li class="reveal"><span class="card-icon"><x-glyph name="map-pin" /></span><span><small>Alamat</small>{{ $profile->address }}</span></li>
          @endif
          @if ($profile->email)
            <li class="reveal"><span class="card-icon"><x-glyph name="mail" /></span><span><small>Email</small><a href="mailto:{{ $profile->email }}">{{ $profile->email }}</a></span></li>
          @endif
          @if ($profile->instagram)
            <li class="reveal"><span class="card-icon"><x-glyph name="instagram" /></span><span><small>Instagram</small><a href="https://instagram.com/{{ ltrim($profile->instagram, '@') }}" target="_blank" rel="noopener">{{ '@'.ltrim($profile->instagram, '@') }}</a></span></li>
          @endif
          @if ($profile->tiktok)
            <li class="reveal"><span class="card-icon"><x-glyph name="music" /></span><span><small>TikTok</small><a href="https://www.tiktok.com/@{{ ltrim($profile->tiktok, '@') }}" target="_blank" rel="noopener">{{ '@'.ltrim($profile->tiktok, '@') }}</a></span></li>
          @endif
          @if ($profile->phone)
            <li class="reveal"><span class="card-icon"><x-glyph name="phone" /></span><span><small>Telepon</small>{{ $profile->phone }}</span></li>
          @endif
        </ul>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="container footer-inner">
      <a href="#beranda" class="brand">
        <span>{{ $profile->name }}</span>
      </a>
      <p>&copy; {{ now()->year }} UKM Fotografi Universitas Pancasila</p>
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
