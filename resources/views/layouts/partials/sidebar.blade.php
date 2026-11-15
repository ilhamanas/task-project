@php
    $isDashboard = request()->routeIs('dashboard');
    $isProjects = request()->routeIs('projects.*');
    $isReports = request()->routeIs('reports.*');
    $isUsers = request()->routeIs('users.*');
    $isMobileDrawer = $isMobileDrawer ?? false;
@endphp

<div class="d-flex flex-column h-100">
    <div class="brand-logo justify-content-between">
        <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none text-dark min-w-0">
            <img
                src="{{ asset('images/brand/icon.png') }}"
                alt="Task Project"
                width="32"
                height="32"
                style="object-fit: contain; flex-shrink: 0;">
            <span class="fw-bold fs-5 site-logo-text" @if($isMobileDrawer) id="mobileSidebarLabel" @endif>Task Project</span>
        </a>
        @if($isMobileDrawer)
            <button
                type="button"
                class="btn-close drawer-close-btn flex-shrink-0"
                data-bs-dismiss="offcanvas"
                aria-label="Tutup menu"></button>
        @endif
    </div>

    <div class="flex-grow-1 overflow-auto py-2 sidebar-nav-scroll">
        <nav aria-label="{{ $isMobileDrawer ? 'Menu navigasi mobile' : 'Menu navigasi utama' }}">
            <div class="nav-heading">Menu Utama</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ $isDashboard ? 'active' : '' }}" @if($isDashboard) aria-current="page" @endif>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4 4h6v8h-6z" />
                            <path d="M4 16h6v4h-6z" />
                            <path d="M14 12h6v8h-6z" />
                            <path d="M14 4h6v4h-6z" />
                        </svg>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('projects.index') }}" class="nav-link {{ $isProjects ? 'active' : '' }}" @if($isProjects) aria-current="page" @endif>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M15 3v4a1 1 0 0 0 1 1h4" />
                            <path d="M18 17h-7a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h4l5 5v7a2 2 0 0 1 -2 2z" />
                            <path d="M16 17v2a2 2 0 0 1 -2 2h-7a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h2" />
                        </svg>
                        <span class="sidebar-text">Daftar Project</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('reports.index') }}" class="nav-link {{ $isReports ? 'active' : '' }}" @if($isReports) aria-current="page" @endif>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" />
                            <path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" />
                            <path d="M9 17v-5" />
                            <path d="M12 17v-1" />
                            <path d="M15 17v-3" />
                        </svg>
                        <span class="sidebar-text">Laporan</span>
                    </a>
                </li>
            </ul>

            @if(auth()->check() && auth()->user()->role === 'admin')
                <div class="nav-heading">Administrasi</div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="{{ route('users.index') }}" class="nav-link {{ $isUsers ? 'active' : '' }}" @if($isUsers) aria-current="page" @endif>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                                <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />
                            </svg>
                            <span class="sidebar-text">Manajemen User</span>
                        </a>
                    </li>
                </ul>
            @endif
        </nav>
    </div>
</div>
