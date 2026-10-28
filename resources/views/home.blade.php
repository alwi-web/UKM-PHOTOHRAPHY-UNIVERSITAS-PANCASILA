@extends('layouts.site')

@section('content')
  {{-- ================= SLIDER FOTO (nominasi foto mingguan) ================= --}}
  <section class="hero-slider" id="beranda" data-slider aria-roledescription="carousel" aria-label="Foto pilihan PhotoUP">
    <div class="slides">
      @forelse ($slides as $artwork)
        <figure @class(['slide', 'is-active' => $loop->first]) role="group" aria-roledescription="slide"
          aria-label="{{ $loop->iteration }} dari {{ $loop->count }}: {{ $artwork->title }}">
          <img src="{{ $artwork->imageUrl() }}" alt="{{ $artwork->title }} — {{ $artwork->user->name }}" @unless ($loop->first) loading="lazy" @endunless draggable="false" />
          <figcaption class="slide-credit">
            @if ($nomineeWeek)
              <span class="credit-badge"><x-glyph name="star" :size="12" /> Nominasi Foto Mingguan</span>
            @endif
            <span><strong>{{ $artwork->title }}</strong> — {{ $artwork->user->name }}</span>
          </figcaption>
        </figure>
      @empty
        <div class="slide slide-empty is-active" aria-hidden="true"></div>
      @endforelse
    </div>
    <div class="hero-shade" aria-hidden="true"></div>

    <div class="hero-content">
      <p class="hero-eyebrow">{{ $profile->name }}</p>
      <h1>Mengabadikan Cerita, <span>Menangkap Makna</span></h1>
      @if ($profile->tagline)
        <p class="hero-lead">{{ $profile->tagline }}</p>
      @endif
      <a href="{{ route('gallery') }}" class="btn btn-light">Jelajahi Galeri <x-glyph name="arrow-right" :size="18" /></a>
    </div>

    @if ($slides->count() > 1)
      <button type="button" class="slider-btn slider-prev" data-prev aria-label="Foto sebelumnya"><x-glyph name="arrow-left" :size="20" /></button>
      <button type="button" class="slider-btn slider-next" data-next aria-label="Foto berikutnya"><x-glyph name="arrow-right" :size="20" /></button>
      <div class="slider-dots">
        @foreach ($slides as $artwork)
          <button type="button" @class(['slider-dot', 'active' => $loop->first]) data-dot="{{ $loop->index }}" aria-label="Tampilkan foto {{ $loop->iteration }}"></button>
        @endforeach
      </div>
    @endif
  </section>

  {{-- di HP: menu ringkas berupa daftar tombol ke setiap halaman --}}
  <nav class="quick-menu container" aria-label="Menu cepat">
    <a href="{{ route('profile') }}"><span class="toggle-icon"><x-glyph name="users" /></span><span><strong>Profil</strong><small>Sejarah, visi misi, struktur BPH</small></span><x-glyph name="chevron-right" /></a>
    <a href="{{ route('gallery') }}"><span class="toggle-icon"><x-glyph name="image" /></span><span><strong>Galeri</strong><small>Karya anggota pilihan kurator</small></span><x-glyph name="chevron-right" /></a>
    <a href="#kegiatan"><span class="toggle-icon"><x-glyph name="calendar" /></span><span><strong>Kegiatan</strong><small>Agenda kegiatan mendatang</small></span><x-glyph name="chevron-right" /></a>
    <a href="#kontak"><span class="toggle-icon"><x-glyph name="mail" /></span><span><strong>Kontak</strong><small>Alamat, email, dan media sosial</small></span><x-glyph name="chevron-right" /></a>
  </nav>

  {{-- ================= PROFIL SINGKAT ================= --}}
  <section class="block profile-teaser container" id="profil">
    <div class="teaser-text reveal">
      <span class="pill">Profil</span>
      <h2>Profil {{ $profile->name }}</h2>
      @if ($profile->about)
        <p>{{ $profile->about }}</p>
      @endif
      <a href="{{ route('profile') }}" class="btn btn-outline">Selengkapnya <x-glyph name="arrow-right" :size="18" /></a>
    </div>
    {{-- ikon mengambang di depan bubble: pintasan ke tiap bagian halaman Profil --}}
    <div class="collage reveal">
      <span class="dot dot-1" aria-hidden="true"></span><span class="dot dot-2" aria-hidden="true"></span><span class="dot dot-3" aria-hidden="true"></span>
      <span class="dot dot-4" aria-hidden="true"></span><span class="dot dot-5" aria-hidden="true"></span><span class="dot dot-6" aria-hidden="true"></span><span class="dot dot-7" aria-hidden="true"></span>
      <svg class="spark" viewBox="0 0 40 40" width="40" height="40" aria-hidden="true"><path d="M8 14 3 6M18 10l1-9M27 15l7-6"/></svg>
      <span class="collage-logo" aria-hidden="true"><img src="{{ asset('images/photoup-logo.svg') }}" alt="" width="64" height="64" /></span>
      <nav class="float-icons" aria-label="Isi halaman Profil">
        <a href="{{ route('profile') }}#sejarah" class="float-card float-1"><span class="float-icon"><x-glyph name="users" /></span>Profil</a>
        <a href="{{ route('profile') }}#visi" class="float-card float-2"><span class="float-icon"><x-glyph name="eye" /></span>Visi</a>
        <a href="{{ route('profile') }}#misi" class="float-card float-3"><span class="float-icon"><x-glyph name="target" /></span>Misi</a>
        <a href="{{ route('profile') }}#struktur" class="float-card float-4"><span class="float-icon"><x-glyph name="sitemap" /></span>Struktural</a>
      </nav>
    </div>
  </section>

  {{-- ================= KARYA TERBARU ================= --}}
  <section class="block container" id="galeri">
    <div class="block-head reveal">
      <div>
        <span class="pill">Galeri</span>
        <h2>Karya Terbaru</h2>
      </div>
      <a href="{{ route('gallery') }}" class="link-arrow">Lihat Semua <x-glyph name="arrow-right" :size="16" /></a>
    </div>
    @if ($latest->isEmpty())
      <p class="empty">Belum ada karya yang ditampilkan.</p>
    @else
      <div class="latest-grid" id="gallery">
        @foreach ($latest as $artwork)
          @include('partials.artwork-card')
        @endforeach
      </div>
    @endif
  </section>

  {{-- ================= KEGIATAN ================= --}}
  <section class="block container" id="kegiatan">
    <div class="block-head reveal">
      <div>
        <span class="pill">Kegiatan</span>
        <h2>Kegiatan Mendatang</h2>
        <p>Hunting bareng, workshop, sampai pameran. Catat tanggalnya!</p>
      </div>
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
  </section>

  {{-- ================= KONTAK ================= --}}
  <section class="block container" id="kontak">
    <div class="block-head reveal">
      <div>
        <span class="pill">Kontak</span>
        <h2>Ngobrol bareng kami</h2>
        <p>Ada pertanyaan soal pendaftaran, kerja sama, atau liputan? Hubungi kami.</p>
      </div>
    </div>
    <ul class="contact">
      @if ($profile->address)
        <li class="reveal"><span class="card-icon"><x-glyph name="map-pin" /></span><span><small>Alamat</small>{{ $profile->address }}</span></li>
      @endif
      @if ($profile->email)
        <li class="reveal"><span class="card-icon"><x-glyph name="mail" /></span><span><small>Email</small><a href="mailto:{{ $profile->email }}">{!! str_replace('@', '@<wbr>', e($profile->email)) !!}</a></span></li>
      @endif
      @if ($profile->instagram)
        <li class="reveal"><span class="card-icon"><x-glyph name="instagram" /></span><span><small>Instagram</small><a href="https://instagram.com/{{ ltrim($profile->instagram, '@') }}" target="_blank" rel="noopener">{{ '@'.ltrim($profile->instagram, '@') }}</a></span></li>
      @endif
      @if ($profile->tiktok)
        <li class="reveal"><span class="card-icon"><x-glyph name="music" /></span><span><small>TikTok</small><a href="https://www.tiktok.com/{{ '@'.ltrim($profile->tiktok, '@') }}" target="_blank" rel="noopener">{{ '@'.ltrim($profile->tiktok, '@') }}</a></span></li>
      @endif
      @if ($profile->phone)
        <li class="reveal"><span class="card-icon"><x-glyph name="phone" /></span><span><small>Telepon</small>{{ $profile->phone }}</span></li>
      @endif
    </ul>
  </section>
@endsection
