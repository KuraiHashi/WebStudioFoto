<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Studio Fotografi — Liquif Picture</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&family=Jost:wght@300;400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('Assets/css/style.css') }}">
</head>

<body data-pg="index">
    <nav>
        <div class="wrap">
            <a class="logo" href="index.html"><i></i>liquif <small>Picture</small></a>
            <div class="menu"><a class="l" href="#profil">Profil</a><a class="l" href="#galeri">Galeri</a><a
                    class="l" href="#paket">Paket</a><a class="l" href="#testimoni">Testimoni</a><a
                    class="l" href="#kontak">Kontak</a>
                <a class="btn sm" href="cek.html">Cek Reservasi</a><a class="btn sm gold"
                    href="/reservasi">Reservasi</a>
            </div>
            <button class="bg" aria-label="Menu"><i></i><i></i><i></i></button>
        </div>
    </nav>

    {{-- KONTEN DETAIL --}}
    @yield('konten_detail')

    @sectionMissing('konten_detail')


    <header class="hero">
        <div class="wrap">
            <div>
                <div class="eyebrow" style="color:var(--gold)">Studio Fotografi</div>
                <h1 style="margin-top:22px">Cahaya yang mengalir, <em>kenangan</em> yang tinggal.</h1>
                <p>Kami mengabadikan momen Anda dengan sentuhan yang tenang dan personal — dari sesi pemotretan hingga
                    galeri privat yang hanya untuk Anda.</p>
                <div class="acts"><a class="btn gold" href="reservasi.html">Reservasi Sekarang</a><a class="btn"
                        href="#paket">Lihat Paket</a></div>
            </div>
            <div class="vis">
                <div class="blob ph"></div>
                <div class="dot ph c"></div>
            </div>
        </div>
    </header>

    <section class="profil" id="profil">
        <div class="wrap">
            <div class="rv">
                <div class="num">01</div>
                <div class="eyebrow" style="margin-top:18px">Profil Studio</div>
            </div>
            <div class="rv">
                <h2>Fotografi sebagai cara <em>merawat</em> ingatan.</h2>
                <p>Liquif Picture adalah studio fotografi yang percaya bahwa foto terbaik lahir dari suasana yang
                    nyaman. Setiap sesi kami arahkan dengan sabar, kami sunting dengan teliti, lalu kami serahkan
                    melalui galeri privat yang aman.</p>
                <div class="pillars">
                    <div><b>Natural</b><span>Cahaya &amp; ekspresi apa adanya</span></div>
                    <div><b>Personal</b><span>Konsep sesuai karakter Anda</span></div>
                    <div><b>Privat</b><span>Hasil foto hanya via kode Anda</span></div>
                </div>
            </div>
        </div>
    </section>




        {{-- GALERY --}}
        @include('template/layout/galery')

        {{-- PAKET FOTOGRAFI --}}
        @include('paketFotografi/paket_fotografi')

        {{-- ALUR RESERVASI --}}
        @include('template/layout/reservasi')

        {{-- TESTIMONI --}}
        @include('template/layout/testimoni')
    @endif
    
    {{-- FOOTER --}}
    @include('template/layout/footer')

    <script src="{{ asset('Assets/js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/gh/mcstudios/glightbox/dist/js/glightbox.min.js"></script>
</body>

</html>
