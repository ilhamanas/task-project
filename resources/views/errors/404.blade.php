<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Halaman Tidak Ditemukan - Task Project</title>
    <link rel="icon" type="image/png" href="{{ asset('images/brand/icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/dasher.css') }}" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 p-3">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 text-center">
                <div class="mb-4">
                    <img
                        src="{{ asset('images/svg/404.svg') }}"
                        alt="Ilustrasi halaman tidak ditemukan"
                        class="img-fluid"
                        style="max-height: 280px;">
                </div>

                <h1 class="h2 fw-bold mb-2">Halaman Tidak Ditemukan</h1>
                <p class="text-secondary mb-4">
                    Maaf, halaman atau data yang Anda cari tidak tersedia atau sudah dipindahkan.
                </p>

                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="{{ route('dashboard') }}" class="btn btn-primary fw-semibold px-4 py-2">
                        Kembali ke Dashboard
                    </a>
                    @if(url()->previous() !== url()->current())
                        <a href="{{ url()->previous() }}" class="btn btn-light border px-4 py-2">
                            Halaman Sebelumnya
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>
