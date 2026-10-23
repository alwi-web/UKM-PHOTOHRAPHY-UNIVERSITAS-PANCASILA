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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
</head>
<body>

  <header class="topbar">
    <div class="container topbar-inner">
      <a href="#beranda" class="brand">
        <img src="{{ asset('images/logo.svg') }}" alt="" width="32" height="32" />
        <span>UKM Fotografi UP</span>
      </a>
      <nav class="nav" aria-label="Menu utama">
        <a href="#tentang">Tentang</a>
        <a href="#galeri">Galeri</a>
        <a href="#kegiatan">Kegiatan</a>
        <a href="#kontak">Kontak</a>
        <a href="{{ url('/admin') }}">Masuk</a>
        <a href="#daftar" class="btn btn-sm">Daftar</a>
      </nav>
    </div>
  </header>

  <main>
    <section class="hero" id="beranda">
      <div class="container">
        <p class="label">Unit Kegiatan Mahasiswa · Universitas Pancasila</p>
        <h1>{{ $profile->name }}</h1>
        @if ($profile->tagline)
          <p class="lead">{{ $profile->tagline }}</p>
        @endif
        <div class="actions">
          <a href="#daftar" class="btn">Daftar Calon Anggota</a>
          <a href="#galeri" class="btn btn-ghost">Lihat Karya</a>
        </div>
      </div>
    </section>

    <section class="section" id="tentang">
      <div class="container narrow">
        <h2>Tentang Kami</h2>
        @if ($profile->about)
          <p>{{ $profile->about }}</p>
        @endif

        @if ($profile->historyParagraphs())
          <div class="about-block">
            <h3>Sejarah {{ $profile->name }}</h3>
            @foreach ($profile->historyParagraphs() as $paragraph)
              <p>{{ $paragraph }}</p>
            @endforeach
          </div>
        @endif

        @if ($profile->vision || $profile->missions)
          <div class="about-block">
            <h3>Visi &amp; Misi</h3>
            <dl class="vm">
              @if ($profile->vision)
                <dt>Visi</dt>
                <dd>{{ $profile->vision }}</dd>
              @endif
              @if ($profile->missions)
                <dt>Misi</dt>
                <dd>
                  <ol class="missions">
                    @foreach ($profile->missions as $mission)
                      <li>{{ $mission }}</li>
                    @endforeach
                  </ol>
                </dd>
              @endif
            </dl>
          </div>
        @endif

        @if ($profile->core_members || $profile->divisions)
          <div class="about-block">
            <h3>Struktur Organisasi</h3>
            @if ($profile->core_members)
              <p class="org-label">Pengurus Inti</p>
              <ul class="org org-core">
                @foreach ($profile->core_members as $member)
                  <li>
                    @if ($photo = \App\Models\OrganizationProfile::photoUrl($member['photo'] ?? null))
                      <img src="{{ $photo }}" alt="" class="org-photo" width="40" height="40" loading="lazy" />
                    @endif
                    <span>
                      <strong>{{ $member['position'] }}</strong>
                      @if (! empty($member['name']))<small>{{ $member['name'] }}</small>@endif
                    </span>
                  </li>
                @endforeach
              </ul>
            @endif
            @if ($profile->divisions)
              <p class="org-label">Divisi</p>
              <ul class="org">
                @foreach ($profile->divisions as $division)
                  <li>
                    <span>
                      <strong>Divisi {{ $division['name'] }}</strong>
                      @if (! empty($division['coordinator']))<small>Koordinator: {{ $division['coordinator'] }}</small>@endif
                      @if (! empty($division['description']))<small>{{ $division['description'] }}</small>@endif
                    </span>
                  </li>
                @endforeach
              </ul>
            @endif
          </div>
        @endif
      </div>
    </section>

    <section class="section section-muted" id="galeri">
      <div class="container">
        <h2>Karya Anggota</h2>
        <p class="muted">Karya pilihan kurator. Klik foto untuk melihat detail dan pengaturan kamera.</p>

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
                  <span>{{ $artwork->user->name }}</span>
                </figcaption>
              </figure>
            @endforeach
          </div>
        @endif
      </div>
    </section>

    <section class="section" id="kegiatan">
      <div class="container narrow">
        <h2>Kegiatan Mendatang</h2>
        @forelse ($events as $event)
          <article class="event">
            <time datetime="{{ $event->start_at->toDateString() }}">
              {{ $event->start_at->translatedFormat('d M Y') }}
            </time>
            <div>
              <h3>{{ $event->title }}</h3>
              <p class="muted">
                {{ $event->type->getLabel() }} · {{ $event->location }} · {{ $event->start_at->format('H.i') }}@if ($event->end_at)–{{ $event->end_at->format('H.i') }}@endif
              </p>
            </div>
          </article>
        @empty
          <p class="empty">Belum ada kegiatan terjadwal.</p>
        @endforelse
      </div>
    </section>

    <section class="section" id="daftar">
      <div class="container narrow">
        <h2>Daftar Calon Anggota</h2>
        <p class="muted">
          Khusus mahasiswa Universitas Pancasila yang belum pernah mengikuti kaderisasi UKM Fotografi.
          Pengurus akan menghubungimu lewat WhatsApp untuk info selanjutnya.
        </p>

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
            <input type="text" id="name" name="name" value="{{ old('name') }}" autocomplete="name" required />
            @error('name')<small class="error">{{ $message }}</small>@enderror
          </div>
          <div class="field @error('npm') invalid @enderror">
            <label for="npm">NPM</label>
            <input type="text" id="npm" name="npm" value="{{ old('npm') }}" inputmode="numeric" pattern="\d{6,20}" required />
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
          <button type="submit" class="btn btn-block">Kirim Pendaftaran</button>
        </form>
      </div>
    </section>

    <section class="section section-muted" id="kontak">
      <div class="container narrow">
        <h2>Kontak</h2>
        <ul class="contact">
          @if ($profile->address)
            <li><span>Alamat</span>{{ $profile->address }}</li>
          @endif
          @if ($profile->email)
            <li><span>Email</span><a href="mailto:{{ $profile->email }}">{{ $profile->email }}</a></li>
          @endif
          @if ($profile->instagram)
            <li><span>Instagram</span><a href="https://instagram.com/{{ ltrim($profile->instagram, '@') }}" target="_blank" rel="noopener">{{ '@'.ltrim($profile->instagram, '@') }}</a></li>
          @endif
          @if ($profile->tiktok)
            <li><span>TikTok</span><a href="https://www.tiktok.com/@{{ ltrim($profile->tiktok, '@') }}" target="_blank" rel="noopener">{{ '@'.ltrim($profile->tiktok, '@') }}</a></li>
          @endif
          @if ($profile->phone)
            <li><span>Telepon</span>{{ $profile->phone }}</li>
          @endif
        </ul>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="container">
      &copy; {{ now()->year }} UKM Fotografi Universitas Pancasila
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
