{{-- Kerangka halaman yang terpisah dari website (login, daftar calon anggota). --}}
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title') — UKM Fotografi Universitas Pancasila</title>
  @stack('meta')
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
    <div @class(['auth-card', 'auth-card-wide' => View::hasSection('wide')])>
      @yield('content')
    </div>
  </main>

  @stack('scripts')
</body>
</html>
