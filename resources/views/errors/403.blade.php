<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Akses Ditolak - Task Project</title>
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
            <div class="col-md-8 col-lg-5 text-center">
                <div class="card-dasher p-4 p-lg-5">
                    <div class="icon-shape bg-danger-subtle-ds mx-auto mb-3" style="width: 56px; height: 56px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                            <path d="M10 10l4 4m0 -4l-4 4" />
                        </svg>
                    </div>
                    <div class="small fw-bold text-danger mb-1">403</div>
                    <h1 class="h4 fw-bold mb-2">Akses Ditolak</h1>
                    <p class="text-secondary small mb-4">
                        Anda tidak memiliki izin untuk mengakses halaman atau project ini.
                    </p>
                    <a href="{{ route('dashboard') }}" class="btn btn-primary fw-semibold px-4">
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
