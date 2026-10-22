{{-- Pratinjau karya di modal kurasi, supaya kurator bisa menilai tanpa membuka halaman lain. --}}
<div style="display: grid; gap: 0.75rem;">
    <img src="{{ $artwork->imageUrl() }}" alt="{{ $artwork->title }}"
        style="width: 100%; max-height: 22rem; object-fit: contain; border-radius: 0.5rem; background: rgba(0,0,0,.05);" />

    <dl style="display: grid; gap: 0.5rem; font-size: 0.875rem;">
        <div>
            <dt style="font-weight: 600;">Kategori</dt>
            <dd>{{ $artwork->category?->name ?? '-' }}</dd>
        </div>
        @if ($artwork->caption)
            <div>
                <dt style="font-weight: 600;">Caption</dt>
                <dd>{{ $artwork->caption }}</dd>
            </div>
        @endif
        <div>
            <dt style="font-weight: 600;">Segitiga Exposure</dt>
            <dd>{{ $artwork->exposureSummary() ?? '-' }}</dd>
            @if ($artwork->exposure_notes)
                <dd style="opacity: .8;">{{ $artwork->exposure_notes }}</dd>
            @endif
        </div>
        @if ($artwork->instagram_url)
            <div>
                <dt style="font-weight: 600;">Instagram</dt>
                <dd><a href="{{ $artwork->instagram_url }}" target="_blank" rel="noopener" style="text-decoration: underline;">{{ $artwork->instagram_url }}</a></dd>
            </div>
        @endif
    </dl>
</div>
