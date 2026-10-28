@extends('layouts.site')

@section('title', 'Galeri')

@section('content')
  <section class="page-head container">
    <span class="pill">Galeri</span>
    <h1>Karya Anggota</h1>
    <p>Karya pilihan kurator. Klik foto untuk melihat cerita dan pengaturan kameranya.</p>
    <form class="gallery-search" action="{{ route('gallery') }}" method="GET" role="search">
      <label for="gallerySearch" class="sr-only">Cari karya atau fotografer</label>
      <x-glyph name="search" :size="18" />
      <input type="search" id="gallerySearch" name="q" value="{{ $search }}" placeholder="Cari judul karya atau nama fotografer" />
      <button type="submit" class="btn btn-sm">Cari</button>
    </form>
  </section>

  <section class="container gallery-page">
    @if ($search !== '')
      <p class="search-result">
        {{ $artworks->count() }} karya untuk <strong>“{{ $search }}”</strong>
        <a href="{{ route('gallery') }}">Hapus pencarian</a>
      </p>
    @endif

    @if ($artworks->isEmpty())
      <p class="empty">{{ $search !== '' ? 'Tidak ada karya yang cocok dengan pencarian.' : 'Belum ada karya yang ditampilkan.' }}</p>
    @else
      @if ($categories->count() > 1)
        <div class="filters" id="filterBar">
          <button type="button" class="chip active" data-filter="semua">Semua</button>
          @foreach ($categories as $category)
            <button type="button" class="chip" data-filter="{{ $category->slug }}">{{ $category->name }}</button>
          @endforeach
        </div>
      @endif
      <div class="gallery" id="gallery">
        @foreach ($artworks as $artwork)
          @include('partials.artwork-card')
        @endforeach
      </div>
    @endif
  </section>
@endsection
