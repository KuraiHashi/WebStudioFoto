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
</head>

<body data-pg="dashboard" data-adm>
    <div class="mbar"><a class="logo" href="dashboard.html"><i></i>liquif <small>Admin</small></a><button
            class="bg" aria-label="Menu"><i></i><i></i><i></i></button></div>
    <div class="sbk"></div>
    <div class="dsh">
        <aside class="sb">
            <a class="logo" href="dashboard.html"><i></i>liquif <small>Admin</small></a>
            <a class="m @yield('dashboard')" href="/dashboard"><b>01</b>Reservasi</a>
            <a class="m @yield('laporan')" href="/laporan"><b>02</b>Laporan</a>
            <a class="m @yield('paket')" href="paket-admin.html"><b>03</b>Paket Fotografi</a>
            <a class="m @yield('galery')" href="/galery"><b>04</b>Galeri Studio</a>
            <a class="m @yield('profil')" href="profil-admin.html"><b>05</b>Profil Studio</a>
            <a class="m out" href="login.html"><b>↩</b>Logout</a>
        </aside>

        {{-- KONTEN MAIN --}}
        @yield('konten')

    </div>
    <script src="{{ asset('Assets/js/app.js') }}"></script>
</body>

</html>
