<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $title ?? 'Dashboard') - Task Project</title>
    <link rel="icon" type="image/png" href="{{ asset('images/brand/icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/dasher.css') }}" rel="stylesheet">

    @livewireStyles
    @stack('styles')
</head>
<body>
    <div class="app-wrapper">
        {{-- Sidebar Desktop --}}
        <aside class="app-sidebar" aria-label="Navigasi utama">
            @include('layouts.partials.sidebar')
        </aside>

        {{-- Offcanvas Sidebar Mobile --}}
        <div class="offcanvas offcanvas-start app-sidebar-drawer d-lg-none" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
            <div class="offcanvas-body p-0 d-flex flex-column">
                @include('layouts.partials.sidebar', ['isMobileDrawer' => true])
            </div>
        </div>

        {{-- Konten Utama --}}
        <div class="app-main">
            @include('layouts.partials.header')

            <main class="container-fluid p-3 p-lg-4 flex-grow-1">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                @endif

                @yield('content')
                {{ $slot ?? '' }}
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('sidebarCollapseBtn');
            if (!btn) return;
            if (localStorage.getItem('sidebarCollapsed') === '1') {
                document.body.classList.add('sidebar-collapsed');
            }
            btn.addEventListener('click', function () {
                document.body.classList.toggle('sidebar-collapsed');
                localStorage.setItem(
                    'sidebarCollapsed',
                    document.body.classList.contains('sidebar-collapsed') ? '1' : '0'
                );
            });
        });
    </script>

    @livewireScripts
    @stack('scripts')
</body>
</html>
