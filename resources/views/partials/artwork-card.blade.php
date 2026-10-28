{{-- kartu foto karya; data-* dibaca lightbox untuk menampilkan detail & segitiga exposure --}}
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
