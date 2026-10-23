@extends('layouts.standalone')

@section('title', 'Login')

@push('meta')
  <meta name="robots" content="noindex" />
@endpush

@section('content')
  <form class="auth-form" method="POST" action="{{ route('login.store') }}" novalidate>
    @csrf
    <h2>Login</h2>
    <p class="auth-sub">Masukkan email dan password akunmu.</p>

    @if ($errors->any())
      <p class="notice notice-error" role="alert">{{ $errors->first() }}</p>
    @endif

    <div class="field @error('email') invalid @enderror">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus />
    </div>

    <div class="field @error('password') invalid @enderror">
      <label for="password">Password</label>
      <div class="password-wrap">
        <input type="password" id="password" name="password" autocomplete="current-password" required />
        <button type="button" class="password-toggle" id="passwordToggle" aria-label="Tampilkan password" aria-pressed="false">
          <x-glyph name="eye" :size="18" class="show-icon" />
          <x-glyph name="eye-off" :size="18" class="hide-icon" />
        </button>
      </div>
    </div>

    <div class="field field-check">
      <label for="remember">
        <input type="checkbox" id="remember" name="remember" value="1" @checked(old('remember')) />
        Ingat saya
      </label>
    </div>

    <button type="submit" class="btn btn-block">Masuk <x-glyph name="arrow-right" :size="18" /></button>

    <p class="auth-note">Lupa password? Hubungi Admin UKM.</p>
  </form>
@endsection

@push('scripts')
<script>
  // tampilkan / sembunyikan password
  const toggle = document.getElementById("passwordToggle");
  const input = document.getElementById("password");
  toggle.addEventListener("click", () => {
    const show = input.type === "password";
    input.type = show ? "text" : "password";
    toggle.setAttribute("aria-pressed", String(show));
    toggle.setAttribute("aria-label", show ? "Sembunyikan password" : "Tampilkan password");
  });
  document.querySelector(".auth-form").addEventListener("submit", (e) => {
    const btn = e.target.querySelector("button[type=submit]");
    btn.disabled = true;
    btn.textContent = "Memeriksa...";
  });
</script>
@endpush
