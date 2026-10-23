@extends('layouts.standalone')

@section('title', 'Daftar Calon Anggota')
@section('wide', true)

@section('content')
  @if (session('registered'))
    {{-- setelah berhasil: tampilkan konfirmasi, bukan formulir lagi --}}
    <div class="auth-form auth-done">
      <span class="done-icon"><x-glyph name="check" :size="28" /></span>
      <h2>Pendaftaran terkirim!</h2>
      <p class="auth-sub">{{ session('registered') }}</p>
      <a href="{{ route('home') }}" class="btn btn-block">Kembali ke website</a>
    </div>
  @else
    <form class="auth-form" id="registerForm" method="POST" action="{{ route('register') }}">
      @csrf
      <h2>Daftar Calon Anggota</h2>
      <p class="auth-sub">
        Khusus mahasiswa Universitas Pancasila yang belum pernah mengikuti kaderisasi UKM Fotografi.
        Pengurus akan menghubungimu lewat WhatsApp untuk info selanjutnya.
      </p>

      @if ($errors->any())
        <p class="notice notice-error" role="alert">Periksa kembali isian yang ditandai merah.</p>
      @endif

      <div class="field @error('name') invalid @enderror">
        <label for="name">Nama Lengkap</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}" autocomplete="name" required autofocus />
        @error('name')<small class="error">{{ $message }}</small>@enderror
      </div>
      <div class="field-row">
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
      </div>
      <div class="field @error('phone') invalid @enderror">
        <label for="phone">Nomor WhatsApp</label>
        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" required />
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
  @endif
@endsection

@push('scripts')
<script>
  document.getElementById("registerForm")?.addEventListener("submit", (e) => {
    const btn = e.target.querySelector("button[type=submit]");
    btn.disabled = true;
    btn.textContent = "Mengirim...";
  });
</script>
@endpush
