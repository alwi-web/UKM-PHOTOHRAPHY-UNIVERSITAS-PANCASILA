<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login — UKM Fotografi Universitas Pancasila</title>
  <meta name="robots" content="noindex" />
  <link rel="icon" href="{{ asset('images/photoup-logo.svg') }}" type="image/svg+xml" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
  <link rel="stylesheet" href="{{ asset('css/login.css') }}" />
</head>
<body class="auth">
  <div class="hero-grid-bg" aria-hidden="true"></div>

  <header class="auth-top">
    <a href="{{ route('home') }}" class="brand">
      <img src="{{ asset('images/photoup-logo.svg') }}" alt="" width="40" height="40" />
      <span>PHOTO <b>UP</b></span>
    </a>
    <a href="{{ route('home') }}" class="auth-back"><x-glyph name="arrow-left" :size="16" /> Kembali ke website</a>
  </header>

  <main class="auth-main">
    <div class="auth-card">
      {{-- panel kuning: siapa saja yang bisa masuk --}}
      <aside class="auth-side">
        <span class="eyebrow eyebrow-dark"><x-glyph name="lock" :size="14" /> Panel Anggota</span>
        <h1>Masuk ke panel PhotoUP</h1>
        <p>Satu pintu untuk pengurus, anggota, kurator, dan anggota UKM lain.</p>
        <ul class="auth-roles">
          <li><b>Admin UKM</b><span>Kelola semua data UKM</span></li>
          <li><b>Admin Inventaris</b><span>Barang &amp; peminjaman</span></li>
          <li><b>Member UKM</b><span>Unggah karya foto</span></li>
          <li><b>Kurator</b><span>Kurasi karya untuk website</span></li>
          <li><b>Anggota UKM lain</b><span>Pinjam peralatan</span></li>
        </ul>
      </aside>

      <form class="auth-form" method="POST" action="{{ route('login.store') }}" novalidate>
        @csrf
        <h2>Login</h2>
        <p class="auth-sub">Masukkan email dan password akunmu.</p>

        @if ($errors->any())
          <p class="notice notice-error" role="alert">{{ $errors->first() }}</p>
        @endif

        <div class="field @error('email') invalid @enderror">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="username" placeholder="nama@email.com" required autofocus />
        </div>

        <div class="field @error('password') invalid @enderror">
          <label for="password">Password</label>
          <div class="password-wrap">
            <input type="password" id="password" name="password" autocomplete="current-password" placeholder="••••••••" required />
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

        <p class="auth-note">
          Belum punya akun? Calon anggota bisa <a href="{{ route('home') }}#daftar">mendaftar di sini</a>.
          Lupa password? Hubungi Admin UKM.
        </p>
      </form>
    </div>
  </main>

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
</body>
</html>
