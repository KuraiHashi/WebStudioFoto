<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard Admin — Liquif Picture</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&family=Jost:wght@300;400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('Assets/css/style.css') }}">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
</head>

<body data-pg="dashboard" data-adm>
    <div class="mbar">
        <a class="logo" href="{{ route('admin.dashboard') }}" aria-label="Dashboard Liquif Admin">
            @if ($studio?->foto_studio)
                <img src="{{ asset('storage/' . $studio->foto_studio) }}" alt="" class="admin-studio-logo">
            @else
                <span class="admin-logo-placeholder" aria-label="Logo belum tersedia">Logo belum tersedia</span>
            @endif
            liquif <small>Admin</small>
        </a>
        <button class="bg" aria-label="Menu"><i></i><i></i><i></i></button>
    </div>
    <div class="sbk"></div>
    <div class="dsh">
        <aside class="sb">
            <a class="logo" href="{{ route('admin.dashboard') }}" aria-label="Dashboard Liquif Admin">
                @if ($studio?->foto_studio)
                    <img src="{{ asset('storage/' . $studio->foto_studio) }}" alt="" class="admin-studio-logo">
                @else
                    <span class="admin-logo-placeholder" aria-label="Logo belum tersedia">Logo belum tersedia</span>
                @endif
                liquif <small>Admin</small>
            </a>
            <a class="m @yield('dashboard')" href="/dashboard">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                    <rect x="3" y="3" width="7" height="7" rx="1" />
                    <rect x="14" y="3" width="7" height="7" rx="1" />
                    <rect x="3" y="14" width="7" height="7" rx="1" />
                    <rect x="14" y="14" width="7" height="7" rx="1" />
                </svg>
                <span>Dashboard</span>
            </a>
            <a class="m @yield('laporan')" href="/admin/laporan">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                    <path d="M4 19V5m0 14h17" />
                    <path d="m7 15 4-4 3 2 6-7" />
                </svg>
                <span>Laporan</span>
            </a>
            <a class="m @yield('fotografy')" href="/fotografi">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                    <path d="m12 3 9 5-9 5-9-5 9-5Z" />
                    <path d="m3 12 9 5 9-5M3 16l9 5 9-5M12 13v4" />
                </svg>
                <span>Paket Fotografi</span>
            </a>
            <a class="m @yield('galery')" href="/admin/galery">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                    <rect x="3" y="4" width="18" height="16" rx="2" />
                    <circle cx="8.5" cy="9" r="1.5" />
                    <path d="m21 15-5-5L5 20" />
                </svg>
                <span>Galeri Studio</span>
            </a>
            <a class="m @yield('profile')" href="/admin/profile">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                    <circle cx="12" cy="8" r="4" />
                    <path d="M5 21v-2a7 7 0 0 1 14 0v2" />
                </svg>
                <span>Profil Studio</span>
            </a>
            <a class="m @yield('rekening')" href="/rekening">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                    <rect x="3" y="5" width="18" height="14" rx="2" />
                    <path d="M3 10h18m-14 5h4" />
                </svg>
                <span>Rekening</span>
            </a>
            @php($adminUser = auth()->user())
            <div class="admin-profile-menu" data-admin-profile-menu>
                <button
                    class="admin-profile-toggle"
                    type="button"
                    data-admin-profile-toggle
                    aria-expanded="false"
                    aria-controls="admin-profile-actions"
                >
                    <span class="admin-profile-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($adminUser->name, 0, 1)) }}</span>
                    <span class="admin-profile-identity">
                        <strong>{{ $adminUser->name }}</strong>
                        <small>{{ $adminUser->email }}</small>
                    </span>
                    <svg class="admin-profile-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </button>
                <div class="admin-profile-actions" id="admin-profile-actions">
                    <a class="admin-profile-action" href="{{ route('admin.account.edit') }}">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle cx="12" cy="8" r="4" />
                            <path d="M5 21v-2a7 7 0 0 1 14 0v2" />
                        </svg>
                        <span>
                            <strong>Akun &amp; kata sandi</strong>
                            <small>Ubah email atau password</small>
                        </span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="admin-profile-logout">
                        @csrf
                        <button class="admin-profile-action" type="submit">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M10 17l5-5-5-5m5 5H3" />
                                <path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7" />
                            </svg>
                            <span><strong>Keluar</strong></span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- KONTEN MAIN --}}
        @yield('konten')

    </div>
    <script src="{{ asset('Assets/js/app.js') }}"></script>
    @stack('scripts')
    <script>
        const adminSuccessMessage = @json(session('success'));
        const adminValidationErrors = @json($errors->all());

        if (adminSuccessMessage) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: adminSuccessMessage,
                timer: 2500,
                showConfirmButton: false
            });
        } else if (adminValidationErrors.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Periksa kembali data Anda',
                text: adminValidationErrors.join('\n'),
                confirmButtonText: 'OK'
            });
        }
    </script>

</body>

</html>
