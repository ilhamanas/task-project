<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Task Project</title>
    <link rel="icon" type="image/png" href="{{ asset('images/brand/icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/dasher.css') }}" rel="stylesheet">
</head>
<body class="login-page d-flex align-items-center justify-content-center min-vh-100 p-3">
    @php
        $bgKey = $bgKey ?? 'work-1';
    @endphp

    <div class="login-backdrop" aria-hidden="true">
        <picture>
            <source srcset="{{ asset("images/login-bg/{$bgKey}.webp") }}" type="image/webp">
            <img
                src="{{ asset("images/login-bg/{$bgKey}.jpg") }}"
                alt=""
                width="1600"
                height="893"
                fetchpriority="high"
                class="login-backdrop-img">
        </picture>
        <div class="login-backdrop-scrim"></div>
    </div>

    <main class="login-card-wrap w-100" style="max-width: 440px;">
        <div class="card-dasher login-card p-4">
            <div class="text-center pb-3 mb-4 border-bottom">
                <div class="mb-2">
                    <img
                        src="{{ asset('images/brand/logo-rinjan.png') }}"
                        alt="Rinjan Media"
                        class="img-fluid"
                        style="max-height: 44px;">
                </div>
                <p class="text-secondary small mb-0">Aplikasi Task Project &middot; Masuk untuk memantau project dan progres fitur</p>
            </div>

            <h1 class="h5 fw-bold mb-3">Masuk ke Akun</h1>

            <form method="POST" action="{{ route('login.attempt') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="form-control @error('email') is-invalid @enderror"
                        placeholder="nama@perusahaan.com"
                        required
                        autofocus
                        autocomplete="email">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label small fw-semibold">Kata Sandi</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Masukkan kata sandi"
                        required
                        autocomplete="current-password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label small text-secondary" for="remember">
                        Ingat saya
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
                    Masuk
                </button>
            </form>
        </div>
    </main>
</body>
</html>
