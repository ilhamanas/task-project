<header class="navbar navbar-expand-lg navbar-glass px-3 px-lg-4">
    <div class="container-fluid px-0 flex-nowrap gap-2">
        <div class="d-flex align-items-center gap-2 min-w-0 flex-grow-1">
            {{-- Tombol drawer mobile --}}
            <button
                type="button"
                class="btn btn-light border d-inline-flex align-items-center justify-content-center d-lg-none p-2 flex-shrink-0"
                data-bs-toggle="offcanvas"
                data-bs-target="#mobileSidebar"
                aria-controls="mobileSidebar"
                aria-label="Buka menu navigasi">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 6l16 0" />
                    <path d="M4 12l16 0" />
                    <path d="M4 18l16 0" />
                </svg>
            </button>

            {{-- Tombol collapse sidebar desktop --}}
            <button
                type="button"
                id="sidebarCollapseBtn"
                class="btn btn-light border d-none d-lg-inline-flex align-items-center justify-content-center p-2 flex-shrink-0"
                aria-label="Ciutkan atau lebarkan sidebar">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" />
                    <path d="M9 4v16" />
                </svg>
            </button>

            <span class="fw-semibold text-dark ms-1 min-w-0">@yield('title', $title ?? 'Dashboard')</span>
        </div>

        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            @auth
                <div class="dropdown">
                    <button
                        class="btn btn-light border rounded-pill d-flex align-items-center gap-2 py-1 px-2 pe-3 dropdown-toggle"
                        type="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">
                        @if(auth()->user()->avatarUrl())
                            <img
                                src="{{ auth()->user()->avatarUrl() }}"
                                alt=""
                                class="rounded-circle border"
                                width="30"
                                height="30"
                                style="width: 30px; height: 30px; object-fit: cover; flex-shrink: 0;">
                        @else
                            <span
                                class="rounded-circle bg-primary-subtle-ds fw-bold d-inline-flex align-items-center justify-content-center"
                                style="width: 30px; height: 30px; font-size: 0.8rem; flex-shrink: 0;"
                                aria-hidden="true">
                                {{ auth()->user()->initials() }}
                            </span>
                        @endif
                        <span class="d-none d-sm-inline small fw-semibold">{{ auth()->user()->nama }}</span>
                        <span class="visually-hidden d-sm-none">Menu akun {{ auth()->user()->nama }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border">
                        <li class="px-3 py-2 border-bottom">
                            <div class="fw-semibold small">{{ auth()->user()->nama }}</div>
                            <div class="text-secondary" style="font-size: 0.75rem;">{{ auth()->user()->email }}</div>
                            <span class="badge bg-primary-subtle-ds mt-1 text-uppercase">{{ auth()->user()->role }}</span>
                        </li>
                        <li>
                            <a href="{{ route('account.settings') }}" class="dropdown-item py-2 d-flex align-items-center gap-2 {{ request()->routeIs('account.*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
                                    <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                </svg>
                                <span>Pengaturan Akun</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger py-2 d-flex align-items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" />
                                        <path d="M9 12h12l-3 -3" />
                                        <path d="M18 15l3 -3" />
                                    </svg>
                                    <span>Keluar</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                <span class="badge bg-primary-subtle-ds px-3 py-2">Belum Login</span>
            @endauth
        </div>
    </div>
</header>
